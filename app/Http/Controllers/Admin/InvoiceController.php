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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;


class InvoiceController extends Controller
{
    /**
     * Admin invoice listing.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($validated['search'] ?? '');

        $invoices = Invoice::query()
            ->with([
                'user:id,full_name,membership_number,mobile,email',
                'items',
                'transactions' => function ($query) {
                    $query->whereNull('invoice_item_id')
                        ->whereIn('status', ['pending', 'approved', 'rejected'])
                        ->with('paymentGateway:id,name,account_number')
                        ->latest('id');
                },
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where(function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%")
                            ->orWhere('membership_number', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $paymentGateways = PaymentGateway::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.invoices.index', compact(
            'invoices',
            'paymentGateways',
            'search'
        ));
    }

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



    public function print(Invoice $invoice)
    {
        $invoice->load([
            'user:id,full_name,membership_number,mobile,email',
            'items:id,invoice_id,fee_key,title,description,unit_amount,quantity,amount',
        ]);

        $fontPath = public_path('assets/fonts');

        $fontFile = $fontPath . '/SolaimanLipi.ttf';

        abort_unless(
            File::exists($fontFile),
            500,
            'SolaimanLipi font file not found.'
        );

        // Prepare mPDF temporary directory.
        $tempPath = storage_path('app/mpdf-temp');

        File::ensureDirectoryExists($tempPath);

        // Load default mPDF fonts.
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',

            // A5 Portrait
            'format' => 'A5',
            'orientation' => 'P',

            'fontDir' => array_merge($fontDirs, [
                $fontPath,
            ]),

            'fontdata' => $fontData + [
                'solaimanlipi' => [
                    'R'  => 'SolaimanLipi.ttf',
                    'B'  => 'SolaimanLipi.ttf',
                    'I'  => 'SolaimanLipi.ttf',
                    'BI' => 'SolaimanLipi.ttf',
                    'useOTL' => 0xFF,
                ],
            ],

            'default_font' => 'solaimanlipi',

            'autoScriptToLang' => true,
            'autoLangToFont' => true,

            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,

            'tempDir' => $tempPath,
        ]);

        $html = view('admin.invoices.print', compact('invoice'))->render();

        $mpdf->WriteHTML($html);

        $filename = $invoice->invoice_number . '.pdf';

        $pdf = $mpdf->Output(
            $filename,
            \Mpdf\Output\Destination::STRING_RETURN
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}