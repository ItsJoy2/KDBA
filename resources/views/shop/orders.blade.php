
@extends('layouts.app')

@section('title', 'My Order History')

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="mb-0">My Order History</h3>

                <a href="{{ route('shop.index') }}" class="btn btn-primary">
                    <i class="bi bi-grid me-1"></i>
                    Browse Products
                </a>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @include('components.global-feedback')

            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order Number</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Order Status</th>
                                <th>Date</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>

                                    <td>
                                        {{ $order->items->count() }}
                                    </td>

                                    <td>
                                        {{ number_format((float) $order->total_amount, 2) }}
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $order->created_at?->format('d M Y, h:i A') }}
                                    </td>

                                    <td>
                                        <a href="{{ route('orders.show', $order) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        You haven't placed any orders yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($orders->hasPages())
                    <div class="card-footer">
                        @include('layouts.partials.__pagination', [
                            'items' => $orders,
                            'label' => 'orders',
                        ])
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
