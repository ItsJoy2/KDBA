@extends('layouts.app')

@section('content')
<div class="container-fluid">

    @include('components.global-feedback')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">My Membership Invoices</h4>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Invoice</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoices->firstItem() + $loop->index }}</td>

                                <td>
                                    <strong>{{ $invoice->invoice_number }}</strong>
                                    <div class="small text-muted">
                                        {{ $invoice->title }}
                                    </div>
                                </td>

                                <td>
                                    {{ $invoice->invoice_type === 'yearly_renewal'
                                        ? 'Yearly Renewal'
                                        : 'Registration' }}

                                    @if($invoice->cycle_key !== 'registration')
                                        <span class="text-muted">
                                            ({{ $invoice->cycle_key }})
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ number_format((float) $invoice->amount, 2) }}
                                </td>

                                <td>
                                    {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                                </td>

                                <td>
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-warning text-dark',
                                            'processing' => 'bg-info text-dark',
                                            'paid' => 'bg-success',
                                            'overdue' => 'bg-danger',
                                            'cancelled' => 'bg-secondary',
                                        ];
                                    @endphp

                                    <span class="badge {{ $statusClasses[$invoice->status] ?? 'bg-secondary' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if(in_array($invoice->status, ['pending', 'overdue'], true))
                                        @php
                                            $hasPendingPayment = $invoice->transactions
                                                ->contains(fn ($transaction) => in_array(
                                                    $transaction->status,
                                                    ['pending', 'approved'],
                                                    true
                                                ));
                                        @endphp

                                        @if(!$hasPendingPayment)
                                            <button type="button"
                                                    class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#memberPaymentModal{{ $invoice->id }}">
                                                <i class="bi bi-credit-card me-1"></i>
                                                Pay
                                            </button>
                                        @else
                                            <span class="text-muted small">
                                                Payment submitted
                                            </span>
                                        @endif
                                    @elseif($invoice->status === 'processing')
                                        <span class="text-info small">
                                            Awaiting verification
                                        </span>
                                    @elseif($invoice->status === 'paid')
                                        <span class="text-success small">
                                            <i class="bi bi-check-circle"></i> Paid
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>

                            @if(in_array($invoice->status, ['pending', 'overdue'], true))
                                @php
                                    $hasPendingPayment = $invoice->transactions
                                        ->contains(fn ($transaction) => in_array(
                                            $transaction->status,
                                            ['pending', 'approved'],
                                            true
                                        ));
                                @endphp

                                @if(!$hasPendingPayment)
                                    <div class="modal fade"
                                         id="memberPaymentModal{{ $invoice->id }}"
                                         tabindex="-1"
                                         aria-hidden="true">

                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">

                                                <form
                                                    action="{{ route('member.invoices.pay', $invoice) }}"
                                                    method="POST"
                                                    enctype="multipart/form-data"
                                                >
                                                    @csrf

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            Submit Payment
                                                        </h5>

                                                        <button type="button"
                                                                class="btn-close"
                                                                data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body">

                                                        <div class="alert alert-info">
                                                            <div>
                                                                <strong>Invoice:</strong>
                                                                {{ $invoice->invoice_number }}
                                                            </div>

                                                            <div>
                                                                <strong>Amount:</strong>
                                                                {{ number_format((float) $invoice->amount, 2) }}
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">
                                                                Payment Gateway
                                                                <span class="text-danger">*</span>
                                                            </label>

                                                            <select name="payment_gateway_id"
                                                                    class="form-select" required>
                                                                <option value="">
                                                                    Select gateway
                                                                </option>

                                                                @foreach($paymentGateways as $gateway)
                                                                    <option value="{{ $gateway->id }}"
                                                                        @selected(old('payment_gateway_id') == $gateway->id)>
                                                                        {{ $gateway->name }}
                                                                        — {{ $gateway->account_number }}
                                                                    </option>
                                                                @endforeach
                                                            </select>

                                                            @error('payment_gateway_id')
                                                                <div class="text-danger small mt-1">
                                                                    {{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">
                                                                Sender Mobile / Account
                                                                <span class="text-danger">*</span>
                                                            </label>

                                                            <input type="text"
                                                                   name="sender_mobile"
                                                                   class="form-control"
                                                                   maxlength="30"
                                                                   value="{{ old('sender_mobile') }}"
                                                                   required>

                                                            @error('sender_mobile')
                                                                <div class="text-danger small mt-1">
                                                                    {{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">
                                                                Transaction ID
                                                                <span class="text-danger">*</span>
                                                            </label>

                                                            <input type="text"
                                                                   name="trx_id"
                                                                   class="form-control"
                                                                   minlength="4"
                                                                   maxlength="100"
                                                                   pattern="[A-Za-z0-9_-]+"
                                                                   value="{{ old('trx_id') }}"
                                                                   required>

                                                            @error('trx_id')
                                                                <div class="text-danger small mt-1">
                                                                    {{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">
                                                                Receipt (Optional)
                                                            </label>

                                                            <input type="file"
                                                                   name="receipt"
                                                                   class="form-control"
                                                                   accept=".jpg,.jpeg,.png,.pdf">

                                                            <small class="text-muted">
                                                                JPG, JPEG, PNG or PDF; maximum 5 MB.
                                                            </small>

                                                            @error('receipt')
                                                                <div class="text-danger small mt-1">
                                                                    {{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>

                                                        @if($paymentGateways->isNotEmpty())
                                                            <div class="border rounded p-3">
                                                                <strong>Payment Instructions</strong>

                                                                @foreach($paymentGateways as $gateway)
                                                                    <div class="mt-2">
                                                                        <div>
                                                                            <strong>{{ $gateway->name }}</strong>
                                                                            — {{ $gateway->account_number }}
                                                                        </div>

                                                                        @if($gateway->instruction)
                                                                            <div class="small text-muted">
                                                                                {{ $gateway->instruction }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button"
                                                                class="btn btn-secondary"
                                                                data-bs-dismiss="modal">
                                                            Cancel
                                                        </button>

                                                        <button type="submit"
                                                                class="btn btn-primary">
                                                            Submit Payment
                                                        </button>
                                                    </div>

                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No invoices found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('layouts.partials.__pagination', ['items' => $invoices])

        </div>
    </div>
</div>

@if($errors->any() && old('trx_id'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const invoiceId = @json(old('invoice_id'));

            // Member payment route uses the invoice ID in the URL.
            // Reopen the modal for the invoice whose form was submitted.
            const modalElement = document.getElementById(
                'memberPaymentModal' + invoiceId
            );

            if (modalElement) {
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }
        });
    </script>
@endif
@endsection