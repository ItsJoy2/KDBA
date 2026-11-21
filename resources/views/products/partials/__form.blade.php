
@csrf

{{-- Basic Information --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-info-circle me-2 text-primary"></i>
            Basic Information
        </h6>
    </div>

    <div class="card-body p-4">
        <div class="row g-4">

            <div class="col-12">
                <label for="name" class="form-label fw-medium">
                    Product Name <span class="text-danger">*</span>
                </label>

                <input type="text" id="name" name="name"
                       class="form-control form-control-lg @error('name') is-invalid @enderror"
                       required maxlength="150"
                       placeholder="e.g. Vakalatnama, Stamp, etc."
                       value="{{ old('name', $product->name ?? '') }}">

                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="slug" class="form-label fw-medium">
                    Slug <span class="text-muted small">(auto/edit)</span>
                </label>

                <div class="input-group">
                    <span class="input-group-text text-muted">/</span>

                    <input type="text" id="slug" name="slug"
                           class="form-control @error('slug') is-invalid @enderror"
                           value="{{ old('slug', $product->slug ?? '') }}">

                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label fw-medium">
                    Status <span class="text-danger">*</span>
                </label>

                <select id="status" name="status"
                        class="form-select @error('status') is-invalid @enderror"
                        required>
                    <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>
                        Active
                    </option>
                    <option value="inactive" @selected(old('status', $product->status ?? '') === 'inactive')>
                        Inactive
                    </option>
                </select>

                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label fw-medium">
                    Description
                </label>

                <textarea id="description" name="description" rows="4"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="Enter product description...">{{ old('description', $product->description ?? '') }}</textarea>

                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>
</div>

{{-- Pricing & Stock --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-cash-stack me-2 text-success"></i>
            Pricing & Stock
        </h6>
    </div>

    <div class="card-body p-4">
        <div class="row g-4">

            {{-- Selling Price --}}
            <div class="col-md-4">
                <label for="selling_price" class="form-label fw-medium">
                    Selling Price <span class="text-danger">*</span>
                </label>

                <div class="input-group">
                    <span class="input-group-text">৳</span>

                    <input type="number" id="selling_price" name="selling_price"
                           step="0.01" min="0"
                           class="form-control @error('selling_price') is-invalid @enderror"
                           value="{{ old('selling_price', $product->selling_price ?? '0.00') }}">

                </div>
            </div>

            {{-- Stock Type --}}
            <div class="col-md-4">
                <label for="stock_type" class="form-label fw-medium">
                    Stock Type <span class="text-danger">*</span>
                </label>

                <select id="stock_type" name="stock_type"
                        class="form-select @error('stock_type') is-invalid @enderror"
                        required>
                    <option value="limited"
                        @selected(old('stock_type', $product->stock_type ?? 'limited') === 'limited')>
                        Limited Stock
                    </option>

                    <option value="unlimited"
                        @selected(old('stock_type', $product->stock_type ?? 'limited') === 'unlimited')>
                        Unlimited Stock
                    </option>
                </select>

                @error('stock_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Stock Quantity --}}
            <div class="col-md-4" id="stockQuantityWrapper">
                <label for="stock_quantity" class="form-label fw-medium">
                    Stock Quantity <span class="text-danger">*</span>
                </label>

                <input type="number" id="stock_quantity" name="stock_quantity"
                       min="0"
                       class="form-control @error('stock_quantity') is-invalid @enderror"
                       value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}">

                @error('stock_quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <small class="text-muted" id="stockHelp">
                    Available product quantity.
                </small>
            </div>

            {{-- Unlimited Stock Information --}}
            <div class="col-md-4 d-none" id="unlimitedStockInfo">
                <label class="form-label fw-medium">Stock Availability</label>

                <div class="form-control text-success fw-semibold">
                    <i class="bi bi-infinity me-1"></i>
                    Unlimited Stock
                </div>

                <small class="text-muted">
                    No stock quantity limit will be applied.
                </small>
            </div>

        </div>
    </div>
</div>

{{-- Serial Tracking --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-upc-scan me-2 text-warning"></i>
            Serial Tracking
        </h6>

        <div class="form-check form-switch mb-0">
            <input type="hidden" name="track_serials" value="0">

            <input type="checkbox" class="form-check-input"
                   id="track_serials" name="track_serials"
                   value="1" role="switch"
                   @checked(old('track_serials', $product->track_serials ?? false))>

            <label class="form-check-label small" for="track_serials">
                Enable
            </label>
        </div>
    </div>

    <div class="card-body p-4" id="serialFields">
        <div class="row g-4">

            <div class="col-md-4">
                <label for="serial_start" class="form-label fw-medium">
                    Serial Start
                </label>

                <input type="number" id="serial_start" name="serial_start"
                       min="0" class="form-control"
                       value="{{ old('serial_start', $product->serial_start ?? '') }}">
            </div>

            <div class="col-md-4">
                <label for="serial_end" class="form-label fw-medium">
                    Serial End
                </label>

                <input type="number" id="serial_end" name="serial_end"
                       min="0" class="form-control"
                       value="{{ old('serial_end', $product->serial_end ?? '') }}">
            </div>

            <div class="col-md-4">
                <label for="current_serial" class="form-label fw-medium">
                    Next Serial
                </label>

                <input type="number" id="current_serial" name="current_serial"
                       min="0" class="form-control"
                       value="{{ old('current_serial', $product->current_serial ?? '') }}">
            </div>

        </div>
    </div>
</div>

{{-- Actions --}}
<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('products.index') }}"
       class="btn btn-outline-secondary px-4">
        Cancel
    </a>

    <button class="btn btn-primary px-4" type="submit">
        <i class="bi bi-check2-circle me-1"></i>
        Save Product
    </button>
</div>

@push('scripts')
<script>
(function () {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    const stockType = document.getElementById('stock_type');
    const stockQuantity = document.getElementById('stock_quantity');
    const stockQuantityWrapper = document.getElementById('stockQuantityWrapper');
    const unlimitedStockInfo = document.getElementById('unlimitedStockInfo');

    const serialToggle = document.getElementById('track_serials');
    const serialWrap = document.getElementById('serialFields');
    const serialInputs = serialWrap.querySelectorAll('input');

    // Slug generation
    let slugTouched = slugInput.value.trim() !== '';

    function makeSlug(text) {
        return text
            .normalize('NFC')
            .trim()
            .toLowerCase()
            .replace(/[^\p{L}\p{M}\p{N}\s\-\u200C\u200D]/gu, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    nameInput.addEventListener('input', function () {
        if (!slugTouched) {
            slugInput.value = makeSlug(nameInput.value);
        }
    });

    slugInput.addEventListener('input', function () {
        slugTouched = slugInput.value.trim() !== '';
    });

    slugInput.addEventListener('blur', function () {
        slugInput.value = makeSlug(
            slugTouched ? slugInput.value : nameInput.value
        );
    });

    // Stock type handling
    function syncStockType() {
        const isUnlimited = stockType.value === 'unlimited';

        stockQuantityWrapper.classList.toggle('d-none', isUnlimited);
        unlimitedStockInfo.classList.toggle('d-none', !isUnlimited);

        stockQuantity.disabled = isUnlimited;
        stockQuantity.required = !isUnlimited;

        if (isUnlimited) {
            stockQuantity.value = 0;
        }
    }

    stockType.addEventListener('change', syncStockType);
    syncStockType();

    // Serial tracking handling
    function syncSerial() {
        const enabled = serialToggle.checked;

        serialWrap.classList.toggle('opacity-50', !enabled);

        serialInputs.forEach(function (input) {
            input.disabled = !enabled;
        });
    }

    serialToggle.addEventListener('change', syncSerial);
    syncSerial();

})();
</script>
@endpush
