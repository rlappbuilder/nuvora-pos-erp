<?php

namespace App\Exports\Report;

use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ConsignmentSalesExport implements
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

        $resellers = $report['resellers'] ?? collect();

        $totals = $report['totals'] ?? [];

        $filters = $this->data['filters'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Report Information
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'title',
            'label' => 'CONSIGNMENT SALES REPORT',
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
            'label' => 'Reseller',
            'value' => $filters['reseller_name']
                ?? 'All Resellers',
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
            'label' => 'Total Resellers',
            'value' => $summary['total_resellers'] ?? 0,
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
            'label' => 'Gross Profit Margin',
            'value' => $summary['margin'] ?? 0,
        ];

        yield [];

        /*
        |--------------------------------------------------------------------------
        | Reseller Details
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'section',
            'label' => 'RESELLER PERFORMANCE',
        ];

        yield [
            'type' => 'reseller_heading',
        ];

        /*
        |--------------------------------------------------------------------------
        | Stream Resellers
        |--------------------------------------------------------------------------
        */

        $number = 1;

        foreach ($resellers as $reseller) {

            yield [
                'type' => 'reseller',

                'number' => $number++,

                'reseller' => $reseller['reseller_name']
                    ?? 'Unknown Reseller',

                'total_transactions' =>
                    $reseller['total_transactions'] ?? 0,

                'total_qty' =>
                    $reseller['total_qty'] ?? 0,

                'gross_sales' =>
                    $reseller['gross_sales'] ?? 0,

                'cogs' =>
                    $reseller['cogs'] ?? 0,

                'gross_profit' =>
                    $reseller['gross_profit'] ?? 0,

                'margin' =>
                    $reseller['margin'] ?? 0,

                'sales_contribution' =>
                    $reseller['sales_contribution'] ?? 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Grand Total
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'total',

            'reseller' => 'GRAND TOTAL',

            'total_transactions' =>
                $totals['total_transactions'] ?? 0,

            'total_qty' =>
                $totals['total_qty'] ?? 0,

            'gross_sales' =>
                $totals['gross_sales'] ?? 0,

            'cogs' =>
                $totals['cogs'] ?? 0,

            'gross_profit' =>
                $totals['gross_profit'] ?? 0,

            'margin' =>
                $totals['margin'] ?? 0,

            'sales_contribution' =>
                $totals['sales_contribution'] ?? 0,
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
            'Reseller',
            'Transactions',
            'Total Qty',
            'Gross Sales',
            'COGS',
            'Gross Profit',
            'Margin (%)',
            'Sales Contribution (%)',
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
                ];

            case 'reseller_heading':

                return [
                    'No.',
                    'Reseller',
                    'Transactions',
                    'Total Qty',
                    'Gross Sales',
                    'COGS',
                    'Gross Profit',
                    'Margin (%)',
                    'Sales Contribution (%)',
                ];

            case 'reseller':
            case 'total':

                return [
                    $row['number'] ?? null,

                    $row['reseller'] ?? null,

                    $row['total_transactions'] ?? 0,

                    $row['total_qty'] ?? 0,

                    $row['gross_sales'] ?? 0,

                    $row['cogs'] ?? 0,

                    $row['gross_profit'] ?? 0,

                    $row['margin'] ?? 0,

                    $row['sales_contribution'] ?? 0,
                ];

            default:

                return array_fill(0, 9, null);
        }
    }
}