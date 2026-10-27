
@extends('layouts.app')

@section('title', 'Product Showcase')

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h3 class="mb-1">Product Showcase</h3>
                    <p class="text-muted mb-0">Browse available products and items.</p>
                </div>

                <a href="{{ route('shop.orders') }}" class="btn btn-outline-primary">
                    <i class="bi bi-clock-history me-1"></i>
                    My Order History
                </a>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @include('components.global-feedback')

            <div class="row g-3">
                @forelse ($products as $product)
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3 d-flex align-items-center justify-content-center bg-light rounded"
                                     style="height: 130px;">
                                    <i class="bi bi-box-seam text-secondary" style="font-size: 3rem;"></i>
                                </div>

                                <h5 class="card-title">{{ $product->name }}</h5>

                                @if ($product->description)
                                    <p class="text-muted small">
                                        {{ \Illuminate\Support\Str::limit($product->description, 100) }}
                                    </p>
                                @endif

                                <div class="mb-2">
                                    <span class="fw-bold fs-5">
                                        {{ number_format((float) $product->selling_price, 2) }}
                                    </span>
                                    <span class="text-muted">BDT</span>
                                </div>

                                <p class="small text-muted mb-3">
                                    Available stock: {{ $product->stock_quantity }}
                                </p>

                                <div class="mt-auto">
                                    @if ($canOrder)
                                        <a href="{{ route('orders.create', ['product_id' => $product->id]) }}"
                                           class="btn btn-primary w-100">
                                            <i class="bi bi-cart-plus me-1"></i>
                                            Order Now
                                        </a>
                                    @else
                                        <button type="button"
                                                class="btn btn-secondary w-100"
                                                disabled>
                                            Ordering Not Available
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-box-seam fs-1 text-muted"></i>
                                <h5 class="mt-3">No Products Available</h5>
                                <p class="text-muted mb-0">
                                    There are currently no products in stock.
                                </p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($products->hasPages())
                <div class="mt-4">
                    @include('layouts.partials.__pagination', [
                        'items' => $products,
                        'label' => 'products',
                    ])
                </div>
            @endif
        </div>
    </div>
@endsection
