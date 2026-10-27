<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class GeneralSecretaryDashboardController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->hasRole('general-secretary'), 403);

        return view('dashboard.index', [
            'pageTitle' => 'General Secretary Dashboard',
            'isSuperAdmin' => false,
            'stats' => [
                'members' => User::whereIn(
                    'status',
                    ['Active', 'Life Member', 'Apprentice']
                )->count(),
                'activeMembers' => User::where('status', 'Active')->count(),
                'roles' => null,
                'permissions' => null,
            ],
            'recentMembers' => collect(),
        ]);
    }
}
