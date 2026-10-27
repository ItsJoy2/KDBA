@extends('layouts.app')

@section('title', 'Products')

@section('content')
    {{-- App Content Header --}}
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Products</h1>
                </div>

                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Products
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

            {{-- Global Feedback Messages --}}
            @include('components.global-feedback')

            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">

                        {{-- Card Header --}}
                        <div class="card-header">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-4">
                                    <h3 class="card-title">Product Directory</h3>
                                </div>

                                <div class="col-12 col-md-8">
                                    <form method="GET"
                                          action="{{ route('products.index') }}"
                                          class="d-flex flex-wrap justify-content-md-end gap-2">

                                        {{-- Search --}}
                                        <div class="input-group input-group-sm w-auto">
                                            <span class="input-group-text">
                                                <i class="bi bi-search" aria-hidden="true"></i>
                                            </span>

                                            <input
                                                type="search"
                                                name="search"
                                                value="{{ request('search') }}"
                                                class="form-control"
                                                placeholder="Search products..."
                                                aria-label="Search products"
                                                style="width: 220px">
                                        </div>

                                        {{-- Status Filter --}}
                                        <select name="status"
                                                class="form-select form-select-sm w-auto"
                                                aria-label="Filter by product status">
                                            <option value="">All statuses</option>

                                            @foreach (['active', 'inactive'] as $status)
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

                                        {{-- Clear Filters --}}
                                        @if (request()->hasAny(['search', 'status']))
                                            <a href="{{ route('products.index') }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Clear filters"
                                               aria-label="Clear filters">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>
                                        @endif

                                        {{-- Create Product --}}
                                        @if (auth()->user()->hasPermission('products.create'))
                                            <a href="{{ route('products.create') }}"
                                               class="btn btn-sm btn-success">
                                                <i class="bi bi-plus-circle me-1"
                                                   aria-hidden="true"></i>
                                                Add Product
                                            </a>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle m-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Product</th>
                                            <th>Selling Price</th>
                                            <th>Stock</th>
                                            <th>Serial Tracking</th>
                                            <th>Status</th>
                                            <th class="text-end pe-3">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($products as $product)
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="fw-medium">
                                                        {{ $product->name }}
                                                    </div>

                                                    <small class="text-body-secondary">
                                                        {{ $product->slug }}
                                                    </small>
                                                </td>

                                                <td>
                                                    {{ number_format((float) $product->selling_price, 2) }}
                                                </td>

                                                <td>
                                                    {{ number_format((int) $product->stock_quantity) }}
                                                </td>

                                                <td>
                                                    @if ($product->track_serials)
                                                        <span class="badge text-bg-success">
                                                            Enabled
                                                        </span>
                                                    @else
                                                        <span class="badge text-bg-secondary">
                                                            Disabled
                                                        </span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <span class="badge text-bg-{{ $product->status === 'active' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($product->status) }}
                                                    </span>
                                                </td>

                                                <td class="text-end pe-3">
                                                    <div class="btn-group btn-group-sm gap-2"
                                                         role="group"
                                                         aria-label="Product actions">

                                                        {{-- View --}}
                                                        @if (auth()->user()->hasPermission('products.view'))
                                                            <a href="{{ route('products.show', $product) }}"
                                                               class="btn btn-outline-primary"
                                                               title="View"
                                                               aria-label="View {{ $product->name }}">
                                                                <i class="bi bi-eye"
                                                                   aria-hidden="true"></i>
                                                            </a>
                                                        @endif

                                                        {{-- Edit --}}
                                                        @if (auth()->user()->hasPermission('products.edit'))
                                                            <a href="{{ route('products.edit', $product) }}"
                                                               class="btn btn-outline-secondary"
                                                               title="Edit"
                                                               aria-label="Edit {{ $product->name }}">
                                                                <i class="bi bi-pencil"
                                                                   aria-hidden="true"></i>
                                                            </a>
                                                        @endif

                                                        {{-- Delete --}}
                                                        @if (auth()->user()->hasPermission('products.delete'))
                                                            <form method="POST"
                                                                  action="{{ route('products.destroy', $product) }}"
                                                                  onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit"
                                                                        class="btn btn-outline-danger"
                                                                        title="Delete"
                                                                        aria-label="Delete {{ $product->name }}">
                                                                    <i class="bi bi-trash"
                                                                       aria-hidden="true"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <i class="bi bi-box-seam fs-2 d-block mb-2 text-body-secondary"></i>

                                                    <div class="fw-medium">
                                                        No products found
                                                    </div>

                                                    <small class="text-body-secondary">
                                                        Try changing your search or filters.
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Reusable Pagination --}}
                        <div class="card-footer">
                            @include('layouts.partials.__pagination', ['items' => $products,'label' => 'products', ])
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
