<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\OtpCodeNotification;
use App\Services\Auth\OtpService;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PasswordResetController extends Controller
{
    private const OTP_PURPOSE = 'password_reset';
    private const USER_KEY = 'password_reset.user_id';
    private const EMAIL_KEY = 'password_reset.email';
    private const TOKEN_KEY = 'password_reset.token';
    private const VERIFIED_UNTIL_KEY = 'password_reset.verified_until';

    public function __construct(
        private readonly PasswordResetService $passwordResetService,
        private readonly OtpService $otpService,
    ) {
    }

    public function requestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(
        ForgotPasswordRequest $request
    ): RedirectResponse {
        $email = $request->validated('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()
                ->route('password.reset', ['email' => $email])
                ->with('status', 'If an account exists for that email, a verification code will be sent.');
        }

        $this->clearResetSession($request);

        try {
            $code = $this->otpService->issue(
                $user,
                self::OTP_PURPOSE,
                'email',
                $user->email
            );

            Notification::route('mail', $user->email)->notify(
                new OtpCodeNotification(
                    (string) $code,
                    self::OTP_PURPOSE
                )
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'email' => 'Unable to send the verification code. Please try again.',
            ])->withInput();
        }

        return redirect()
            ->route('password.reset', ['email' => $user->email])
            ->with('status', 'If an account exists for that email, a verification code has been sent.');
    }

    public function resetForm(Request $request): View
    {
        $email = (string) $request->query('email', '');

        $verifiedEmail = $request->session()->get(self::EMAIL_KEY);
        $verifiedUntil = (int) $request->session()->get(
            self::VERIFIED_UNTIL_KEY,
            0
        );

        $passwordStep = filled($verifiedEmail)
            && hash_equals((string) $verifiedEmail, $email)
            && $verifiedUntil >= now()->timestamp
            && $request->session()->has(self::TOKEN_KEY);

        if (!$passwordStep && $request->session()->has(self::TOKEN_KEY)) {
            $this->clearResetSession($request);
        }

        return view('auth.reset-password', [
            'email' => $passwordStep ? $verifiedEmail : $email,
            'passwordStep' => $passwordStep,
        ]);
    }

    public function verifyResetOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            'code' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (
            !$user
            || !$this->otpService->verify(
                $user,
                self::OTP_PURPOSE,
                $validated['code']
            )
        ) {
            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid or expired.'],
            ]);
        }

        // OTP is valid. Create the reset token only after OTP verification.
        $token = Password::broker('users')->createToken($user);

        $request->session()->put([
            self::USER_KEY => $user->id,
            self::EMAIL_KEY => $user->email,
            self::TOKEN_KEY => $token,
            self::VERIFIED_UNTIL_KEY => now()->addMinutes(10)->timestamp,
        ]);

        return redirect()
            ->route('password.reset', ['email' => $user->email])
            ->with('status', 'Email verified. You can now create a new password.');
    }

    public function reset(
        ResetPasswordRequest $request
    ): RedirectResponse {
        $verifiedEmail = $request->session()->get(self::EMAIL_KEY);
        $userId = (int) $request->session()->get(self::USER_KEY, 0);
        $token = $request->session()->get(self::TOKEN_KEY);
        $verifiedUntil = (int) $request->session()->get(
            self::VERIFIED_UNTIL_KEY,
            0
        );

        if (
            !$verifiedEmail
            || !$token
            || $verifiedUntil < now()->timestamp
        ) {
            $this->clearResetSession($request);

            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' => 'Your password-reset session expired. Please request a new OTP.',
                ]);
        }

        $user = User::whereKey($userId)
            ->where('email', $verifiedEmail)
            ->first();

        if (!$user) {
            $this->clearResetSession($request);

            return redirect()->route('password.request')
                ->withErrors([
                    'email' => 'The password-reset request could not be completed.',
                ]);
        }

        $data = array_merge($request->validated(), [
            'email' => $verifiedEmail,
            'token' => $token,
        ]);

        $status = $this->passwordResetService->resetPassword($data);

        if ($status === Password::PASSWORD_RESET) {
            $this->clearResetSession($request);

            return redirect()->route('login')
                ->with('success', 'Your password has been reset. Please log in.');
        }

        $this->clearResetSession($request);

        return redirect()
            ->route('password.request')
            ->withErrors([
                'email' => 'The password-reset request expired. Please request a new OTP.',
            ]);
    }

    private function clearResetSession(Request $request): void
    {
        $request->session()->forget([
            self::USER_KEY,
            self::EMAIL_KEY,
            self::TOKEN_KEY,
            self::VERIFIED_UNTIL_KEY,
        ]);
    }
}
