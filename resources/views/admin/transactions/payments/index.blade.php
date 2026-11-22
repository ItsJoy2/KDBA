
@extends('layouts.app')

@section('title', 'Payment Transactions')

@section('content')
<div class="container-fluid">

    @include('components.global-feedback')


    <div class="card">

        <div class="card-header">
            <h3 class="card-title">
                Payment Verification
            </h3>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Member</th>
                            <th>Invoice / Order</th>
                            <th>Gateway</th>
                            <th>TrxID</th>
                            <th>Sender</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($transactions as $transaction)

                            <tr>

                                {{-- Serial --}}
                                <td>
                                    {{ $transactions->firstItem() + $loop->index }}
                                </td>

                                {{-- Member --}}
                                <td>
                                    <strong>
                                        {{ $transaction->user?->full_name ?? 'N/A' }}
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        {{ $transaction->user?->membership_number ?? '—' }}
                                    </small>
                                </td>

                                {{-- Invoice / Order --}}
                                <td class="text-nowrap">

                                    @if($transaction->invoice)

                                        <strong>
                                            {{ $transaction->invoice->invoice_number }}
                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            {{ ucfirst($transaction->invoice->invoice_type ?? 'N/A') }}
                                        </small>

                                    @endif

                                    @if($transaction->order)

                                        @if($transaction->invoice)
                                            <hr class="my-1">
                                        @endif

                                        <strong>
                                            {{ $transaction->order->order_number }}
                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            Order
                                        </small>

                                    @endif

                                    @if(!$transaction->invoice && !$transaction->order)
                                        —
                                    @endif

                                </td>

                                {{-- Payment Gateway --}}
                                <td>
                                    <strong>
                                        {{ $transaction->paymentGateway?->name ?? '—' }}
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        {{ $transaction->paymentGateway?->account_number ?? '' }}
                                    </small>
                                </td>

                                {{-- Transaction ID --}}
                                <td class="text-nowrap">
                                    <strong>
                                        {{ $transaction->trx_id ?? '—' }}
                                    </strong>
                                </td>

                                {{-- Sender --}}
                                <td class="text-nowrap">
                                    {{ $transaction->sender_mobile ?? '—' }}
                                </td>

                                {{-- Amount --}}
                                <td>
                                    <strong>
                                        {{ number_format((float) $transaction->amount, 2) }}
                                    </strong>
                                </td>

                                {{-- Status --}}
                                <td>

                                    @php
                                        $statusClass = match ($transaction->status) {
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            default => 'warning',
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>

                                    @if($transaction->status === 'rejected')
                                        <br>
                                        <small class="text-danger">
                                            {{ $transaction->rejection_reason }}
                                        </small>
                                    @endif

                                </td>

                                {{-- Submitted --}}
                                <td class="text-nowrap">
                                    {{ $transaction->submitted_at?->format('d M Y h:i A') ?? '—' }}
                                </td>

                                {{-- Actions --}}
                                <td>

                                    <div class="d-flex gap-1 flex-wrap">

                                        {{-- Approve --}}
                                        @if($transaction->status === 'pending')

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-success"
                                                onclick="openVerifyModal(
                                                    '{{ route('admin.payment-transactions.verify', $transaction) }}',
                                                    'approved',
                                                    '{{ $transaction->order_id ? 'order' : 'invoice' }}'
                                                )">

                                                <i class="bi bi-check-lg"></i>
                                                Approve

                                            </button>

                                            {{-- Reject --}}
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="openVerifyModal(
                                                    '{{ route('admin.payment-transactions.verify', $transaction) }}',
                                                    'rejected',
                                                    '{{ $transaction->order_id ? 'order' : 'invoice' }}'
                                                )">

                                                <i class="bi bi-x-lg"></i>
                                                Reject

                                            </button>

                                        @endif

                                        {{-- Invoice Details --}}
                                        @if($transaction->invoice)

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#invoiceItemsModal{{ $transaction->id }}"
                                                title="View Invoice">

                                                <i class="bi bi-receipt"></i>

                                            </button>

                                        @endif

                                        {{-- Order Details --}}
                                        @if($transaction->order)

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#orderDetailsModal{{ $transaction->id }}"
                                                title="View Order">
                                                <i class="bi bi-cart3"></i>

                                            </button>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">

                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>

                                    No payment transactions found.

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>
        </div>

        {{-- Pagination --}}
        <div class="card-footer">

            @include('layouts.partials.__pagination', [
                'items' => $transactions
            ])

        </div>

    </div>

