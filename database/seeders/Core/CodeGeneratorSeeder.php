<?php

namespace Database\Seeders\Core;

use Illuminate\Database\Seeder;
use App\Models\Core\CodeGenerator;

class CodeGeneratorSeeder extends Seeder
{
    public function run(): void
    {
$modules = [

    // ==========================
    // Master Data
    // ==========================
    ['module' => 'category',          'prefix' => 'CAT', 'format' => '{PREFIX}{SEQ}',              'digit' => 4],
    ['module' => 'unit',              'prefix' => 'UNT', 'format' => '{PREFIX}{SEQ}',              'digit' => 4],
    ['module' => 'brand',             'prefix' => 'BRD', 'format' => '{PREFIX}{SEQ}',              'digit' => 4],
    ['module' => 'color',             'prefix' => 'CLR', 'format' => '{PREFIX}{SEQ}',              'digit' => 4],
    ['module' => 'size',              'prefix' => 'SIZ', 'format' => '{PREFIX}{SEQ}',              'digit' => 4],
    ['module' => 'tax',               'prefix' => 'TAX', 'format' => '{PREFIX}{SEQ}',              'digit' => 4],
    ['module' => 'warehouse',         'prefix' => 'WH',  'format' => '{PREFIX}{SEQ}',              'digit' => 4],
    ['module' => 'supplier',          'prefix' => 'SUP', 'format' => '{PREFIX}{SEQ}',              'digit' => 4],
    ['module' => 'customer',          'prefix' => 'CUS', 'format' => '{PREFIX}{SEQ}',              'digit' => 4],

    // ==========================
    // Product
    // ==========================
    ['module' => 'product',           'prefix' => 'PRD', 'format' => '{PREFIX}{SEQ}',              'digit' => 5],
    ['module' => 'product_variant',   'prefix' => 'PVR', 'format' => '{PREFIX}{SEQ}',              'digit' => 5],
    ['module' => 'product_price',     'prefix' => 'PPR', 'format' => '{PREFIX}{SEQ}',              'digit' => 5],

    // ==========================
    // Inventory
    // ==========================
    ['module' => 'stock_adjustment',  'prefix' => 'ADJ', 'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'stock_transfer',    'prefix' => 'TRF', 'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'stock_opname',      'prefix' => 'OPN', 'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],

    // ==========================
    // Purchasing
    // ==========================
    ['module' => 'purchase_order',    'prefix' => 'PO',  'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'purchase_receive',  'prefix' => 'GRN', 'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'purchase_invoice',  'prefix' => 'PIN', 'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'purchase_return',   'prefix' => 'PR',  'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],

    // ==========================
    // Sales
    // ==========================
    ['module' => 'quotation',         'prefix' => 'QT',  'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'sales_order',       'prefix' => 'SO',  'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'delivery_order',    'prefix' => 'DO',  'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'sales_invoice',     'prefix' => 'INV', 'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'sales_return',      'prefix' => 'SR',  'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],

    // ==========================
    // Accounting
    // ==========================
    ['module' => 'cash_bank',         'prefix' => 'CB',  'format' => '{PREFIX}{SEQ}',              'digit' => 4],
    ['module' => 'journal',           'prefix' => 'JR',  'format' => '{PREFIX}-{YYYY}-{MM}-{SEQ}', 'digit' => 6],
    ['module' => 'expense',           'prefix' => 'EXP', 'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'income',            'prefix' => 'INC', 'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'payment_in',        'prefix' => 'RCV', 'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],
    ['module' => 'payment_out',       'prefix' => 'PAY', 'format' => '{PREFIX}-{YYYY}-{SEQ}',      'digit' => 6],

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