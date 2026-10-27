<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Notifications\OtpCodeNotification;
use App\Services\Auth\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService
    ) {
    }

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

        // Use the same generic error for unknown accounts and
        // incorrect passwords to reduce account enumeration.
        if (
            !$user
            || !Hash::check($request->input('password'), $user->password)
            || !in_array(
                $user->status,
                ['Active', 'Life Member',],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'login' => [
                    'The supplied credentials are invalid.',
                ],
            ]);
        }

        if ($user->two_factor_enabled) {
            // Until the OTP is verified, do not authenticate the user.
            if (!$user->email) {
                throw ValidationException::withMessages([
                    'login' => [
                        'Two-factor authentication is not configured correctly for this account.',
                    ],
                ]);
            }

            $code = $this->otpService->issue(
                $user,
                'login_2fa',
                'email',
                $user->email
            );

            try {
                Notification::route('mail', $user->email)
                    ->notify(new OtpCodeNotification($code, 'login_2fa'));
            } catch (\Throwable $exception) {
                report($exception);

                throw ValidationException::withMessages([
                    'login' => [
                        'We could not send the verification code. Please try again later.',
                    ],
                ]);
            }

            $request->session()->regenerate();

            $request->session()->put([
                'auth.pending_user_id' => $user->id,
                'auth.pending_remember' => $request->boolean('remember'),
                'auth.pending_expires_at' => now()->addMinutes(5)->timestamp,
            ]);
            return redirect()->route('auth.2fa.create')
                ->with('status', 'A verification code has been sent.');
        }

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
        $rolePaths = [
            'super-admin' => '/dashboard/super-admin',
            'general-secretary' => '/dashboard/general-secretary',
            'office-staff' => '/dashboard/office-staff',
            'member' => '/dashboard/member',
            'user-apprentice' => '/dashboard/apprentice',
        ];

        $roles = $user->roles()
            ->where('roles.is_active', true)
            ->pluck('roles.slug');

        foreach (array_keys($rolePaths) as $roleSlug) {
            if ($roles->contains($roleSlug)) {
                return $rolePaths[$roleSlug];
            }
        }

        return '/dashboard';
    }
}