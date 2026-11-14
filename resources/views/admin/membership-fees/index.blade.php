@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Membership Fee Settings</h4>
            <p class="text-muted mb-0">
                Configure registration and yearly membership fees.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-currency-exchange me-2"></i>
                Fee Configuration
            </h5>
        </div>

        <form action="{{ route('admin.membership-fees.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="row g-3">

                    {{-- Registration Fee --}}
                    <div class="col-md-4">
                        <label for="registration_fee" class="form-label">
                            Registration Fee <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input
                                type="number"
                                name="registration_fee"
                                id="registration_fee"
                                class="form-control @error('registration_fee') is-invalid @enderror"
                                value="{{ old('registration_fee', $settings->get('membership.registration_fee', 0)) }}"
                                min="0"
                                step="0.01"
                                max="9999999999.99"
                                required
                            >
                            @error('registration_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <small class="text-muted">
                            Initial registration fee for new members.
                        </small>
                    </div>

                    {{-- Member Yearly Fee --}}
                    <div class="col-md-4">
                        <label for="yearly_fee_member" class="form-label">
                            Member Yearly Fee <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input
                                type="number"
                                name="yearly_fee_member"
                                id="yearly_fee_member"
                                class="form-control @error('yearly_fee_member') is-invalid @enderror"
                                value="{{ old('yearly_fee_member', $settings->get('membership.yearly_fee.member', 0)) }}"
                                min="0"
                                step="0.01"
                                max="9999999999.99"
                                required
                            >
                            @error('yearly_fee_member')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <small class="text-muted">
                            Annual renewal fee for regular members.
                        </small>
                    </div>

                    {{-- Life Member Yearly Fee --}}
                    <div class="col-md-4">
                        <label for="yearly_fee_life_member" class="form-label">
                            Life Member Yearly Fee <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input
                                type="number"
                                name="yearly_fee_life_member"
                                id="yearly_fee_life_member"
                                class="form-control @error('yearly_fee_life_member') is-invalid @enderror"
                                value="{{ old('yearly_fee_life_member', $settings->get('membership.yearly_fee.life_member', 0)) }}"
                                min="0"
                                step="0.01"
                                max="9999999999.99"
                                required
                            >
                            @error('yearly_fee_life_member')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <small class="text-muted">
                            Annual renewal fee for life members.
                        </small>
                    </div>

                </div>

                <div class="alert alert-info mt-4 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Updated fees will apply to newly generated invoices.
                    Existing invoices will retain their original amounts.
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>
                    Save Fee Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
