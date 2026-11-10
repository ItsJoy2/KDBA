<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitPaymentRequest;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::query()
            ->where('user_id', auth()->id())
            ->with([
                'transactions' => fn ($query) => $query->latest(),
            ])
            ->latest()
            ->paginate(15);

        $paymentGateways = PaymentGateway::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('member.invoices.index', compact(
            'invoices',
            'paymentGateways'
        ));
    }

    public function pay(
        SubmitPaymentRequest $request,
        Invoice $invoice
    ): RedirectResponse {
        $validated = $request->validated();

        // Store the receipt on the private local disk.
        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store('payment-receipts', 'local')
            : null;

        try {
            DB::transaction(function () use (
                $validated,
                $invoice,
                $receiptPath
            ) {
                $lockedInvoice = Invoice::query()
                    ->whereKey($invoice->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Always verify ownership on the locked database record.
                if ((int) $lockedInvoice->user_id !== (int) auth()->id()) {
                    abort(403);
                }

                if (! in_array($lockedInvoice->status, ['pending', 'overdue'], true)) {
                    throw ValidationException::withMessages([
                        'invoice' => 'This invoice is not available for payment.',
                    ]);
                }

                $existingPayment = Transaction::query()
                    ->where('invoice_id', $lockedInvoice->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($existingPayment) {
                    throw ValidationException::withMessages([
                        'invoice' => 'A payment has already been submitted for this invoice.',
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

                Transaction::create([
                    'invoice_id' => $lockedInvoice->id,
                    'user_id' => auth()->id(),
                    'payment_gateway_id' => $gateway->id,
                    'trx_id' => $validated['trx_id'],
                    'sender_mobile' => $validated['sender_mobile'],
                    'amount' => $lockedInvoice->amount,
                    'status' => 'pending',
                    'source' => 'member',
                    'recorded_by' => null,
                    'receipt_path' => $receiptPath,
                    'submitted_at' => now(),
                ]);

                $lockedInvoice->update([
                    'status' => 'processing',
                ]);
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
            ->route('member.invoices.index')
            ->with('success', 'Payment submitted successfully. Awaiting admin verification.');
    }
}
