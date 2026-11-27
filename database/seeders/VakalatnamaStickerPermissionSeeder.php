<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class VakalatnamaStickerPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'vakalatnama.view' => [
                'name' => 'View Vakalatnama Stickers',
                'action' => 'view',
                'description' => 'View Vakalatnama sticker records.',
            ],

            'vakalatnama.view_all' => [
                'name' => 'View All Vakalatnama Stickers',
                'action' => 'view_all',
                'description' => 'View all members Vakalatnama sticker orders.',
            ],

            'vakalatnama.view_own' => [
                'name' => 'View Own Vakalatnama Stickers',
                'action' => 'view_own',
                'description' => 'View own Vakalatnama sticker orders.',
            ],

            'vakalatnama.create' => [
                'name' => 'Create Vakalatnama Sticker Orders',
                'action' => 'create',
                'description' => 'Create sticker orders for any eligible member.',
            ],

            'vakalatnama.create_own' => [
                'name' => 'Create Own Vakalatnama Sticker Orders',
                'action' => 'create_own',
                'description' => 'Create sticker orders for own account only.',
            ],

            'vakalatnama.pay' => [
                'name' => 'Pay Vakalatnama Sticker Orders',
                'action' => 'pay',
                'description' => 'Submit or record payments for sticker orders.',
            ],

            'vakalatnama.print' => [
                'name' => 'Print Vakalatnama Stickers',
                'action' => 'print',
                'description' => 'Print eligible paid stickers for the first time.',
            ],

            'vakalatnama.reprint' => [
                'name' => 'Reprint Vakalatnama Stickers',
                'action' => 'reprint',
                'description' => 'Authorize and perform sticker reprints.',
            ],

            'vakalatnama.verify' => [
                'name' => 'Verify Vakalatnama Stickers',
                'action' => 'verify',
                'description' => 'Access sticker verification operations.',
            ],

            'vakalatnama.settings' => [
                'name' => 'Manage Vakalatnama Sticker Settings',
                'action' => 'settings',
                'description' => 'Manage sticker prices and feature settings.',
            ],
        ];

        $permissionIds = [];

        foreach ($permissions as $slug => $permission) {
            $record = Permission::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $permission['name'],
                    'module' => 'vakalatnama',
                    'action' => $permission['action'],
                    'description' => $permission['description'],
                ]
            );

            $permissionIds[] = $record->id;
        }

        /*
         * Super Admin:
         * Assign all Vakalatnama permissions.
         */
        $superAdmin = Role::where('slug', 'super-admin')->first();

        if ($superAdmin) {
            $superAdmin->permissions()->syncWithoutDetaching(
                $permissionIds
            );
        }

        /*
         * Member:
         * Only own order viewing and creation.
         */
        $member = Role::where('slug', 'member')->first();

        if ($member) {
            $memberPermissionIds = Permission::whereIn('slug', [
                'vakalatnama.view_own',
                'vakalatnama.create_own',
            ])->pluck('id')->all();

            $member->permissions()->syncWithoutDetaching(
                $memberPermissionIds
            );
        }
    }
}
