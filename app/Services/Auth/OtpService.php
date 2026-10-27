<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpService
{
    /**
     * Issue a six-digit OTP.
     *
     * Return the plaintext code only to the trusted delivery layer.
     * Never return it directly from a production API response.
     */
    public function issue(
        User $user,
        string $purpose,
        string $channel,
        string $destination
    ): string {
        if (!in_array($channel, ['email', 'sms'], true)) {
            throw ValidationException::withMessages([
                'channel' => ['Unsupported OTP delivery channel.'],
            ]);
        }

        if (!in_array(
            $purpose,
            ['login_2fa', 'password_reset', 'email_verification'],
            true
        )) {
            throw ValidationException::withMessages([
                'purpose' => ['Unsupported OTP purpose.'],
            ]);
        }

        if ($channel === 'email' && !filter_var(
            $destination,
            FILTER_VALIDATE_EMAIL
        )) {
            throw ValidationException::withMessages([
                'destination' => ['A valid email address is required.'],
            ]);
        }

        if ($channel === 'sms' && !preg_match(
            '/^\+[1-9]\d{7,14}$/',
            $destination
        )) {
            throw ValidationException::withMessages([
                'destination' => [
                    'Provide a valid E.164 mobile number.',
                ],
            ]);
        }

        $code = (string) random_int(100000, 999999);

        DB::transaction(function () use (
            $user,
            $purpose,
            $channel,
            $destination,
            $code
        ): void {
            UserOtp::query()
                ->where('user_id', $user->id)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->update([
                    'consumed_at' => now(),
                    'updated_at' => now(),
                ]);

            UserOtp::create([
                'user_id' => $user->id,
                'purpose' => $purpose,
                'channel' => $channel,
                'code_hash' => Hash::make($code),
                'sent_to' => $destination,
                'attempts' => 0,
                'max_attempts' => 5,
                'expires_at' => now()->addMinutes(5),
            ]);
        });

        return $code;
    }

    /**
     * Verify and consume an OTP atomically.
     */
    public function verify(
        User $user,
        string $purpose,
        string $code
    ): bool {
        return DB::transaction(function () use (
            $user,
            $purpose,
            $code
        ): bool {
            $otp = UserOtp::query()
                ->where('user_id', $user->id)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$otp || !$otp->isUsable()) {
                return false;
            }

            // Count every submitted attempt, including an incorrect one.
            $otp->increment('attempts');
            $otp->refresh();

            if (!Hash::check($code, $otp->code_hash)) {
                return false;
            }

            $otp->forceFill([
                'consumed_at' => now(),
            ])->save();

            return true;
        });
    }
}