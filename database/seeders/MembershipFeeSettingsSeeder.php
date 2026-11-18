<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembershipFeeSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [

            // =====================================
            // Member Admission Fees
            // =====================================

            [
                'key' => 'membership.admission.mother_member',
                'value' => '50000.00',
                'type' => 'text',
                'group' => 'membership',
            ],
            [
                'key' => 'membership.admission.sub_member',
                'value' => '100000.00',
                'type' => 'text',
                'group' => 'membership',
            ],

            // =====================================
            // Monthly Membership Fee
            // =====================================

            [
                'key' => 'membership.recurring.monthly_membership',
                'value' => '100.00',
                'type' => 'text',
                'group' => 'membership',
            ],

            // =====================================
            // Annual Membership Fees
            // =====================================

            [
                'key' => 'membership.recurring.annual_building_fund',
                'value' => '100.00',
                'type' => 'text',
                'group' => 'membership',
            ],
            [
                'key' => 'membership.recurring.annual_library_fee',
                'value' => '100.00',
                'type' => 'text',
                'group' => 'membership',
            ],
            [
                'key' => 'membership.recurring.annual_library_card_fee',
                'value' => '100.00',
                'type' => 'text',
                'group' => 'membership',
            ],
            [
                'key' => 'membership.recurring.annual_relief_fund',
                'value' => '100.00',
                'type' => 'text',
                'group' => 'membership',
            ],
            [
                'key' => 'membership.recurring.annual_education_fund',
                'value' => '100.00',
                'type' => 'text',
                'group' => 'membership',
            ],
            [
                'key' => 'membership.recurring.annual_retirement_fund',
                'value' => '100.00',
                'type' => 'text',
                'group' => 'membership',
            ],
            [
                'key' => 'membership.recurring.annual_mb_fund',
                'value' => '2000.00',
                'type' => 'text',
                'group' => 'membership',
            ],

            // =====================================
            // Apprentice Fees
            // =====================================

            [
                'key' => 'apprentice.admission_fee',
                'value' => '1000.00',
                'type' => 'text',
                'group' => 'apprentice',
            ],
            [
                'key' => 'apprentice.card_fee',
                'value' => '500.00',
                'type' => 'text',
                'group' => 'apprentice',
            ],

            // =====================================
            // Lawyer Assistant Fees
            // =====================================

            [
                'key' => 'lawyer_assistant.card_renewal_fee',
                'value' => '200.00',
                'type' => 'text',
                'group' => 'lawyer_assistant',
            ],

            // =====================================
            // Chamber Rent
            // =====================================

            [
                'key' => 'chamber.rent.standard_monthly',
                'value' => '500.00',
                'type' => 'text',
                'group' => 'chamber',
            ],
            [
                'key' => 'chamber.rent.joint_monthly',
                'value' => '500.00',
                'type' => 'text',
                'group' => 'chamber',
            ],
            [
                'key' => 'chamber.rent.ac.first_six_months',
                'value' => '500.00',
                'type' => 'text',
                'group' => 'chamber',
            ],
            [
                'key' => 'chamber.rent.ac.remaining_six_months',
                'value' => '1000.00',
                'type' => 'text',
                'group' => 'chamber',
            ],
        ];

        // Insert settings without overwriting existing values
        $now = now();

        foreach ($settings as $setting) {
            DB::table('system_settings')->insertOrIgnore([
                ...$setting,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
