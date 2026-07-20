<?php

namespace Database\Seeders\Accounting;

use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = 1;

        $accounts = [

    // Current Assets
[
    'code' => '110100',
    'name' => 'Cash & Bank',
    'category' => 'Cash & Bank',
    'parent' => null,
    'normal_balance' => 'Debit',
    'is_header' => true,
    'is_posting' => false,
],
[
    'code' => '110101',
    'name' => 'Cash',
    'category' => 'Cash & Bank',
    'parent' => '110100',
    'normal_balance' => 'Debit',
    'is_header' => false,
    'is_posting' => true,
],

[
    'code' => '110102',
    'name' => 'Bank',
    'category' => 'Cash & Bank',
    'parent' => '110100',
    'normal_balance' => 'Debit',
    'is_header' => false,
    'is_posting' => true,
],
[
    'code' => '110200',
    'name' => 'Accounts Receivable',
    'category' => 'Accounts Receivable',
    'parent' => null,
    'normal_balance' => 'Debit',
    'is_header' => true,
    'is_posting' => false,
],
[
    'code' => '110201',
    'name' => 'Trade Receivable',
    'category' => 'Accounts Receivable',
    'parent' => 110200,
    'normal_balance' => 'Debit',
    'is_header' => false,
    'is_posting' => true,
],
[
    'code' => '110202',
    'name' => 'Employee Receivable',
    'category' => 'Accounts Receivable',
    'parent' => 110200,
    'normal_balance' => 'Debit',
    'is_header' => false,
    'is_posting' => true,
],
[
    'code' => '110300',
    'name' => 'Inventory',
    'category' => 'Inventory',
    'parent' => null,
    'normal_balance' => 'Debit',
    'is_header' => true,
    'is_posting' => false,
],
[
    'code' => '110400',
    'name' => 'Prepaid Expense',
    'category' => 'Prepaid Expense',
    'parent' => null,
    'normal_balance' => 'Debit',
    'is_header' => true,
    'is_posting' => false,
],

// Fixed Assets
[
    'code' => '120100',
    'name' => 'Land',
    'category' => 'Land',
    'parent' => null,
    'normal_balance' => 'Debit',
    'is_header' => true,
    'is_posting' => false,
],
[
    'code' => '120200',
    'name' => 'Building',
    'category' => 'Building',
    'parent' => null,
    'normal_balance' => 'Debit',
    'is_header' => true,
    'is_posting' => false,
],
[
    'code' => '120300',
    'name' => 'Vehicle',
    'category' => 'Vehicle',
    'parent' => null,
    'normal_balance' => 'Debit',
    'is_header' => true,
    'is_posting' => false,
],
[
    'code' => '120400',
    'name' => 'Equipment',
    'category' => 'Equipment',
    'parent' => null,
    'normal_balance' => 'Debit',
    'is_header' => true,
    'is_posting' => false,
],

// Current Liabilities
[
    'code' => '210100',
    'name' => 'Accounts Payable',
    'category' => 'Accounts Payable',
    'parent' => null,
    'normal_balance' => 'Credit',
    'is_header' => false,
    'is_posting' => true,
],
[
    'code' => '210200',
    'name' => 'Tax Payable',
    'category' => 'Tax Payable',
    'parent' => null,
    'normal_balance' => 'Credit',
    'is_header' => false,
    'is_posting' => true,
],

// Long Term Liabilities
[
    'code' => '220100',
    'name' => 'Bank Loan',
    'category' => 'Bank Loan',
    'parent' => null,
    'normal_balance' => 'Credit',
    'is_header' => false,
    'is_posting' => true,
],

// Equity
[
    'code' => '310100',
    'name' => 'Owner Capital',
    'category' => 'Owner Capital',
    'parent' => null,
    'normal_balance' => 'Credit',
    'is_header' => false,
    'is_posting' => true,
],

// Revenue
[
    'code' => '410100',
    'name' => 'Sales Revenue',
    'category' => 'Sales Revenue',
    'parent' => null,
    'normal_balance' => 'Credit',
    'is_header' => false,
    'is_posting' => true,
],

// Cost of Goods Sold
[
    'code' => '510100',
    'name' => 'Cost of Goods Sold',
    'category' => 'Cost of Goods Sold',
    'parent' => null,
    'normal_balance' => 'Debit',
    'is_header' => false,
    'is_posting' => true,
],

// Operating Expenses
[
    'code' => '610100',
    'name' => 'Operating Expense',
    'category' => 'Operating Expense',
    'parent' => null,
    'normal_balance' => 'Debit',
    'is_header' => false,
    'is_posting' => true,
],

// Other Income
[
    'code' => '710100',
    'name' => 'Other Income',
    'category' => 'Other Income',
    'parent' => null,
    'normal_balance' => 'Credit',
    'is_header' => false,
    'is_posting' => true,
],

// Other Expense
[
    'code' => '810100',
    'name' => 'Other Expense',
    'category' => 'Other Expense',
    'parent' => null,
    'normal_balance' => 'Debit',
    'is_header' => false,
    'is_posting' => true,
],
];

        foreach ($accounts as $account) {

            $category = AccountCategory::where('name', $account['category'])->firstOrFail();

            $parent = null;
            $level = 1;

            if ($account['parent']) {
                $parent = ChartOfAccount::where('code', $account['parent'])
                    ->where('company_id', $companyId)
                    ->first();

                $level = $parent ? $parent->level + 1 : 1;
            }

            ChartOfAccount::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'code'       => $account['code'],
                ],
                [
                    'parent_id'           => $parent?->id,
                    'account_category_id' => $category->id,
                    'name'                => $account['name'],
                    'normal_balance'      => $account['normal_balance'],
                    'level'               => $level,
                    'is_header'           => $account['is_header'],
                    'is_posting'          => $account['is_posting'],
                    'opening_balance'     => 0,
                    'status'              => true,
                    'description'         => null,
                ]
            );
        }
    }
}