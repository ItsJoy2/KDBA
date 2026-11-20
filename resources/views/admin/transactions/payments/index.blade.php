
@extends('layouts.app')

@section('title', 'Payment Transactions')

@section('content')
<div class="container-fluid">

    @include('components.global-feedback')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-wallet2 me-1"></i>
                Payment Verification
            </h3>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Member</th>
                        <th>Invoice</th>
                        <th>Gateway</th>
                        <th>TrxID</th>
                        <th>Sender</th>
                        <th>Amount</th>
                        <th>Receipt</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th width="190">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                {{ $transactions->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <strong>
                                    {{ $transaction->user?->full_name ?? 'N/A' }}
                                </strong>
                                <br>
                                <small class="text-muted">
                                    {{ $transaction->user?->membership_number ?? '—' }}
                                </small>
                            </td>

                            <td>
                                <strong>
                                    {{ $transaction->invoice?->invoice_number ?? '—' }}
                                </strong>
                                <br>
                                <small class="text-muted">
                                    {{ ucfirst($transaction->invoice?->invoice_type ?? 'N/A') }}
                                </small>
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
                            <td>
                                <strong>
                                    {{ $transaction->trx_id ?? '—' }}
                                </strong>
                            </td>

                            <td>
                                {{ $transaction->sender_mobile ?? '—' }}
                            </td>

                            <td>
                                <strong>
                                    {{ number_format((float) $transaction->amount, 2) }}
                                </strong>
                            </td>

                            {{-- Receipt --}}
                            <td>
                                @if($transaction->receipt_path)
                                    <a
                                        href="{{ route('admin.payment-transactions.receipt', $transaction) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark"></i>
                                        View
                                    </a>
                                @else
                                    —
                                @endif
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

                            <td>
                                {{ $transaction->submitted_at?->format('d M Y h:i A') ?? '—' }}
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="d-flex gap-1 flex-wrap">

                                    {{-- Approve / Reject --}}
                                    @if($transaction->status === 'pending')

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success"
                                            onclick="openVerifyModal(
                                                '{{ route('admin.payment-transactions.verify', $transaction) }}',
                                                'approved'
                                            )">
                                            <i class="bi bi-check-lg"></i>
                                            Approve
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger"
                                            onclick="openVerifyModal(
                                                '{{ route('admin.payment-transactions.verify', $transaction) }}',
                                                'rejected'
                                            )">
                                            <i class="bi bi-x-lg"></i>
                                            Reject
                                        </button>

                                    {{-- @else

                                        <span class="text-muted small align-self-center">
                                            <i class="bi bi-person-check"></i>
                                            {{ $transaction->verifier?->full_name ?? 'Verified' }}
                                        </span> --}}

                                    @endif

                                    {{-- View Invoice Items --}}
                                    @if($transaction->invoice?->items?->isNotEmpty())
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#invoiceItemsModal{{ $transaction->id }}">
                                            <i class="bi bi-list-ul"></i>
                                            View
                                        </button>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No payment transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            @include('layouts.partials.__pagination', [
                'items' => $transactions
            ])
        </div>
    </div>
</div>


{{-- =====================================================
    INVOICE ITEMS MODALS
===================================================== --}}

@foreach($transactions as $transaction)
    @if($transaction->invoice?->items?->isNotEmpty())

        <div
            class="modal fade"
            id="invoiceItemsModal{{ $transaction->id }}"
            tabindex="-1"
            aria-labelledby="invoiceItemsTitle{{ $transaction->id }}"
            aria-hidden="true">

            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <div>
                            <h5
                                class="modal-title"
                                id="invoiceItemsTitle{{ $transaction->id }}">
                                <i class="bi bi-receipt me-1"></i>
                                Invoice Items
                            </h5>

                            <small class="text-muted">
                                Invoice:
                                {{ $transaction->invoice->invoice_number }}
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

                        {{-- Invoice Information --}}
                        <div class="row g-3 mb-3">

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

                        </div>

                        {{-- Items Table --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">

                                <thead>
                                    <tr>
                                        <th width="45">#</th>
                                        <th>Item</th>
                                        {{-- <th>Quantity</th> --}}
                                        <th>Unit Amount</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($transaction->invoice->items as $item)
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

                                            {{-- <td>
                                                {{ $item->quantity }}
                                            </td> --}}

                                            <td>
                                                {{ number_format((float) $item->unit_amount, 2) }}
                                            </td>

                                            <td class="text-end">
                                                <strong>
                                                    {{ number_format((float) $item->amount, 2) }}
                                                </strong>
                                            </td>
                                        </tr>
                                    @endforeach
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
                        <div class="border rounded p-3 mt-3">
                            <h6 class="mb-3">
                                <i class="bi bi-credit-card me-1"></i>
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

                                    <strong>
                                        {{ number_format((float) $transaction->amount, 2) }}
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

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary" id="verifySubmit">
                        Confirm
                    </button>

                </div>

            </div>
        </form>
    </div>
</div>

@endsection


@push('scripts')
<script>
    /**
     * Open payment verification modal.
     */
    function openVerifyModal(url, status) {

        const form = document.getElementById('verifyForm');

        const reasonWrapper = document.getElementById(
            'rejectionReasonWrapper'
        );

        const reasonInput = document.getElementById(
            'rejectionReason'
        );

        const isApproved = status === 'approved';

        form.action = url;

        document.getElementById('verifyStatus').value = status;

        document.getElementById('verifyTitle').textContent =
            isApproved ? 'Approve Payment' : 'Reject Payment';

        document.getElementById('verifyMessage').textContent =
            isApproved
                ? 'Approving this payment will mark the related invoice as paid.'
                : 'Rejecting this payment will allow the member to submit another payment.';

        document.getElementById('verifySubmit').textContent =
            isApproved ? 'Approve Payment' : 'Reject Payment';

        document.getElementById('verifySubmit').className =
            isApproved
                ? 'btn btn-success'
                : 'btn btn-danger';

        reasonWrapper.classList.toggle('d-none', isApproved);

        reasonInput.required = !isApproved;

        reasonInput.value = '';

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('verifyModal')
        ).show();
    }
</script>
@endpush
