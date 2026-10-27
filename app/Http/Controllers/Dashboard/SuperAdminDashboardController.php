<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\UserManagementService;
use Illuminate\Contracts\View\View;

class SuperAdminDashboardController extends Controller
{
    public function index(UserManagementService $userManagementService): View
    {
        abort_unless(auth()->user()?->hasRole('super-admin'), 403);

        return app(DashboardController::class)->index($userManagementService);
    }
}
