
@extends('layouts.app')

@section('title', 'Two-Step Verification')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Protect your account</h3>
        </div>

        <div class="card-body">
            @include('components.global-feedback')

            <div class="border rounded p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5>Google Authenticator</h5>
                        <p class="text-muted mb-1">
                            Use Google Authenticator or another compatible TOTP app.
                        </p>

                        @if ($authenticatorEnabled)
                            <span class="badge bg-success">Configured</span>
                        @else
                            <span class="badge bg-secondary">Not configured</span>
                        @endif
                    </div>

                    @if (!$authenticatorEnabled)
                        <form method="POST"
                            action="{{ route('profile.two-factor.authenticator.setup') }}">
                            @csrf
                            <button class="btn btn-primary" type="submit">
                                {{ $secret ? 'Regenerate Setup Key' : 'Set Up Authenticator' }}
                            </button>
                        </form>
                    @endif
                </div>

                @if ($secret && !$authenticatorEnabled)
                    <hr>

                    <p>1. Scan this QR code in your authenticator app.</p>

                    @if ($qrCode)
                        <img
                            src="data:image/svg+xml;base64,{{ $qrCode }}"
                            alt="Authenticator QR code"
                            width="220"
                            height="220"
                            class="img-fluid border rounded p-2"
                        >
                    @endif

                    <p class="mt-3 mb-1">
                        Can't scan? Enter this setup key manually:
                    </p>

                    <div class="input-group mb-3" style="max-width: 420px;">
                        <input id="authenticator-secret"
                            class="form-control"
                            value="{{ $secret }}"
                            readonly>
                        <button class="btn btn-outline-secondary"
                            type="button"
                            onclick="navigator.clipboard.writeText(document.getElementById('authenticator-secret').value)">
                            Copy
                        </button>
                    </div>

                    <p>2. Enter the six-digit code displayed in your app.</p>

                    <form method="POST"
                        action="{{ route('profile.two-factor.authenticator.confirm') }}"
                        class="mt-3"
                        style="max-width: 420px;">
                        @csrf

                        <label for="code" class="form-label">
                            Authenticator code
                        </label>

                        <input type="text"
                            id="code"
                            name="code"
                            class="form-control mb-3"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            required>

                        <button class="btn btn-success" type="submit">
                            Verify and Activate
                        </button>
                    </form>
                @endif

                @if ($authenticatorEnabled)
                    <hr>

                    <form method="POST"
                        action="{{ route('profile.two-factor.authenticator.disable') }}"
                        style="max-width: 420px;">
                        @csrf

                        <label for="disable-code" class="form-label">
                            Enter current authenticator code to disable
                        </label>

                        <input type="text"
                            id="disable-code"
                            name="code"
                            class="form-control mb-3"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            required>

                        <button class="btn btn-outline-danger" type="submit">
                            Disable Authenticator
                        </button>
                    </form>
                @endif
            </div>

            <div class="border rounded p-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5>SMS Verification</h5>
                        <p class="text-muted mb-1">
                            Phone-based verification using a one-time SMS code.
                        </p>
                        <span class="badge bg-secondary">
                            {{ $smsEnabled ? 'Phone verified' : 'Not configured' }}
                        </span>
                    </div>

                    <form method="POST"
                        action="{{ route('profile.two-factor.sms.setup') }}">
                        @csrf
                        <button class="btn btn-outline-primary" type="submit">
                            Set Up SMS
                        </button>
                    </form>
                </div>

                <small class="text-muted d-block mt-2">
                    SMS activation is unavailable until an SMS provider is configured.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
