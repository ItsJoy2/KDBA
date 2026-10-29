<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultRolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            // 'system' => [
            //     'name' => 'System',
            //     'description' => 'Protected system role.',
            // ],

            'super-admin' => [
                'name' => 'Super Admin',
                'description' => 'Full administrative access.',
            ],

            'member' => [
                'name' => 'Member',
                'description' => 'Registered bar association member.',
            ],

            'general-secretary' => [
                'name' => 'General Secretary',
                'description' => 'Authorized association administration.',
            ],

            'office-staff' => [
                'name' => 'Office Staff',
                'description' => 'Operational office access.',
            ],

            'user-apprentice' => [
                'name' => 'User/Apprentice',
                'description' => 'Basic user and apprentice access.',
            ],
        ];

        DB::transaction(function () use ($roles): void {
            foreach ($roles as $slug => $data) {
                DB::table('roles')->updateOrInsert(
                    ['slug' => $slug],
                    [
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'is_system' => $slug === 'super-admin',
                        'is_active' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            $roleIds = DB::table('roles')
                ->whereIn('slug', array_keys($roles))
                ->pluck('id', 'slug');

            $permissionIds = Permission::query()
                ->pluck('id', 'slug');

            $baselinePermissions = [
                'member' => [
                    'dashboard.view',
                    'profile.view',
                    'profile.edit',
                ],

                'general-secretary' => [
                    'dashboard.view',
                    'members.view',
                    'members.create',
                    'members.edit',
                    'members.export',
                    'reports.view',
                    'reports.export',
                    'profile.view',
                    'profile.edit',
                    'assistants.view_assigned_lawyer',
                ],

                'office-staff' => [
                    'dashboard.view',
                    'members.view',
                    'members.create',
                    'members.edit',
                    'profile.view',
                    'profile.edit',
                ],

                'user-apprentice' => [
                    'dashboard.view',
                    'profile.view',
                    'profile.edit',
                ],
            ];

            // Assign every existing permission to Super Admin.
            $superAdmin = Role::query()
                ->where('slug', 'super-admin')
                ->firstOrFail();

            $superAdmin->permissions()->sync(
                Permission::query()->pluck('id')->all()
            );

            // Assign baseline permissions to other roles.
            foreach ($baselinePermissions as $roleSlug => $slugs) {
                $roleId = $roleIds->get($roleSlug);

                if (!$roleId) {
                    continue;
                }

                foreach ($slugs as $permissionSlug) {
                    $permissionId = $permissionIds->get(
                        $permissionSlug
                    );

                    if (!$permissionId) {
                        continue;
                    }

                    DB::table('role_has_permissions')->insertOrIgnore([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
        });
    }
}
