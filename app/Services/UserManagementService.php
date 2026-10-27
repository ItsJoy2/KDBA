<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class UserManagementService
{
    public function dashboardStats(): array
    {
        return Cache::remember(
            'dashboard.user_stats',
            now()->addMinutes(5),
            fn () => [
                'totalUsers' => User::query()
                    ->whereDoesntHave('roles', function (Builder $query) {
                        $query->where('slug', 'super-admin');
                    })
                    ->count(),

                'activeUsers' => User::query()
                    ->whereDoesntHave('roles', function (Builder $query) {
                        $query->where('slug', 'super-admin');
                    })
                    ->where('approval_status', 'approved')
                    ->where('status', 'Active')
                    ->count(),

                'pendingUsers' => User::query()
                    ->whereDoesntHave('roles', function (Builder $query) {
                        $query->where('slug', 'super-admin');
                    })
                    ->where('approval_status', 'pending')
                    ->count(),

                'inactiveUsers' => User::query()
                    ->whereDoesntHave('roles', function (Builder $query) {
                        $query->where('slug', 'super-admin');
                    })
                    ->whereIn('member_status', [
                        'inactive',
                        'suspended',
                    ])
                    ->count(),
            ]
        );
    }

    public function clearDashboardStats(): void
    {
        Cache::forget('dashboard.user_stats');
    }
}