
@extends('layouts.app')

@section('title', 'My Vakalatnama Sticker Orders')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">My Vakalatnama Sticker Orders</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Orders</h5>

                <a href="{{ route('member.vakalatnama.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> New Order
                </a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order No.</th>
                                <th>Quantity</th>
                                <th>Total Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th width="140" class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($orders as $order)
                                @php
                                    $paymentClass = match ($order->payment_status) {
                                        'paid' => 'success',
                                        'pending' => 'info',
                                        'rejected' => 'danger',
                                        default => 'warning',
                                    };

                                    $paymentLabel = match ($order->payment_status) {
                                        'pending' => 'Awaiting Verification',
                                        default => ucfirst($order->payment_status ?? 'unpaid'),
                                    };

                                    // Payment can be submitted only when unpaid or rejected.
                                    $canPay = in_array(
                                        $order->payment_status,
                                        ['unpaid', 'rejected'],
                                        true
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
                                    <td>{{ $order->order_number }}</td>

                                    <td>{{ $order->quantity }}</td>

                                    <td>
                                        ৳{{ number_format((float) $order->total_amount, 2) }}
                                    </td>

                                    <td>
                                        <span class="badge text-bg-{{ $paymentClass }}">
                                            {{ $paymentLabel }}
                                        </span>

                                        @if ($order->payment_status === 'rejected' && $order->rejection_reason)
                                            <div>
                                                <small class="text-danger">
                                                    {{ $order->rejection_reason }}
                                                </small>
                                            </div>
                                        @endif
                                    </td>

                                    <td>{{ ucfirst($order->status) }}</td>

                                    <td>{{ $order->created_at?->format('d M Y') }}</td>

                                    <td>
                                        <div class="d-flex justify-content-center gap-1">

                                            {{-- View Order --}}
                                            <a href="{{ route('member.vakalatnama.show', $order) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Order">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            {{-- Pay Now --}}
                                            @if ($canPay)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-success js-open-sticker-payment"
                                                    data-pay-url="{{ route('member.vakalatnama.payment', $order) }}"
                                                    data-order-number="{{ $order->order_number }}"
                                                    data-amount="{{ number_format((float) $order->total_amount, 2, '.', '') }}"
                                                    title="Pay Now">
                                                    <i class="bi bi-cash-coin"></i>
                                                </button>
                                            @endif

                                            {{-- Print All Stickers --}}
                                            @if ($canPrint)
                                                <form action="{{ route('member.vakalatnama.print', $order) }}"
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
                                            @elseif ($order->payment_status === 'paid')
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
                                    <td colspan="7" class="text-center text-muted py-4">
                                        You have not placed any sticker orders yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>


{{-- =========================================================
     MEMBER VAKALATNAMA STICKER PAYMENT MODAL
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
                            Pay for Sticker Order
                        </h5>
                        <small class="text-muted">
                            Your payment will be verified before the stickers are issued.
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
                            <label class="form-label">Total Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number"
                                       id="sticker_payment_amount"
                                       class="form-control"
                                       readonly>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">
                                Payment Method
                                <span class="text-danger">*</span>
                            </label>

                            <select name="payment_gateway_id"
                                    id="sticker_payment_gateway"
                                    class="form-select"
                                    required>

                                <option value="">Select Payment Method</option>

                                @foreach ($paymentGateways as $gateway)
                                    <option value="{{ $gateway->id }}"
                                            data-type="{{ $gateway->type }}"
                                            data-account="{{ $gateway->account_number }}"
                                            data-instruction="{{ $gateway->instruction }}">
                                        {{ $gateway->name }}
                                        @if ($gateway->account_number)
                                            - {{ $gateway->account_number }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            @if ($paymentGateways->isEmpty())
                                <small class="text-danger">
                                    No payment method is available right now.
                                </small>
                            @endif

                            <div class="alert alert-info mt-2 mb-0 d-none"
                                 id="stickerGatewayInstruction"></div>
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
                                   maxlength="30"
                                   placeholder="Enter sender mobile or account"
                                   required>
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
                                   maxlength="100"
                                   pattern="[A-Za-z0-9_-]+"
                                   placeholder="Enter transaction ID"
                                   required>
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
                                      placeholder="Optional"></textarea>
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
    const amountInput = document.getElementById('sticker_payment_amount');

    const gatewayInput = document.getElementById('sticker_payment_gateway');
    const instructionBox = document.getElementById('stickerGatewayInstruction');

    const senderField = document.getElementById('stickerSenderField');
    const trxField = document.getElementById('stickerTrxField');
    const senderInput = document.getElementById('sticker_sender_mobile');
    const trxInput = document.getElementById('sticker_trx_id');

    const submitButton = document.getElementById('submitStickerPayment');

    const initialSubmitDisabled = {{ $paymentGateways->isEmpty() ? 'true' : 'false' }};
    const submitHtml = '<i class="bi bi-check-circle me-1"></i> Submit Payment';

    function onGatewayChange() {
        const option = gatewayInput.options[gatewayInput.selectedIndex];
        const isCash = option?.dataset.type === 'cash';

        senderField.classList.toggle('d-none', isCash);
        trxField.classList.toggle('d-none', isCash);

        senderInput.required = !isCash;
        trxInput.required = !isCash;

        if (isCash) {
            senderInput.value = '';
            trxInput.value = '';
        }

        const instruction = option?.dataset.instruction || '';

        instructionBox.textContent = instruction;
        instructionBox.classList.toggle('d-none', instruction === '');
    }

    gatewayInput.addEventListener('change', onGatewayChange);

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.js-open-sticker-payment');

        if (!button) {
            return;
        }

        form.reset();

        form.action = button.dataset.payUrl || '#';

        orderNumberInput.value = button.dataset.orderNumber || '';
        amountInput.value = button.dataset.amount || '';

        submitButton.disabled = initialSubmitDisabled;
        submitButton.innerHTML = submitHtml;

        onGatewayChange();

        paymentModal.show();
    });

    form.addEventListener('submit', function (event) {
        if (!form.reportValidity()) {
            event.preventDefault();
            return;
        }

        if (submitButton.disabled || form.action === '#' || !form.action) {
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

        onGatewayChange();

        submitButton.disabled = initialSubmitDisabled;
        submitButton.innerHTML = submitHtml;
    });

});
</script>
@endpush
