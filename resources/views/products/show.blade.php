@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <strong>{{ $product->name }}</strong>
            <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary">
                Back
            </a>
        </div>
        <div class="card-body">
            <p>{{ $product->description }}</p>
            <p>Buy price: {{ number_format((float) $product->buy_price, 2) }}</p>
            <p>Selling price: {{ number_format((float) $product->selling_price, 2) }}</p>
            <p>Stock: {{ $product->stock_quantity }}</p>
            <p>Serial tracking: {{ $product->track_serials ? 'Enabled' : 'Disabled' }}</p>
            <p>Status: {{ ucfirst($product->status) }}</p>

            @if (auth()->user()->hasPermission('products.edit'))
                <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">
                    Edit Product
                </a>
            @endif

            @if (auth()->user()->hasPermission('products.buy') && $product->status === 'active' && $product->stock_quantity > 0)
                <a href="{{ route('orders.create') }}" class="btn btn-primary">
                    Create Order
                </a>
            @endif
        </div>
    </div>
</div>
@endsection