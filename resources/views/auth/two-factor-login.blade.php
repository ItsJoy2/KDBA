
@extends('layouts.auth')

@section('title', 'Verify Your Identity')

@section('content')

<div class="login-page bg-body-secondary">
    <div class="login-box">

        {{-- Logo --}}
        <h1 class="login-logo">
            <a href="{{ route('login') }}" class="text-decoration-none">
                <b>KD</b>BA
            </a>
        </h1>

        <div class="card shadow-sm">
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
                            aria-hidden="true"
                        ></i>
                    </div>

                    <h5 class="fw-semibold">Verify Your Identity</h5>

                    <p class="text-secondary small mb-0">
                        Open Google Authenticator and enter the
                        6-digit code displayed in your app.
                    </p>
                </div>

                {{-- Global Feedback --}}
                @include('components.global-feedback')

                {{-- Verification Form --}}
                <form method="POST" action="{{ route('auth.2fa.store') }}" id="two-factor-form">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label d-block text-center mb-3">
                            Authentication Code
                        </label>

                        <div class="d-flex justify-content-center gap-2"
                            id="otp-inputs"
                            role="group"
                            aria-label="Six-digit authentication code">

                            @for ($i = 0; $i < 6; $i++)
                                <input
                                    type="text"
                                    class="form-control text-center fw-bold otp-digit"
                                    style="width: 45px; height: 52px; font-size: 1.4rem;"
                                    inputmode="numeric"
                                    pattern="[0-9]"
                                    maxlength="1"
                                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                    aria-label="Digit {{ $i + 1 }}"
                                    required
                                    @if ($i === 0) autofocus @endif
                                >
                            @endfor

                        </div>

                        {{-- Actual value submitted to Laravel --}}
                        <input
                            type="hidden"
                            name="code"
                            id="verification-code"
                            value="{{ old('code') }}"
                        >

                        <div class="form-text text-center mt-3">
                            Enter the 6-digit code from your authenticator app.
                        </div>

                        @error('code')
                            <div class="text-danger small text-center mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-shield-check me-2" aria-hidden="true"></i>
                            Verify &amp; Continue
                        </button>
                    </div>
                </form>

                {{-- Back to Login --}}
                <p class="mb-1 mt-3">
                    <a href="{{ route('login') }}" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                        Back to Sign In
                    </a>
                </p>

                {{-- Security Footer --}}
                <p class="text-secondary small mt-3 mb-0">
                    <i
                        class="bi bi-shield-lock me-1"
                        aria-hidden="true"
                    ></i>
                    Never share your authentication code with anyone.
                </p>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('two-factor-form');
        const container = document.getElementById('otp-inputs');
        const inputs = Array.from(
            container.querySelectorAll('.otp-digit')
        );
        const hiddenInput = document.getElementById('verification-code');

        function syncCode() {
            hiddenInput.value = inputs.map(input => input.value).join('');
        }

        function fillCode(value, startIndex = 0) {
            const digits = value.replace(/\D/g, '').slice(0, 6);

            inputs.forEach((input, index) => {
                input.value = '';
            });

            digits.split('').forEach((digit, index) => {
                if (inputs[startIndex + index]) {
                    inputs[startIndex + index].value = digit;
                }
            });

            syncCode();

            const nextEmpty = inputs.find(input => !input.value);

            (nextEmpty || inputs[inputs.length - 1]).focus();
        }

        inputs.forEach((input, index) => {
            input.addEventListener('input', function () {
                const digits = this.value.replace(/\D/g, '');

                if (digits.length > 1) {
                    fillCode(digits, index);
                    return;
                }

                this.value = digits.slice(0, 1);
                syncCode();

                if (this.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function (event) {
                if (
                    event.key === 'Backspace' &&
                    !this.value &&
                    index > 0
                ) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                    syncCode();
                }

                if (
                    event.key === 'ArrowLeft' &&
                    index > 0
                ) {
                    event.preventDefault();
                    inputs[index - 1].focus();
                }

                if (
                    event.key === 'ArrowRight' &&
                    index < inputs.length - 1
                ) {
                    event.preventDefault();
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('paste', function (event) {
                event.preventDefault();

                const pastedText = (
                    event.clipboardData || window.clipboardData
                ).getData('text');

                fillCode(pastedText, 0);
            });
        });

        form.addEventListener('submit', function (event) {
            syncCode();

            if (!/^\d{6}$/.test(hiddenInput.value)) {
                event.preventDefault();

                const firstEmpty = inputs.find(input => !input.value);

                if (firstEmpty) {
                    firstEmpty.focus();
                }
            }
        });

        // Restore the previous code after validation failure.
        const oldCode = hiddenInput.value;

        if (oldCode) {
            fillCode(oldCode);
        }
    });
</script>
    
@endpush