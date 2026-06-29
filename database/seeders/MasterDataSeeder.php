<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterData\Unit;
use App\Models\MasterData\Color;
use App\Models\MasterData\Size;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Units
        |--------------------------------------------------------------------------
        */

        $units = [
            ['code' => 'PCS', 'name' => 'Pieces'],
            ['code' => 'BOX', 'name' => 'Box'],
            ['code' => 'PACK', 'name' => 'Pack'],
            ['code' => 'SET', 'name' => 'Set'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['code' => $unit['code']],
                [
                    'name' => $unit['name'],
                    'status' => true,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Colors
        |--------------------------------------------------------------------------
        */

        $colors = [
            ['code' => 'BLACK', 'name' => 'Black', 'hex_color' => '#000000'],
            ['code' => 'WHITE', 'name' => 'White', 'hex_color' => '#FFFFFF'],
            ['code' => 'RED', 'name' => 'Red', 'hex_color' => '#FF0000'],
            ['code' => 'BLUE', 'name' => 'Blue', 'hex_color' => '#0000FF'],
            ['code' => 'GREEN', 'name' => 'Green', 'hex_color' => '#00FF00'],
        ];

        foreach ($colors as $color) {
            Color::firstOrCreate(
                ['code' => $color['code']],
                [
                    'name' => $color['name'],
                    'hex_color' => $color['hex_color'],
                    'status' => true,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sizes
        |--------------------------------------------------------------------------
        */

        $sizes = [
            ['code' => 'XS', 'name' => 'XS', 'sort_order' => 1],
            ['code' => 'S', 'name' => 'S', 'sort_order' => 2],
            ['code' => 'M', 'name' => 'M', 'sort_order' => 3],
            ['code' => 'L', 'name' => 'L', 'sort_order' => 4],
            ['code' => 'XL', 'name' => 'XL', 'sort_order' => 5],
            ['code' => 'XXL', 'name' => 'XXL', 'sort_order' => 6],
        ];

        foreach ($sizes as $size) {
            Size::firstOrCreate(
                ['code' => $size['code']],
                [
                    'name' => $size['name'],
                    'sort_order' => $size['sort_order'],
                    'status' => true,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Default Branch
        |--------------------------------------------------------------------------
        */

        $branch = Branch::firstOrCreate(
            ['code' => 'HO'],
            [
                'name' => 'Head Office Padang',
                'phone' => '-',
                'email' => '-',
                'address' => 'Padang',
                'status' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Default Warehouse
        |--------------------------------------------------------------------------
        */

        Warehouse::firstOrCreate(
            ['code' => 'WH-001'],
            [
                'branch_id' => $branch->id,
                'name' => 'Gudang Utama',
                'warehouse_type' => 'MAIN',
                'address' => 'Padang',
                'status' => true,
            ]
        );
    }
}