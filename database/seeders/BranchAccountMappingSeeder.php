<?php

namespace Database\Seeders;

use App\Models\Accounting\BranchAccountMapping;
use App\Models\Accounting\ChartOfAccount;
use App\Models\MasterData\Branch;
use Illuminate\Database\Seeder;

class BranchAccountMappingSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 2;

        $mappings = [

            /*
            |--------------------------------------------------------------------------
            | Ralisa Store LB 1
            |--------------------------------------------------------------------------
            */
            [
                'branch_code' => 'BR0002',
                'cashier_drawer' => '110111',
                'cash_coh' => '110114',
            ],

            /*
            |--------------------------------------------------------------------------
            | Ralisa Konveksi
            |--------------------------------------------------------------------------
            */
            [
                'branch_code' => 'BR0005',
                'cashier_drawer' => '110112',
                'cash_coh' => '110115',
            ],

            /*
            |--------------------------------------------------------------------------
            | Cab. GHAS
            |--------------------------------------------------------------------------
            */
            [
                'branch_code' => 'BR0003',
                'cashier_drawer' => '110113',
                'cash_coh' => '110116',
            ],
        ];

        foreach ($mappings as $mapping) {

            $branch = Branch::query()
                ->where('company_id', $companyId)
                ->where('code', $mapping['branch_code'])
                ->firstOrFail();

            foreach ([
                'cashier_drawer' => $mapping['cashier_drawer'],
                'cash_coh' => $mapping['cash_coh'],
            ] as $key => $accountCode) {

                $account = ChartOfAccount::query()
                    ->where('company_id', $companyId)
                    ->where('code', $accountCode)
                    ->where('is_posting', true)
                    ->where('status', true)
                    ->firstOrFail();

                BranchAccountMapping::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'branch_id' => $branch->id,
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