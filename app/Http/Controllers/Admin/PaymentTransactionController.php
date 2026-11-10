<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyTransactionRequest;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentTransactionController extends Controller
{
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

    public function verify( VerifyTransactionRequest $request, Transaction $transaction ): RedirectResponse {
        $data = $request->validated();

        $approvedUserId = null;

        DB::transaction(function () use ($transaction, $data, &$approvedUserId) {
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
                if (! \App\Support\Amount::equals(
                    $lockedTransaction->amount,
                    $invoice->amount
                )) {
                    throw ValidationException::withMessages([
                        'transaction' => 'Transaction amount does not match the invoice amount. Please investigate before approving.',
                    ]);
                }

                $lockedTransaction->update([
                    'status' => 'approved',
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                    'rejection_reason' => null,
                ]);

                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Registration payment approves the membership application.
                // Do not change member_status: it identifies the membership category.
                if ($invoice->invoice_type === 'registration') {
                    $invoice->user?->update([
                        'approval_status' => 'approved',
                    ]);

                    $approvedUserId = $invoice->user_id;
                }

                return;
            }

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

        if ($approvedUserId) {
            \Illuminate\Support\Facades\Cache::forget(
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