</div>


{{-- =====================================================
    INVOICE DETAILS MODALS
===================================================== --}}

@foreach($transactions as $transaction)

    @if($transaction->invoice)

        <div
            class="modal fade"
            id="invoiceItemsModal{{ $transaction->id }}"
            tabindex="-1"
            aria-labelledby="invoiceItemsTitle{{ $transaction->id }}"
            aria-hidden="true">

            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

                <div class="modal-content">

                    {{-- Header --}}
                    <div class="modal-header">

                        <div>

                            <h5
                                class="modal-title"
                                id="invoiceItemsTitle{{ $transaction->id }}">
                                Invoice Details

                            </h5>

                            <small class="text-muted">

                                Invoice:
                                {{ $transaction->invoice->invoice_number }}

                            </small>

                            @if($transaction->order)
                                <br>
                                <small class="text-primary">
                                    Order:
                                    {{ $transaction->order->order_number }}
                                </small>
                            @endif

                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                        </button>

                    </div>

                    <div class="modal-body">

                        {{-- Invoice Information --}}
                        <h6 class="mb-3">
                            Invoice Information
                        </h6>

                        <div class="row g-3 mb-4">

                            <div class="col-md-6">

                                <div class="border rounded p-3 h-100">

                                    <small class="text-muted d-block">
                                        Member
                                    </small>

                                    <strong>
                                        {{ $transaction->user?->full_name ?? 'N/A' }}
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        {{ $transaction->user?->membership_number ?? '—' }}
                                    </small>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="border rounded p-3 h-100">

                                    <small class="text-muted d-block">
                                        Invoice Amount
                                    </small>

                                    <h5 class="mb-0 text-primary">
                                        {{ number_format((float) $transaction->invoice->amount, 2) }}
                                    </h5>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="border rounded p-3 h-100">

                                    <small class="text-muted d-block">
                                        Invoice Number
                                    </small>

                                    <strong>
                                        {{ $transaction->invoice->invoice_number }}
                                    </strong>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="border rounded p-3 h-100">

                                    <small class="text-muted d-block">
                                        Invoice Type
                                    </small>

                                    <strong>
                                        {{ ucfirst($transaction->invoice->invoice_type ?? 'N/A') }}
                                    </strong>

                                </div>

                            </div>

                        </div>

                        {{-- Invoice Items --}}
                        <h6 class="mb-3">
                            Invoice Items
                        </h6>

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped align-middle">

                                <thead>
                                    <tr>
                                        <th width="45">#</th>
                                        <th>Item</th>
                                        <th>Unit Amount</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($transaction->invoice->items as $item)

                                        <tr>

                                            <td>
                                                {{ $loop->iteration }}
                                            </td>

                                            <td>

                                                <strong>
                                                    {{ $item->title }}
                                                </strong>

                                                <br>

                                                <small class="text-muted">
                                                    {{ $item->fee_key }}
                                                </small>

                                                @if($item->description)
                                                    <br>
                                                    <small>
                                                        {{ $item->description }}
                                                    </small>
                                                @endif

                                            </td>

                                            <td>
                                                {{ number_format((float) $item->unit_amount, 2) }}
                                            </td>

                                            <td class="text-end">
                                                <strong>
                                                    {{ number_format((float) $item->amount, 2) }}
                                                </strong>
                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                No invoice items found.
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>

                                <tfoot>

                                    <tr>
                                        <th colspan="3" class="text-end">
                                            Invoice Total
                                        </th>

                                        <th class="text-end text-primary">
                                            {{ number_format((float) $transaction->invoice->amount, 2) }}
                                        </th>
                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                        {{-- Payment Information --}}
                        <div class="border rounded p-3 mt-4">

                            <h6 class="mb-3">
                                Payment Information
                            </h6>

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <small class="text-muted d-block">
                                        Payment Gateway
                                    </small>

                                    <strong>
                                        {{ $transaction->paymentGateway?->name ?? '—' }}
                                    </strong>

                                </div>

                                <div class="col-md-6">

                                    <small class="text-muted d-block">
                                        Transaction ID
                                    </small>

                                    <strong>
                                        {{ $transaction->trx_id ?? '—' }}
                                    </strong>

                                </div>

                                <div class="col-md-6">

                                    <small class="text-muted d-block">
                                        Sender Mobile
                                    </small>

                                    <strong>
                                        {{ $transaction->sender_mobile ?? '—' }}
                                    </strong>

                                </div>

                                <div class="col-md-6">

                                    <small class="text-muted d-block">
                                        Payment Amount
                                    </small>

                                    <strong class="text-primary">
                                        {{ number_format((float) $transaction->amount, 2) }}
                                    </strong>

                                </div>

                                <div class="col-md-6">

                                    <small class="text-muted d-block">
                                        Payment Status
                                    </small>

                                    <span class="badge bg-{{ $transaction->status === 'approved' ? 'success' : ($transaction->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>

                                </div>

                                <div class="col-md-6">

                                    <small class="text-muted d-block">
                                        Submitted At
                                    </small>

                                    <strong>
                                        {{ $transaction->submitted_at?->format('d M Y h:i A') ?? '—' }}
                                    </strong>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                            Close

                        </button>

                    </div>

                </div>

            </div>

        </div>

    @endif

