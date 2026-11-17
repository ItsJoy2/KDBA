
@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <h4 class="mb-0"><i class="bi bi-bar-chart-line me-1"></i> Income & Expense Report</h4>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.finance.reports.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-primary">
                            <i class="bi bi-filter me-1"></i> Generate Report
                        </button>
                        <a href="{{ route('admin.finance.reports.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Income</div>
                    <h3 class="text-success mb-0">৳{{ number_format((float) $income, 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-start border-danger border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Expense</div>
                    <h3 class="text-danger mb-0">৳{{ number_format((float) $expense, 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted small">Net Balance</div>
                    <h3 class="mb-0">৳{{ number_format((float) $netBalance, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Category-wise Breakdown</strong>
            <span class="text-muted small ms-2">{{ $dateFrom }} — {{ $dateTo }}</span>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Transactions</th>
                            <th class="text-end">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categoryBreakdown as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row->name }}</td>
                                <td>
                                    <span class="badge {{ $row->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($row->type) }}
                                    </span>
                                </td>
                                <td>{{ $row->transaction_count }}</td>
                                <td class="text-end fw-semibold">
                                    ৳{{ number_format((float) $row->total_amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No approved categorized transactions found for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
