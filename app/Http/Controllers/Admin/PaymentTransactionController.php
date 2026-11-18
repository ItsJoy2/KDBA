<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyTransactionRequest;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Support\Amount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Services\Finance\FinancialTransactionService;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class PaymentTransactionController extends Controller
{

    public function __construct(
        private readonly FinancialTransactionService $financialService
    ) {}

public function index(): View
{
        $transactions = Transaction::query()
            ->whereNotNull('invoice_id')
            ->with([
                'user:id,full_name,membership_number',
                'invoice:id,invoice_number,amount,invoice_type,cycle_key',
                'paymentGateway:id,name,account_number',
                'verifier:id,full_name',
            ])
            ->latest()
            ->paginate(20);

        return view('admin.transactions.payments.index', compact('transactions'));
    }

    
    public function verify( VerifyTransactionRequest $request, Transaction $transaction): RedirectResponse {
        $data = $request->validated();

        $approvedUserId = null;

        DB::transaction(function () use (
            $transaction,
            $data,
            &$approvedUserId
        ) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransaction->status !== 'pending') {
                throw ValidationException::withMessages([
                    'transaction' => 'This transaction has already been processed.',
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

            if ($data['status'] === 'approved') {

                // Verify transaction amount.
                if (! Amount::equals(
                    $lockedTransaction->amount,
                    $invoice->amount
                )) {
                    throw ValidationException::withMessages([
                        'transaction' => 'Transaction amount does not match the invoice amount. Please investigate before approving.',
                    ]);
                }

                // Approve transaction.
                $lockedTransaction->update([
                    'status' => 'approved',
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                    'rejection_reason' => null,
                ]);

                // Assign income category to the approved payment.
                $this->financialService->categorizeInvoicePayment(
                    $lockedTransaction,
                    $invoice
                );

                // Mark invoice as paid.
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Create individual ledger entries for each invoice item.
                $this->membershipFeeService->createPaidInvoiceItemLedger(
                    $invoice,
                    auth()->id()
                );

                // Registration payment approves the membership application.
                if ($invoice->invoice_type === 'registration') {
                    $invoice->user?->update([
                        'approval_status' => 'approved',
                    ]);

                    $approvedUserId = $invoice->user_id;
                }

                return;
            }

            // Reject transaction.
            $lockedTransaction->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'rejection_reason' => $data['rejection_reason'] ?? null,
            ]);

            // A rejected payment allows the member to submit another payment.
            $invoice->update([
                'status' => 'pending',
                'paid_at' => null,
            ]);
        });

        // Clear user permission cache after registration approval.
        if ($approvedUserId) {
            Cache::forget(
                "rbac:user:{$approvedUserId}:permissions"
            );
        }

        return back()->with(
            'success',
            $data['status'] === 'approved'
                ? 'Payment verified and invoice marked as paid.'
                : 'Payment rejected successfully.'
        );
    }

}