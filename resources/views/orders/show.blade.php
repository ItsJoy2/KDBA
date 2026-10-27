@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <strong>Order {{ $order->order_number }}</strong>
            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-secondary">
                Back
            </a>
        </div>

        <div class="card-body">
            <p><strong>Order owner:</strong> {{ $order->user?->full_name }}</p>
            <p><strong>Created by:</strong> {{ $order->purchaser?->full_name ?? 'Unknown' }}</p>
            <p><strong>Total:</strong> {{ number_format((float) $order->total_amount, 2) }}</p>
            <p><strong>Payment:</strong> {{ ucfirst($order->payment_status) }}</p>
            <p><strong>Status:</strong> {{ ucfirst($order->order_status) }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Order Items</div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Line Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? 'Deleted product' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format((float) $item->price, 2) }}</td>
                        <td>{{ number_format((float) $item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($order->serials->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">Allocated Serial Numbers</div>
            <div class="card-body">
                @foreach ($order->serials->groupBy('product_id') as $productId => $serials)
                    <h6>{{ $serials->first()->product?->name ?? 'Product' }}</h6>
                    <ul>
                        @foreach ($serials as $serial)
                            <li>{{ $serial->serial_number }}</li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>
    @endif

    @if (auth()->user()->hasPermission('orders.status_update'))
        <form method="POST" action="{{ route('orders.update-status', $order) }}"
              class="card card-body">
            @csrf
            @method('PATCH')

            <label class="form-label">Update Order Status</label>
            <div class="d-flex gap-2">
                <select name="order_status" class="form-select" required>
                    @foreach (['pending', 'processing', 'approved', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}"
                            @selected($order->order_status === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-primary">Update</button>
            </div>
        </form>
    @endif
</div>
@endsection