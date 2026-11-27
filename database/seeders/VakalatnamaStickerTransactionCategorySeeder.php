<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VakalatnamaStickerTransactionCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('transaction_categories')->updateOrInsert(
            [
                'slug' => 'vakalatnama-sticker',
            ],
            [
                'name' => 'Vakalatnama Sticker',
                'type' => 'income',
                'description' => 'Income generated from Vakalatnama sticker orders.',
                'is_active' => true,
                'is_system' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
