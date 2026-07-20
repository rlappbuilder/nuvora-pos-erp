<?php

namespace Database\Seeders\Accounting;

use Illuminate\Database\Seeder;
use App\Models\Accounting\AccountType;
use App\Models\Accounting\AccountGroup;

class AccountTypeSeeder extends Seeder
{
    public function run(): void
    {
$types = [

    // Assets
    ['group' => '100000', 'code' => '110000', 'name' => 'Current Assets', 'sort_order' => 1],
    ['group' => '100000', 'code' => '120000', 'name' => 'Fixed Assets', 'sort_order' => 2],

    // Liabilities
    ['group' => '200000', 'code' => '210000', 'name' => 'Current Liabilities', 'sort_order' => 1],
    ['group' => '200000', 'code' => '220000', 'name' => 'Long Term Liabilities', 'sort_order' => 2],

    // Equity
    ['group' => '300000', 'code' => '310000', 'name' => 'Equity', 'sort_order' => 1],

    // Revenue
    ['group' => '400000', 'code' => '410000', 'name' => 'Revenue', 'sort_order' => 1],

    // Cost of Goods Sold
    ['group' => '500000', 'code' => '510000', 'name' => 'Cost of Goods Sold', 'sort_order' => 1],

    // Operating Expenses
    ['group' => '600000', 'code' => '610000', 'name' => 'Operating Expenses', 'sort_order' => 1],

    // Other Income
    ['group' => '700000', 'code' => '710000', 'name' => 'Other Income', 'sort_order' => 1],

    // Other Expense
    ['group' => '800000', 'code' => '810000', 'name' => 'Other Expense', 'sort_order' => 1],

];
        foreach ($types as $type) {

            $group = AccountGroup::where('code', $type['group'])->first();

            if (!$group) {
                continue;
            }

            AccountType::updateOrCreate(

                [
                    'code' => $type['code'],
                ],

                [
                    'account_group_id' => $group->id,
                    'name'             => $type['name'],
                    'sort_order'       => $type['sort_order'],
                    'status'           => true,
                ]

            );
        }
    }
}