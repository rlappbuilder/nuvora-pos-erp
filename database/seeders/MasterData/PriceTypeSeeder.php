<?php

namespace Database\Seeders\MasterData;

use Illuminate\Database\Seeder;
use App\Models\MasterData\PriceType;

class PriceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [

            [
                'code'        => 'PURCHASE',
                'name'        => 'Purchase',
                'description' => 'Purchase price from supplier',
                'sort_order'  => 1,
                'is_default'   => true,
                'is_active'   => true,
            ],

            [
                'code'        => 'COST',
                'name'        => 'Cost',
                'description' => 'Cost of goods',
                'sort_order'  => 2,
                'is_default'   => false,
                'is_active'   => true,
            ],

            [
                'code'        => 'WHOLESALE',
                'name'        => 'Wholesale',
                'description' => 'Wholesale selling price',
                'sort_order'  => 3,
                'is_default'   => false,
                'is_active'   => true,
            ],

            [
                'code'        => 'RETAIL',
                'name'        => 'Retail',
                'description' => 'Retail selling price',
                'sort_order'  => 4,
                'is_default'   => true,
                'is_active'   => true,
            ],

            [
                'code'        => 'MEMBER',
                'name'        => 'Member',
                'description' => 'Member selling price',
                'sort_order'  => 5,
                'is_default'   => false,
                'is_active'   => true,
            ],

            [
                'code'        => 'PROMO',
                'name'        => 'Promotion',
                'description' => 'Promotional selling price',
                'sort_order'  => 6,
                'is_default'   => false,
                'is_active'   => true,
            ],

        ];

        foreach ($items as $item) {

            PriceType::updateOrCreate(

                [
                    'code' => $item['code'],
                ],

                $item

            );

        }
    }
}