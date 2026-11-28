@extends('layouts.app')

@section('title', 'Membership Invoices')

@section('content')
<div class="container-fluid">

    @include('components.global-feedback')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">Membership Invoices</h3>
            <small class="text-muted">
                Manage membership invoices and payments.
            </small>
        </div>

        <span class="badge bg-primary">
            Total: {{ $invoices->total() }}
        </span>
    </div>

    {{-- Search --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET"
                  action="{{ route('admin.invoices.index') }}"
                  class="row g-2">

                <div class="col-md-4">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search name, membership number, phone or email..."
                        value="{{ $search }}"
                    >
                </div>

                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>

                    @if($search)
                        <a href="{{ route('admin.invoices.index') }}"
                           class="btn btn-outline-secondary">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Invoice Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">

                    <thead>
                        <tr>
                            <th width="55">#</th>
                            <th>Invoice Number</th>
                            <th>Member</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>
                                    {{ $invoices->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $invoice->invoice_number }}
                                    </strong>
                                </td>

                                <td>
                                    <div>
                                        {{ $invoice->user->full_name ?? 'N/A' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $invoice->user->email ?? '' }}
                                    </small>
                                </td>

                                <td>
                                    {{ str($invoice->invoice_type)->replace('_', ' ')->title() }}
                                </td>

                                <td>
                                    <strong>
                                        {{ number_format((float) $invoice->amount, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                                </td>

                                <td>
                                    @php
                                        $statusClass = match($invoice->status) {
                                            'paid' => 'success',
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'overdue' => 'danger',
                                            'cancelled' => 'secondary',
                                            default => 'secondary',
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ str($invoice->status)->replace('_', ' ')->title() }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex gap-1">

                                        {{-- View Invoice Items --}}
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#invoiceViewModal{{ $invoice->id }}"
                                            title="View invoice details"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        @if(in_array($invoice->status, ['pending', 'overdue'], true))
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success js-open-invoice-payment"
                                                    data-invoice-id="{{ $invoice->id }}"
                                                    data-invoice-number="{{ $invoice->invoice_number }}"
                                                    data-user-name="{{ $invoice->user->full_name ?? 'N/A' }}"
                                                    data-amount="{{ number_format((float) $invoice->amount, 2, '.', '') }}"
                                                    title="Record Payment">

                                                <i class="bi bi-cash-coin"></i>
                                            </button>
                                        @endif

                                        <a href="{{ route('admin.invoices.print', $invoice->id) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Print Invoice">

                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No invoices found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($invoices->hasPages())
            <div class="card-footer">
                @include('layouts.partials.__pagination', [
                    'items' => $invoices
                ])
            </div>
        @endif
    </div>

    {{-- Invoice View Modals --}}
    {{-- @foreach($invoices as $invoice)
        @include('admin.invoices.partials.__view-modal', [
            'invoice' => $invoice
        ])
    @endforeach --}}



{{-- Admin Invoice Payment Modal --}}
<div class="modal fade"
     id="invoicePaymentModal"
     tabindex="-1"
     aria-labelledby="invoicePaymentModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form action="{{ route('admin.transactions.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  id="invoicePaymentForm">

                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="invoicePaymentModalLabel">
                        Record Invoice Payment
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden"
                           name="invoice_id"
                           id="payment_invoice_id">

                    <div class="row g-3">

                        {{-- Invoice Number --}}
                        <div class="col-md-6">
                            <label class="form-label">Invoice Number</label>
                            <input type="text"
                                   id="payment_invoice_number"
                                   class="form-control"
                                   readonly
                                   disabled>
                        </div>

                        {{-- Member Name --}}
                        <div class="col-md-6">
                            <label class="form-label">Member Name</label>
                            <input type="text"
                                   id="payment_user_name"
                                   class="form-control"
                                   readonly
                                   disabled>
                        </div>

                        {{-- Amount --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Invoice Amount <span class="text-danger">*</span>
                            </label>

                            <input type="number"
                                   name="amount"
                                   id="payment_amount"
                                   class="form-control"
                                   min="0.01"
                                   step="0.01"
                                   readonly
                                   required>
                        </div>

                        {{-- Payment Gateway --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Payment Gateway <span class="text-danger">*</span>
                            </label>

                            <select name="payment_gateway_id"
                                    id="payment_gateway_id"
                                    class="form-select"
                                    required>

                                <option value="">Select Payment Gateway</option>

                                @foreach($paymentGateways as $gateway)
                                    <option value="{{ $gateway->id }}"
                                            data-type="{{ $gateway->type }}">
                                        {{ $gateway->name }}

                                        @if($gateway->account_number)
                                            - {{ $gateway->account_number }}
                                        @endif
                                    </option>
                                @endforeach

                            </select>

                            @if($paymentGateways->isEmpty())
                                <small class="text-danger">
                                    No active payment gateway available.
                                </small>
                            @endif
                        </div>

                        {{-- Sender Mobile / Account --}}
                        <div class="col-md-6" id="senderMobileField">
                            <label class="form-label">
                                Sender Mobile / Account
                                <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="sender_mobile"
                                   id="payment_sender_mobile"
                                   class="form-control"
                                   maxlength="100"
                                   placeholder="Enter sender mobile or account number"
                                   required>
                        </div>

                        {{-- Transaction ID --}}
                        <div class="col-md-6" id="trxIdField">
                            <label class="form-label">
                                Transaction ID
                                <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="trx_id"
                                   id="payment_trx_id"
                                   class="form-control"
                                   minlength="4"
                                   maxlength="150"
                                   pattern="[A-Za-z0-9_-]+"
                                   placeholder="Enter transaction ID"
                                   required>
                        </div>

                        {{-- Receipt --}}
                        <div class="col-md-6">
                            <label class="form-label">Payment Receipt</label>

                            <input type="file"
                                   name="receipt"
                                   class="form-control"
                                   accept=".jpg,.jpeg,.png,.pdf">

                            <small class="text-muted">
                                JPG, JPEG, PNG or PDF. Maximum 5 MB.
                            </small>
                        </div>

                        {{-- Payment Status --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Payment Status <span class="text-danger">*</span>
                            </label>

                            <select name="status"
                                    id="payment_status"
                                    class="form-select"
                                    required>

                                <option value="approved" selected>Approved</option>
                                <option value="pending">Pending</option>

                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-success"
                            id="submitInvoicePayment"
                            @if($paymentGateways->isEmpty()) disabled @endif>

                        <i class="bi bi-check-circle me-1"></i>
                        Submit Payment
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>



</div>
@endsection

@push('scripts')
<script>

    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('invoicePaymentModal');
        const form = document.getElementById('invoicePaymentForm');

        if (!modalElement || !form) {
            return;
        }

        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap JavaScript is not loaded.');
            return;
        }

        const paymentModal = bootstrap.Modal.getOrCreateInstance(modalElement);

        // Invoice fields
        const invoiceIdInput = document.getElementById('payment_invoice_id');
        const invoiceNumberInput = document.getElementById('payment_invoice_number');
        const userNameInput = document.getElementById('payment_user_name');
        const amountInput = document.getElementById('payment_amount');

        // Payment fields
        const gatewayInput = document.getElementById('payment_gateway_id');
        const statusInput = document.getElementById('payment_status');

        const senderMobileField = document.getElementById('senderMobileField');
        const trxIdField = document.getElementById('trxIdField');

        const senderMobileInput = document.getElementById('payment_sender_mobile');
        const trxIdInput = document.getElementById('payment_trx_id');

        const submitButton = document.getElementById('submitInvoicePayment');

        const initialSubmitDisabled = {{ $paymentGateways->isEmpty() ? 'true' : 'false' }};

        /**
         * Toggle payment fields based on selected gateway.
         */
        function toggleCashFields() {
            const selectedOption = gatewayInput.options[gatewayInput.selectedIndex];

            const isCash = selectedOption?.dataset.type === 'cash';

            // Hide fields for Cash gateway.
            senderMobileField.classList.toggle('d-none', isCash);
            trxIdField.classList.toggle('d-none', isCash);

            // Update required attributes.
            senderMobileInput.required = !isCash;
            trxIdInput.required = !isCash;

            // Clear values when Cash is selected.
            if (isCash) {
                senderMobileInput.value = '';
                trxIdInput.value = '';
            }
        }

        // Listen for gateway changes.
        gatewayInput.addEventListener('change', toggleCashFields);

        /**
         * Open payment modal.
         */
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.js-open-invoice-payment');

            if (!button) {
                return;
            }

            form.reset();

            invoiceIdInput.value = button.dataset.invoiceId || '';
            invoiceNumberInput.value = button.dataset.invoiceNumber || '';
            userNameInput.value = button.dataset.userName || '';
            amountInput.value = button.dataset.amount || '';

            statusInput.value = 'approved';

            // Reset gateway-dependent fields.
            toggleCashFields();

            submitButton.disabled = initialSubmitDisabled;
            submitButton.innerHTML =
                '<i class="bi bi-check-circle me-1"></i> Submit Payment';

            paymentModal.show();
        });

        /**
         * Prevent duplicate submission.
         */
        form.addEventListener('submit', function (event) {
            if (!form.reportValidity()) {
                event.preventDefault();
                return;
            }

            if (submitButton.disabled) {
                event.preventDefault();
                return;
            }

            submitButton.disabled = true;
            submitButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
        });

        /**
         * Reset modal after closing.
         */
        modalElement.addEventListener('hidden.bs.modal', function () {
            form.reset();

            statusInput.value = 'approved';

            toggleCashFields();

            submitButton.disabled = initialSubmitDisabled;
            submitButton.innerHTML =
                '<i class="bi bi-check-circle me-1"></i> Submit Payment';
        });
    });

</script>
@endpush