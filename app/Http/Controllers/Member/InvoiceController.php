<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitPaymentRequest;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::query()
            ->where('user_id', auth()->id())
            ->with([
                'transactions.paymentGateway',
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

        $receiptPath = null;

        try {
            // Store receipt on private local disk.
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')
                    ->store('payment-receipts', 'local');
            }

            DB::transaction(function () use (
                $validated,
                $invoice,
                $receiptPath
            ) {
                $lockedInvoice = Invoice::query()
                    ->whereKey($invoice->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Verify invoice ownership.
                if ((int) $lockedInvoice->user_id !== (int) auth()->id()) {
                    abort(403);
                }

                // Only pending or overdue invoices can be paid.
                if (! in_array(
                    $lockedInvoice->status,
                    ['pending', 'overdue'],
                    true
                )) {
                    throw ValidationException::withMessages([
                        'invoice' => 'This invoice is not available for payment.',
                    ]);
                }

                // Prevent duplicate main invoice payments.
                $existingPayment = Transaction::query()
                    ->where('invoice_id', $lockedInvoice->id)
                    ->whereNull('invoice_item_id')
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($existingPayment) {
                    throw ValidationException::withMessages([
                        'invoice' => 'A payment has already been submitted for this invoice.',
                    ]);
                }

                // Validate active payment gateway.
                $gateway = PaymentGateway::query()
                    ->whereKey($validated['payment_gateway_id'])
                    ->where('is_active', true)
                    ->first();

                if (! $gateway) {
                    throw ValidationException::withMessages([
                        'payment_gateway_id' => 'The selected payment gateway is unavailable.',
                    ]);
                }

                // Cash gateway handling.
                $isCash = $gateway->type === 'cash';

                $trxId = $isCash
                    ? 'CASH-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(8))
                    : $validated['trx_id'];

                $senderMobile = $isCash
                    ? 'N/A'
                    : $validated['sender_mobile'];

                // Create exactly one main invoice transaction.
                Transaction::create([
                    'invoice_id' => $lockedInvoice->id,
                    'invoice_item_id' => null,
                    'user_id' => $lockedInvoice->user_id,
                    'payment_gateway_id' => $gateway->id,
                    'trx_id' => $trxId,
                    'sender_mobile' => $senderMobile,
                    'amount' => $lockedInvoice->amount,
                    'status' => 'pending',
                    'source' => 'member',
                    'recorded_by' => null,
                    'receipt_path' => $receiptPath,
                    'submitted_at' => now(),
                ]);

                // Mark invoice as processing until admin verification.
                $lockedInvoice->update([
                    'status' => 'processing',
                ]);
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            if ($receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }

            throw ValidationException::withMessages([
                'trx_id' => 'This transaction ID has already been used. Please try again.',
            ]);
        } catch (Throwable $exception) {
            if ($receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('member.invoices.index')
            ->with(
                'success',
                'Payment submitted successfully. Awaiting admin verification.'
            );
    }
}
