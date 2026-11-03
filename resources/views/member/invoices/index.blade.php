
@extends('layouts.app')

@section('title', 'My Membership Invoices')

@section('content')
<div class="container-fluid">
    @include('components.global-feedback')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">My Membership Invoices</h3>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Payment Details</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($invoices as $invoice)
                        @php
                            $payment = $invoice->latestTransaction;
                        @endphp

                        <tr>
                            <td>{{ $invoices->firstItem() + $loop->index }}</td>

                            <td>
                                <strong>{{ $invoice->invoice_number }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $invoice->title }}
                                </small>
                            </td>

                            <td>
                                {{ ucfirst(str_replace('_', ' ', $invoice->invoice_type)) }}
                                <br>
                                <small class="text-muted">{{ $invoice->cycle_key }}</small>
                            </td>

                            <td>
                                <strong>{{ number_format((float) $invoice->amount, 2) }}</strong>
                            </td>

                            <td>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>

                            <td>
                                @php
                                    $statusClass = match ($invoice->status) {
                                        'paid' => 'success',
                                        'processing' => 'info',
                                        'overdue' => 'danger',
                                        'cancelled' => 'secondary',
                                        default => 'warning',
                                    };
                                @endphp

                                <span class="badge bg-{{ $statusClass }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>

                            <td>
                                @if($payment)
                                    <div>
                                        <strong>Gateway:</strong>
                                        {{ $payment->paymentGateway?->name ?? '—' }}
                                    </div>

                                    <div>
                                        <strong>TrxID:</strong>
                                        {{ $payment->trx_id }}
                                    </div>

                                    <div>
                                        <strong>Payment:</strong>
                                        <span class="badge bg-{{
                                            $payment->status === 'approved'
                                                ? 'success'
                                                : ($payment->status === 'rejected' ? 'danger' : 'warning')
                                        }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </div>

                                    @if($payment->status === 'rejected' && $payment->rejection_reason)
                                        <small class="text-danger">
                                            {{ $payment->rejection_reason }}
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">No payment submitted</span>
                                @endif
                            </td>

                            <td>
                                @if(in_array($invoice->status, ['pending', 'overdue'], true))
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#paymentModal"
                                        data-url="{{ route('member.invoices.payment.submit', $invoice) }}"
                                        data-invoice="{{ $invoice->invoice_number }}"
                                        data-amount="{{ number_format((float) $invoice->amount, 2, '.', '') }}"
                                    >
                                        <i class="bi bi-credit-card"></i> Pay Now
                                    </button>
                                @elseif($invoice->status === 'processing')
                                    <span class="text-info">Awaiting verification</span>
                                @elseif($invoice->status === 'paid')
                                    <span class="text-success">
                                        <i class="bi bi-check-circle"></i> Paid
                                    </span>
                                @else
                                    <span class="text-muted">Unavailable</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $invoices->links('admin.layouts.partials.pagination-links') }}
        </div>
    </div>
</div>

{{-- Payment Submission Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="paymentForm" enctype="multipart/form-data">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit Payment</h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info">
                        <div>
                            <strong>Invoice:</strong>
                            <span id="paymentInvoice"></span>
                        </div>
                        <div>
                            <strong>Amount:</strong>
                            <span id="paymentAmount"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Gateway</label>
                        <select name="payment_gateway_id" class="form-select" required>
                            <option value="">Select Gateway</option>

                            @foreach($gateways as $gateway)
                                <option
                                    value="{{ $gateway->id }}"
                                    data-account="{{ $gateway->account_number }}"
                                    data-instruction="{{ $gateway->instruction }}"
                                >
                                    {{ $gateway->name }}
                                    @if($gateway->account_number)
                                        — {{ $gateway->account_number }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sender Mobile / Account</label>
                        <input type="text" name="sender_mobile"
                               class="form-control" maxlength="100" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Transaction ID (TrxID)</label>
                        <input type="text" name="trx_id"
                               class="form-control" maxlength="150"
                               pattern="[A-Za-z0-9_-]+"
                               title="Use letters, numbers, hyphens and underscores only"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Receipt (Optional)</label>
                        <input type="file" name="receipt"
                               class="form-control"
                               accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">
                            JPG, PNG or PDF. Maximum 5 MB.
                        </small>
                    </div>

                    <div class="alert alert-warning mb-0">
                        Please verify the payment details before submitting.
                        Duplicate TrxID submissions are not allowed.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>

                    <button type="submit" class="btn btn-primary">
                        Submit Payment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('paymentModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        document.getElementById('paymentForm').action = button.dataset.url;
        document.getElementById('paymentInvoice').textContent = button.dataset.invoice;
        document.getElementById('paymentAmount').textContent = button.dataset.amount;
    });
</script>
@endpush
