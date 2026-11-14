
@php
    $isEdit = $gateway !== null;
    $prefix = $prefix ?? 'gateway';
@endphp

<div class="row g-3">

    <div class="col-md-6">
        <label class="form-label" for="{{ $prefix }}_name">
            Gateway Name <span class="text-danger">*</span>
        </label>
        <input
            type="text"
            id="{{ $prefix }}_name"
            name="name"
            class="form-control"
            value="{{ old('name', $gateway?->name) }}"
            maxlength="100"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label" for="{{ $prefix }}_type">
            Gateway Type <span class="text-danger">*</span>
        </label>
        <input
            type="text"
            id="{{ $prefix }}_type"
            name="type"
            class="form-control"
            value="{{ old('type', $gateway?->type) }}"
            maxlength="50"
            placeholder="e.g. bKash, Nagad, Bank"
            required
        >
    </div>

    <div class="col-md-12">
        <label class="form-label" for="{{ $prefix }}_account_number">
            Account Number <span class="text-danger">*</span>
        </label>
        <input
            type="text"
            id="{{ $prefix }}_account_number"
            name="account_number"
            class="form-control"
            value="{{ old('account_number', $gateway?->account_number) }}"
            maxlength="100"
            required
        >
    </div>

    <div class="col-md-12">
        <label class="form-label" for="{{ $prefix }}_instruction">
            Payment Instructions
        </label>
        <textarea
            id="{{ $prefix }}_instruction"
            name="instruction"
            class="form-control"
            rows="3"
            maxlength="5000"
        >{{ old('instruction', $gateway?->instruction) }}</textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="{{ $prefix }}_sort_order">
            Sort Order
        </label>
        <input
            type="number"
            id="{{ $prefix }}_sort_order"
            name="sort_order"
            class="form-control"
            value="{{ old('sort_order', $gateway?->sort_order ?? 0) }}"
            min="0"
            max="9999"
        >
    </div>

    <div class="col-md-6">
        <label class="form-label d-block">Gateway Status</label>
        <div class="form-check form-switch mt-2">
            <input
                type="hidden"
                name="is_active"
                value="0"
            >
            <input
                class="form-check-input"
                type="checkbox"
                role="switch"
                id="{{ $prefix }}_is_active"
                name="is_active"
                value="1"
                @checked((bool) old('is_active', $gateway?->is_active ?? true))
            >
            <label class="form-check-label" for="{{ $prefix }}_is_active">
                Active
            </label>
        </div>
    </div>

</div>
