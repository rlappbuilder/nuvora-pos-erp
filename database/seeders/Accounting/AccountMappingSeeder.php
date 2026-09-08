<?php

namespace Database\Seeders\Accounting;

use Illuminate\Database\Seeder;
use App\Models\Accounting\AccountMapping;
use App\Models\Accounting\ChartOfAccount;
use App\Models\MasterData\Company;

class AccountMappingSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::query()->get();

        $mappings = [
            'inventory_merchandise' => '110301',
            'inventory_adjustment_expense' => '510102',
            'inventory_adjustment_gain' => '710104',
            'inventory_in_transit' => '110304',
            'inventory_provision' => '110306',
            'opening_balance_equity' => '310401',
            'stock_issue_marketing_expense' => '610110',
            'stock_issue_operating_expense' => '610113',
            'goods_received_not_invoiced' => '210103',
            'input_vat' => '110501',
            'trade_payable' => '210101',
            
        ];

        foreach ($companies as $company) {

            foreach ($mappings as $key => $accountCode) {

                $account = ChartOfAccount::query()
                    ->where('company_id', $company->id)
                    ->where('code', $accountCode)
                    ->where('is_posting', true)
                    ->where('status', true)
                    ->first();

                if (! $account) {
                    continue;
                }

                AccountMapping::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'key' => $key,
                    ],
                    [
                        'account_id' => $account->id,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}