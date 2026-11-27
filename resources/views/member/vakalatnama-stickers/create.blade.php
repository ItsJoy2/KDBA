@extends('layouts.app')

@section('title', 'Order Vakalatnama Stickers')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">Order Vakalatnama Stickers</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">New Sticker Order</h5>
            </div>

            <form method="POST" action="{{ route('member.vakalatnama.store') }}">
                @csrf

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <strong>Unit price:</strong>
                        ৳{{ number_format((float) $unitPrice, 2) }}
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input
                            type="number"
                            name="quantity"
                            id="quantity"
                            class="form-control"
                            min="1"
                            max="500"
                            value="{{ old('quantity', 1) }}"
                            required
                        >
                        @error('quantity') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <p class="text-muted small mb-0">
                        Your order will be processed after payment verification.
                        Sticker numbers become available only after payment approval.
                    </p>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        Submit Order
                    </button>
                    <a href="{{ route('member.vakalatnama.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection