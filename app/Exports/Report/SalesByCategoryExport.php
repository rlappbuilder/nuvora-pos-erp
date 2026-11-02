<?php

namespace App\Exports\Report;

use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesByCategoryExport implements
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

        $categories = $report['categories'] ?? collect();

        $totals = $report['totals'] ?? [];

        $filters = $this->data['filters'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Report Information
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'title',
            'label' => 'SALES BY CATEGORY REPORT',
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
            'label' => 'Category',
            'value' => $filters['category_label']
                ?? 'All Categories',
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
            'label' => 'Total Categories',
            'value' => $summary['total_categories'] ?? 0,
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

        yield [];

        /*
        |--------------------------------------------------------------------------
        | Category Details
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'section',
            'label' => 'SALES BY CATEGORY',
        ];

        yield [
            'type' => 'category_heading',
        ];

        /*
        |--------------------------------------------------------------------------
        | Stream Categories
        |--------------------------------------------------------------------------
        */

        $number = 1;

        foreach ($categories as $category) {

            yield [
                'type' => 'category',

                'number' => $number++,

                'category' => $category['category_name']
                    ?? 'Uncategorized',

                'total_qty' => $category['total_qty'] ?? 0,

                'gross_sales' => $category['gross_sales'] ?? 0,

                'discount' => $category['discount'] ?? 0,

                'net_sales' => $category['net_sales'] ?? 0,

                'cogs' => $category['cogs'] ?? 0,

                'gross_profit' => $category['gross_profit'] ?? 0,

                'margin' => $category['margin'] ?? 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Grand Total
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'total',

            'category' => 'TOTAL',

            'total_qty' => $totals['total_qty'] ?? 0,

            'gross_sales' => $totals['gross_sales'] ?? 0,

            'discount' => $totals['discount'] ?? 0,

            'net_sales' => $totals['net_sales'] ?? 0,

            'cogs' => $totals['cogs'] ?? 0,

            'gross_profit' => $totals['gross_profit'] ?? 0,

            'margin' => $totals['margin'] ?? 0,
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
            'Category',
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

            case 'category_heading':

                return [
                    'No.',
                    'Category',
                    'Total Qty',
                    'Gross Sales',
                    'Discount',
                    'Net Sales',
                    'COGS',
                    'Gross Profit',
                    'Margin (%)',
                ];

            case 'category':
            case 'total':

                return [
                    $row['number'] ?? null,

                    $row['category'] ?? null,

                    $row['total_qty'] ?? 0,

                    $row['gross_sales'] ?? 0,

                    $row['discount'] ?? 0,

                    $row['net_sales'] ?? 0,

                    $row['cogs'] ?? 0,

                    $row['gross_profit'] ?? 0,

                    $row['margin'] ?? 0,
                ];

            default:

                return array_fill(0, 9, null);
        }
    }
}