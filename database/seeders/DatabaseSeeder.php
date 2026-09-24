<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\User\PermissionSeeder;
use Database\Seeders\Core\CodeGeneratorSeeder;
use Database\Seeders\MasterData\TaxSeeder;
use Database\Seeders\MasterData\CurrencySeeder;
use Database\Seeders\MasterData\PriceTypeSeeder;
use Database\Seeders\MasterData\PaymentTermSeeder;
use Database\Seeders\User\PermissionSeeder;
use Database\Seeders\User\RoleSeeder;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([

            MasterDataSeeder::class,

            AccountingSeeder::class,

            CodeGeneratorSeeder::class,

            CurrencySeeder::class,

            PriceTypeSeeder::class,

            PaymentTermSeeder::class,

            PermissionSeeder::class,

            RoleSeeder::class,


        ]);
    }
}