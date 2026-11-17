
@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-wallet2 me-1"></i>
            My Transactions
        </h4>
    </div>

    @include('components.global-feedback')

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('member.transactions.index') }}">
                <div class="row g-2">

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach(['pending', 'approved', 'rejected'] as $status)
                                <option value="{{ $status }}"
                                    @selected(request('status') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date"
                               name="from"
                               class="form-control"
                               value="{{ request('from') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date"
                               name="to"
                               class="form-control"
                               value="{{ request('to') }}">
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filter
                        </button>

                        <a href="{{ route('member.transactions.index') }}"
                           class="btn btn-secondary">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Transactions --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Transaction History</h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transactions->firstItem() + $loop->index }}</td>

                                <td>
                                    {{ $transaction->created_at->format('d M Y') }}
                                </td>

                                <td>
                                    <small class="fw-semibold">
                                        {{ $transaction->trx_id }}
                                    </small>
                                    @if($transaction->invoice)
                                        <br>
                                        <small class="text-muted">
                                            Invoice: {{ $transaction->invoice->invoice_number }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    {{ $transaction->category?->name ?? 'Uncategorized' }}
                                </td>

                                <td>
                                    {{ $transaction->description ?? '—' }}
                                </td>

                                <td class="fw-semibold">
                                    {{ number_format((float) $transaction->amount, 2) }}
                                </td>

                                <td>
                                    @php
                                        $statusClass = match($transaction->status) {
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            @include('layouts.partials.__pagination', [
                'items' => $transactions
            ])
        </div>
    </div>

</div>
@endsection
