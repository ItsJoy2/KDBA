<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionCategorySeeder extends Seeder
{
    public function run(): void
    {
        $systemCategories = [
            'invoice',
            'membership-fees',
            'monthly-fees',
            'yearly-fees',
        ];

        $categories = [
            // Income Categories
            [
                'name' => 'Membership Fees',
                'slug' => 'membership-fees',
                'type' => 'income',
                'description' => 'Initial membership registration fees.',
            ],
            [
                'name' => 'Monthly Fees',
                'slug' => 'monthly-fees',
                'type' => 'income',
                'description' => 'Monthly membership fees.',
            ],
            [
                'name' => 'Yearly Fees',
                'slug' => 'yearly-fees',
                'type' => 'income',
                'description' => 'Annual membership fees.',
            ],
            [
                'name' => 'Invoice',
                'slug' => 'invoice',
                'type' => 'income',
                'description' => 'Income received from invoice payments.',
            ],
            [
                'name' => 'Product Sales',
                'slug' => 'product-sales',
                'type' => 'income',
                'description' => 'Income generated from shop product sales.',
            ],
            [
                'name' => 'Hall Rent',
                'slug' => 'hall-rent',
                'type' => 'income',
                'description' => 'Income generated from hall rental.',
            ],
            [
                'name' => 'Shop Rent',
                'slug' => 'shop-rent',
                'type' => 'income',
                'description' => 'Income received from shop rent.',
            ],
            [
                'name' => 'Other Income',
                'slug' => 'other-income',
                'type' => 'income',
                'description' => 'Other miscellaneous income.',
            ],

            // Expense Categories
            [
                'name' => 'Salary',
                'slug' => 'salary',
                'type' => 'expense',
                'description' => 'Staff and employee salaries.',
            ],
            [
                'name' => 'Office Maintenance',
                'slug' => 'office-maintenance',
                'type' => 'expense',
                'description' => 'Office repair and maintenance costs.',
            ],
            [
                'name' => 'Electricity Bill',
                'slug' => 'electricity-bill',
                'type' => 'expense',
                'description' => 'Office electricity expenses.',
            ],
            [
                'name' => 'Internet Bill',
                'slug' => 'internet-bill',
                'type' => 'expense',
                'description' => 'Office internet and connectivity expenses.',
            ],
            [
                'name' => 'Stationery',
                'slug' => 'stationery',
                'type' => 'expense',
                'description' => 'Office stationery and supplies.',
            ],
            [
                'name' => 'Product Cost / COGS',
                'slug' => 'product-cost-cogs',
                'type' => 'expense',
                'description' => 'Purchase or manufacturing cost of sold products.',
            ],
            [
                'name' => 'Event Expenses',
                'slug' => 'event-expenses',
                'type' => 'expense',
                'description' => 'Bar association events and programs.',
            ],
            [
                'name' => 'Other Expense',
                'slug' => 'other-expense',
                'type' => 'expense',
                'description' => 'Other miscellaneous expenses.',
            ],
        ];

        DB::transaction(function () use ($categories, $systemCategories) {
            foreach ($categories as $category) {
                $isSystem = in_array(
                    $category['slug'],
                    $systemCategories,
                    true
                );

                DB::table('transaction_categories')->updateOrInsert(
                    ['slug' => $category['slug']],
                    [
                        'name' => $category['name'],
                        'type' => $category['type'],
                        'description' => $category['description'],
                        'is_system' => $isSystem,
                        'is_active' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });
    }
}
