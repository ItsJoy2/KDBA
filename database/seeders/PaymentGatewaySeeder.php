<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            ['name' => 'bKash Personal', 'type' => 'mobile_banking'],
            ['name' => 'bKash Merchant', 'type' => 'mobile_banking'],
            ['name' => 'Nagad', 'type' => 'mobile_banking'],
            ['name' => 'Rocket', 'type' => 'mobile_banking'],
            ['name' => 'Bank Transfer', 'type' => 'bank'],
        ];

        foreach ($gateways as $index => $gateway) {
            DB::table('payment_gateways')->updateOrInsert(
                ['name' => $gateway['name']],
                [
                    'type' => $gateway['type'],
                    'account_number' => null,
                    'instruction' => null,
                    'is_active' => false,
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // Cash payment gateway.
        DB::table('payment_gateways')->updateOrInsert(
            ['name' => 'Cash'],
            [
                'type' => 'cash',
                'account_number' => null,
                'instruction' => 'Direct cash payment received.',
                'is_active' => true,
                'sort_order' => count($gateways) + 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}