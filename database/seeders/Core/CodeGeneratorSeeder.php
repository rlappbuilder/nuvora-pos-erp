<?php

namespace Database\Seeders\Core;

use Illuminate\Database\Seeder;
use App\Models\Core\CodeGenerator;

class CodeGeneratorSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [

            ['module' => 'category',         'prefix' => 'CAT', 'format' => '{PREFIX}{SEQ}',               'digit' => 4],
            ['module' => 'unit',             'prefix' => 'UNT', 'format' => '{PREFIX}{SEQ}',               'digit' => 4],
            ['module' => 'brand',            'prefix' => 'BRD', 'format' => '{PREFIX}{SEQ}',               'digit' => 4],
            ['module' => 'color',            'prefix' => 'CLR', 'format' => '{PREFIX}{SEQ}',               'digit' => 4],
            ['module' => 'size',             'prefix' => 'SIZ', 'format' => '{PREFIX}{SEQ}',               'digit' => 4],
            ['module' => 'warehouse',        'prefix' => 'WH',  'format' => '{PREFIX}{SEQ}',               'digit' => 4],
            ['module' => 'supplier',         'prefix' => 'SUP', 'format' => '{PREFIX}{SEQ}',               'digit' => 4],
            ['module' => 'customer',         'prefix' => 'CUS', 'format' => '{PREFIX}{SEQ}',               'digit' => 4],
            ['module' => 'cash_bank',        'prefix' => 'CB',  'format' => '{PREFIX}{SEQ}',               'digit' => 4],

            ['module' => 'purchase_order',   'prefix' => 'PO',  'format' => '{PREFIX}-{YYYY}-{SEQ}',       'digit' => 6],
            ['module' => 'purchase_return',  'prefix' => 'PR',  'format' => '{PREFIX}-{YYYY}-{SEQ}',       'digit' => 6],

            ['module' => 'sales_order',      'prefix' => 'SO',  'format' => '{PREFIX}-{YYYY}-{SEQ}',       'digit' => 6],
            ['module' => 'sales_return',     'prefix' => 'SR',  'format' => '{PREFIX}-{YYYY}-{SEQ}',       'digit' => 6],

            ['module' => 'journal',          'prefix' => 'JR',  'format' => '{PREFIX}-{YYYY}-{MM}-{SEQ}',  'digit' => 6],

        ];

        foreach ($modules as $module) {

            CodeGenerator::updateOrCreate(

                [
                    'company_id' => null,
                    'module' => $module['module'],
                ],

                [
                    'prefix' => $module['prefix'],
                    'format' => $module['format'],
                    'separator' => '-',
                    'digit' => $module['digit'],
                    'next_number' => 1,
                    'reset_type' => 'Never',
                    'is_active' => true,
                ]

            );
        }
    }
}