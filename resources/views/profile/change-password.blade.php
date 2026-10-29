
@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="fw-bold mb-1">Change Password</h3>
        <p class="text-secondary mb-0">Update your password to keep your account secure.</p>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-6">
                
                @include('components.global-feedback')

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header border-0 p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-primary-subtle text-primary p-3">
                                <i class="bi bi-shield-lock fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Password settings</h5>
                                <p class="text-secondary small mb-0">Use a strong password you do not use elsewhere.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-4">
                        <form method="POST" action="{{ route('profile.password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold">Current Password</label>
                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    class="form-control form-control-lg @error('current_password') is-invalid @enderror"
                                    autocomplete="current-password"
                                    required
                                >
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">New Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                    autocomplete="new-password"
                                    required
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Minimum 8 characters, uppercase, lowercase, number and symbol.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold">
                                    Confirm New Password
                                </label>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control form-control-lg"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <a href="{{ route('profile.show') }}" class="btn btn-light border px-4">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check2-circle me-1"></i>Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Never share your password or verification codes with anyone.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
