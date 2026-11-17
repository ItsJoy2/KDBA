<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStoreTransactionRequest;
use App\Http\Requests\StoreManualTransactionRequest;
use App\Models\Invoice;
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
    public function store(
        AdminStoreTransactionRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store('payment-receipts', 'local')
            : null;

        try {
            DB::transaction(function () use (
                $validated,
                $receiptPath,
                $request
            ) {
                $invoice = Invoice::query()
                    ->whereKey($validated['invoice_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! in_array($invoice->status, ['pending', 'overdue'], true)) {
                    throw ValidationException::withMessages([
                        'invoice_id' => 'This invoice is not available for payment.',
                    ]);
                }

                // Prevent duplicate payments against the same invoice.
                $existingPayment = Transaction::query()
                    ->where('invoice_id', $invoice->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($existingPayment) {
                    throw ValidationException::withMessages([
                        'invoice_id' => 'A payment has already been submitted for this invoice.',
                    ]);
                }

                // Compare decimal amounts without floating-point arithmetic.
                if (! Amount::equals($validated['amount'], $invoice->amount)) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount must match the invoice amount.',
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

                $autoApprove = $request->boolean('auto_approve');

                // Recheck permission at the action boundary.
                if (
                    $autoApprove
                    && ! $request->user()->hasPermission('transactions.verify')
                ) {
                    abort(403);
                }

                $transaction = Transaction::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                    'payment_gateway_id' => $gateway->id,
                    'trx_id' => $validated['trx_id'],
                    'sender_mobile' => $validated['sender_mobile'],
                    'amount' => $invoice->amount,
                    'status' => $autoApprove ? 'approved' : 'pending',
                    'source' => 'admin',
                    'recorded_by' => $request->user()->id,
                    'verified_by' => $autoApprove
                        ? $request->user()->id
                        : null,
                    'verified_at' => $autoApprove ? now() : null,
                    'receipt_path' => $receiptPath,
                    'submitted_at' => now(),
                ]);

                $invoice->update([
                    'status' => $autoApprove ? 'paid' : 'processing',
                    'paid_at' => $autoApprove ? now() : null,
                ]);

                if ($autoApprove) {
                    $this->approveInitialMembershipIfRequired($invoice);

                    // Clear cached permissions after approval.
                    Cache::forget(
                        "rbac:user:{$invoice->user_id}:permissions"
                    );
                }
            }, 3);
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

        return redirect()
            ->back()
            ->with('success', 'Payment recorded successfully.');
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
                'trx_id' => 'MAN-' . Str::upper((string) Str::uuid()),

                'category_id' => $category->id,
                'sender_mobile' => null,
                'amount' => $validated['amount'],
                'description' => $validated['description'],

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

}
