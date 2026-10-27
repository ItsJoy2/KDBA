<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use App\Services\UserManagementService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(UserManagementService $userManagementService): View
    {
        $user = auth()->user();

        $canViewUsers = $user->hasPermission('users.view');
        $canApproveUsers = $user->hasPermission('users.approve');
        $canChangeMemberStatus = $user->hasPermission('users.change_status');

        // Load cached statistics only if the user can see at least one card.
        $stats = (
            $canViewUsers ||
            $canApproveUsers ||
            $canChangeMemberStatus
        )
            ? $userManagementService->dashboardStats()
            : [];

        // Each value is provided only to a user with the relevant permission.
        $totalUsers = $canViewUsers
            ? ($stats['totalUsers'] ?? 0)
            : null;

        $activeUsers = $canViewUsers
            ? ($stats['activeUsers'] ?? 0)
            : null;

        $pendingUsers = $canApproveUsers
            ? ($stats['pendingUsers'] ?? 0)
            : null;

        $inactiveUsers = $canChangeMemberStatus
            ? ($stats['inactiveUsers'] ?? 0)
            : null;

        // Do not query or expose the recent users table without users.view.
        $recentUsers = $canViewUsers
            ? User::query()
                ->whereDoesntHave('roles', function (Builder $query) {
                    $query->where('slug', 'super-admin');
                })
                ->where('status', 'Active')
                ->select([
                    'id',
                    'full_name',
                    'email',
                    'mobile',
                    'approval_status',
                    'member_status',
                    'membership_number',
                    'created_at',
                ])
                ->latest('id')
                ->limit(8)
                ->get()
            : collect();

        return view('dashboard.index', compact('canViewUsers','canApproveUsers','canChangeMemberStatus','totalUsers','activeUsers','pendingUsers','inactiveUsers','recentUsers'
        ));
    }
}
