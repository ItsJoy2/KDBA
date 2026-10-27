<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HardenSession
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        if ($request->user()) {
            $response->headers->set(
                'Cache-Control',
                'no-store, private, max-age=0'
            );

            $response->headers->set('Pragma', 'no-cache');
        }

        $response->headers->set(
            'X-Content-Type-Options',
            'nosniff'
        );

        $response->headers->set(
            'X-Frame-Options',
            'SAMEORIGIN'
        );

        $response->headers->set(
            'Referrer-Policy',
            'strict-origin-when-cross-origin'
        );

        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=()'
        );

        return $response;
    }
}