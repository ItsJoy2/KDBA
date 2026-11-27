@extends('layouts.app')

@section('title', 'My Vakalatnama Sticker Orders')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">My Vakalatnama Sticker Orders</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Orders</h5>
                <a href="{{ route('member.vakalatnama.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> New Order
                </a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order No.</th>
                                <th>Quantity</th>
                                <th>Total Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th width="80">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->quantity }}</td>
                                    <td>৳{{ number_format((float) $order->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge text-bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($order->status) }}</td>
                                    <td>{{ $order->created_at?->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('member.vakalatnama.show', $order) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        You have not placed any sticker orders yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection