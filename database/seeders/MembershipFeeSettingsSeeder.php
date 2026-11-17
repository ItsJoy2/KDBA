<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembershipFeeSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'membership.monthly_rent',
                'value' => '0.00',
                'type' => 'text',
                'group' => 'membership',
            ],
            [
                'key' => 'membership.yearly_fee',
                'value' => '0.00',
                'type' => 'text',
                'group' => 'membership',
            ],

        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->insertOrIgnore([
                ...$setting,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
