@extends('layouts.app')

@section('title', 'Vakalatnama Sticker Settings')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">Vakalatnama Sticker Settings</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">General Configuration</h5>
            </div>

            <form method="POST" action="{{ route('admin.vakalatnama.settings.update') }}">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="mb-3">
                        <label for="unit_price" class="form-label">Sticker Unit Price (৳)</label>
                        <input
                            type="number"
                            name="unit_price"
                            id="unit_price"
                            class="form-control"
                            min="0.01"
                            step="0.01"
                            value="{{ old('unit_price', $settings['unit_price'] ?? 0) }}"
                            required
                        >
                        @error('unit_price') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check form-switch">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            role="switch"
                            id="enabled"
                            name="enabled"
                            value="1"
                            @checked(old('enabled', $settings['enabled'] ?? false))
                        >
                        <label class="form-check-label" for="enabled">
                            Enable Vakalatnama Sticker Orders
                        </label>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection