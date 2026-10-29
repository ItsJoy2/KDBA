<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $identifier = trim($request->string('login')->toString());

        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'mobile';

        $user = User::query()
            ->where($field, $identifier)
            ->first();

        if (
            !$user
            || !Hash::check(
                $request->input('password'),
                $user->password
            )
            || !in_array(
                $user->status,
                ['Active', 'Life Member'],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'login' => [
                    'The supplied credentials are invalid.',
                ],
            ]);
        }

        /*
         * Do not authenticate until the required 2FA check succeeds.
         */
        $authenticatorEnabled = $user->hasTwoFactorAuthenticator();
        $smsEnabled = $user->hasTwoFactorPhone();

        if ($user->two_factor_enabled && (
            $authenticatorEnabled || $smsEnabled
        )) {
            $request->session()->regenerate();

            $request->session()->put([
                'auth.pending_user_id' => $user->id,
                'auth.pending_remember' => $request->boolean('remember'),
                'auth.pending_method' => $authenticatorEnabled
                    ? 'authenticator'
                    : 'sms',
                'auth.pending_expires_at' => now()
                    ->addMinutes(5)
                    ->timestamp,
            ]);

            /*
             * Authenticator takes priority when both methods are enabled.
             * No email OTP is generated or sent.
             */
            if ($authenticatorEnabled) {
                return redirect()->route('auth.2fa.create');
            }

            /*
             * SMS delivery must be implemented using your SMS provider.
             * Do not silently fall back to email.
             */
            return redirect()->route('auth.2fa.create');
        }

        /*
         * If 2FA is off, log in directly.
         */
        $request->session()->forget([
            'auth.pending_user_id',
            'auth.pending_remember',
            'auth.pending_method',
            'auth.pending_expires_at',
        ]);

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return redirect()->to($this->dashboardPath($user));
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'You have been logged out.');
    }

    private function dashboardPath(User $user): string
    {
        // Keep your existing role-based dashboard redirect here.
        return route('dashboard');
    }
}