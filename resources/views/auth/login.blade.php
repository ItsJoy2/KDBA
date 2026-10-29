
@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')

<body class="login-page bg-body-secondary">
    <main class="login-box">
        <h1 class="login-logo">
            <a href="{{ route('login') }}">
                <b>KD</b>BA
            </a>
        </h1>

        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Welcome back! Sign in to start your session.</p>

                @include('components.global-feedback')
                
                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    {{-- Email or Mobile --}}
                    <div class="mb-3">
                        <label for="login" class="form-label">
                            Email or Mobile Number
                        </label>

                        <div class="input-group">
                            <input
                                type="text"
                                id="login"
                                name="login"
                                class="form-control @error('login') is-invalid @enderror"
                                placeholder="Email or Mobile Number"
                                value="{{ old('login') }}"
                                autocomplete="username"
                                maxlength="254"
                                required
                                autofocus
                            >

                            <div class="input-group-text">
                                <span class="bi bi-person"></span>
                            </div>
                        </div>

                        @error('login')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label">
                                Password
                            </label>

                            <a
                                href="{{ route('password.request') }}"
                                class="small text-decoration-none"
                            >
                                Forgot password?
                            </a>
                        </div>

                        <div class="input-group">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Password"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                class="input-group-text"
                                type="button"
                                id="togglePassword"
                                aria-label="Show password"
                                aria-pressed="false"
                                style="cursor: pointer;"
                            >
                                <i class="bi bi-eye" id="passwordIcon"></i>
                            </button>
                        </div>

                        @error('password')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Remember Me and Sign In --}}
                    <div class="row align-items-center">
                        <div class="col-8">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    id="remember"
                                    @checked(old('remember'))
                                >

                                <label class="form-check-label" for="remember">
                                    Remember Me
                                </label>
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="d-grid gap-2">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Sign In
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- <p class="mb-1 mt-3">
                    <a href="{{ route('password.request') }}">
                        I forgot my password
                    </a>
                </p> --}}

                <p class="text-secondary small mt-3 mb-0">
                    <i class="bi bi-shield-check me-1"></i>
                    Secure access to your Bar Association account.
                </p>
            </div>
        </div>
    </main>

    {{-- Show / Hide Password --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('togglePassword');
            const input = document.getElementById('password');
            const icon = document.getElementById('passwordIcon');

            if (!button || !input || !icon) {
                return;
            }

            button.addEventListener('click', function () {
                const showPassword = input.type === 'password';

                input.type = showPassword ? 'text' : 'password';

                icon.classList.toggle('bi-eye', !showPassword);
                icon.classList.toggle('bi-eye-slash', showPassword);

                button.setAttribute(
                    'aria-label',
                    showPassword ? 'Hide password' : 'Show password'
                );

                button.setAttribute(
                    'aria-pressed',
                    String(showPassword)
                );
            });
        });
    </script>
</body>

@endsection
