@csrf

<div class="mb-3">
    <label class="form-label">Product Name</label>
    <input name="name" class="form-control" required maxlength="150"
           value="{{ old('name', $product->name ?? '') }}">
    @error('name') <div class="text-danger">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Slug (optional)</label>
    <input name="slug" class="form-control"
           value="{{ old('slug', $product->slug ?? '') }}">
    @error('slug') <div class="text-danger">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control">{{ old('description', $product->description ?? '') }}</textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Buy Price</label>
        <input type="number" step="0.01" min="0" name="buy_price"
               class="form-control" required
               value="{{ old('buy_price', $product->buy_price ?? '0.00') }}">
        @error('buy_price') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Selling Price</label>
        <input type="number" step="0.01" min="0" name="selling_price"
               class="form-control" required
               value="{{ old('selling_price', $product->selling_price ?? '0.00') }}">
        @error('selling_price') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Stock Quantity</label>
    <input type="number" min="0" name="stock_quantity" class="form-control"
           required value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}">
    @error('stock_quantity') <div class="text-danger">{{ $message }}</div> @enderror
</div>

<div class="form-check mb-3">
    <input type="hidden" name="track_serials" value="0">
    <input type="checkbox" class="form-check-input" id="track_serials"
           name="track_serials" value="1"
           @checked(old('track_serials', $product->track_serials ?? false))>
    <label class="form-check-label" for="track_serials">
        Track serial numbers
    </label>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Serial Start</label>
        <input type="number" min="0" name="serial_start" class="form-control"
               value="{{ old('serial_start', $product->serial_start ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Serial End</label>
        <input type="number" min="0" name="serial_end" class="form-control"
               value="{{ old('serial_end', $product->serial_end ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Next Serial</label>
        <input type="number" min="0" name="current_serial" class="form-control"
               value="{{ old('current_serial', $product->current_serial ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
        <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>
            Active
        </option>
        <option value="inactive" @selected(old('status', $product->status ?? '') === 'inactive')>
            Inactive
        </option>
    </select>
</div>

<button class="btn btn-primary" type="submit">Save Product</button>
<a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>