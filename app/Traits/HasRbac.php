<?php

namespace App\Traits;

use App\Models\Role;
use App\Services\Rbac\PermissionService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRbac
{
    public function hasRole(string $slug): bool
    {
        return $this->roles()
            ->where('roles.slug', $slug)
            ->where('roles.is_active', true)
            ->exists();
    }

    public function hasAnyRole(array $slugs): bool
    {
        if ($slugs === []) {
            return false;
        }

        return $this->roles()
            ->whereIn('roles.slug', $slugs)
            ->where('roles.is_active', true)
            ->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return app(PermissionService::class)
            ->userHasPermission($this, $permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        if ($permissions === []) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Optional direct relationship access when needed.
     */
    public function assignedRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'model_has_roles',
            'user_id',
            'role_id'
        );
    }
}