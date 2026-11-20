<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DefaultRolesSeeder::class,
            DefaultPermissionsSeeder::class,
            DefaultSuperAdminSeeder::class,
            MembershipFeeSettingsSeeder::class,
            PaymentGatewaySeeder::class,
            TransactionCategorySeeder::class,
        ]);
    }
}