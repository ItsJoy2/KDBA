<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DefaultSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@example.com';
        $password = 'Admin123$';

        DB::transaction(function () use ($email, $password): void {
            DB::table('users')->insertOrIgnore([
                'full_name' => 'Super Administrator',
                'email' => $email,
                'mobile' => null,
                'password' => Hash::make($password),
                'status' => 'Active',
                'approval_status' => 'Approved',
                'two_factor_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user = DB::table('users')
                ->where('email', $email)
                ->first(['id']);

            $role = DB::table('roles')
                ->where('slug', 'super-admin')
                ->first(['id']);

            if (!$user || !$role) {
                throw new \RuntimeException(
                    'Super Admin user or role could not be initialized.'
                );
            }

            DB::table('model_has_roles')->insertOrIgnore([
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
        });
    }
}