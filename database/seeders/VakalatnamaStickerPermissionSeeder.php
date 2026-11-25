<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VakalatnamaStickerPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $module = 'vakalatnama_sticker';

        $permissions = [
            'view' => 'View Vakalatnama Stickers',
            'view_all' => 'View All Vakalatnama Stickers',
            'create' => 'Create Vakalatnama Stickers',
            'create_own' => 'Create Own Vakalatnama Stickers',
            'payment_verify' => 'Verify Vakalatnama Sticker Payments',
            'print' => 'Print Vakalatnama Stickers',
            'reprint' => 'Reprint Vakalatnama Stickers',
            'settings' => 'Manage Vakalatnama Sticker Settings',
        ];

        DB::transaction(function () use ($module, $permissions) {
            $permissionIds = [];

            foreach ($permissions as $action => $description) {
                $name = "{$module}.{$action}";

                // Explicit slug generation ensures correct hyphen separators.
                $slug = 'vakalatnama-sticker-' . str_replace('_', '-', $action);

                $permission = Permission::updateOrCreate(
                    ['name' => $name],
                    [
                        'slug' => $slug,
                        'module' => $module,
                        'action' => $action,
                        'description' => $description,
                    ]
                );

                $permissionIds[] = $permission->id;
            }

            $superAdminRole = Role::query()
                ->where('slug', 'super-admin')
                ->first();

            if (!$superAdminRole) {
                throw new \RuntimeException(
                    'Super Admin role was not found.'
                );
            }

            // Assign all sticker permissions to Super Admin.
            $superAdminRole->permissions()->syncWithoutDetaching(
                $permissionIds
            );
        });

        // Clear permission caches after updating permissions.
        $userIds = DB::table('model_has_roles')
            ->where('role_id', function ($query) {
                $query->select('id')
                    ->from('roles')
                    ->where('slug', 'super-admin')
                    ->limit(1);
            })
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            app(\App\Services\Rbac\PermissionService::class)
                ->clearUserCache((int) $userId);
        }

        $this->command->info(
            'Vakalatnama Sticker permissions created and assigned to Super Admin successfully.'
        );
    }
}
