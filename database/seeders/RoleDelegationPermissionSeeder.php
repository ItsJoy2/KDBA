<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RoleDelegationPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name'   => 'Assign User Roles',
                'slug'   => 'users.assign_roles',
                'module' => 'Users',
                'action' => 'assign_roles',
            ],
            [
                'name'   => 'Manage Role Permissions',
                'slug'   => 'roles.permissions.manage',
                'module' => 'Roles',
                'action' => 'manage_permissions',
            ],
        ];

        foreach ($permissions as $data) {
            Permission::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        // Super Admin-কে permission দুটো দেওয়া
        $superAdmin = Role::where('slug', 'super-admin')->first();

        if ($superAdmin) {
            $permissionIds = Permission::whereIn('slug', [
                'users.assign_roles',
                'roles.permissions.manage',
            ])->pluck('id');

            $superAdmin->permissions()->syncWithoutDetaching(
                $permissionIds
            );
        }
    }
}