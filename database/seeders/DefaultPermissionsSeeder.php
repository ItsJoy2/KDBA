<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DefaultPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'dashboard' => ['view'],

            'members' => [
                'view', 'create', 'edit', 'delete', 'export',
            ],

            'roles' => [
                'view', 'create', 'edit', 'delete',
            ],

            'permissions' => [
                'view', 'assign',
            ],

            'users' => [
                'view', 'create', 'edit', 'delete', 'approve', 'change_status',
            ],

            'profile' => [
                'view', 'edit',
            ],

            'settings' => [
                'view', 'edit',
            ],
            'assistants' => [
                'view', 'view_assigned_lawyer',
            ],

            'reports' => [
                'view', 'export',
            ],

            'audit_logs' => [
                'view', 'export',
            ],
        ];

        $customPermissions = [
            'users.approve' => [
                'name' => 'Approve Users',
                'description' => 'Approve or reject user registration requests.',
            ],
            'users.change_status' => [
                'name' => 'Change Member Status',
                'description' => 'Change a member status such as active or suspended.',
            ],
            'assistants.view_assigned_lawyer' => [
                'name' => 'View Assigned Lawyer',
                'description' => 'Allows viewing the assigned lawyer details of an assistant or apprentice.',
            ],
        ];

        DB::transaction(function () use ($modules, $customPermissions): void {
            foreach ($modules as $module => $actions) {
                foreach ($actions as $action) {
                    $slug = "{$module}.{$action}";

                    $custom = $customPermissions[$slug] ?? [];

                    DB::table('permissions')->updateOrInsert(
                        [
                            'module' => $module,
                            'action' => $action,
                        ],
                        [
                            'name' => $custom['name'] ?? Str::headline("{$module} {$action}"),
                            'slug' => $slug,
                            'description' => $custom['description'] ?? sprintf(
                                'Allows the user to %s %s.',
                                $action,
                                str_replace('_', ' ', $module)
                            ),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            // Create or update Super Admin role.
            Role::updateOrCreate(
                ['slug' => 'super-admin'],
                [
                    'name' => 'Super Admin',
                    'description' => 'System administrator with full access.',
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        });
    }
}
