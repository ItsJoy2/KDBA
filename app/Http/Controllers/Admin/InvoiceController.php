<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\SubmitPaymentRequest;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\Membership\MembershipFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    /**
     * Admin invoice listing.
     */
    public function index(): View
    {
        $invoices = Invoice::query()
            ->with('user:id,full_name,membership_number,email')
            ->latest()
            ->paginate(20);

        return view('admin.invoices.index', compact('invoices'));
    }

    /**
     * Admin creates an invoice.
     *
     * The amount is calculated on the server. The submitted amount and
     * member_status are not trusted.
     */
    public function store(
        StoreInvoiceRequest $request,
        MembershipFeeService $feeService
    ): RedirectResponse {
        $data = $request->validated();

        $user = User::query()
            ->whereIn('member_status', ['member', 'life_member'])
            ->findOrFail($data['user_id']);

        $amount = $data['invoice_type'] === 'registration'
            ? $feeService->registrationFee()
            : $feeService->yearlyFee($user->member_status);

        if ($amount <= 0) {
            return back()->with('error', 'The configured fee must be greater than zero.');
        }

        Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => 'KDBA-' . now()->format('Ymd') . '-' .
                strtoupper(bin2hex(random_bytes(4))),
            'invoice_type' => $data['invoice_type'],
            'cycle_key' => $data['cycle_key'],
            'member_status' => $user->member_status,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'amount' => $amount,
            'status' => 'pending',
            'due_date' => $data['due_date'] ?? now()->addDays(30)->toDateString(),
        ]);

        return back()->with('success', 'Invoice created successfully.');
    }

    /**
     * Member views their own invoices.
     */
    public function myInvoices(): View
    {
        $invoices = auth()->user()
            ->invoices()
            ->with('latestTransaction.paymentGateway')
            ->latest()
            ->paginate(15);

        $gateways = PaymentGateway::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'account_number']);

        return view('member.invoices.index', compact('invoices', 'gateways'));
    }

    /**
     * Member submits manual payment.
     */
    public function submitPayment(
        SubmitPaymentRequest $request,
        Invoice $invoice
    ): RedirectResponse {
        $data = $request->validated();

        $receiptPath = null;

        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')
                ->store("payment-receipts/{$invoice->id}", 'private');
        }

        try {
            DB::transaction(function () use (
                $invoice,
                $data,
                $receiptPath
            ) {
                $lockedInvoice = Invoice::query()
                    ->whereKey($invoice->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedInvoice->user_id !== auth()->id()) {
                    abort(403);
                }

                if (! in_array($lockedInvoice->status, ['pending', 'overdue'], true)) {
                    throw ValidationException::withMessages([
                        'invoice' => 'This invoice is not available for payment.',
                    ]);
                }

                $hasPendingTransaction = $lockedInvoice->transactions()
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($hasPendingTransaction) {
                    throw ValidationException::withMessages([
                        'invoice' => 'A payment has already been submitted for this invoice.',
                    ]);
                }

                $gateway = PaymentGateway::query()
                    ->active()
                    ->findOrFail($data['payment_gateway_id']);

                $lockedInvoice->transactions()->create([
                    'user_id' => auth()->id(),
                    'payment_gateway_id' => $gateway->id,
                    'trx_id' => $data['trx_id'],
                    'sender_mobile' => $data['sender_mobile'],
                    'amount' => $lockedInvoice->amount,
                    'status' => 'pending',
                    'receipt_path' => $receiptPath,
                    'submitted_at' => now(),
                ]);

                $lockedInvoice->update([
                    'status' => 'processing',
                ]);
            });
        } catch (\Throwable $exception) {
            if ($receiptPath) {
                Storage::disk('private')->delete($receiptPath);
            }

            throw $exception;
        }

        return back()->with(
            'success',
            'Payment submitted successfully. Please wait for verification.'
        );
    }
}