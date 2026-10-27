<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DefaultPermissionsSeeder::class,
            DefaultRolesSeeder::class,
            DefaultSuperAdminSeeder::class,
        ]);
    }
}