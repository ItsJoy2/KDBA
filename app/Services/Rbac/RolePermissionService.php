<?php

namespace App\Services\Rbac;

use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RolePermissionService
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {
    }

    /**
     * Replace the permissions assigned to a role.
     *
     * Call this method only after the caller has passed
     * the appropriate authorization checks.
     */
    public function syncPermissions(
        Role $role,
        array $permissionIds
    ): void {
        $permissionIds = array_values(array_unique(
            array_map('intval', $permissionIds)
        ));

        $existingCount = DB::table('permissions')
            ->whereIn('id', $permissionIds)
            ->count();

        if ($existingCount !== count($permissionIds)) {
            throw ValidationException::withMessages([
                'permissions' => [
                    'One or more selected permissions are invalid.',
                ],
            ]);
        }

        DB::transaction(function () use (
            $role,
            $permissionIds
        ): void {
            DB::table('role_has_permissions')
                ->where('role_id', $role->id)
                ->delete();

            if ($permissionIds !== []) {
                DB::table('role_has_permissions')->insert(
                    array_map(
                        fn (int $permissionId): array => [
                            'role_id' => $role->id,
                            'permission_id' => $permissionId,
                        ],
                        $permissionIds
                    )
                );
            }

            DB::afterCommit(function () use (
                $role,
                $permissionIds
            ): void {
                $this->permissionService
                    ->clearRoleUsersCache($role->id);

                Log::info('RBAC permissions updated', [
                    'actor_id' => auth()->id(),
                    'role_id' => $role->id,
                    'permission_count' => count($permissionIds),
                    'ip' => request()->ip(),
                ]);
            });

        });
    }
}