@endforeach


{{-- =====================================================
    ORDER DETAILS & PAYMENT HISTORY MODALS
===================================================== --}}

@foreach($transactions as $transaction)

    @php
        $order = $transaction->order;
    @endphp

    @if($order)

        <div
            class="modal fade"
            id="orderDetailsModal{{ $transaction->id }}"
            tabindex="-1"
            aria-labelledby="orderDetailsTitle{{ $transaction->id }}"
            aria-hidden="true">

            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" max-width="500px">

                <div class="modal-content">

                    {{-- Header --}}
                    <div class="modal-header">

                        <div>

                            <h5
                                class="modal-title"
                                id="orderDetailsTitle{{ $transaction->id }}">
                                Order Details

                            </h5>

                            <small class="text-muted">
                                Order:
                                {{ $order->order_number }}
                            </small>

                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                        </button>

                    </div>

                    <div class="modal-body">

                        {{-- Order Information --}}
                        <h6 class="mb-3">
                            Order Information
                        </h6>

                        <div class="row g-3 mb-4">

                            <div class="col-md-6">

                                <div class="border rounded p-3 h-100">

                                    <strong>
                                        Order Number: {{ $order->order_number }}
                                    </strong><br>
                                    <small text-muted>
                                        Order Date: {{ $order->created_at?->format('d M Y h:i A') ?? '—' }}
                                    </small>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="border rounded p-3 h-100">

                                    <small class="text-muted d-block">
                                        Total Amount
                                    </small>

                                    <h5 class="mb-0 text-primary">
                                        {{ number_format((float) $order->total_amount, 2) }}
                                    </h5>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="border rounded p-3 h-100">

                                    <small class="text-muted d-block">
                                        Member
                                    </small>

                                    <strong>
                                        {{ $order->user?->full_name ?? 'N/A' }}
                                    </strong>

                                </div>

                            </div>

                            <div class="col-md-3">

                                <div class="border rounded p-3 h-100">

                                    <small class="text-muted d-block">
                                        Order Status
                                    </small>

                                    @php
                                        $orderStatusClass = match ($order->order_status) {
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'info',
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $orderStatusClass }}">
                                        {{ ucfirst($order->order_status ?? 'N/A') }}
                                    </span>

                                </div>

                            </div>

                            <div class="col-md-3">

                                <div class="border rounded p-3 h-100">

                                    <small class="text-muted d-block">
                                        Payment Status
                                    </small>

                                    @php
                                        $orderPaymentClass = match ($order->payment_status) {
                                            'paid' => 'success',
                                            'rejected', 'failed' => 'danger',
                                            default => 'warning',
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $orderPaymentClass }}">
                                        {{ ucfirst($order->payment_status ?? 'N/A') }}
                                    </span>

                                </div>

                            </div>

                        </div>


                        {{-- Order Items --}}
                        <h6 class="mb-3">
                            Order Items
                        </h6>

                        <div class="table-responsive mb-4">

                            <table class="table table-bordered table-striped align-middle">

                                <thead>
                                    <tr>
                                        <th width="45">#</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($order->items as $item)

                                        <tr>

                                            <td>
                                                {{ $loop->iteration }}
                                            </td>

                                            <td>
                                                <strong>
                                                    {{ $item->product?->name ?? 'N/A' }}
                                                </strong>
                                            </td>

                                            <td>
                                                {{ $item->quantity }}
                                            </td>

                                            <td>
                                                {{ number_format((float) $item->price, 2) }}
                                            </td>

                                            <td class="text-end">
                                                <strong>
                                                    {{ number_format((float) ($item->price * $item->quantity), 2) }}
                                                </strong>
                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                No order items found.
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>

                                <tfoot>

                                    <tr>
                                        <th colspan="4" class="text-end">
                                            Grand Total
                                        </th>

                                        <th class="text-end text-primary">
                                            {{ number_format((float) $order->total_amount, 2) }}
                                        </th>
                                    </tr>

                                </tfoot>

                            </table>

                        </div>


                        {{-- Serial Numbers --}}
                        @if($order->serials->isNotEmpty())

                            <h6 class="mb-3">
                                Serial Numbers
                            </h6>

                            <div class="border rounded p-3 mb-4">

                                @foreach($order->serials as $serial)

                                    <span class="badge bg-light text-dark border mb-1">
                                        {{ $serial->serial_number }}
                                    </span>

                                @endforeach

                            </div>

                        @endif


                        {{-- Current Payment Information --}}
                        <h6 class="mb-3">
                            Current Payment Information
                        </h6>

                        <div class="border rounded p-3 mb-4">

                            <div class="row g-3">

                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Gateway
                                    </small>

                                    <strong>
                                        {{ $transaction->paymentGateway?->name ?? '—' }}
                                    </strong>

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Transaction ID
                                    </small>

                                    <strong>
                                        {{ $transaction->trx_id ?? '—' }}
                                    </strong>

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Sender Mobile
                                    </small>

                                    <strong>
                                        {{ $transaction->sender_mobile ?? '—' }}
                                    </strong>

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Payment Amount
                                    </small>

                                    <strong class="text-primary">
                                        {{ number_format((float) $transaction->amount, 2) }}
                                    </strong>

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Payment Status
                                    </small>

                                    @php
                                        $currentStatusClass = match ($transaction->status) {
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            default => 'warning',
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $currentStatusClass }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Submitted At
                                    </small>

                                    <strong>
                                        {{ $transaction->submitted_at?->format('d M Y h:i A') ?? '—' }}
                                    </strong>

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Verified By
                                    </small>

                                    <strong>
                                        {{ $transaction->verifier?->full_name ?? '—' }}
                                    </strong>

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Verified At
                                    </small>

                                    <strong>
                                        {{ $transaction->verified_at?->format('d M Y h:i A') ?? '—' }}
                                    </strong>

                                </div>

                                @if($transaction->rejection_reason)

                                    <div class="col-12">

                                        <small class="text-muted d-block">
                                            Rejection Reason
                                        </small>

                                        <span class="text-danger">
                                            {{ $transaction->rejection_reason }}
                                        </span>

                                    </div>

                                @endif

                            </div>

                        </div>


                        {{-- Order Payment History --}}
                        <h6 class="mb-3">
                            Order Payment History

                            <span class="badge bg-secondary">
                                {{ $order->transactions->count() }}
                            </span>

                        </h6>

                        <div class="table-responsive">

                            <table class="table table-bordered table-hover align-middle">

                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Transaction ID</th>
                                        <th>Gateway</th>
                                        <th>Sender</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Verified By</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($order->transactions->sortByDesc('created_at') as $history)

                                        <tr>

                                            <td>
                                                {{ $loop->iteration }}
                                            </td>

                                            <td>
                                                <strong>
                                                    {{ $history->trx_id ?? '—' }}
                                                </strong>
                                            </td>

                                            <td>
                                                {{ $history->paymentGateway?->name ?? '—' }}
                                            </td>

                                            <td>
                                                {{ $history->sender_mobile ?? '—' }}
                                            </td>

                                            <td>
                                                {{ number_format((float) $history->amount, 2) }}
                                            </td>

                                            <td>

                                                @php
                                                    $historyStatusClass = match ($history->status) {
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        default => 'warning',
                                                    };
                                                @endphp

                                                <span class="badge bg-{{ $historyStatusClass }}">
                                                    {{ ucfirst($history->status) }}
                                                </span>

                                            </td>

                                            <td>
                                                {{ $history->submitted_at?->format('d M Y h:i A') ?? '—' }}
                                            </td>

                                            <td>
                                                {{ $history->verifier?->full_name ?? '—' }}
                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                No payment history found.
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                            Close

                        </button>

                    </div>

                </div>

            </div>

        </div>

    @endif

