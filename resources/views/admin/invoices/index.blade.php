
@extends('layouts.app')

@section('title', 'Membership Invoices')

@section('content')
<div class="container-fluid">
    @include('components.global-feedback')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Membership Invoices</h3>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice Number</th>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Cycle</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoices->firstItem() + $loop->index }}</td>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>
                                <strong>{{ $invoice->user->full_name ?? 'N/A' }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $invoice->user->membership_number ?? '—' }}
                                </small>
                            </td>
                            <td>
                                {{ ucfirst(str_replace('_', ' ', $invoice->invoice_type)) }}
                            </td>
                            <td>{{ $invoice->cycle_key }}</td>
                            <td>{{ number_format((float) $invoice->amount, 2) }}</td>
                            <td>
                                {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                            </td>
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
@endsection
