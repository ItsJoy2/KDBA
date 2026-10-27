<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $identifier = Str::lower(
                trim((string) $request->input('login', ''))
            );

            $identifierKey = hash('sha256', $identifier);

            return [
                Limit::perMinute(10)
                    ->by('login-ip:'.$request->ip()),

                Limit::perMinute(5)
                    ->by('login-identifier:'.$identifierKey),
            ];
        });

        RateLimiter::for('two-factor', function (Request $request) {
            $userId = (string) $request->session()
                ->get('auth.pending_user_id', 'unknown');

            return [
                Limit::perMinute(10)
                    ->by('two-factor-ip:'.$request->ip()),

                Limit::perMinute(5)
                    ->by('two-factor-user:'.$userId),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = Str::lower(
                trim((string) $request->input('email', ''))
            );

            return [
                Limit::perMinute(10)
                    ->by('password-reset-ip:'.$request->ip()),

                Limit::perMinute(3)
                    ->by('password-reset-email:'.hash('sha256', $email)),
            ];
        });

        View::composer('layouts.partials.__sidebar', function ($view) {
            $currentUser = Auth::user();

            $hasAssistants = false;

            if ($currentUser) {
                $canManageAllUsers = $currentUser->roles()
                    ->whereIn('slug', [
                        'super-admin',
                        'system',
                        'general-secretary',
                        'office-staff',
                    ])
                    ->exists();

                if ($canManageAllUsers) {
                    $hasAssistants = User::query()
                        ->where('member_status', ['lawyer_assistant','apprentice'])
                        ->exists();
                } else {
                    $hasAssistants = $currentUser->assistants()
                        ->where('member_status', 'lawyer_assistant')
                        ->exists();
                }
            }

            $view->with('hasAssistants', $hasAssistants);
        });
    }
    
}