@endforeach


{{-- =====================================================
    PAYMENT VERIFICATION MODAL
===================================================== --}}

<div
    class="modal fade"
    id="verifyModal"
    tabindex="-1"
    aria-labelledby="verifyTitle"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <form method="POST" id="verifyForm">

            @csrf

            <div class="modal-content">

                {{-- Header --}}
                <div class="modal-header">

                    <h5 class="modal-title" id="verifyTitle">
                        Verify Payment
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>

                </div>

                {{-- Body --}}
                <div class="modal-body">

                    <input
                        type="hidden"
                        name="status"
                        id="verifyStatus">

                    {{-- Rejection Reason --}}
                    <div
                        class="mb-3 d-none"
                        id="rejectionReasonWrapper">

                        <label class="form-label">

                            Rejection Reason

                            <span class="text-danger">*</span>

                        </label>

                        <textarea
                            name="rejection_reason"
                            id="rejectionReason"
                            class="form-control"
                            rows="4"
                            maxlength="2000"></textarea>

                        <div class="form-text">
                            Required when rejecting a payment.
                        </div>

                    </div>

                    <div
                        class="alert alert-warning mb-0"
                        id="verifyMessage">

                        Confirm this payment action.

                    </div>

                </div>

                {{-- Footer --}}
                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="verifySubmit">

                        Confirm

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

