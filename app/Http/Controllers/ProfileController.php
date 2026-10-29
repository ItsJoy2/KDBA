<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\OtpCodeNotification;
use App\Services\Auth\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private const TWO_FACTOR_PURPOSE = 'enable_2fa';

    private const PENDING_USER_KEY = 'profile.2fa_setup_user_id';

    private const PENDING_EXPIRES_KEY = 'profile.2fa_setup_expires_at';

    public function __construct(
        private readonly OtpService $otpService
    ) {
    }

    /**
     * Show authenticated user's profile.
     */

    public function show(Request $request): View
    {
        $user = $request->user();

        $disk = Storage::disk('local');

        // Profile photo
        $photoUrl = null;

        if ($user->photo && $disk->exists($user->photo)) {
            $photoUrl = 'data:' . $disk->mimeType($user->photo)
                . ';base64,' . base64_encode($disk->get($user->photo));
        }

        // NID / Passport front and back images
        $nidImages = [];

        foreach (['front', 'back'] as $side) {
            $path = data_get($user->nid_images, $side);

            $nidImages[$side] = $path && $disk->exists($path)
                ? 'data:' . $disk->mimeType($path)
                    . ';base64,' . base64_encode($disk->get($path))
                : null;
        }

        return view('profile.show', compact(
            'user',
            'photoUrl',
            'nidImages'
        ));
    }

    /**
     * Show change-password form.
     */
    public function editPassword(): View
    {
        return view('profile.change-password');
    }

    /**
     * Update authenticated user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
                'current_password',
            ],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $user = $request->user();

        // Explicit password verification before changing credentials.
        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'The current password is incorrect.',
                ],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        $request->session()->regenerate();

        return redirect()
            ->route('profile.password.edit')
            ->with('success', 'Your password has been changed successfully.');
    }

    /**
     * Show two-factor authentication settings.
     */
    public function showTwoFactor(Request $request): View
    {
        $user = $request->user();

        $pendingUserId = (int) $request->session()->get(
            self::PENDING_USER_KEY,
            0
        );

        $expiresAt = (int) $request->session()->get(
            self::PENDING_EXPIRES_KEY,
            0
        );

        $setupPending = $pendingUserId === (int) $user->id
            && $expiresAt >= now()->timestamp;

        if (!$setupPending) {
            $this->clearPendingTwoFactor($request);
        }

        return view('profile.two-factor', [
            'setupPending' => $setupPending,
        ]);
    }

    /**
     * Send an OTP to begin two-factor setup.
     */
    public function enableTwoFactor(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->two_factor_enabled) {
            return redirect()
                ->route('profile.two-factor.show')
                ->with('status', 'Two-step verification is already enabled.');
        }

        if (blank($user->email)) {
            throw ValidationException::withMessages([
                'email' => [
                    'A registered email address is required to enable two-step verification.',
                ],
            ]);
        }

        $code = $this->otpService->issue(
            $user,
            self::TWO_FACTOR_PURPOSE,
            'email',
            $user->email
        );

        try {
            Notification::route('mail', $user->email)
                ->notify(
                    new OtpCodeNotification(
                        $code,
                        self::TWO_FACTOR_PURPOSE
                    )
                );
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'email' => [
                    'We could not send a verification code. Please try again later.',
                ],
            ]);
        }

        $request->session()->put([
            self::PENDING_USER_KEY => $user->id,
            self::PENDING_EXPIRES_KEY => now()
                ->addMinutes(5)
                ->timestamp,
        ]);

        return redirect()
            ->route('profile.two-factor.show')
            ->with('status', 'A verification code has been sent to your email.');
    }

    /**
     * Verify setup OTP and enable two-factor authentication.
     */
    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'digits:6',
            ],
        ]);

        $user = $request->user();

        $pendingUserId = (int) $request->session()->get(
            self::PENDING_USER_KEY,
            0
        );

        $expiresAt = (int) $request->session()->get(
            self::PENDING_EXPIRES_KEY,
            0
        );

        if (
            $pendingUserId !== (int) $user->id
            || $expiresAt < now()->timestamp
        ) {
            $this->clearPendingTwoFactor($request);

            throw ValidationException::withMessages([
                'code' => [
                    'Your setup session has expired. Please request a new code.',
                ],
            ]);
        }

        if ($user->two_factor_enabled) {
            $this->clearPendingTwoFactor($request);

            return redirect()
                ->route('profile.two-factor.show')
                ->with('status', 'Two-step verification is already enabled.');
        }

        $verified = $this->otpService->verify(
            $user,
            self::TWO_FACTOR_PURPOSE,
            $validated['code']
        );

        if (!$verified) {
            throw ValidationException::withMessages([
                'code' => [
                    'The verification code is invalid or expired.',
                ],
            ]);
        }

        $user->forceFill([
            'two_factor_enabled' => true,
        ])->save();

        $this->clearPendingTwoFactor($request);

        return redirect()
            ->route('profile.two-factor.show')
            ->with('status', 'Two-step verification has been enabled successfully.');
    }

    /**
     * Disable two-factor authentication after password confirmation.
     */
    public function disableTwoFactor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'The current password is incorrect.',
                ],
            ]);
        }

        $user->forceFill([
            'two_factor_enabled' => false,
        ])->save();

        $this->clearPendingTwoFactor($request);

        return redirect()
            ->route('profile.two-factor.show')
            ->with('status', 'Two-step verification has been disabled.');
    }

    /**
     * Clear temporary 2FA setup state.
     */
    private function clearPendingTwoFactor(Request $request): void
    {
        $request->session()->forget([
            self::PENDING_USER_KEY,
            self::PENDING_EXPIRES_KEY,
        ]);
    }
}
