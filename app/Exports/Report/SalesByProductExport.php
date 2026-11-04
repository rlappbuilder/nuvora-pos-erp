<?php

namespace App\Exports\Report;

use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesByProductExport implements
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

        $productVariants = $report['product_variants'] ?? collect();

        $totals = $report['totals'] ?? [];

        $filters = $this->data['filters'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Report Information
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'title',
            'label' => 'SALES BY PRODUCT REPORT',
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
            'label' => 'Product',
            'value' => $filters['product_label']
                ?? 'All Products',
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
            'label' => 'Total Products',
            'value' => $summary['total_products'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Total Variants',
            'value' => $summary['total_variants'] ?? 0,
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
        | Product Variant Details
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'section',
            'label' => 'SALES BY PRODUCT VARIANT',
        ];

        yield [
            'type' => 'product_heading',
        ];

        /*
        |--------------------------------------------------------------------------
        | Stream Product Variants
        |--------------------------------------------------------------------------
        */

        $number = 1;

        foreach ($productVariants as $product) {

            $productName = $product['product_name'] ?? '';

            $variantName = $product['variant_name'] ?? '';

            $productInformation = trim(
                $productName . ' ' . $variantName
            );

            yield [
                'type' => 'product',

                'number' => $number++,

                'product_information' => $productInformation,

                'variant_sku' => $product['variant_sku'] ?? '-',

                'product_code' => $product['product_code'] ?? '-',

                'total_transactions' =>
                    $product['total_transactions'] ?? 0,

                'total_qty' =>
                    $product['total_qty'] ?? 0,

                'gross_sales' =>
                    $product['gross_sales'] ?? 0,

                'discount' =>
                    $product['discount'] ?? 0,

                'net_sales' =>
                    $product['net_sales'] ?? 0,

                'cogs' =>
                    $product['cogs'] ?? 0,

                'gross_profit' =>
                    $product['gross_profit'] ?? 0,

                'margin' =>
                    $product['margin'] ?? 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Grand Total
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'total',

            'product_information' => 'TOTAL',

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
            'Product Information',
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
                ];

            case 'product_heading':

                return [
                    'No.',
                    'Product Information',
                    'Total Transactions',
                    'Total Qty',
                    'Gross Sales',
                    'Discount',
                    'Net Sales',
                    'COGS',
                    'Gross Profit',
                    'Margin (%)',
                ];

            case 'product':

                return [
                    $row['number'] ?? null,

                    ($row['product_information'] ?? '-') .
                    "\nSKU: " .
                    ($row['variant_sku'] ?? '-') .
                    "\nProduct Code: " .
                    ($row['product_code'] ?? '-'),

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

                    $row['product_information'] ?? 'TOTAL',

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

                return array_fill(0, 10, null);
        }
    }
}