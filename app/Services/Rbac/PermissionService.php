<?php

namespace App\Services\Rbac;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    private const CACHE_TTL_SECONDS = 300;

    private function cacheKey(int $userId): string
    {
        return "rbac:user:{$userId}:permissions";
    }

    /**
     * Return all permission slugs for a user.
     *
     * Only permissions attached to active roles are included.
     */
    public function getPermissionSlugs(User $user): array
    {
        return Cache::remember(
            $this->cacheKey($user->id),
            now()->addSeconds(self::CACHE_TTL_SECONDS),
            function () use ($user): array {
                return DB::table('permissions')
                    ->join(
                        'role_has_permissions',
                        'permissions.id',
                        '=',
                        'role_has_permissions.permission_id'
                    )
                    ->join(
                        'model_has_roles',
                        'role_has_permissions.role_id',
                        '=',
                        'model_has_roles.role_id'
                    )
                    ->join(
                        'roles',
                        'roles.id',
                        '=',
                        'model_has_roles.role_id'
                    )
                    ->where('model_has_roles.user_id', $user->id)
                    ->where('roles.is_active', true)
                    ->distinct()
                    ->orderBy('permissions.slug')
                    ->pluck('permissions.slug')
                    ->all();
            }
        );
    }

    public function userHasPermission(
        User $user,
        string $permission
    ): bool {
        return in_array(
            $permission,
            $this->getPermissionSlugs($user),
            true
        );
    }

    public function userHasAnyPermission(
        User $user,
        array $permissions
    ): bool {
        if ($permissions === []) {
            return false;
        }

        return array_intersect(
            $permissions,
            $this->getPermissionSlugs($user)
        ) !== [];
    }

    public function userHasAllPermissions(
        User $user,
        array $permissions
    ): bool {
        return array_diff(
            $permissions,
            $this->getPermissionSlugs($user)
        ) === [];
    }

    /**
     * Invalidate one user's cached permissions.
     */
    public function clearUserCache(int $userId): void
    {
        Cache::forget($this->cacheKey($userId));
    }

    /**
     * Invalidate permissions for every user assigned to a role.
     * Call this after changing that role's permissions or active state.
     */
    public function clearRoleUsersCache(int $roleId): void
    {
        $userIds = DB::table('model_has_roles')
            ->where('role_id', $roleId)
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $this->clearUserCache((int) $userId);
        }
    }

    /**
     * Invalidate every user's permission cache.
     * Use after deleting or changing a permission definition.
     */
    public function clearAllUserCaches(): void
    {
        $userIds = DB::table('model_has_roles')
            ->distinct()
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $this->clearUserCache((int) $userId);
        }
    }
}