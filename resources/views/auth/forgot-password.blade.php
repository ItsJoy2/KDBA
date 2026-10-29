
@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')

<body class="login-page bg-body-secondary">
    <main class="login-box">

        {{-- Logo --}}
        <h1 class="login-logo">
            <a href="{{ route('login') }}">
                <b>KD</b>BA
            </a>
        </h1>

        <div class="card">
            <div class="card-body login-card-body">

                <p class="login-box-msg">
                    Forgot your password? Enter your registered email
                    to receive password reset instructions.
                </p>

                {{-- Global Success / Error Messages --}}
                @include('components.global-feedback')

                {{-- Forgot Password Form --}}
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            Email Address
                        </label>

                        <div class="input-group">
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                placeholder="Enter your registered email"
                                autocomplete="email"
                                maxlength="254"
                                required
                                autofocus
                            >

                            <div class="input-group-text">
                                <span class="bi bi-envelope"></span>
                            </div>
                        </div>

                        @error('email')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="d-grid gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-send me-2"></i>
                            Send Reset Instructions
                        </button>
                    </div>
                </form>

                {{-- Back to Login --}}
                <p class="mb-1 mt-3">
                    <a href="{{ route('login') }}">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Sign In
                    </a>
                </p>

                {{-- Footer --}}
                <p class="text-secondary small mt-3 mb-0">
                    <i class="bi bi-shield-check me-1"></i>
                    Secure password recovery for your Bar Association account.
                </p>

            </div>
        </div>
    </main>
</body>

@endsection
