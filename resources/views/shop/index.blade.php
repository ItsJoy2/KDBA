
@extends('layouts.app')

@section('title', 'Product Showcase')

@section('content')
    @include('components.global-feedback')

    <div class="container-fluid py-3">

        {{-- Page Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h2 class="mb-1">Product Showcase</h2>
                <p class="text-muted mb-0">
                    Available products and pricing
                </p>
            </div>

            @if (Route::has('orders.history'))
                <a href="{{ route('orders.history') }}"
                   class="btn btn-outline-primary">
                    <i class="bi bi-receipt me-1"></i>
                    My Orders
                </a>
            @endif
        </div>

        {{-- Product List --}}
        <div class="row g-3">

            @forelse ($products as $product)
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">

                    <div class="card h-100 shadow-sm border-0">

                        <div class="card-body d-flex flex-column">

                            {{-- Product Name --}}
                            <h5 class="card-title text-center fw-bold mb-3">
                                {{ $product->name }}
                            </h5>

                            {{-- Product Description --}}
                            @if ($product->description)
                                <p class="text-muted small mb-3">
                                    {{ $product->description }}
                                </p>
                            @endif

                            {{-- Price --}}
                            <div class="text-center mb-3">
                                <div class="text-muted small">Price</div>

                                <div class="fs-4 fw-bold text-primary">
                                    ৳{{ number_format((float) $product->selling_price, 2) }}
                                </div>
                            </div>

                            {{-- Stock --}}
                            <div class="text-center mb-3">
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    Available Stock:
                                    {{ number_format((int) $product->stock_quantity) }}
                                </span>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-auto">

                                @if ($canOrder)
                                    <button
                                        type="button"
                                        class="btn btn-primary w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#orderModal"
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->selling_price }}"
                                        data-product-stock="{{ $product->stock_quantity }}"
                                        data-product-track-serials="{{ (int) ($product->track_serials ?? false) }}"
                                        data-product-current-serial="{{ $product->current_serial ?? '' }}"
                                        data-product-serial-end="{{ $product->serial_end ?? '' }}"
                                    >
                                        <i class="bi bi-cart-plus me-1"></i>
                                        Order Now
                                    </button>
                                @else
                                    <button type="button"
                                            class="btn btn-outline-secondary w-100"
                                            disabled>
                                        <i class="bi bi-lock me-1"></i>
                                        Ordering Not Available
                                    </button>
                                @endif

                            </div>

                        </div>
                    </div>

                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        No products are currently available.
                    </div>
                </div>
            @endforelse

        </div>

        {{-- Pagination --}}
        @if ($products->hasPages())
            <div class="mt-4">
                @include('layouts.partials.__pagination', [
                    'items' => $products,
                    'label' => 'products',
                ])
            </div>
        @endif

    </div>

    @include('shop.partials.__order-model')

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const orderModal = document.getElementById('orderModal');

    if (!orderModal) {
        return;
    }

    const productIdInput = document.getElementById('order_product_id');
    const productNameInput = document.getElementById('order_product_name');
    const productPriceInput = document.getElementById('order_product_price');
    const quantityInput = document.getElementById('order_quantity');
    const availableStock = document.getElementById('order_available_stock');
    const totalAmount = document.getElementById('order_total_amount');
    const serialWrapper = document.getElementById('serialInfoWrapper');
    const serialInputsContainer = document.getElementById('serialInputsContainer');
    const confirmButton = document.getElementById('confirmOrderButton');

    let unitPrice = 0;
    let stockQuantity = 0;
    let tracksSerials = false;
    let selectedProductId = null;

    function calculateTotal() {
        const quantity = parseInt(quantityInput.value, 10) || 0;

        totalAmount.textContent = (unitPrice * quantity).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        confirmButton.disabled =
            quantity < 1 ||
            quantity > stockQuantity;

        renderSerialInputs();
    }

    function renderSerialInputs() {
        serialInputsContainer.replaceChildren();

        if (!tracksSerials) {
            serialWrapper.classList.add('d-none');
            return;
        }

        serialWrapper.classList.remove('d-none');

        const quantity = parseInt(quantityInput.value, 10) || 0;

        for (let i = 0; i < quantity; i++) {
            const wrapper = document.createElement('div');
            wrapper.className = 'mb-3';

            const label = document.createElement('label');
            label.className = 'form-label';
            label.htmlFor = `order_serial_${i}`;
            label.textContent = `Serial Number ${i + 1}`;

            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.id = `order_serial_${i}`;

            // Match the nested items structure used by the Create Order form.
            input.name = `items[${selectedProductId}][serials][${i}]`;

            input.placeholder = `Enter serial number ${i + 1}`;
            input.autocomplete = 'off';
            input.required = true;

            wrapper.append(label, input);
            serialInputsContainer.appendChild(wrapper);
        }
    }

    orderModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        if (!button) {
            return;
        }

        selectedProductId = button.dataset.productId;

        productIdInput.name = `items[${selectedProductId}][product_id]`;
        quantityInput.name = `items[${selectedProductId}][quantity]`;

        productIdInput.value = selectedProductId;

        const productName = button.dataset.productName || '';
        const price = parseFloat(button.dataset.productPrice) || 0;
        const stock = parseInt(button.dataset.productStock, 10) || 0;

        tracksSerials =
            button.dataset.productTrackSerials === '1';

        productIdInput.value = selectedProductId;
        productNameInput.value = productName;
        productPriceInput.value = price.toFixed(2);

        unitPrice = price;
        stockQuantity = stock;

        availableStock.textContent = stock.toLocaleString('en-US');

        quantityInput.min = 1;
        quantityInput.max = stock;
        quantityInput.value = 1;

        // Never auto-fill serials from current_serial.
        calculateTotal();
    });

    quantityInput.addEventListener('input', calculateTotal);
    quantityInput.addEventListener('change', calculateTotal);

    orderModal.addEventListener('hidden.bs.modal', function () {
        serialInputsContainer.replaceChildren();
        serialWrapper.classList.add('d-none');

        tracksSerials = false;
        selectedProductId = null;
    });
});
</script>
@endpush