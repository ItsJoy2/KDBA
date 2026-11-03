
@extends('layouts.app')

@section('title', 'Payment Transactions')

@section('content')
<div class="container-fluid">
    @include('components.global-feedback')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payment Verification</h3>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
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
                        <th width="170">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transactions->firstItem() + $loop->index }}</td>

                            <td>
                                <strong>{{ $transaction->user->full_name ?? 'N/A' }}</strong>
                                <br>
                                <small>
                                    {{ $transaction->user->membership_number ?? '—' }}
                                </small>
                            </td>

                            <td>
                                {{ $transaction->invoice->invoice_number ?? '—' }}
                            </td>

                            <td>
                                {{ $transaction->paymentGateway->name ?? '—' }}
                                <br>
                                <small>
                                    {{ $transaction->paymentGateway->account_number ?? '' }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $transaction->trx_id }}</strong>
                            </td>

                            <td>{{ $transaction->sender_mobile }}</td>

                            <td>{{ number_format((float) $transaction->amount, 2) }}</td>

                            <td>
                                @if($transaction->receipt_path)
                                    <a href="{{ route('admin.payment-transactions.receipt', $transaction) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark"></i> View
                                    </a>
                                @else
                                    —
                                @endif
                            </td>

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

                            <td>
                                @if($transaction->status === 'pending')
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-success"
                                        onclick="openVerifyModal(
                                            {{ $transaction->id }},
                                            'approved',
                                            '{{ route('admin.payment-transactions.verify', $transaction) }}'
                                        )">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger"
                                        onclick="openVerifyModal(
                                            {{ $transaction->id }},
                                            'rejected',
                                            '{{ route('admin.payment-transactions.verify', $transaction) }}'
                                        )">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                @else
                                    <span class="text-muted">
                                        {{ $transaction->verifier->full_name ?? 'Verified' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">
                                No payment transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $transactions->links('admin.layouts.partials.pagination-links') }}
        </div>
    </div>
</div>

{{-- Verification Modal --}}
<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="verifyForm">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verifyTitle">Verify Payment</h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="status" id="verifyStatus">

                    <div class="mb-3" id="rejectionReasonWrapper">
                        <label class="form-label">Rejection Reason</label>
                        <textarea name="rejection_reason" id="rejectionReason"
                                  class="form-control" rows="4"
                                  maxlength="2000"></textarea>
                        <div class="form-text">
                            Required when rejecting a payment.
                        </div>
                    </div>

                    <div class="alert alert-warning mb-0" id="verifyMessage">
                        Confirm this payment action.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>

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
    function openVerifyModal(id, status, url) {
        const form = document.getElementById('verifyForm');
        const reasonWrapper = document.getElementById('rejectionReasonWrapper');
        const reasonInput = document.getElementById('rejectionReason');

        form.action = url;

        document.getElementById('verifyStatus').value = status;
        document.getElementById('verifyTitle').textContent =
            status === 'approved' ? 'Approve Payment' : 'Reject Payment';

        document.getElementById('verifyMessage').textContent =
            status === 'approved'
                ? 'Approving this payment will mark the related invoice as paid.'
                : 'Rejecting this payment will allow the member to submit another payment.';

        document.getElementById('verifySubmit').textContent =
            status === 'approved' ? 'Approve Payment' : 'Reject Payment';

        reasonWrapper.classList.toggle('d-none', status !== 'rejected');
        reasonInput.required = status === 'rejected';
        reasonInput.value = '';

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('verifyModal')
        ).show();
    }
</script>
@endpush
