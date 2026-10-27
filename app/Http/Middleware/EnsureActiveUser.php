<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $allowedStatuses = [
            'Active',
            'Life Member',
            'Apprentice',
        ];

        if (!in_array($user->status, $allowedStatuses, true)) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Your account is not currently authorized to access this system.');
        }

        return $next($request);
    }
}