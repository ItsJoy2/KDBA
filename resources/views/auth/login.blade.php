@extends('layouts.auth')

@section('title', 'login')

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
                <p class="login-box-msg">Sign in to start your session</p>

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="input-group mb-3">
                        <input
                            type="text"
                            name="login"
                            class="form-control @error('login') is-invalid @enderror"
                            placeholder="Email or Mobile Number"
                            value="{{ old('login') }}"
                            autocomplete="username"
                            required
                            autofocus
                        >

                        <div class="input-group-text">
                            <span class="bi bi-person"></span>
                        </div>
                    </div>

                    @error('login')
                        <div class="text-danger small mb-3">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="input-group mb-3">
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Password"
                            autocomplete="current-password"
                            required
                        >

                        <div class="input-group-text">
                            <span class="bi bi-lock-fill"></span>
                        </div>
                    </div>

                    @error('password')
                        <div class="text-danger small mb-3">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="row">
                        <div class="col-8">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    id="remember"
                                >

                                <label
                                    class="form-check-label"
                                    for="remember"
                                >
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

                <p class="mb-1 mt-3">
                    <a href="{{ route('password.request') }}">
                        I forgot my password
                    </a>
                </p>
            </div>
        </div>
    </main>
</body>

@endsection