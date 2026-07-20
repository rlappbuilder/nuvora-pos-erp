<?php

namespace Database\Seeders\Accounting;

use Illuminate\Database\Seeder;
use App\Models\Accounting\AccountGroup;

class AccountGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [

            [
                'code' => '100000',
                'name' => 'Assets',
                'sort_order' => 1,
            ],

            [
                'code' => '200000',
                'name' => 'Liabilities',
                'sort_order' => 2,
            ],

            [
                'code' => '300000',
                'name' => 'Equity',
                'sort_order' => 3,
            ],

            [
                'code' => '400000',
                'name' => 'Revenue',
                'sort_order' => 4,
            ],

            [
                'code' => '500000',
                'name' => 'Cost of Goods Sold',
                'sort_order' => 5,
            ],

            [
                'code' => '600000',
                'name' => 'Expense',
                'sort_order' => 6,
            ],

            [
                'code' => '700000',
                'name' => 'Other Income',
                'sort_order' => 7,
            ],

            [
                'code' => '800000',
                'name' => 'Other Expense',
                'sort_order' => 8,
            ],

        ];

        foreach ($groups as $group) {

            AccountGroup::updateOrCreate(

                [

                    'code' => $group['code'],

                ],

                [

                    'name' => $group['name'],

                    'sort_order' => $group['sort_order'],

                    'status' => true,

                ]

            );

        }
    }
}