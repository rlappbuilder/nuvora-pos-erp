<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\Core\CodeGeneratorSeeder;
use Database\Seeders\MasterData\TaxSeeder;
use Database\Seeders\MasterData\CurrencySeeder;
use Database\Seeders\MasterData\PriceTypeSeeder;
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
            currencySeeder::class,
            PriceTypeSeeder::class,
        ]);
    }
}
