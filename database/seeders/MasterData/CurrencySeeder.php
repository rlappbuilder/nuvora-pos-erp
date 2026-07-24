<?php

namespace Database\Seeders\MasterData;

use Illuminate\Database\Seeder;
use App\Models\MasterData\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [

            [
                'code'             => 'IDR',
                'name'             => 'Indonesian Rupiah',
                'symbol'           => 'Rp',
                'decimal_places'   => 0,
                'exchange_rate'    => 1,
                'is_base_currency' => true,
                'is_active'        => true,
                'description'      => 'Default base currency.',
            ],

            [
                'code'             => 'USD',
                'name'             => 'US Dollar',
                'symbol'           => '$',
                'decimal_places'   => 2,
                'exchange_rate'    => 16000,
                'is_base_currency' => false,
                'is_active'        => true,
                'description'      => 'United States Dollar.',
            ],

            [
                'code'             => 'EUR',
                'name'             => 'Euro',
                'symbol'           => '€',
                'decimal_places'   => 2,
                'exchange_rate'    => 17500,
                'is_base_currency' => false,
                'is_active'        => true,
                'description'      => 'Euro currency.',
            ],

            [
                'code'             => 'SGD',
                'name'             => 'Singapore Dollar',
                'symbol'           => 'S$',
                'decimal_places'   => 2,
                'exchange_rate'    => 12000,
                'is_base_currency' => false,
                'is_active'        => true,
                'description'      => 'Singapore Dollar.',
            ],

            [
                'code'             => 'MYR',
                'name'             => 'Malaysian Ringgit',
                'symbol'           => 'RM',
                'decimal_places'   => 2,
                'exchange_rate'    => 3800,
                'is_base_currency' => false,
                'is_active'        => true,
                'description'      => 'Malaysian Ringgit.',
            ],

            [
                'code'             => 'JPY',
                'name'             => 'Japanese Yen',
                'symbol'           => '¥',
                'decimal_places'   => 0,
                'exchange_rate'    => 110,
                'is_base_currency' => false,
                'is_active'        => true,
                'description'      => 'Japanese Yen.',
            ],

            [
                'code'             => 'CNY',
                'name'             => 'Chinese Yuan',
                'symbol'           => '¥',
                'decimal_places'   => 2,
                'exchange_rate'    => 2200,
                'is_base_currency' => false,
                'is_active'        => true,
                'description'      => 'Chinese Yuan.',
            ],

            [
                'code'             => 'AUD',
                'name'             => 'Australian Dollar',
                'symbol'           => 'A$',
                'decimal_places'   => 2,
                'exchange_rate'    => 10500,
                'is_base_currency' => false,
                'is_active'        => true,
                'description'      => 'Australian Dollar.',
            ],

            [
                'code'             => 'GBP',
                'name'             => 'British Pound',
                'symbol'           => '£',
                'decimal_places'   => 2,
                'exchange_rate'    => 20500,
                'is_base_currency' => false,
                'is_active'        => true,
                'description'      => 'British Pound Sterling.',
            ],

        ];

        foreach ($currencies as $currency) {

            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );

        }
    }
}