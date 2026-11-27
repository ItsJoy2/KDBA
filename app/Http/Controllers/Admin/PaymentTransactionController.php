<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyTransactionRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\VakalatnamaStickerOrder;
use App\Services\Finance\FinancialTransactionService;
use App\Services\VakalatnamaStickerService;
use App\Support\Amount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentTransactionController extends Controller
{
    public function __construct(
        private readonly FinancialTransactionService $financialService
    ) {}

    public function index(): View
    {
        $transactions = Transaction::query()
            ->where(function ($query) {
                $query->whereNotNull('invoice_id')
                    ->orWhereNotNull('order_id');
            })
            ->with([
                'user:id,full_name,membership_number',

                // Invoice
                'invoice:id,user_id,invoice_number,amount,invoice_type,cycle_key',
                'invoice.items:id,invoice_id,fee_key,title,description,unit_amount,quantity,amount',
                'invoice.user:id,full_name,membership_number',

                // Order
                'order.user:id,full_name',
                'order.purchasedBy:id,full_name',
                'order.items.product',
                'order.serials',
                'order.transactions.paymentGateway',
                'order.transactions.verifier',

                // Payment
                'paymentGateway:id,name,account_number',
                'verifier:id,full_name',
            ])
            ->latest()
            ->paginate(20);

        return view(
            'admin.transactions.payments.index',
            compact('transactions')
        );
    }

    public function verify(
        VerifyTransactionRequest $request,
        Transaction $transaction
    ): RedirectResponse {
        $data = $request->validated();

        $verifiedBy = $request->user()->id;

        DB::transaction(function () use (
            $request,
            $transaction,
            $data,
            $verifiedBy
        ): void {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransaction->status !== 'pending') {
                throw ValidationException::withMessages([
                    'transaction' => 'This payment has already been processed.',
                ]);
            }

            /*
             * Vakalatnama Sticker Payment Verification
             */
            if (
                VakalatnamaStickerOrder::where(
                    'transaction_id',
                    $lockedTransaction->id
                )->exists()
            ) {
                if ($data['status'] === 'approved') {
                    $lockedTransaction->update([
                        'status' => 'approved',
                        'verified_by' => $verifiedBy,
                        'verified_at' => now(),
                        'rejection_reason' => null,
                    ]);

                    app(VakalatnamaStickerService::class)
                        ->completePayment(
                            $request->user(),
                            $lockedTransaction->id
                        );
                } else {
                    $lockedTransaction->update([
                        'status' => 'rejected',
                        'verified_by' => $verifiedBy,
                        'verified_at' => now(),
                        'rejection_reason' => $data['rejection_reason'] ?? null,
                    ]);

                    app(VakalatnamaStickerService::class)
                        ->rejectPayment(
                            $request->user(),
                            $lockedTransaction->id,
                            $data['rejection_reason'] ?? null
                        );
                }

                return;
            }

            /*
             * Order Payment Verification
             */
            if ($lockedTransaction->order_id !== null) {
                $this->verifyOrderPayment(
                    $lockedTransaction,
                    $data,
                    $verifiedBy
                );

                return;
            }

            /*
             * Invoice Payment Verification
             */
            if (
                ! $lockedTransaction->invoice_id ||
                $lockedTransaction->invoice_item_id !== null
            ) {
                throw ValidationException::withMessages([
                    'transaction' => 'Invalid invoice payment transaction.',
                ]);
            }

            $invoice = Invoice::query()
                ->whereKey($lockedTransaction->invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->status !== 'processing') {
                throw ValidationException::withMessages([
                    'invoice' => 'The related invoice is not awaiting verification.',
                ]);
            }

            if (! Amount::equals(
                (string) $lockedTransaction->amount,
                (string) $invoice->amount
            )) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount does not match the invoice amount.',
                ]);
            }

            if ($data['status'] === 'approved') {
                $lockedTransaction->update([
                    'status' => 'approved',
                    'verified_by' => $verifiedBy,
                    'verified_at' => now(),
                    'rejection_reason' => null,
                ]);

                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                $this->financialService->categorizeInvoicePayment(
                    $lockedTransaction
                );

                if ($invoice->invoice_type === 'registration') {
                    $invoice->user()->update([
                        'approval_status' => 'approved',
                    ]);
                }
            } else {
                $lockedTransaction->update([
                    'status' => 'rejected',
                    'verified_by' => $verifiedBy,
                    'verified_at' => now(),
                    'rejection_reason' => $data['rejection_reason'] ?? null,
                ]);

                $invoice->update([
                    'status' => 'pending',
                    'paid_at' => null,
                ]);
            }
        }, 3);

        // Clear financial cache after successful verification.
        $this->financialService->clearFinancialCache();

        return back()->with(
            'success',
            $data['status'] === 'approved'
                ? 'Payment verified successfully.'
                : 'Payment rejected successfully.'
        );
    }

    private function verifyOrderPayment(
        Transaction $transaction,
        array $data,
        int $verifiedBy
    ): void {
        $order = Order::query()
            ->whereKey($transaction->order_id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            throw ValidationException::withMessages([
                'order' => 'This order has already been paid.',
            ]);
        }

        if ($order->order_status === 'cancelled') {
            throw ValidationException::withMessages([
                'order' => 'Payment cannot be verified for a cancelled order.',
            ]);
        }

        if (! Amount::equals(
            (string) $transaction->amount,
            (string) $order->total_amount
        )) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount does not match the order amount.',
            ]);
        }

        if ($data['status'] === 'approved') {
            $transaction->update([
                'status' => 'approved',
                'verified_by' => $verifiedBy,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            $order->update([
                'payment_status' => 'paid',
            ]);

            return;
        }

        $transaction->update([
            'status' => 'rejected',
            'verified_by' => $verifiedBy,
            'verified_at' => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        $order->update([
            'payment_status' => 'pending',
        ]);
    }
}