<?php

namespace App\Http\Middleware;

use App\Services\Rbac\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
        string ...$permissions
    ): Response {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if ($permissions === []) {
            abort(403, 'No permission was specified.');
        }

        if (!$this->permissionService->userHasAllPermissions(
            $user,
            $permissions
        )) {
            abort(403, 'You are not authorized to perform this action.');
        }

        return $next($request);
    }
}