
@extends('layouts.app')

@section('title', 'Vakalatnama Sticker Settings')

@section('content')
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <h4>
                Vakalatnama Sticker Settings
            </h4>
        </div>
    </div>

    @include('components.global-feedback')

    <div class="card shadow-sm">

        <div class="card-header">
            <h5 class="mb-0">Sticker Configuration</h5>
        </div>

        <div class="card-body">

            <form method="POST"
                  action="{{ route('admin.vakalatnama-stickers.settings.update') }}">

                @csrf
                @method('PUT')

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Sticker Price (BDT)
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">৳</span>

                            <input type="number"
                                   name="price"
                                   class="form-control"
                                   value="{{ old('price', $price) }}"
                                   min="0.01"
                                   max="1000000"
                                   step="0.01"
                                   required>
                        </div>

                        <small class="text-muted">
                            Price per individual sticker.
                        </small>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Sticker Service Status
                        </label>

                        <select name="enabled"
                                class="form-select"
                                required>

                            <option value="1"
                                {{ (string) old('enabled', $enabled ? '1' : '0') === '1' ? 'selected' : '' }}>
                                Enabled
                            </option>

                            <option value="0"
                                {{ (string) old('enabled', $enabled ? '1' : '0') === '0' ? 'selected' : '' }}>
                                Disabled
                            </option>

                        </select>

                        <small class="text-muted">
                            Disable to prevent new sticker orders.
                        </small>

                    </div>

                </div>

                <hr>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    Save Settings
                </button>

            </form>

        </div>
    </div>

</div>
@endsection
