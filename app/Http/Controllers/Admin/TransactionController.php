<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStoreTransactionRequest;
use App\Http\Requests\StoreManualTransactionRequest;
use App\Http\Requests\SubmitOrderPaymentRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Services\Finance\FinancialTransactionService;
use App\Support\Amount;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class TransactionController extends Controller
{
    
    // public function store(AdminStoreTransactionRequest $request): RedirectResponse
    // {
    //     $validated = $request->validated();

    //     $receiptPath = $request->hasFile('receipt')
    //         ? $request->file('receipt')->store('payment-receipts', 'local')
    //         : null;

    //     try {
    //         DB::transaction(function () use (
    //             $validated,
    //             $receiptPath,
    //             $request
    //         ): void {
    //             $invoice = Invoice::query()
    //                 ->whereKey($validated['invoice_id'])
    //                 ->lockForUpdate()
    //                 ->firstOrFail();

    //             if (! in_array($invoice->status, ['pending', 'overdue'], true)) {
    //                 throw ValidationException::withMessages([
    //                     'invoice_id' => 'This invoice is not available for payment.',
    //                 ]);
    //             }

    //             $existingPayment = Transaction::query()
    //                 ->where('invoice_id', $invoice->id)
    //                 ->whereNotNull('invoice_item_id')
    //                 ->whereIn('status', ['pending', 'approved'])
    //                 ->exists();

    //             if ($existingPayment) {
    //                 throw ValidationException::withMessages([
    //                     'invoice_id' => 'A payment has already been submitted for this invoice.',
    //                 ]);
    //             }

    //             if (! Amount::equals($validated['amount'], $invoice->amount)) {
    //                 throw ValidationException::withMessages([
    //                     'amount' => 'Payment amount must match the invoice amount.',
    //                 ]);
    //             }

    //             $gateway = PaymentGateway::query()
    //                 ->whereKey($validated['payment_gateway_id'])
    //                 ->where('is_active', true)
    //                 ->first();

    //             if (! $gateway) {
    //                 throw ValidationException::withMessages([
    //                     'payment_gateway_id' => 'The selected payment gateway is unavailable.',
    //                 ]);
    //             }

    //             $autoApprove = $request->boolean('auto_approve');

    //             if (
    //                 $autoApprove
    //                 && ! $request->user()->hasPermission('transactions.verify')
    //             ) {
    //                 abort(403);
    //             }

    //             app(FinancialTransactionService::class)
    //                 ->createInvoicePaymentTransactions(
    //                     invoice: $invoice,
    //                     paymentReference: $validated['trx_id'],
    //                     gatewayId: $gateway->id,
    //                     senderMobile: $validated['sender_mobile'],
    //                     status: $autoApprove ? 'approved' : 'pending',
    //                     recordedBy: $request->user()->id,
    //                     receiptPath: $receiptPath,
    //                 );

    //             if ($autoApprove) {
    //                 $this->approveInitialMembershipIfRequired($invoice);

    //                 Cache::forget(
    //                     "rbac:user:{$invoice->user_id}:permissions"
    //                 );
    //             }
    //         }, 3);
    //     } catch (UniqueConstraintViolationException $exception) {
    //         if ($receiptPath) {
    //             Storage::disk('local')->delete($receiptPath);
    //         }

    //         throw ValidationException::withMessages([
    //             'trx_id' => 'This transaction ID or one of its item references has already been used.',
    //         ]);
    //     } catch (Throwable $exception) {
    //         if ($receiptPath) {
    //             Storage::disk('local')->delete($receiptPath);
    //         }

    //         throw $exception;
    //     }

    //     return back()->with(
    //         'success',
    //         'Invoice payment recorded successfully.'
    //     );
    // }


    public function store(AdminStoreTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (! empty($validated['order_id'])) {
            return $this->storeOrderPayment($request, $validated);
        }

        $paymentStatus = $validated['status'];
        $autoApprove = $paymentStatus === 'approved';

        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store('payment-receipts', 'local')
            : null;

        try {
            DB::transaction(function () use (
                $validated,
                $receiptPath,
                $request,
                $autoApprove,
                $paymentStatus
            ): void {

                /*
                |--------------------------------------------------------------------------
                | Lock Invoice
                |--------------------------------------------------------------------------
                */

                $invoice = Invoice::query()
                    ->whereKey($validated['invoice_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! in_array($invoice->status, ['pending', 'overdue'], true)) {
                    throw ValidationException::withMessages([
                        'invoice_id' => 'This invoice is not available for payment.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Prevent Duplicate Payment
                |--------------------------------------------------------------------------
                */

                $existingPayment = Transaction::query()
                    ->where('invoice_id', $invoice->id)
                    ->whereNull('invoice_item_id')
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($existingPayment) {
                    throw ValidationException::withMessages([
                        'invoice_id' => 'A payment has already been submitted for this invoice.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Amount
                |--------------------------------------------------------------------------
                */

                if (! Amount::equals(
                    (string) $validated['amount'],
                    (string) $invoice->amount
                )) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount must match the invoice amount.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Payment Gateway
                |--------------------------------------------------------------------------
                */

                $gateway = PaymentGateway::query()
                    ->whereKey($validated['payment_gateway_id'])
                    ->where('is_active', true)
                    ->first();

                if (! $gateway) {
                    throw ValidationException::withMessages([
                        'payment_gateway_id' => 'The selected payment gateway is unavailable.',
                    ]);
                }

                $isCash = $gateway->type === 'cash';

                /*
                |--------------------------------------------------------------------------
                | Verify Approval Permission
                |--------------------------------------------------------------------------
                */

                if (
                    $autoApprove &&
                    ! $request->user()->hasPermission('transactions.verify')
                ) {
                    abort(403);
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Invoice Income Category
                |--------------------------------------------------------------------------
                */

                $invoiceCategory = TransactionCategory::query()
                    ->where('slug', 'invoice')
                    ->where('type', 'income')
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (! $invoiceCategory) {
                    throw new \RuntimeException(
                        'Active income transaction category [invoice] was not found.'
                    );
                }


                if ($isCash) {
                    $trxId = 'CASH-'
                        . strtoupper(Str::random(8));

                    $senderMobile = 'N/A';
                } else {
                    $trxId = $validated['trx_id'];
                    $senderMobile = $validated['sender_mobile'];
                }


                $userId = $request->user()->id;

                Transaction::create([
                    'invoice_id'         => $invoice->id,
                    'invoice_item_id'    => null,
                    'user_id'            => $invoice->user_id,
                    'category_id'        => $invoiceCategory->id,
                    'payment_gateway_id' => $gateway->id,

                    'trx_id'             => $trxId,
                    'sender_mobile'      => $senderMobile,

                    'amount'             => $invoice->amount,
                    'status'             => $paymentStatus,
                    'source'             => 'admin',
                    'recorded_by'        => $userId,
                    'verified_by'        => $autoApprove ? $userId : null,
                    'verified_at'        => $autoApprove ? now() : null,
                    'rejection_reason'   => null,
                    'receipt_path'       => $receiptPath,
                    'submitted_at'       => now(),
                ]);


                $invoice->update([
                    'status' => $autoApprove ? 'paid' : 'processing',
                    'paid_at' => $autoApprove ? now() : null,
                ]);


                if ($autoApprove) {
                    $this->approveInitialMembershipIfRequired($invoice);

                    Cache::forget(
                        "rbac:user:{$invoice->user_id}:permissions"
                    );
                }
            }, 3);

            app(FinancialTransactionService::class)
                ->clearFinancialCache();

        } catch (UniqueConstraintViolationException $exception) {

            if ($receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }

            throw ValidationException::withMessages([
                'trx_id' => 'This transaction ID has already been used.',
            ]);

        } catch (Throwable $exception) {

            if ($receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }

            throw $exception;
        }

        return back()->with(
            'success',
            $autoApprove
                ? 'Invoice payment recorded and approved successfully.'
                : 'Invoice payment submitted successfully and is awaiting verification.'
        );
    }


    /**
     * Approve account registration after initial membership payment.
     */
    private function approveInitialMembershipIfRequired(
        Invoice $invoice
    ): void {
        if ($invoice->invoice_type !== 'registration') {
            return;
        }

        $user = $invoice->user;

        if (! $user) {
            throw ValidationException::withMessages([
                'invoice_id' => 'The invoice owner could not be found.',
            ]);
        }

        $user->update([
            'approval_status' => 'approved',
        ]);
    }


    public function storeManual(StoreManualTransactionRequest $request): RedirectResponse {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $category = TransactionCategory::query()
                ->whereKey($validated['category_id'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $userId = auth()->id();

            Transaction::create([
                'user_id' => $userId,
                'invoice_id' => null,
                'payment_gateway_id' => $validated['payment_gateway_id'] ?? null,

                // Internal unique reference for manual accounting entries.
                'trx_id' => 'KDBA-' . Str::upper(Str::random(8)),

                'category_id' => $category->id,
                'sender_mobile' => null,
                'amount' => $validated['amount'],
                // 'description' => $validated['description'],

                // Manual financial entries are recorded as approved.
                'status' => 'approved',
                'source' => 'admin',
                'recorded_by' => $userId,
                'verified_by' => $userId,
                'verified_at' => now(),
                'submitted_at' => now(),
            ]);
        });

        app(FinancialTransactionService::class)
            ->clearFinancialCache();

        return back()->with(
            'success',
            'Financial transaction recorded successfully.'
        );
    }
    public function createManual(): View
    {
        $categories = TransactionCategory::query()
            ->active()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $paymentGateways = PaymentGateway::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.transactions.manual-create', compact(
            'categories',
            'paymentGateways'
        ));
    }


    private function storeOrderPayment(
        AdminStoreTransactionRequest $request,
        array $validated
    ): RedirectResponse {
        $user = $request->user();

        // Admin order payments must be approved immediately.
        if ($validated['status'] !== 'approved') {
            throw ValidationException::withMessages([
                'status' => 'Admin order payments must be approved.',
            ]);
        }

        abort_unless(
            $user->hasPermission('transactions.verify'),
            403
        );

        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store('payment-receipts', 'local')
            : null;

        try {
            DB::transaction(function () use (
                $validated,
                $receiptPath,
                $user
            ): void {

                $order = Order::query()
                    ->whereKey($validated['order_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($order->payment_status === 'paid') {
                    throw ValidationException::withMessages([
                        'order_id' => 'This order has already been paid.',
                    ]);
                }

                if ($order->order_status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'order_id' => 'Cancelled orders cannot be paid.',
                    ]);
                }

                $existingPayment = Transaction::query()
                    ->where('order_id', $order->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($existingPayment) {
                    throw ValidationException::withMessages([
                        'order_id' => 'A payment has already been submitted for this order.',
                    ]);
                }

                if (! Amount::equals(
                    (string) $validated['amount'],
                    (string) $order->total_amount
                )) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount must match the order amount.',
                    ]);
                }


                $gateway = PaymentGateway::query()
                    ->whereKey($validated['payment_gateway_id'])
                    ->where('is_active', true)
                    ->first();

                if (! $gateway) {
                    throw ValidationException::withMessages([
                        'payment_gateway_id' => 'The selected payment gateway is unavailable.',
                    ]);
                }

                $category = TransactionCategory::query()
                    ->where('slug', 'product-sales')
                    ->where('type', 'income')
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (! $category) {
                    throw new \RuntimeException(
                        'Active income transaction category [product-sales] was not found.'
                    );
                }

                $isCash = $gateway->type === 'cash';

                $trxId = $isCash
                    ? 'CASH-' . strtoupper(Str::random(8))
                    : $validated['trx_id'];

                $senderMobile = $isCash
                    ? 'N/A'
                    : $validated['sender_mobile'];



                $now = now();

                Transaction::create([
                    'order_id'          => $order->id,
                    'invoice_id'        => null,
                    'invoice_item_id'   => null,

                    'user_id'           => $order->user_id,
                    'category_id'       => $category->id,
                    'payment_gateway_id'=> $gateway->id,

                    'trx_id'            => $trxId,
                    'sender_mobile'     => $senderMobile,

                    'amount'            => $order->total_amount,
                    'status'            => 'approved',
                    'source'            => 'admin',

                    'recorded_by'       => $user->id,
                    'verified_by'       => $user->id,
                    'verified_at'       => $now,

                    'rejection_reason'  => null,
                    'receipt_path'      => $receiptPath,
                    'submitted_at'      => $now,
                ]);


                $order->update([
                    'payment_status' => 'paid',
                ]);
            }, 3);

            app(FinancialTransactionService::class)
                ->clearFinancialCache();

        } catch (UniqueConstraintViolationException $exception) {

            if ($receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }

            throw ValidationException::withMessages([
                'trx_id' => 'This transaction ID has already been used.',
            ]);

        } catch (Throwable $exception) {

            if ($receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }

            throw $exception;
        }

        return back()->with(
            'success',
            'Order payment recorded and approved successfully.'
        );
    }


    public function submitPayment(
        SubmitOrderPaymentRequest $request,
        Order $order
    ): RedirectResponse {
        $validated = $request->validated();

        $receiptPath = null;

        try {
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')
                    ->store('transactions/receipts', 'public');
            }

            DB::transaction(function () use (
                $validated,
                $order,
                $request,
                &$receiptPath
            ): void {
                $lockedOrder = Order::query()
                    ->whereKey($order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $lockedOrder->user_id !== (int) auth()->id()) {
                    abort(403);
                }

                if ($lockedOrder->payment_status === 'paid') {
                    throw ValidationException::withMessages([
                        'order' => 'This order has already been paid.',
                    ]);
                }

                if ($lockedOrder->order_status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'order' => 'Payment is not allowed for a cancelled order.',
                    ]);
                }

                $existingPayment = Transaction::query()
                    ->where('order_id', $lockedOrder->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($existingPayment) {
                    throw ValidationException::withMessages([
                        'payment_gateway_id' => 'A payment is already submitted for this order.',
                    ]);
                }

                $gateway = PaymentGateway::query()
                    ->whereKey($validated['payment_gateway_id'])
                    ->where('is_active', true)
                    ->firstOrFail();

                $category = TransactionCategory::query()
                    ->where('slug', 'product-sales')
                    ->where('type', 'income')
                    ->where('is_active', true)
                    ->first();

                if (! $category) {
                    throw ValidationException::withMessages([
                        'payment_gateway_id' => 'Product Sales income category is not configured.',
                    ]);
                }

                $isCash = strtolower($gateway->type ?? '') === 'cash';

                Transaction::create([
                    'order_id' => $lockedOrder->id,
                    'invoice_id' => null,
                    'invoice_item_id' => null,

                    'user_id' => $lockedOrder->user_id,
                    'category_id' => $category->id,
                    'payment_gateway_id' => $gateway->id,

                    'trx_id' => $isCash
                        ? 'CASH-' . Str::upper(Str::random(8))
                        : $validated['trx_id'],

                    'sender_mobile' => $isCash
                        ? 'N/A'
                        : $validated['sender_mobile'],

                    'amount' => $lockedOrder->total_amount,
                    'status' => 'pending',
                    'source' => 'member',

                    'recorded_by' => auth()->id(),
                    'verified_by' => null,
                    'verified_at' => null,

                    'rejection_reason' => null,
                    'receipt_path' => $receiptPath,
                    'submitted_at' => now(),
                ]);

                $lockedOrder->update([
                    'payment_status' => 'pending',
                ]);
            }, 3);

            app(FinancialTransactionService::class)
                ->clearFinancialCache();

            return redirect()
                ->route('orders.index')
                ->with('success', 'Order payment submitted successfully. Awaiting admin verification.');

        } catch (Throwable $exception) {
            if ($receiptPath) {
                Storage::disk('public')->delete($receiptPath);
            }

            throw $exception;
        }
    }


}
