<?php

namespace Database\Seeders\Accounting;

use Illuminate\Database\Seeder;
use App\Models\Accounting\AccountType;
use App\Models\Accounting\AccountCategory;

class AccountCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

    // Current Assets
    ['type' => '110000', 'code' => '110100', 'name' => 'Cash & Bank', 'sort_order' => 1],
    ['type' => '110000', 'code' => '110200', 'name' => 'Accounts Receivable', 'sort_order' => 2],
    ['type' => '110000', 'code' => '110300', 'name' => 'Inventory', 'sort_order' => 3],
    ['type' => '110000', 'code' => '110400', 'name' => 'Prepaid Expense', 'sort_order' => 4],

    // Fixed Assets
    ['type' => '120000', 'code' => '120100', 'name' => 'Land', 'sort_order' => 1],
    ['type' => '120000', 'code' => '120200', 'name' => 'Building', 'sort_order' => 2],
    ['type' => '120000', 'code' => '120300', 'name' => 'Vehicle', 'sort_order' => 3],
    ['type' => '120000', 'code' => '120400', 'name' => 'Equipment', 'sort_order' => 4],

    // Current Liabilities
    ['type' => '210000', 'code' => '210100', 'name' => 'Accounts Payable', 'sort_order' => 1],
    ['type' => '210000', 'code' => '210200', 'name' => 'Tax Payable', 'sort_order' => 2],

    // Long Term Liabilities
    ['type' => '220000', 'code' => '220100', 'name' => 'Bank Loan', 'sort_order' => 1],

    // Equity
    ['type' => '310000', 'code' => '310100', 'name' => 'Owner Capital', 'sort_order' => 1],

    // Revenue
    ['type' => '410000', 'code' => '410100', 'name' => 'Sales Revenue', 'sort_order' => 1],

    // Cost of Goods Sold
    ['type' => '510000', 'code' => '510100', 'name' => 'Cost of Goods Sold', 'sort_order' => 1],

    // Operating Expenses
    ['type' => '610000', 'code' => '610100', 'name' => 'Operating Expense', 'sort_order' => 1],

    // Other Income
    ['type' => '710000', 'code' => '710100', 'name' => 'Other Income', 'sort_order' => 1],

    // Other Expense
    ['type' => '810000', 'code' => '810100', 'name' => 'Other Expense', 'sort_order' => 1],

];

        foreach ($categories as $category) {

            $type = AccountType::where('code', $category['type'])->first();

            if (!$type) {
                continue;
            }

            AccountCategory::updateOrCreate(

                [
                    'code' => $category['code'],
                ],

                [
                    'account_type_id' => $type->id,
                    'name'            => $category['name'],
                    'sort_order'      => $category['sort_order'],
                    'status'          => true,
                ]
            );
        }
    }
}