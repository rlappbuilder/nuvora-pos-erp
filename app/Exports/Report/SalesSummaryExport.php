<?php

namespace App\Exports\Report;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesSummaryExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    protected Collection $rows;

    public function __construct(array $data)
    {
        $report = $data['report'] ?? [];

        $summary = $report['summary'] ?? [];
        $sources = $report['sources'] ?? [];
        $trend = $report['sales_trend'] ?? [];

        $filters = $data['filters'] ?? [];
        $branches = collect($data['branches'] ?? []);

        $rows = [];

        /*
        |--------------------------------------------------------------------------
        | Report Information
        |--------------------------------------------------------------------------
        */

        $rows[] = [
            'type' => 'title',
            'label' => 'SALES SUMMARY REPORT',
        ];

        $rows[] = [
            'type' => 'info',
            'label' => 'Period',
            'value' => ($filters['date_from'] ?? '-')
                . ' - '
                . ($filters['date_to'] ?? '-'),
        ];

        $rows[] = [
            'type' => 'info',
            'label' => 'Branch',
            'value' => $branches->pluck('name')->implode(', ') ?: 'All Branches',
        ];

        $rows[] = [];

        /*
        |--------------------------------------------------------------------------
        | KPI Summary
        |--------------------------------------------------------------------------
        */

        $rows[] = [
            'type' => 'section',
            'label' => 'KPI SUMMARY',
        ];

        $rows[] = [
            'type' => 'metric',
            'label' => 'Total Revenue',
            'value' => $summary['total_revenue'] ?? 0,
        ];

        $rows[] = [
            'type' => 'metric',
            'label' => 'Total Transactions',
            'value' => $summary['total_transactions'] ?? 0,
        ];

        $rows[] = [
            'type' => 'metric',
            'label' => 'Items Sold',
            'value' => $summary['total_items'] ?? 0,
        ];

        $rows[] = [
            'type' => 'metric',
            'label' => 'Gross Profit',
            'value' => $summary['gross_profit'] ?? 0,
        ];

        $rows[] = [];

        /*
        |--------------------------------------------------------------------------
        | Financial Summary
        |--------------------------------------------------------------------------
        */

        $rows[] = [
            'type' => 'section',
            'label' => 'FINANCIAL SUMMARY',
        ];

        $rows[] = [
            'type' => 'metric',
            'label' => 'Total Revenue',
            'value' => $summary['total_revenue'] ?? 0,
        ];

        $rows[] = [
            'type' => 'metric',
            'label' => 'Total Discount',
            'value' => $summary['total_discount'] ?? 0,
        ];

        $rows[] = [
            'type' => 'metric',
            'label' => 'Total COGS',
            'value' => $summary['total_cogs'] ?? 0,
        ];

        $rows[] = [
            'type' => 'metric',
            'label' => 'Gross Profit',
            'value' => $summary['gross_profit'] ?? 0,
        ];

        $rows[] = [
            'type' => 'metric',
            'label' => 'Gross Profit Margin',
            'value' => $summary['gross_profit_margin'] ?? 0,
            'unit' => '%',
        ];

        $rows[] = [];

        /*
        |--------------------------------------------------------------------------
        | Sales By Source
        |--------------------------------------------------------------------------
        */

        $rows[] = [
            'type' => 'section',
            'label' => 'SALES BY SOURCE',
        ];

        $rows[] = [
            'type' => 'source_heading',
        ];

        $rows[] = [
            'type' => 'source',
            'label' => 'POS Sales',
            'transactions' => $sources['pos']['transactions'] ?? 0,
            'revenue' => $sources['pos']['revenue'] ?? 0,
            'items' => $sources['pos']['items'] ?? 0,
            'discount' => $sources['pos']['discount'] ?? 0,
            'cogs' => $sources['pos']['cogs'] ?? 0,
        ];

        $rows[] = [
            'type' => 'source',
            'label' => 'Consignment',
            'transactions' => $sources['consignment']['transactions'] ?? 0,
            'revenue' => $sources['consignment']['revenue'] ?? 0,
            'items' => $sources['consignment']['items'] ?? 0,
            'discount' => 0,
            'cogs' => $sources['consignment']['cogs'] ?? 0,
        ];

        $rows[] = [];

        /*
        |--------------------------------------------------------------------------
        | Daily Sales Trend
        |--------------------------------------------------------------------------
        */

        $rows[] = [
            'type' => 'section',
            'label' => 'DAILY SALES TREND',
        ];

        $rows[] = [
            'type' => 'trend_heading',
        ];

        foreach ($trend as $item) {
            $rows[] = [
                'type' => 'trend',
                'label' => $item['date'] ?? '-',
                'pos' => $item['pos'] ?? 0,
                'consignment' => $item['consignment'] ?? 0,
                'revenue' => $item['revenue'] ?? 0,
            ];
        }

        $this->rows = collect($rows);
    }

    /*
    |--------------------------------------------------------------------------
    | Collection
    |--------------------------------------------------------------------------
    */

    public function collection(): Collection
    {
        return $this->rows;
    }

    /*
    |--------------------------------------------------------------------------
    | Headings
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        return [
            'Description / Date',
            'Value',
            'Transactions',
            'Revenue / POS Revenue',
            'Items / Consignment Revenue',
            'Discount / Total Revenue',
            'COGS',
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
                ];

            case 'source_heading':
                return [
                    'Source',
                    'Transactions',
                    null,
                    'Revenue',
                    'Items Sold',
                    'Discount',
                    'COGS',
                ];

            case 'source':
                return [
                    $row['label'] ?? null,
                    null,
                    $row['transactions'] ?? 0,
                    $row['revenue'] ?? 0,
                    $row['items'] ?? 0,
                    $row['discount'] ?? 0,
                    $row['cogs'] ?? 0,
                ];

            case 'trend_heading':
                return [
                    'Date',
                    null,
                    null,
                    'POS Revenue',
                    'Consignment Revenue',
                    'Total Revenue',
                    null,
                ];

            case 'trend':
                return [
                    $row['label'] ?? null,
                    null,
                    null,
                    $row['pos'] ?? 0,
                    $row['consignment'] ?? 0,
                    $row['revenue'] ?? 0,
                    null,
                ];

            default:
                return array_fill(0, 7, null);
        }
    }
}