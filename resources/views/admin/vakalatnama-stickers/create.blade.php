@extends('layouts.app')

@section('title', 'Create Vakalatnama Sticker Order')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">Create Sticker Order</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Information</h5>
            </div>

            <form method="POST" action="{{ route('admin.vakalatnama.store') }}">
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

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="user_id" class="form-label">Select Member</label>
                            <select name="user_id" id="user_id" class="form-select" required>
                                <option value="">Choose member</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}" @selected(old('user_id') == $member->id)>
                                        {{ $member->name }}
                                        @if ($member->membership_number)
                                            — {{ $member->membership_number }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
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
                    </div>
                </div>

                <div class="card-footer d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Create Order
                    </button>
                    <a href="{{ route('admin.vakalatnama.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection