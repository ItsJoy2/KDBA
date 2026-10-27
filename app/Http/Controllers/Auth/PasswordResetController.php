<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService
    ) {
    }

    public function requestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendLink(
        ForgotPasswordRequest $request
    ): RedirectResponse {
        $this->passwordResetService->sendResetLink(
            $request->validated()
        );

        // Generic response: do not reveal account existence.
        return back()->with(
            'status',
            'If an account exists for that email, password-reset instructions will be sent.'
        );
    }

    public function resetForm(string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request()->query('email', ''),
        ]);
    }

    public function reset(
        ResetPasswordRequest $request
    ): RedirectResponse {
        $status = $this->passwordResetService->resetPassword(
            $request->validated()
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Your password has been reset. Please log in.');
        }

        return back()->withErrors([
            'email' => 'The password reset request could not be completed.',
        ]);
    }
}