
@extends('layouts.auth')

@section('title', 'Verify Your Identity')

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
                    Two-step verification
                </p>

                {{-- Verification Header --}}
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i
                            class="bi bi-shield-lock text-primary"
                            style="font-size: 3rem;"
                        ></i>
                    </div>

                    <h5 class="fw-semibold">Verify Your Identity</h5>

                    <p class="text-secondary small mb-0">
                        Enter the 6-digit verification code sent to your
                        registered email address.
                    </p>
                </div>

                {{-- Global Feedback --}}
                @include('components.global-feedback')

                {{-- Verification Form --}}
                <form method="POST" action="{{ route('auth.2fa.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="code" class="form-label">
                            Verification Code
                        </label>

                        <div class="input-group">
                            <input
                                type="text"
                                id="code"
                                name="code"
                                class="form-control text-center @error('code') is-invalid @enderror"
                                style="letter-spacing: .5rem; font-size: 1.25rem;"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                minlength="6"
                                maxlength="6"
                                autocomplete="one-time-code"
                                placeholder="000000"
                                aria-describedby="code-help"
                                required
                                autofocus
                            >

                            <div class="input-group-text">
                                <i class="bi bi-key"></i>
                            </div>
                        </div>

                        <div id="code-help" class="form-text">
                            Enter the 6-digit code from your email.
                        </div>

                        @error('code')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-shield-check me-2"></i>
                            Verify &amp; Continue
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

                {{-- Security Footer --}}
                <p class="text-secondary small mt-3 mb-0">
                    <i class="bi bi-shield-lock me-1"></i>
                    Keep your verification code private.
                </p>

            </div>
        </div>
    </main>
</body>

@endsection
