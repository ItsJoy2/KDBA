<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class VakalatnamaStickerSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'vakalatnama_sticker_price',
                'value' => '300.00',
                'type' => 'text',
                'group' => 'vakalatnama_sticker',
            ],
            [
                'key' => 'vakalatnama_sticker_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'vakalatnama_sticker',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                ]
            );
        }
    }
}
