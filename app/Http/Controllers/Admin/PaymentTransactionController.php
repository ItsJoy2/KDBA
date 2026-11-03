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
            ->with([
                'user:id,full_name,membership_number',
                'invoice:id,invoice_number,amount',
                'paymentGateway:id,name,account_number',
                'verifier:id,full_name',
            ])
            ->latest()
            ->paginate(20);

        return view('admin.payment-transactions.index', compact('transactions'));
    }

    public function verify(
        VerifyTransactionRequest $request,
        Transaction $transaction
    ): RedirectResponse {
        $data = $request->validated();

        DB::transaction(function () use ($transaction, $data) {
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

                return;
            }

            $lockedTransaction->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'rejection_reason' => $data['rejection_reason'] ?? null,
            ]);

            // Allow the member to submit payment again.
            $invoice->update([
                'status' => 'pending',
                'paid_at' => null,
            ]);
        });

        return back()->with(
            'success',
            $data['status'] === 'approved'
                ? 'Payment verified and invoice marked as paid.'
                : 'Payment rejected successfully.'
        );
    }
}