
@extends('layouts.app')

@section('title', 'Membership Fee Settings')

@section('content')
<div class="container-fluid">
    @include('components.global-feedback')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Membership Fee Settings</h3>
        </div>

        <form action="{{ route('admin.membership-fees.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="registration_fee">
                            Initial Registration Fee
                        </label>

                        <input
                            type="number"
                            name="registration_fee"
                            id="registration_fee"
                            class="form-control @error('registration_fee') is-invalid @enderror"
                            value="{{ old('registration_fee', $settings['membership.registration_fee'] ?? '0.00') }}"
                            min="0"
                            max="9999999999.99"
                            step="0.01"
                            required
                        >

                        @error('registration_fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="yearly_fee_member">
                            Yearly Fee — Member
                        </label>

                        <input
                            type="number"
                            name="yearly_fee_member"
                            id="yearly_fee_member"
                            class="form-control @error('yearly_fee_member') is-invalid @enderror"
                            value="{{ old('yearly_fee_member', $settings['membership.yearly_fee.member'] ?? '0.00') }}"
                            min="0"
                            max="9999999999.99"
                            step="0.01"
                            required
                        >

                        @error('yearly_fee_member')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="yearly_fee_life_member">
                            Yearly Fee — Life Member
                        </label>

                        <input
                            type="number"
                            name="yearly_fee_life_member"
                            id="yearly_fee_life_member"
                            class="form-control @error('yearly_fee_life_member') is-invalid @enderror"
                            value="{{ old('yearly_fee_life_member', $settings['membership.yearly_fee.life_member'] ?? '0.00') }}"
                            min="0"
                            max="9999999999.99"
                            step="0.01"
                            required
                        >

                        @error('yearly_fee_life_member')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i>
                    Changes will apply to newly generated invoices.
                    Existing invoices will retain their original amounts.
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Fee Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
