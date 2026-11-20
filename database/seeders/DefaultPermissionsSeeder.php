<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DefaultPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'dashboard' => ['view'],

            'members' => [
                'view',
                'create',
                'edit',
                'delete',
                'export',
            ],

            'roles' => [
                'view',
                'create',
                'edit',
                'delete',
            ],

            'permissions' => [
                'view',
                'assign',
            ],

            'users' => [
                'view',
                'create',
                'edit',
                'delete',
                'approve',
                'change_status',
            ],

            'profile' => [
                'view',
                'edit',
            ],

            'settings' => [
                'view',
                'edit',
            ],

            'assistants' => [
                'view',
                'view_assigned_lawyer',
            ],

            'reports' => [
                'view',
                'export',
            ],

            'audit_logs' => [
                'view',
                'export',
            ],

            'products' => [
                'view',
                'create',
                'edit',
                'delete',
                'buy',
                'buy_for_others',
            ],

            'orders' => [
                'view',
                'view_own',
                'view_all',
                'manage',
                'status_update',
                'update_status',
            ],

            'fees' => [
                'manage',
            ],

            'gateways' => [
                'manage',
            ],

            'invoices' => [
                'view',
                'create',
                'pay',
            ],

            'transactions' => [
                'view',
                'view_all' => 'View all financial transactions',
                'view_own' => 'View own financial transactions',
                'create' => 'Create manual financial transactions',
                'verify',
            ],

            'finance_categories' => [
                'view' => 'View financial transaction categories',
                'create' => 'Create financial transaction categories',
                'edit' => 'Edit financial transaction categories',
                'delete' => 'Delete financial transaction categories',
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

            'products.view' => [
                'name' => 'View Products',
                'description' => 'View products.',
            ],

            'products.create' => [
                'name' => 'Create Products',
                'description' => 'Create new products.',
            ],

            'products.edit' => [
                'name' => 'Edit Products',
                'description' => 'Edit existing products.',
            ],

            'products.delete' => [
                'name' => 'Delete Products',
                'description' => 'Delete products.',
            ],

            'products.buy' => [
                'name' => 'Purchase Products',
                'description' => 'Purchase products.',
            ],

            'products.buy_for_others' => [
                'name' => 'Purchase for Others',
                'description' => 'Purchase products on behalf of eligible members.',
            ],

            'orders.view' => [
                'name' => 'View Orders',
                'description' => 'View orders permitted by the assigned role.',
            ],

            'orders.view_all' => [
                'name' => 'View All Orders',
                'description' => 'View all orders across members.',
            ],

            'orders.view_own' => [
                'name' => 'View Own Orders',
                'description' => 'Allows viewing order details belonging to the authenticated user.',
            ],

            'orders.manage' => [
                'name' => 'Manage Orders',
                'description' => 'Manage orders.',
            ],

            'orders.status_update' => [
                'name' => 'Update Order Status',
                'description' => 'Update the status of orders.',
            ],

            'orders.update_status' => [
                'name' => 'Update Order Status (Legacy)',
                'description' => 'Legacy permission for updating order status.',
            ],

            'transactions.view' => [
                'name' => 'View Financial Transactions',
                'description' => 'View financial transactions for payment verification and authorized operations.',
            ],

            'transactions.view_all' => [
                'name' => 'View All Financial Transactions',
                'description' => 'View all financial transactions across members.',
            ],

            'transactions.view_own' => [
                'name' => 'View Own Financial Transactions',
                'description' => 'View only financial transactions belonging to the authenticated user.',
            ],

            'transactions.create' => [
                'name' => 'Create Financial Transactions',
                'description' => 'Create manual financial transactions.',
            ],

            'transactions.verify' => [
                'name' => 'Verify Transactions',
                'description' => 'Verify or reject financial transactions.',
            ],
        ];

        DB::transaction(function () use (
            $modules,
            $customPermissions
        ): void {

            /*
             * Create or update all permissions.
             */
            foreach ($modules as $module => $actions) {
                foreach ($actions as $action => $description) {

                    // Support indexed and associative definitions.
                    if (is_int($action)) {
                        $action = $description;
                        $description = null;
                    }

                    $slug = "{$module}.{$action}";
                    $custom = $customPermissions[$slug] ?? [];

                    DB::table('permissions')->updateOrInsert(
                        [
                            'module' => $module,
                            'action' => $action,
                        ],
                        [
                            'name' => $custom['name']
                                ?? Str::headline("{$module} {$action}"),

                            'slug' => $slug,

                            'description' => $custom['description']
                                ?? $description
                                ?? sprintf(
                                    'Allows the user to %s %s.',
                                    $action,
                                    str_replace('_', ' ', $module)
                                ),

                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }

            /*
             * Create or update Super Admin role.
             */
            $superAdmin = Role::updateOrCreate(
                ['slug' => 'super-admin'],
                [
                    'name' => 'Super Admin',
                    'description' => 'System administrator with full access.',
                    'is_system' => true,
                    'is_active' => true,
                ]
            );

            /*
             * Assign all permissions to Super Admin.
             */
            $permissionIds = DB::table('permissions')->pluck('id');

            $superAdminPermissions = $permissionIds
                ->map(fn ($permissionId) => [
                    'role_id' => $superAdmin->id,
                    'permission_id' => $permissionId,
                ])
                ->all();

            DB::table('role_has_permissions')->insertOrIgnore(
                $superAdminPermissions
            );

            /*
             * Find Member role.
             */
            $member = Role::query()
                ->where('slug', 'member')
                ->first();

            if (! $member) {
                throw new RuntimeException(
                    'Member role not found. Run DefaultRolesSeeder first.'
                );
            }

            /*
             * Default permissions for Member.
             */
            $memberPermissionSlugs = [
                'products.buy',
                'orders.view', 
                'orders.view_own'
            ];

            $memberPermissionIds = DB::table('permissions')
                ->whereIn('slug', $memberPermissionSlugs)
                ->pluck('id');

            if ($memberPermissionIds->count() !== count($memberPermissionSlugs)) {
                throw new RuntimeException(
                    'One or more required Member permissions were not found.'
                );
            }

            $memberPermissions = $memberPermissionIds
                ->map(fn ($permissionId) => [
                    'role_id' => $member->id,
                    'permission_id' => $permissionId,
                ])
                ->all();

            DB::table('role_has_permissions')->insertOrIgnore(
                $memberPermissions
            );
        });

        /*
         * Clear caches AFTER successful transaction.
         */
        Cache::forget('permissions');
        Cache::forget('roles');

        $this->command?->info(
            'Default permissions seeded successfully.'
        );

        $this->command?->info(
            'Super Admin: All permissions assigned.'
        );

        $this->command?->info(
            'Member: products.buy, orders.view, orders.view_own assigned.'
        );
    }
}
