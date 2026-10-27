<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService
    ) {
    }

    public function create()
    {
        abort_unless(
            session()->has('auth.pending_user_id'),
            403
        );

        return view('auth.verify-otp');
    }

    public function store(
        VerifyOtpRequest $request
    ): RedirectResponse {
        $userId = $request->session()->get('auth.pending_user_id');

        if (!$userId) {
            throw ValidationException::withMessages([
                'code' => ['Your verification session has expired. Please log in again.'],
            ]);
        }

        $user = User::query()->find($userId);

        if (
            !$user
            || !$user->two_factor_enabled
            || !in_array(
                $user->status,
                ['Active', 'Life Member',],
                true
            )
        ) {
            $this->clearPendingSession($request);

            throw ValidationException::withMessages([
                'code' => ['Your verification session is invalid. Please log in again.'],
            ]);
        }

        $verified = $this->otpService->verify(
            $user,
            'login_2fa',
            $request->validated('code')
        );

        if (!$verified) {
            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid or expired.'],
            ]);
        }

        $remember = $request->session()->boolean(
            'auth.pending_remember'
        );

        // Remove temporary authentication state before establishing
        // the final authenticated session.
        $this->clearPendingSession($request);

        Auth::login($user, $remember);

        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return redirect()->intended('/dashboard');
    }

    private function clearPendingSession($request): void
    {
        $request->session()->forget([
            'auth.pending_user_id',
            'auth.pending_remember',
            'auth.pending_expires_at',
        ]);
    }
}