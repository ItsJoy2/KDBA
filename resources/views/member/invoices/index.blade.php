
@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-receipt me-1"></i>
            My Invoices
        </h4>
    </div>

    @include('components.global-feedback')

    {{-- Invoice List --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Membership Invoices</h5>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Type</th>
                        <th>Cycle</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Payment</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($invoices as $invoice)
                        @php
                            $hasPendingPayment = $invoice->transactions
                                ->contains(fn ($transaction) =>
                                    in_array($transaction->status, ['pending', 'approved'], true)
                                );

                            $typeLabels = [
                                'registration' => 'Initial Membership Fee',
                                'yearly_renewal' => 'Yearly Renewal Fee',
                                'membership_fee' => 'Initial Membership Fee',
                                'yearly_fee' => 'Yearly Renewal Fee',
                            ];

                            $statusClass = match ($invoice->status) {
                                'paid' => 'success',
                                'processing' => 'info',
                                'overdue' => 'danger',
                                'cancelled' => 'secondary',
                                default => 'warning',
                            };
                        @endphp

                        <tr>
                            <td>
                                {{ $invoices->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <strong>{{ $invoice->invoice_number }}</strong>
                                <div class="small text-muted">
                                    {{ $invoice->title }}
                                </div>
                            </td>

                            <td>
                                {{ $typeLabels[$invoice->invoice_type] ?? $invoice->invoice_type }}
                            </td>

                            <td>{{ $invoice->cycle_key ?: '—' }}</td>

                            <td>
                                <strong>
                                    ৳{{ number_format((float) $invoice->amount, 2) }}
                                </strong>
                            </td>

                            <td>
                                {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                            </td>

                            <td>
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>

                            <td>
                                @if(
                                    in_array($invoice->status, ['pending', 'overdue'], true)
                                    && ! $hasPendingPayment
                                )
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-success js-member-pay"
                                        data-invoice-number="{{ $invoice->invoice_number }}"
                                        data-invoice-amount="{{ $invoice->amount }}"
                                        data-pay-url="{{ route('member.invoices.pay', $invoice) }}"
                                    >
                                        <i class="bi bi-credit-card me-1"></i>
                                        Pay Now
                                    </button>

                                @elseif($invoice->status === 'processing' || $hasPendingPayment)
                                    <span class="badge bg-info">
                                        Awaiting Verification
                                    </span>

                                @elseif($invoice->status === 'paid')
                                    <span class="text-success">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Paid
                                    </span>

                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-receipt fs-3 d-block mb-2"></i>
                                No invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @include('layouts.partials.__pagination', [
                'items' => $invoices
            ])
        </div>
    </div>
</div>

@include('member.invoices.partials.__pay')
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('memberPaymentModal');

    if (!modalElement || typeof bootstrap === 'undefined') {
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

    const form = document.getElementById('memberPaymentForm');

    const invoiceNumber = document.getElementById('memberInvoiceNumber');
    const invoiceAmount = document.getElementById('memberInvoiceAmount');

    const paymentAmount = document.getElementById('memberPaymentAmount');

    const errorContainer = document.getElementById('memberPaymentErrors');
    const errorList = document.getElementById('memberPaymentErrorList');

    const submitButton = document.getElementById('memberPaymentSubmit');

    const defaultSubmitButtonHtml =
        '<i class="bi bi-send me-1"></i> Submit Payment';

    /**
     * Clear validation errors.
     */
    function clearErrors() {
        errorList.replaceChildren();
        errorContainer.classList.add('d-none');
    }

    /**
     * Display validation errors safely.
     */
    function showErrors(errors) {
        errorList.replaceChildren();

        Object.values(errors).flat().forEach(function (message) {
            const li = document.createElement('li');
            li.textContent = message;
            errorList.appendChild(li);
        });

        errorContainer.classList.remove('d-none');
    }

    /**
     * Open payment modal with selected invoice data.
     */
    document.querySelectorAll('.js-member-pay').forEach(function (button) {
        button.addEventListener('click', function () {
            form.reset();
            clearErrors();

            submitButton.disabled = false;
            submitButton.innerHTML = defaultSubmitButtonHtml;

            const number = button.dataset.invoiceNumber;
            const amount = Number(button.dataset.invoiceAmount);

            if (!Number.isFinite(amount) || amount <= 0) {
                return;
            }

            // Set payment form action.
            form.action = button.dataset.payUrl;

            // Set invoice information.
            invoiceNumber.textContent = number;
            invoiceAmount.textContent = '৳' + amount.toFixed(2);

            // Automatically fill invoice amount.
            paymentAmount.value = amount.toFixed(2);

            modal.show();
        });
    });

    /**
     * Prevent duplicate form submission.
     */
    form.addEventListener('submit', function () {
        clearErrors();

        submitButton.disabled = true;
        submitButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
    });

    /**
     * Restore button after browser back/forward navigation.
     */
    window.addEventListener('pageshow', function () {
        submitButton.disabled = false;
        submitButton.innerHTML = defaultSubmitButtonHtml;
    });

    /**
     * Reset modal when closed.
     */
    modalElement.addEventListener('hidden.bs.modal', function () {
        form.reset();
        clearErrors();

        paymentAmount.value = '';
        invoiceNumber.textContent = '—';
        invoiceAmount.textContent = '৳0.00';

        submitButton.disabled = false;
        submitButton.innerHTML = defaultSubmitButtonHtml;
    });
});
</script>
@endpush
