<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoStepVerificationController extends Controller
{
    public function __construct(
        private readonly Google2FA $google2fa
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $secret = null;
        $qrCode = null;

        if (
            filled($user->two_factor_authenticator_secret)
            && !$user->two_factor_authenticator_confirmed_at
        ) {
            $secret = $user->two_factor_authenticator_secret;

            $otpauthUrl = $this->google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email ?: $user->mobile,
                $secret
            );

            $qrCode = base64_encode(
                (string) QrCode::format('svg')
                    ->size(220)
                    ->margin(1)
                    ->generate($otpauthUrl)
            );
        }

        return view('profile.two-step-verification', [
            'user' => $user,
            'secret' => $secret,
            'qrCode' => $qrCode,
            'authenticatorEnabled' =>
                $user->hasTwoFactorAuthenticator(),
            'smsEnabled' => $user->hasTwoFactorPhone(),
            'twoFactorEnabled' => (bool) $user->two_factor_enabled,
        ]);
    }

    public function setupAuthenticator(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        if ($user->hasTwoFactorAuthenticator()) {
            return back()->with(
                'info',
                'Google Authenticator is already configured.'
            );
        }

        $secret = $this->google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_authenticator_secret' => $secret,
            'two_factor_authenticator_confirmed_at' => null,
        ])->save();

        return redirect()
            ->route('profile.two-factor.index')
            ->with(
                'success',
                'Scan the QR code using Google Authenticator, then enter the code.'
            );
    }

    public function confirmAuthenticator(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        $secret = $user->two_factor_authenticator_secret;

        if (
            !$secret
            || $user->two_factor_authenticator_confirmed_at
        ) {
            return back()->withErrors([
                'code' => 'Start a new authenticator setup first.',
            ]);
        }

        if (!$this->google2fa->verifyKey(
            $secret,
            $validated['code'],
            1
        )) {
            return back()->withErrors([
                'code' => 'Invalid authenticator code. Check your app and try again.',
            ])->withInput();
        }

        $user->forceFill([
            'two_factor_authenticator_confirmed_at' => now(),
            'two_factor_enabled' => true,
        ])->save();

        return redirect()
            ->route('profile.two-factor.index')
            ->with(
                'success',
                'Google Authenticator has been activated successfully.'
            );
    }

    public function disableAuthenticator(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        $secret = $user->two_factor_authenticator_secret;

        if (!$user->hasTwoFactorAuthenticator() || !$secret) {
            return back()->withErrors([
                'code' => 'Google Authenticator is not configured.',
            ]);
        }

        if (!$this->google2fa->verifyKey(
            $secret,
            $validated['code'],
            2
        )) {
            return back()->withErrors([
                'code' => 'The authenticator code is incorrect. Please check your app and try again.',
            ])->withInput();
        }

        $smsEnabled = $user->hasTwoFactorPhone();

        $user->forceFill([
            'two_factor_authenticator_secret' => null,
            'two_factor_authenticator_confirmed_at' => null,
            'two_factor_enabled' => $smsEnabled,
        ])->save();

        return redirect()
            ->route('profile.two-factor.index')
            ->with(
                'success',
                'Google Authenticator has been disabled.'
            );
    }

    public function setupSms(): RedirectResponse
    {
        return back()->with(
            'info',
            'SMS verification is not available yet. Configure an SMS provider first.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Login Two-Factor Verification
    |--------------------------------------------------------------------------
    */

    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (!$user) {
            $this->clearPendingSession($request);

            return redirect()->route('login')->withErrors([
                'login' => 'Your verification session expired. Please log in again.',
            ]);
        }

        $method = $request->session()->get(
            'auth.pending_method'
        );

        if ($method === 'sms') {
            abort(
                503,
                'SMS verification requires a configured SMS delivery provider.'
            );
        }

        if ($method !== 'authenticator') {
            $this->clearPendingSession($request);

            return redirect()->route('login')->withErrors([
                'login' => 'Invalid verification session. Please log in again.',
            ]);
        }

        return view('auth.two-factor-login', [
            'method' => $method,
        ]);
    }

    public function verifyLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $this->pendingUser($request);

        if (!$user) {
            $this->clearPendingSession($request);

            return redirect()->route('login')->withErrors([
                'login' => 'Your verification session expired. Please log in again.',
            ]);
        }

        $method = $request->session()->get(
            'auth.pending_method'
        );

        if (
            $method !== 'authenticator'
            || !$user->two_factor_enabled
            || !$user->hasTwoFactorAuthenticator()
        ) {
            throw ValidationException::withMessages([
                'code' => 'The selected verification method is unavailable.',
            ]);
        }

        if (!$this->google2fa->verifyKey(
            $user->two_factor_authenticator_secret,
            $validated['code'],
            1
        )) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid. Please try again.',
            ]);
        }

        $remember = (bool) $request->session()->get(
            'auth.pending_remember',
            false
        );

        Auth::login($user, $remember);

        $this->clearPendingSession($request);

        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return redirect()->intended(
            $this->dashboardPath($user)
        );
    }

    private function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get(
            'auth.pending_user_id'
        );

        $expiresAt = $request->session()->get(
            'auth.pending_expires_at'
        );

        if (
            !$userId
            || !$expiresAt
            || now()->timestamp >= (int) $expiresAt
        ) {
            return null;
        }

        return User::query()
            ->whereKey($userId)
            ->whereIn('status', ['Active', 'Life Member'])
            ->first();
    }

    private function clearPendingSession(Request $request): void
    {
        $request->session()->forget([
            'auth.pending_user_id',
            'auth.pending_remember',
            'auth.pending_method',
            'auth.pending_expires_at',
        ]);
    }

    private function dashboardPath(User $user): string
    {
        /*
         * Replace this with your existing role-based dashboard logic.
         * Do not remove the existing role-specific redirects.
         */
        return route('dashboard');
    }
}