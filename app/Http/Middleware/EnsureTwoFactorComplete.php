<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorComplete
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        // A pending 2FA challenge must never be treated as an
        // authenticated session.
        if (
            $request->session()->has('auth.pending_user_id')
            && !$request->routeIs(
                'auth.2fa.create',
                'auth.2fa.verify',
                'logout'
            )
        ) {
            if ($request->user()) {
                auth()->logout();
            }

            $request->session()->forget([
                'auth.pending_user_id',
                'auth.pending_remember',
            ]);

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors([
                    'login' => 'Please log in again to continue.',
                ]);
        }

        return $next($request);
    }
}