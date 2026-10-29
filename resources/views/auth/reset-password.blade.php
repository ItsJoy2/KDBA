
@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<main class="login-box">
    <h1 class="login-logo">
        <a href="{{ route('login') }}"><b>KD</b>BA</a>
    </h1>

    <div class="card">
        <div class="card-body login-card-body">

            @include('components.global-feedback')

            @if (!$passwordStep)
                <p class="login-box-msg">
                    Enter the 6-digit verification code sent to your email.
                </p>

                <form method="POST"
                      action="{{ route('password.verify-otp') }}"
                      id="otpForm">
                    @csrf

                    <input type="hidden"
                           name="email"
                           value="{{ old('email', $email) }}">

                    <div class="mb-3">
                        <label class="form-label">Verification Code</label>

                        <div class="d-flex gap-2 justify-content-center"
                             id="otpBoxes">
                            @for ($i = 0; $i < 6; $i++)
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]"
                                    maxlength="1"
                                    class="form-control text-center otp-digit"
                                    style="width: 44px; height: 52px; font-size: 1.4rem;"
                                    aria-label="OTP digit {{ $i + 1 }}"
                                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                    required
                                >
                            @endfor
                        </div>

                        <input type="hidden"
                               name="code"
                               id="otpCode"
                               value="{{ old('code') }}">

                        @error('code')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror

                        @error('email')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit"
                                class="btn btn-primary"
                                id="nextButton"
                                disabled>
                            Next <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>

            @else
                <p class="login-box-msg">
                    Email verified. Create your new password.
                </p>

                <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                    @csrf

                    {{-- New Password --}}
                    <div class="mb-3">
                        <label class="form-label" for="password">
                            New Password <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="input-group-text password-toggle"
                                data-target="password"
                                aria-label="Show password"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        <div class="form-text mt-2">
                            <div id="password-length" class="text-danger">
                                <i class="bi bi-x-circle me-1"></i>
                                At least 8 characters
                            </div>

                            <div id="password-uppercase" class="text-danger">
                                <i class="bi bi-x-circle me-1"></i>
                                At least one uppercase letter (A-Z)
                            </div>

                            <div id="password-lowercase" class="text-danger">
                                <i class="bi bi-x-circle me-1"></i>
                                At least one lowercase letter (a-z)
                            </div>

                            <div id="password-number" class="text-danger">
                                <i class="bi bi-x-circle me-1"></i>
                                At least one number (0-9)
                            </div>

                            <div id="password-symbol" class="text-danger">
                                <i class="bi bi-x-circle me-1"></i>
                                At least one symbol (!@#$...)
                            </div>
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="input-group-text password-toggle"
                                data-target="password_confirmation"
                                aria-label="Show confirm password"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                        <div class="invalid-feedback" id="password-confirm-feedback">
                            Passwords do not match.
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="resetPasswordButton">
                            <i class="bi bi-shield-check me-2"></i>
                            Reset Password
                        </button>
                    </div>
                </form>
            @endif


            <p class="mt-3 mb-0">
                <a href="{{ route('login') }}">
                    <i class="bi bi-arrow-left me-1"></i> Back to Sign In
                </a>
            </p>
        </div>
    </div>
</main>
@endsection

@push('scripts')
   
<script>
    document.addEventListener('DOMContentLoaded', function () {

        /* =========================================================
        * OTP INPUT HANDLING
        * ======================================================= */

        const boxes = Array.from(document.querySelectorAll('.otp-digit'));
        const hiddenCode = document.getElementById('otpCode');
        const nextButton = document.getElementById('nextButton');
        const otpForm = document.getElementById('otpForm');

        function syncOtp() {
            if (!hiddenCode || !nextButton) return;

            const code = boxes.map(box => box.value).join('');

            hiddenCode.value = code;
            nextButton.disabled = !/^\d{6}$/.test(code);
        }

        boxes.forEach((box, index) => {

            box.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 1);

                if (this.value && index < boxes.length - 1) {
                    boxes[index + 1].focus();
                }

                syncOtp();
            });

            box.addEventListener('keydown', function (event) {

                if (
                    event.key === 'Backspace' &&
                    !this.value &&
                    index > 0
                ) {
                    boxes[index - 1].focus();
                }

                if (event.key === 'ArrowLeft' && index > 0) {
                    boxes[index - 1].focus();
                }

                if (
                    event.key === 'ArrowRight' &&
                    index < boxes.length - 1
                ) {
                    boxes[index + 1].focus();
                }
            });

            box.addEventListener('paste', function (event) {
                const clipboard = event.clipboardData || window.clipboardData;

                const pasted = clipboard
                    .getData('text')
                    .replace(/\D/g, '')
                    .slice(0, 6);

                if (!pasted) return;

                event.preventDefault();

                // Clear existing digits before pasting.
                boxes.forEach(input => input.value = '');

                pasted.split('').forEach((digit, i) => {
                    if (boxes[i]) {
                        boxes[i].value = digit;
                    }
                });

                boxes[Math.min(pasted.length, boxes.length - 1)]?.focus();

                syncOtp();
            });
        });

        if (otpForm && hiddenCode) {

            // Restore OTP after a validation error.
            const oldCode = hiddenCode.value || '';

            oldCode.split('').forEach((digit, i) => {
                if (boxes[i]) {
                    boxes[i].value = digit;
                }
            });

            syncOtp();

            otpForm.addEventListener('submit', function (event) {
                syncOtp();

                if (!/^\d{6}$/.test(hiddenCode.value)) {
                    event.preventDefault();
                    boxes[0]?.focus();
                }
            });
        }


        /* =========================================================
        * PASSWORD SHOW / HIDE
        * ======================================================= */

        document.querySelectorAll('.password-toggle').forEach(button => {

            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (!input || !icon) return;

                const showPassword = input.type === 'password';

                input.type = showPassword ? 'text' : 'password';

                icon.classList.toggle('bi-eye', !showPassword);
                icon.classList.toggle('bi-eye-slash', showPassword);

                this.setAttribute(
                    'aria-label',
                    showPassword ? 'Hide password' : 'Show password'
                );
            });
        });


        /* =========================================================
        * PASSWORD LIVE VALIDATION
        * ======================================================= */

        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        const resetForm = document.getElementById('resetPasswordForm');

        const passwordRules = [
            {
                id: 'password-length',
                test: value => value.length >= 8
            },
            {
                id: 'password-uppercase',
                test: value => /[A-Z]/.test(value)
            },
            {
                id: 'password-lowercase',
                test: value => /[a-z]/.test(value)
            },
            {
                id: 'password-number',
                test: value => /[0-9]/.test(value)
            },
            {
                id: 'password-symbol',
                test: value => /[^A-Za-z0-9]/.test(value)
            }
        ];

        function validatePasswordRules() {
            if (!password) return false;

            const value = password.value;
            let allValid = true;

            passwordRules.forEach(rule => {
                const element = document.getElementById(rule.id);

                if (!element) return;

                const passed = rule.test(value);

                element.classList.toggle('text-success', passed);
                element.classList.toggle('text-danger', !passed);

                const icon = element.querySelector('i');

                if (icon) {
                    icon.className = passed
                        ? 'bi bi-check-circle me-1'
                        : 'bi bi-x-circle me-1';
                }

                if (!passed) {
                    allValid = false;
                }
            });

            password.classList.toggle(
                'is-invalid',
                value.length > 0 && !allValid
            );

            password.classList.toggle(
                'is-valid',
                value.length > 0 && allValid
            );

            return allValid;
        }


        /* =========================================================
        * PASSWORD CONFIRMATION VALIDATION
        * ======================================================= */

        function validatePasswordConfirmation() {
            if (!password || !confirmPassword) return false;

            const confirmation = confirmPassword.value;

            const matches =
                confirmation.length > 0 &&
                password.value === confirmation;

            const hasInput = confirmation.length > 0;

            confirmPassword.classList.toggle(
                'is-invalid',
                hasInput && !matches
            );

            confirmPassword.classList.toggle(
                'is-valid',
                matches
            );

            const feedback = document.getElementById(
                'password-confirm-feedback'
            );

            if (feedback) {
                feedback.textContent = !hasInput
                    ? 'Please confirm your password.'
                    : 'Passwords do not match.';
            }

            return matches;
        }


        /* =========================================================
        * PASSWORD FORM EVENTS
        * ======================================================= */

        if (password && confirmPassword && resetForm) {

            password.addEventListener('input', function () {
                validatePasswordRules();
                validatePasswordConfirmation();
            });

            confirmPassword.addEventListener('input', function () {
                validatePasswordConfirmation();
            });

            resetForm.addEventListener('submit', function (event) {

                const passwordValid = validatePasswordRules();
                const confirmationValid = validatePasswordConfirmation();

                if (!passwordValid || !confirmationValid) {
                    event.preventDefault();

                    if (!passwordValid) {
                        password.focus();
                    } else {
                        confirmPassword.focus();
                    }

                    return;
                }

                // Prevent accidental double submission.
                const submitButton = document.getElementById(
                    'resetPasswordButton'
                );

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
                        'Resetting Password...';
                }
            });
        }

    });
</script>
 
@endpush
