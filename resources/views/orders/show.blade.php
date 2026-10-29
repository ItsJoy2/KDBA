 @extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-receipt-cutoff text-primary me-2"></i>Order Details
            </h3>
            <p class="text-secondary mb-0">
                Order overview, products, serial numbers and status management.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary"
                    onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>

            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Orders
            </a>
        </div>
    </div>

    {{-- Order Summary --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-4 align-items-center">

                <div class="col-md-6 col-xl-4">
                    <div class="text-secondary small mb-2">ORDER NUMBER</div>
                    <h4 class="fw-bold text-primary mb-2">
                        {{ $order->order_number }}
                    </h4>
                    <div class="small text-secondary">
                        <i class="bi bi-calendar3 me-1"></i>
                        <span data-local="{{ $order->created_at?->toIso8601String() }}">
                            {{ $order->created_at?->format('d M Y, h:i A') ?? 'N/A' }}
                        </span>
                        
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="text-secondary small mb-2">TOTAL AMOUNT</div>
                    <h3 class="fw-bold mb-1">
                        {{ number_format((float) $order->total_amount, 2) }}
                    </h3>
                    <span class="text-secondary small">Order total</span>
                </div>

                <div class="col-xl-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-secondary small mb-2">PAYMENT</div>
                            @php
                                $paymentClass = match ($order->payment_status) {
                                    'paid', 'completed' => 'bg-success-subtle text-success',
                                    'pending' => 'bg-warning-subtle text-warning-emphasis',
                                    'failed', 'cancelled' => 'bg-danger-subtle text-danger',
                                    default => 'bg-secondary-subtle text-secondary',
                                };
                            @endphp

                            <span class="badge rounded-pill {{ $paymentClass }} px-3 py-2">
                                {{ ucfirst($order->payment_status ?? 'Unknown') }}
                            </span>
                        </div>

                        <div class="col-6">
                            <div class="text-secondary small mb-2">ORDER STATUS</div>
                            @php
                                $statusClass = match ($order->order_status) {
                                    'completed', 'approved' => 'bg-success-subtle text-success',
                                    'processing' => 'bg-info-subtle text-info-emphasis',
                                    'pending' => 'bg-warning-subtle text-warning-emphasis',
                                    'cancelled' => 'bg-danger-subtle text-danger',
                                    default => 'bg-secondary-subtle text-secondary',
                                };
                            @endphp

                            <span class="badge rounded-pill {{ $statusClass }} px-3 py-2">
                                {{ ucfirst($order->order_status ?? 'Unknown') }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Order Information --}}
    <div class="row g-4 mb-4">

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i>
                        Order Information
                    </h5>
                </div>

                <div class="card-body p-4">
                    <div class="d-flex gap-3 mb-4">
                        <div class="text-primary">
                            <i class="bi bi-person-circle fs-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-secondary small mb-1">Order Owner</div>
                            <div class="fw-semibold text-break">
                                {{ $order->user?->full_name ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="text-primary">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-secondary small mb-1">Created By</div>
                            <div class="fw-semibold text-break">
                                {{ $order->purchaser?->full_name ?? 'Unknown' }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="text-primary">
                            <i class="bi bi-hash fs-4"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-secondary small mb-1">Order Reference</div>
                            <div class="fw-semibold text-break">
                                {{ $order->order_number }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Items --}}
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-bag-check text-primary me-2"></i>
                        Order Items
                    </h5>

                    {{-- <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                        {{ $order->items->count() }}
                        {{ $order->items->count() === 1 ? 'Item' : 'Items' }}
                    </span> --}}
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end pe-3">Line Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($order->items as $item)
                                <tr>
                                    <td class="ps-3 text-secondary">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        <span class="fw-semibold">
                                            {{ $item->product?->name ?? 'Deleted product' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="border px-3 my-1">
                                            {{ $item->quantity }}
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        {{ number_format((float) $item->price, 2) }}
                                    </td>
                                    <td class="text-end pe-3 fw-semibold text-nowrap">
                                        {{ number_format((float) $item->price * $item->quantity, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-secondary d-block mb-2"></i>
                                        <span class="text-secondary">No order items found.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if ($order->items->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold py-3">
                                        Total Amount
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-primary py-3" >
                                        {{ number_format((float) $order->total_amount, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Allocated Serial Numbers --}}
    @if ($order->serials->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-upc-scan text-primary me-2"></i>
                    Allocated Serial Numbers
                </h5>

                {{-- <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                    {{ $order->serials->count() }} Serials
                </span> --}}
            </div>

            <div class="card-body p-4">
                @foreach ($order->serials->groupBy('product_id') as $productId => $serials)
                    <div class="{{ !$loop->last ? 'mb-4 pb-3 border-bottom' : '' }}">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-box-seam text-secondary me-2"></i>
                            {{ $serials->first()->product?->name ?? 'Product' }}
                        </h6>

                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($serials as $serial)
                                <span class="badge bg-light text-dark border px-3 py-2">
                                    <i class="bi bi-hash text-primary me-1"></i>{{ $serial->serial_number }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Update Order Status --}}
    @if (auth()->user()->hasPermission('orders.status_update'))
        <div class="card border-0 shadow-sm">
            <div class="card-header py-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-arrow-repeat text-primary me-2"></i>
                    Update Order Status
                </h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('orders.update-status', $order) }}">
                    @csrf
                    @method('PATCH')

                    <div class="row g-3 align-items-end">
                        <div class="col-md-6 col-lg-4">
                            <label for="order_status" class="form-label fw-semibold">
                                Order Status
                            </label>

                            <select name="order_status" id="order_status"
                                    class="form-select @error('order_status') is-invalid @enderror"
                                    required>
                                @foreach (['pending', 'processing', 'approved', 'completed', 'cancelled'] as $status)
                                    <option value="{{ $status }}"
                                        @selected(old('order_status', $order->order_status) === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>

                            @error('order_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check2-circle me-1"></i>
                                Update Status
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection