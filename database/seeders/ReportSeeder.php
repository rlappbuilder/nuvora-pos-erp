<?php

namespace Database\Seeders;

use App\Models\Report\Report;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $reports = [

            /*
            |--------------------------------------------------------------------------
            | PURCHASE
            |--------------------------------------------------------------------------
            */

            [
                'code' => 'PURCHASE_REPORT',
                'name' => 'Purchase Report',
                'category' => 'Purchase',
                'description' => 'Analyze purchasing activities and purchase performance.',
                'route_name' => 'reports.purchase-report',
                'available_formats' => ['print', 'pdf', 'excel'],
                'sort_order' => 10,
            ],

            [
                'code' => 'SUPPLIER_STATEMENT',
                'name' => 'Supplier Statement',
                'category' => 'Purchase',
                'description' => 'View supplier transaction history and balances.',
                'route_name' => 'reports.supplier-statement',
                'available_formats' => ['print', 'pdf', 'excel'],
                'sort_order' => 20,
            ],

            [
                'code' => 'AP_AGING',
                'name' => 'AP Aging',
                'category' => 'Purchase',
                'description' => 'Analyze outstanding supplier payables by aging period.',
                'route_name' => 'reports.ap-aging',
                'available_formats' => ['print', 'pdf', 'excel'],
                'sort_order' => 30,
            ],
            /*
            |--------------------------------------------------------------------------
            | SALES
            |--------------------------------------------------------------------------
            */

            [
                'code' => 'SALES_SUMMARY',
                'name' => 'Sales Summary',
                'category' => 'Sales',
                'description' => 'Summary of POS Sales and Consignment Settlements.',
                'route_name' => 'reports.sales.summary.parameters',
                'available_formats' => ['print', 'pdf', 'excel'],
                'is_active' => true,
                'sort_order' => 10,
            ],

            /*
            |--------------------------------------------------------------------------
            | FINANCE
            |--------------------------------------------------------------------------
            */

            [
                'code' => 'TRIAL_BALANCE',
                'name' => 'Trial Balance',
                'category' => 'Finance',
                'description' => 'View account balances and debit-credit totals.',
                'route_name' => 'trial-balance.index',
                'available_formats' => ['print', 'pdf', 'excel'],
                'sort_order' => 10,
            ],

            [
                'code' => 'BALANCE_SHEET',
                'name' => 'Balance Sheet',
                'category' => 'Finance',
                'description' => 'View assets, liabilities, and equity.',
                'route_name' => 'balance-sheet.index',
                'available_formats' => ['print', 'pdf', 'excel'],
                'sort_order' => 20,
            ],

            [
                'code' => 'INCOME_STATEMENT',
                'name' => 'Income Statement',
                'category' => 'Finance',
                'description' => 'Analyze company revenue, expenses, and profitability.',
                'route_name' => 'income-statement.index',
                'available_formats' => ['print', 'pdf', 'excel'],
                'sort_order' => 30,
            ],


            /*
            |--------------------------------------------------------------------------
            | INVENTORY
            |--------------------------------------------------------------------------
            */

            [
                'code' => 'STOCK_BALANCE',
                'name' => 'Stock Balance',
                'category' => 'Inventory',
                'description' => 'View current inventory quantities by location.',
                'route_name' => 'stock-balance.index',
                'available_formats' => ['print', 'pdf', 'excel'],
                'sort_order' => 10,
            ],

            [
                'code' => 'STOCK_CARD',
                'name' => 'Stock Card',
                'category' => 'Inventory',
                'description' => 'View inventory movement history.',
                'route_name' => 'stock-card.index',
                'available_formats' => ['print', 'pdf', 'excel'],
                'sort_order' => 20,
            ],

        ];

        foreach ($reports as $report) {

            Report::updateOrCreate(
                [
                    'code' => $report['code'],
                ],
                array_merge($report, [
                    'is_active' => true,
                ])
            );

        }
    }
}