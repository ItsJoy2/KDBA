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
    public function resetPassword(array $data): string
    {
        return Password::broker('users')->reset(
            [
                'email' => $data['email'],
                'token' => $data['token'],
                'password' => $data['password'],
            ],
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ]);

                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );
    }
}