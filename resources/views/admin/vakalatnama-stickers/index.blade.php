@extends('layouts.app')

@section('title', 'Vakalatnama Sticker Orders')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Vakalatnama Sticker Orders</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Sticker Orders</li>
                </ol>
            </div>
        </div>
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

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">All Orders</h5>

                @can('vakalatnama.create')
                    <a href="{{ route('admin.vakalatnama.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Create Order
                    </a>
                @endcan
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('admin.vakalatnama.index') }}" class="row g-2 mb-3">
                    <div class="col-md-5">
                        <input
                            type="search"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Search order, member name or membership number"
                        >
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All order statuses</option>
                            @foreach (['pending', 'processing', 'approved', 'rejected', 'completed', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-secondary" type="submit">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.vakalatnama.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order No.</th>
                                <th>Member</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Order Status</th>
                                <th>Date</th>
                                <th width="90">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $order->user?->name ?? 'N/A' }}</div>
                                        <small class="text-muted">
                                            {{ $order->user?->membership_number ?? '' }}
                                        </small>
                                    </td>
                                    <td>{{ $order->quantity }}</td>
                                    <td>৳{{ number_format((float) $order->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge text-bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-secondary">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at?->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('admin.vakalatnama.show', $order) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View order">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No sticker orders found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $orders->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection