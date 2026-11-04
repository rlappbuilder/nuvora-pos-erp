<?php

namespace App\Exports\Report;

use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesByBranchExport implements
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
            'label' => 'SALES BY BRANCH REPORT',
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
            'label' => 'Total Branches',
            'value' => $summary['total_branches'] ?? 0,
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
        | Branch Sales Details
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'section',
            'label' => 'SALES BY BRANCH',
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

        foreach ($branchRows as $branch) {

            $branchName = $branch['branch_name'] ?? '-';

            $branchCode = $branch['branch_code'] ?? '-';

            $salesRows = $branch['sales_rows'] ?? collect();

            /*
            |--------------------------------------------------------------------------
            | Branch Sales
            |--------------------------------------------------------------------------
            */

            foreach ($salesRows as $salesRow) {

                $source = $salesRow['source'] ?? '';

                $sourceLabel = match ($source) {
                    'pos' => 'POS Sales',
                    'consignment' => 'Consignment',
                    default => '-',
                };

                yield [
                    'type' => 'branch',

                    'number' => $number++,

                    'branch_name' => $branchName,

                    'branch_code' => $branchCode,

                    'source' => $sourceLabel,

                    'reseller_name' =>
                        $salesRow['reseller_name'] ?? '-',

                    'total_transactions' =>
                        $salesRow['total_transactions'] ?? 0,

                    'total_qty' =>
                        $salesRow['total_qty'] ?? 0,

                    'gross_sales' =>
                        $salesRow['gross_sales'] ?? 0,

                    'discount' =>
                        $salesRow['discount'] ?? 0,

                    'net_sales' =>
                        $salesRow['net_sales'] ?? 0,

                    'cogs' =>
                        $salesRow['cogs'] ?? 0,

                    'gross_profit' =>
                        $salesRow['gross_profit'] ?? 0,

                    'margin' =>
                        $salesRow['margin'] ?? 0,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Branch Subtotal
            |--------------------------------------------------------------------------
            */

            $subtotal = $branch['subtotal'] ?? [];

            yield [
                'type' => 'subtotal',

                'branch_name' => $branchName,

                'total_transactions' =>
                    $subtotal['total_transactions'] ?? 0,

                'total_qty' =>
                    $subtotal['total_qty'] ?? 0,

                'gross_sales' =>
                    $subtotal['gross_sales'] ?? 0,

                'discount' =>
                    $subtotal['discount'] ?? 0,

                'net_sales' =>
                    $subtotal['net_sales'] ?? 0,

                'cogs' =>
                    $subtotal['cogs'] ?? 0,

                'gross_profit' =>
                    $subtotal['gross_profit'] ?? 0,

                'margin' =>
                    $subtotal['margin'] ?? 0,
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
            'Sales Source',
            'Reseller',
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
                    null,
                ];

            case 'branch_heading':

                return [
                    'No.',
                    'Branch Information',
                    'Sales Source',
                    'Reseller',
                    'Total Transactions',
                    'Total Qty',
                    'Gross Sales',
                    'Discount',
                    'Net Sales',
                    'COGS',
                    'Gross Profit',
                    'Margin (%)',
                ];

            case 'branch':

                return [
                    $row['number'] ?? null,

                    ($row['branch_name'] ?? '-') .
                    "\nBranch Code: " .
                    ($row['branch_code'] ?? '-'),

                    $row['source'] ?? '-',

                    $row['reseller_name'] ?? '-',

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

                return array_fill(0, 12, null);
        }
    }
}