@endsection


{{-- =====================================================
    JAVASCRIPT
===================================================== --}}

@push('scripts')

<script>

    /**
     * Open payment verification modal.
     */
    function openVerifyModal(url, status, paymentType = 'invoice') {

        const form = document.getElementById('verifyForm');

        const reasonWrapper = document.getElementById(
            'rejectionReasonWrapper'
        );

        const reasonInput = document.getElementById(
            'rejectionReason'
        );

        const isApproved = status === 'approved';

        const type = paymentType === 'order'
            ? 'Order'
            : 'Invoice';

        // Form action
        form.action = url;

        // Payment status
        document.getElementById('verifyStatus').value = status;

        // Modal title
        document.getElementById('verifyTitle').textContent =
            isApproved
                ? 'Approve Payment'
                : 'Reject Payment';

        // Confirmation message
        document.getElementById('verifyMessage').textContent =
            isApproved
                ? `Approving this payment will mark the related ${type} as paid.`
                : `Rejecting this payment will allow another payment submission for this ${type}.`;

        // Submit button
        const submitButton = document.getElementById('verifySubmit');

        submitButton.textContent =
            isApproved
                ? 'Approve Payment'
                : 'Reject Payment';

        submitButton.className =
            isApproved
                ? 'btn btn-success'
                : 'btn btn-danger';

        // Rejection reason
        reasonWrapper.classList.toggle(
            'd-none',
            isApproved
        );

        reasonInput.required = !isApproved;

        reasonInput.value = '';

        // Show modal
        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('verifyModal')
        ).show();

    }

</script>

@endpush
