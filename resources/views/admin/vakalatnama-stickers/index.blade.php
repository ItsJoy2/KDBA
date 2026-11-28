
@extends('layouts.app')

@section('title', 'Vakalatnama Sticker Orders')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Vakalatnama Sticker Orders</h3>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Sticker Orders
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- Global Feedback --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Please fix the following errors:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Header --}}
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div>
                    <h5 class="mb-1">All Sticker Orders</h5>
                    <small class="text-muted">
                        Manage Vakalatnama sticker orders and payments.
                    </small>
                </div>

                <div class="d-flex align-items-center gap-2">

                    <span class="badge bg-primary fs-6">
                        Total: {{ $orders->total() }}
                    </span>

                    @if (auth()->user()->hasPermission('vakalatnama.create'))
                        <a href="{{ route('admin.vakalatnama.create') }}"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            Create Order
                        </a>
                    @endif

                </div>
            </div>
        </div>

        {{-- Search and Filters --}}
        <div class="card mb-3">
            <div class="card-body">

                <form method="GET"
                      action="{{ route('admin.vakalatnama.index') }}"
                      class="row g-2">

                    <div class="col-md-5">
                        <input
                            type="search"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Search order number, member name or membership number">
                    </div>

                    <div class="col-md-3">
                        <select name="payment_status" class="form-select">
                            <option value="">All Payment Statuses</option>

                            @foreach (['unpaid', 'pending', 'paid', 'rejected'] as $status)
                                <option value="{{ $status }}"
                                    @selected(request('payment_status') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-search me-1"></i>
                            Filter
                        </button>

                        <a href="{{ route('admin.vakalatnama.index') }}"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Reset
                        </a>
                    </div>

                </form>
            </div>
        </div>

        {{-- Orders Table --}}
        <div class="card">

            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-receipt me-1"></i>
                    Sticker Orders
                </h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th width="55">#</th>
                                <th>Order No.</th>
                                <th>Member</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Payment</th>
                                <th class="text-center">Order Status</th>
                                <th>Date</th>
                                <th width="140" class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($orders as $order)

                                @php
                                    $paymentStatusClass = match ($order->payment_status) {
                                        'paid' => 'success',
                                        'pending' => 'info',
                                        'rejected' => 'danger',
                                        'unpaid' => 'warning',
                                        default => 'secondary',
                                    };

                                    $orderStatusClass = match ($order->status) {
                                        'completed', 'approved' => 'success',
                                        'processing' => 'info',
                                        'pending' => 'warning',
                                        'rejected', 'cancelled' => 'danger',
                                        default => 'secondary',
                                    };

                                    $existingTransaction = $order->transaction;

                                    $canRecordPayment =
                                        auth()->user()->hasPermission('vakalatnama.pay')
                                        && $order->payment_status !== 'paid'
                                        && (
                                            ! $existingTransaction
                                            || $existingTransaction->status === 'rejected'
                                        );

                                    // All stickers must be ready and never printed.
                                    $canPrint = $order->payment_status === 'paid'
                                        && $order->stickers->isNotEmpty()
                                        && $order->stickers->every(
                                            fn ($sticker) =>
                                                $sticker->status === 'ready'
                                                && (int) $sticker->print_count === 0
                                                && $sticker->printed_at === null
                                        );
                                @endphp

                                <tr>

                                    <td>
                                        {{ $orders->firstItem() + $loop->index }}
                                    </td>

                                    <td>
                                        <strong>{{ $order->order_number }}</strong>

                                        <div>
                                            <small class="text-muted">
                                                {{ $order->stickers_count ?? 0 }} stickers
                                            </small>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $order->user?->full_name ?? 'N/A' }}
                                        </div>

                                        <small class="text-muted">
                                            Membership:
                                            {{ $order->user?->membership_number ?? 'N/A' }}
                                        </small>

                                        @if ($order->user?->mobile)
                                            <div>
                                                <small class="text-muted">
                                                    <i class="bi bi-telephone me-1"></i>
                                                    {{ $order->user->mobile }}
                                                </small>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        {{ number_format((int) $order->quantity) }}
                                    </td>

                                    <td class="text-end">
                                        <strong>
                                            ৳{{ number_format((float) $order->total_amount, 2) }}
                                        </strong>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-{{ $paymentStatusClass }}">
                                            {{ str($order->payment_status)->replace('_', ' ')->title() }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-{{ $orderStatusClass }}">
                                            {{ str($order->status)->replace('_', ' ')->title() }}
                                        </span>
                                    </td>

                                    <td>
                                        <div>
                                            {{ $order->created_at?->format('d M Y') ?? '—' }}
                                        </div>

                                        <small class="text-muted">
                                            {{ $order->created_at?->format('h:i A') ?? '' }}
                                        </small>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-center gap-1">

                                            {{-- View Order --}}
                                            <a href="{{ route('admin.vakalatnama.show', $order) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Order">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            {{-- Record Payment --}}
                                            @if ($canRecordPayment)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-success js-open-sticker-payment"
                                                    data-pay-url="{{ route('admin.vakalatnama.pay', $order) }}"
                                                    data-order-number="{{ $order->order_number }}"
                                                    data-user-name="{{ $order->user?->full_name ?? 'N/A' }}"
                                                    data-amount="{{ number_format((float) $order->total_amount, 2, '.', '') }}"
                                                    title="Record Payment">
                                                    <i class="bi bi-cash-coin"></i>
                                                </button>
                                            @endif

                                            {{-- Print All Stickers --}}
                                            @if ($canPrint && auth()->user()->hasPermission('vakalatnama.print'))
                                                <form action="{{ route('admin.vakalatnama.print', $order) }}"
                                                      method="POST"
                                                      target="_blank"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Print all {{ $order->quantity }} stickers for this order?');">
                                                    @csrf

                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-dark"
                                                            title="Print All Stickers">
                                                        <i class="bi bi-printer"></i>
                                                    </button>
                                                </form>
                                            @elseif ($order->payment_status === 'paid' && auth()->user()->hasPermission('vakalatnama.print'))
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        disabled
                                                        title="Already printed or not ready for printing">
                                                    <i class="bi bi-printer-fill"></i>
                                                </button>
                                            @endif

                                        </div>
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        No sticker orders found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>

            @if ($orders->hasPages())
                <div class="card-footer">
                    {{ $orders->withQueryString()->links() }}
                </div>
            @endif

        </div>

    </div>
</div>


{{-- =========================================================
     ADMIN VAKALATNAMA STICKER PAYMENT MODAL
========================================================= --}}

<div class="modal fade"
     id="stickerPaymentModal"
     tabindex="-1"
     aria-labelledby="stickerPaymentModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <form action="#"
                  method="POST"
                  enctype="multipart/form-data"
                  id="stickerPaymentForm">

                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="stickerPaymentModalLabel">
                            <i class="bi bi-cash-coin me-1"></i>
                            Record Sticker Payment
                        </h5>

                        <small class="text-muted">
                            Record payment on behalf of a member.
                        </small>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Order Number</label>

                            <input type="text"
                                   id="sticker_order_number"
                                   class="form-control"
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Member Name</label>

                            <input type="text"
                                   id="sticker_user_name"
                                   class="form-control"
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Total Amount</label>

                            <div class="input-group">
                                <span class="input-group-text">৳</span>

                                <input type="number"
                                       id="sticker_payment_amount"
                                       class="form-control"
                                       readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Payment Gateway
                                <span class="text-danger">*</span>
                            </label>

                            <select name="payment_gateway_id"
                                    id="sticker_payment_gateway"
                                    class="form-select"
                                    required>

                                <option value="">Select Payment Gateway</option>

                                @foreach ($paymentGateways as $gateway)
                                    <option value="{{ $gateway->id }}"
                                            data-type="{{ $gateway->type }}">
                                        {{ $gateway->name }}

                                        @if ($gateway->account_number)
                                            - {{ $gateway->account_number }}
                                        @endif
                                    </option>
                                @endforeach

                            </select>

                            @if ($paymentGateways->isEmpty())
                                <small class="text-danger">
                                    No active payment gateway available.
                                </small>
                            @endif
                        </div>

                        <div class="col-md-6" id="stickerSenderField">
                            <label class="form-label">
                                Sender Mobile / Account
                                <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="sender_mobile"
                                   id="sticker_sender_mobile"
                                   class="form-control"
                                   maxlength="100"
                                   placeholder="Enter sender mobile or account">
                        </div>

                        <div class="col-md-6" id="stickerTrxField">
                            <label class="form-label">
                                Transaction ID
                                <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="trx_id"
                                   id="sticker_trx_id"
                                   class="form-control"
                                   minlength="4"
                                   maxlength="150"
                                   pattern="[A-Za-z0-9_-]+"
                                   placeholder="Enter transaction ID">
                        </div>

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

                        <div class="col-md-6">
                            <label class="form-label">Remarks</label>

                            <textarea name="remarks"
                                      class="form-control"
                                      rows="2"
                                      maxlength="1000"
                                      placeholder="Optional payment remarks"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-success"
                            id="submitStickerPayment"
                            @disabled($paymentGateways->isEmpty())>
                        <i class="bi bi-check-circle me-1"></i>
                        Submit Payment
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalElement = document.getElementById('stickerPaymentModal');
    const form = document.getElementById('stickerPaymentForm');

    if (!modalElement || !form) {
        return;
    }

    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JavaScript is not loaded.');
        return;
    }

    const paymentModal = bootstrap.Modal.getOrCreateInstance(modalElement);

    const orderNumberInput = document.getElementById('sticker_order_number');
    const userNameInput = document.getElementById('sticker_user_name');
    const amountInput = document.getElementById('sticker_payment_amount');

    const gatewayInput = document.getElementById('sticker_payment_gateway');

    const senderField = document.getElementById('stickerSenderField');
    const trxField = document.getElementById('stickerTrxField');

    const senderInput = document.getElementById('sticker_sender_mobile');
    const trxInput = document.getElementById('sticker_trx_id');

    const submitButton = document.getElementById('submitStickerPayment');

    const initialSubmitDisabled = {{ $paymentGateways->isEmpty() ? 'true' : 'false' }};

    const submitHtml =
        '<i class="bi bi-check-circle me-1"></i> Submit Payment';

    function toggleCashFields() {
        const selectedOption = gatewayInput.options[gatewayInput.selectedIndex];
        const isCash = selectedOption?.dataset.type === 'cash';

        senderField.classList.toggle('d-none', isCash);
        trxField.classList.toggle('d-none', isCash);

        senderInput.required = !isCash;
        trxInput.required = !isCash;

        if (isCash) {
            senderInput.value = '';
            trxInput.value = '';
        }
    }

    gatewayInput.addEventListener('change', toggleCashFields);

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.js-open-sticker-payment');

        if (!button) {
            return;
        }

        form.reset();

        form.action = button.dataset.payUrl || '#';

        orderNumberInput.value = button.dataset.orderNumber || '';
        userNameInput.value = button.dataset.userName || '';
        amountInput.value = button.dataset.amount || '';

        submitButton.disabled = initialSubmitDisabled;
        submitButton.innerHTML = submitHtml;

        toggleCashFields();

        paymentModal.show();
    });

    form.addEventListener('submit', function (event) {
        if (!form.reportValidity()) {
            event.preventDefault();
            return;
        }

        if (
            submitButton.disabled
            || form.action === '#'
            || !form.action
        ) {
            event.preventDefault();
            return;
        }

        submitButton.disabled = true;

        submitButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        form.reset();
        form.action = '#';

        toggleCashFields();

        submitButton.disabled = initialSubmitDisabled;
        submitButton.innerHTML = submitHtml;
    });

});
</script>
@endpush
