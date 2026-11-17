
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="mb-0"><i class="bi bi-journal-text me-1"></i> Financial Transactions</h4>

        @if(auth()->user()->hasPermission('transactions.create'))
            <a href="{{ route('admin.transactions.manual.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add Income / Expense
            </a>
        @endif
    </div>

    @include('components.global-feedback')

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.finance.transactions.index') }}">
                <div class="row g-2">
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="income" @selected(request('type') === 'income')>Income</option>
                            <option value="expense" @selected(request('type') === 'expense')>Expense</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach(['pending', 'approved', 'rejected'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                    {{ $category->name }} ({{ ucfirst($category->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">From</label>
                        <input type="date" name="date_from" class="form-control"
                               value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">To</label>
                        <input type="date" name="date_to" class="form-control"
                               value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-1">
                        <button class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.finance.transactions.index') }}"
                           class="btn btn-outline-secondary" title="Reset">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
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
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transactions->firstItem() + $loop->index }}</td>
                                <td>{{ $transaction->created_at?->format('d M Y, h:i A') }}</td>
                                <td><code>{{ $transaction->trx_id }}</code></td>
                                <td>
                                    @if($transaction->category)
                                        <span class="badge {{ $transaction->category->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $transaction->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">Uncategorized</span>
                                    @endif
                                </td>
                                <td>
                                    {{ \Illuminate\Support\Str::limit($transaction->description ?? $transaction->invoice?->title ?? '—', 60) }}
                                </td>
                                <td class="text-end fw-semibold">
                                    ৳{{ number_format((float) $transaction->amount, 2) }}
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
                                <td>{{ $transaction->user?->full_name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('layouts.partials.__pagination', ['items' => $transactions])
        </div>
    </div>
</div>
@endsection
