<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([

            \Database\Seeders\Accounting\AccountGroupSeeder::class,

            \Database\Seeders\Accounting\AccountTypeSeeder::class,

            \Database\Seeders\Accounting\AccountCategorySeeder::class,
            
            \Database\Seeders\Accounting\ChartOfAccountSeeder::class,
            // ChartOfAccountSeeder nanti

        ]);
    }
}