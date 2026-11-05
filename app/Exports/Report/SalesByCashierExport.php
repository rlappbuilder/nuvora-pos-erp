<?php

namespace App\Exports\Report;

use Generator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesByCashierExport implements
    FromGenerator,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Generator
    |--------------------------------------------------------------------------
    */

    public function generator(): Generator
    {
        $report = $this->data['report'] ?? [];

        $summary = $report['summary'] ?? [];

        $branchRows = $report['branch_rows'] ?? collect();

        $totals = $report['totals'] ?? [];

        $filters = $this->data['filters'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Report Information
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'title',
            'label' => 'SALES BY CASHIER REPORT',
        ];

        yield [
            'type' => 'info',
            'label' => 'Company',
            'value' => data_get(
                $this->data,
                'company.company_name',
                '-'
            ),
        ];

        yield [
            'type' => 'info',
            'label' => 'Period',
            'value' =>
                ($filters['date_from'] ?? '-') .
                ' - ' .
                ($filters['date_to'] ?? '-'),
        ];

        yield [
            'type' => 'info',
            'label' => 'Branch',
            'value' => $this->data['branchLabel']
                ?? collect($this->data['branches'] ?? [])
                    ->pluck('name')
                    ->implode(', ')
                ?: 'All Branches',
        ];

        yield [
            'type' => 'info',
            'label' => 'Cashier',
            'value' => $filters['cashier_label']
                ?? 'All Cashiers',
        ];

        yield [
            'type' => 'info',
            'label' => 'Sales Source',
            'value' => match ($filters['source'] ?? 'all') {
                'pos' => 'POS',
                'consignment' => 'Consignment',
                default => 'All Sources',
            },
        ];

        yield [
            'type' => 'info',
            'label' => 'Generated Date',
            'value' => $this->data['generatedAt']
                ?? now()->format('d-m-Y H:i:s'),
        ];

        yield [];

        /*
        |--------------------------------------------------------------------------
        | KPI Summary
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'section',
            'label' => 'KPI SUMMARY',
        ];

        yield [
            'type' => 'metric',
            'label' => 'Total Cashiers',
            'value' => $summary['total_cashiers'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Total Transactions',
            'value' => $summary['total_transactions'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Total Items Sold',
            'value' => $summary['total_items'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Gross Sales',
            'value' => $summary['gross_sales'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Total Discount',
            'value' => $summary['discount'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Net Sales',
            'value' => $summary['net_sales'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'COGS',
            'value' => $summary['cogs'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Gross Profit',
            'value' => $summary['gross_profit'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Gross Margin',
            'value' => $summary['margin'] ?? 0,
        ];

        yield [];

        /*
        |--------------------------------------------------------------------------
        | Cashier Sales Details
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'section',
            'label' => 'SALES BY CASHIER',
        ];

        yield [
            'type' => 'branch_heading',
        ];

        /*
        |--------------------------------------------------------------------------
        | Stream Branch Rows
        |--------------------------------------------------------------------------
        */

        $number = 1;

        $branchRows = $branchRows instanceof Collection
            ? $branchRows
            : collect($branchRows);

        foreach ($branchRows as $branch) {

            $branchName = data_get(
                $branch,
                'branch_name',
                '-'
            );

            $cashierRows = data_get(
                $branch,
                'rows',
                []
            );

            $cashierRows = $cashierRows instanceof Collection
                ? $cashierRows
                : collect($cashierRows);

            /*
            |--------------------------------------------------------------------------
            | Cashier / Consignment Sales
            |--------------------------------------------------------------------------
            */

            foreach ($cashierRows as $cashierRow) {

                $source = data_get(
                    $cashierRow,
                    'source_type',
                    ''
                );

                $sourceLabel = match ($source) {
                    'POS' => 'POS Sales',
                    'Consignment' => 'Consignment',
                    default => $source ?: '-',
                };

                $cashierName = data_get(
                    $cashierRow,
                    'cashier_name',
                    '-'
                );

                yield [
                    'type' => 'cashier',

                    'number' => $number++,

                    'branch_name' => $branchName,

                    'cashier_name' => $cashierName,

                    'source' => $sourceLabel,

                    'total_transactions' =>
                        data_get($cashierRow, 'total_transactions', 0),

                    'total_qty' =>
                        data_get($cashierRow, 'total_qty', 0),

                    'gross_sales' =>
                        data_get($cashierRow, 'gross_sales', 0),

                    'discount' =>
                        data_get($cashierRow, 'discount', 0),

                    'net_sales' =>
                        data_get($cashierRow, 'net_sales', 0),

                    'cogs' =>
                        data_get($cashierRow, 'cogs', 0),

                    'gross_profit' =>
                        data_get($cashierRow, 'gross_profit', 0),

                    'margin' =>
                        data_get($cashierRow, 'margin', 0),
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Branch Subtotal
            |--------------------------------------------------------------------------
            */

            $subtotal = data_get(
                $branch,
                'subtotal',
                []
            );

            yield [
                'type' => 'subtotal',

                'branch_name' => $branchName,

                'total_transactions' =>
                    data_get($subtotal, 'total_transactions', 0),

                'total_qty' =>
                    data_get($subtotal, 'total_qty', 0),

                'gross_sales' =>
                    data_get($subtotal, 'gross_sales', 0),

                'discount' =>
                    data_get($subtotal, 'discount', 0),

                'net_sales' =>
                    data_get($subtotal, 'net_sales', 0),

                'cogs' =>
                    data_get($subtotal, 'cogs', 0),

                'gross_profit' =>
                    data_get($subtotal, 'gross_profit', 0),

                'margin' =>
                    data_get($subtotal, 'margin', 0),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Grand Total
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'total',

            'total_transactions' =>
                $totals['total_transactions'] ?? 0,

            'total_qty' =>
                $totals['total_qty'] ?? 0,

            'gross_sales' =>
                $totals['gross_sales'] ?? 0,

            'discount' =>
                $totals['discount'] ?? 0,

            'net_sales' =>
                $totals['net_sales'] ?? 0,

            'cogs' =>
                $totals['cogs'] ?? 0,

            'gross_profit' =>
                $totals['gross_profit'] ?? 0,

            'margin' =>
                $totals['margin'] ?? 0,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Headings
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        return [
            'No.',
            'Branch Information',
            'Cashier / Sales Source',
            'Total Transactions',
            'Total Qty',
            'Gross Sales',
            'Discount',
            'Net Sales',
            'COGS',
            'Gross Profit',
            'Margin (%)',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Mapping
    |--------------------------------------------------------------------------
    */

    public function map($row): array
    {
        switch ($row['type'] ?? '') {

            case 'title':
            case 'section':

                return [
                    $row['label'] ?? null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                ];

            case 'info':
            case 'metric':

                return [
                    $row['label'] ?? null,
                    $row['value'] ?? null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                ];

            case 'branch_heading':

                return [
                    'No.',
                    'Branch Information',
                    'Cashier / Sales Source',
                    'Total Transactions',
                    'Total Qty',
                    'Gross Sales',
                    'Discount',
                    'Net Sales',
                    'COGS',
                    'Gross Profit',
                    'Margin (%)',
                ];

            case 'cashier':

                return [
                    $row['number'] ?? null,

                    $row['branch_name'] ?? '-',

                    ($row['cashier_name'] ?? '-') .
                    "\n" .
                    ($row['source'] ?? '-'),

                    $row['total_transactions'] ?? 0,

                    $row['total_qty'] ?? 0,

                    $row['gross_sales'] ?? 0,

                    $row['discount'] ?? 0,

                    $row['net_sales'] ?? 0,

                    $row['cogs'] ?? 0,

                    $row['gross_profit'] ?? 0,

                    $row['margin'] ?? 0,
                ];

            case 'subtotal':

                return [
                    null,

                    'SUBTOTAL - ' .
                    ($row['branch_name'] ?? '-'),

                    null,

                    $row['total_transactions'] ?? 0,

                    $row['total_qty'] ?? 0,

                    $row['gross_sales'] ?? 0,

                    $row['discount'] ?? 0,

                    $row['net_sales'] ?? 0,

                    $row['cogs'] ?? 0,

                    $row['gross_profit'] ?? 0,

                    $row['margin'] ?? 0,
                ];

            case 'total':

                return [
                    null,

                    'GRAND TOTAL',

                    null,

                    $row['total_transactions'] ?? 0,

                    $row['total_qty'] ?? 0,

                    $row['gross_sales'] ?? 0,

                    $row['discount'] ?? 0,

                    $row['net_sales'] ?? 0,

                    $row['cogs'] ?? 0,

                    $row['gross_profit'] ?? 0,

                    $row['margin'] ?? 0,
                ];

            default:

                return array_fill(0, 11, null);
        }
    }
}