
@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    {{-- App Content Header --}}
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Orders</h1>
                </div>

                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Orders
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    {{-- App Content --}}
    <div class="app-content">
        <div class="container-fluid">

            @include('components.global-feedback')

            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">

                        {{-- Card Header & Filters --}}
                        <div class="card-header">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-3">
                                    <h3 class="card-title">Order Directory</h3>
                                </div>

                                <div class="col-12 col-md-9">
                                    <form method="GET"
                                          action="{{ route('orders.index') }}"
                                          class="d-flex flex-wrap justify-content-md-end gap-2">

                                        <div class="input-group input-group-sm w-auto">
                                            <span class="input-group-text">
                                                <i class="bi bi-search" aria-hidden="true"></i>
                                            </span>
                                            <input
                                                type="search"
                                                name="search"
                                                value="{{ request('search') }}"
                                                class="form-control"
                                                placeholder="Order number or member name"
                                                aria-label="Search orders"
                                                style="width: 220px">
                                        </div>

                                        <select name="status"
                                                class="form-select form-select-sm w-auto"
                                                aria-label="Filter by order status">
                                            <option value="">All statuses</option>

                                            @foreach ([
                                                'pending',
                                                'processing',
                                                'approved',
                                                'completed',
                                                'cancelled',
                                            ] as $status)
                                                <option value="{{ $status }}"
                                                    @selected(request('status') === $status)>
                                                    {{ ucfirst($status) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="bi bi-funnel me-1" aria-hidden="true"></i>
                                            Filter
                                        </button>

                                        @if (request()->hasAny(['search', 'status', 'per_page']))
                                            <a href="{{ route('orders.index') }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Clear filters"
                                               aria-label="Clear filters">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>
                                        @endif

                                        @if (auth()->user()->hasPermission('products.buy'))
                                            <a href="{{ route('orders.create') }}"
                                               class="btn btn-sm btn-success">
                                                <i class="bi bi-plus-circle me-1"
                                                   aria-hidden="true"></i>
                                                Create Order
                                            </a>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Orders Table --}}
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle m-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Order</th>

                                            @if ($canViewAll)
                                                <th>Order Owner</th>
                                                <th>Created By</th>
                                            @endif

                                            <th class="text-end">Total Amount</th>
                                            <th>Payment Status</th>
                                            <th>Order Status</th>
                                            <th>Order Date</th>
                                            <th class="text-end pe-3">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($orders as $order)
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="fw-medium">
                                                        {{ $order->order_number }}
                                                    </div>
                                                    <small class="text-body-secondary">
                                                        #{{ $order->id }}
                                                    </small>
                                                </td>

                                                @if ($canViewAll)
                                                    <td>
                                                        <div class="fw-medium">
                                                            {{ $order->user?->full_name ?? 'Deleted user' }}
                                                        </div>
                                                        <small class="text-body-secondary">
                                                            {{ $order->user?->mobile ?? 'No mobile' }}
                                                        </small>
                                                    </td>

                                                    <td>
                                                        {{ $order->purchaser?->full_name ?? 'Unknown' }}
                                                    </td>
                                                @endif

                                                <td class="text-end text-nowrap">
                                                    {{ number_format((float) $order->total_amount, 2) }}
                                                </td>

                                                <td>
                                                    @php
                                                        $paymentColors = [
                                                            'paid' => 'success',
                                                            'pending' => 'warning',
                                                            'failed' => 'danger',
                                                            'refunded' => 'info',
                                                        ];

                                                        $paymentStatus = $order->payment_status;
                                                    @endphp

                                                    <span class="badge text-bg-{{ $paymentColors[$paymentStatus] ?? 'secondary' }}">
                                                        {{ ucwords(str_replace('_', ' ', $paymentStatus ?? 'unknown')) }}
                                                    </span>
                                                </td>

                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'warning',
                                                            'processing' => 'info',
                                                            'approved' => 'primary',
                                                            'completed' => 'success',
                                                            'cancelled' => 'danger',
                                                        ];

                                                        $orderStatus = $order->order_status;
                                                    @endphp

                                                    <span class="badge text-bg-{{ $statusColors[$orderStatus] ?? 'secondary' }}">
                                                        {{ ucfirst($orderStatus ?? 'unknown') }}
                                                    </span>
                                                </td>

                                                <td class="text-nowrap">
                                                    <span data-local="{{ $order->created_at?->toIso8601String() }}" data-format="date">
                                                        {{ $order->created_at?->format('d M Y') ?? '—' }}
                                                    </span>

                                                    <small class="d-block text-body-secondary">
                                                        <span data-local="{{ $order->created_at?->toIso8601String() }}" data-format="time">
                                                            {{ $order->created_at?->format('h:i A') ?? '' }}
                                                        </span>
                                                    </small>
                                                </td>

                                                <td class="text-end pe-3">
                                                    <a href="{{ route('orders.show', $order) }}"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="View order"
                                                       aria-label="View order {{ $order->order_number }}">
                                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                                        {{-- <span class="d-none d-lg-inline">View</span> --}}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $canViewAll ? 8 : 6 }}"
                                                    class="text-center py-5">
                                                    <i class="bi bi-receipt fs-2 d-block mb-2 text-body-secondary"></i>

                                                    <div class="fw-medium">
                                                        No orders found
                                                    </div>

                                                    <small class="text-body-secondary">
                                                        Try changing your search or filters.
                                                    </small>

                                                    @if (request()->hasAny(['search', 'status']))
                                                        <div>
                                                            <a href="{{ route('orders.index') }}"
                                                               class="btn btn-sm btn-outline-secondary mt-3">
                                                                Clear filters
                                                            </a>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Pagination --}}
                        @if ($orders->hasPages())
                            <div class="card-footer">
                                @include('layouts.partials.__pagination', ['items' => $orders,'label' => 'orders',
                                ])
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
