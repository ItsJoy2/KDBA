<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    /**
     * Send a password-reset link to a registered email address.
     *
     * The caller should return a generic response to avoid
     * disclosing whether the email exists.
     */
    public function sendResetLink(array $credentials): string
    {
        return Password::broker('users')->sendResetLink([
            'email' => $credentials['email'],
        ]);
    }

    /**
     * Reset the password using a valid, unexpired broker token.
     */
    public function resetPassword(array $credentials): string
    {
        $status = Password::broker('users')->reset(
            [
                'email' => $credentials['email'],
                'token' => $credentials['token'],
                'password' => $credentials['password'],
                'password_confirmation' =>
                    $credentials['password_confirmation'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [
                    __($status),
                ],
            ]);
        }

        return $status;
    }
}