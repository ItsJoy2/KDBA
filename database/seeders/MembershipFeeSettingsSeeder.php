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
                'key' => 'membership.registration_fee',
                'value' => '0.00',
                'type' => 'text',
                'group' => 'membership',
            ],
            [
                'key' => 'membership.yearly_fee.member',
                'value' => '0.00',
                'type' => 'text',
                'group' => 'membership',
            ],
            [
                'key' => 'membership.yearly_fee.life_member',
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
