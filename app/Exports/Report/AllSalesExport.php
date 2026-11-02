<?php

namespace App\Exports\Report;

use Generator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AllSalesExport implements
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

        $sources = $report['sources'] ?? [];

        $filters = $this->data['filters'] ?? [];

        $transactions = $report['transactions'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Report Information
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'title',
            'label' => 'ALL SALES REPORT',
        ];

        yield [
            'type' => 'info',
            'label' => 'Company',
            'value' => data_get(
                $this->data,
                'company.name',
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
            'value' => $this->data['generatedAt'] ?? now()->format('d-m-Y H:i:s'),
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
            'label' => 'Total Transactions',
            'value' => $summary['total_transactions'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Total Items',
            'value' => $summary['total_items'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Total Sales',
            'value' => $summary['total_sales'] ?? 0,
        ];

        yield [];

        /*
        |--------------------------------------------------------------------------
        | Sales By Source
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'section',
            'label' => 'SALES BY SOURCE',
        ];

        yield [
            'type' => 'source_heading',
        ];

        yield [
            'type' => 'source',
            'label' => 'POS',
            'transactions' => $sources['pos']['transactions'] ?? 0,
            'sales' => $sources['pos']['sales'] ?? 0,
        ];

        yield [
            'type' => 'source',
            'label' => 'Consignment',
            'transactions' => $sources['consignment']['transactions'] ?? 0,
            'sales' => $sources['consignment']['sales'] ?? 0,
        ];

        yield [];

        /*
        |--------------------------------------------------------------------------
        | Transaction Details
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'section',
            'label' => 'TRANSACTION DETAILS',
        ];

        yield [
            'type' => 'transaction_heading',
        ];

        /*
        |--------------------------------------------------------------------------
        | Stream Transactions
        |--------------------------------------------------------------------------
        */

        $number = 1;

        if ($transactions !== null) {

            foreach ($transactions->cursor() as $transaction) {

                yield [
                    'type' => 'transaction',

                    'number' => $number++,

                    'date' => $transaction->transaction_date ?? '-',

                    'transaction_number' =>
                        $transaction->transaction_number ?? '-',

                    'source' => $transaction->source ?? '-',

                    'cashier' => $transaction->cashier_name ?? '-',

                    'branch' => $transaction->branch_name ?? '-',

                    'partner_type' =>
                        $transaction->partner_type ?? '-',

                    'customer' => $transaction->customer_name ?? '-',

                    'items' => $transaction->total_items ?? 0,

                    'total_qty' => $transaction->total_qty ?? 0,

                    'discount' => $transaction->discount ?? 0,

                    'grand_total' => $transaction->grand_total ?? 0,
                ];
            }
        }
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
            'Transaction Information',
            'Source / Cashier',
            'Branch / Customer',
            'Items',
            'Total Qty',
            'Discount',
            'Grand Total',
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
                ];

            case 'source_heading':

                return [
                    'Source',
                    'Transactions',
                    null,
                    'Total Sales',
                    null,
                    null,
                    null,
                    null,
                ];

            case 'source':

                return [
                    $row['label'] ?? null,
                    $row['transactions'] ?? 0,
                    null,
                    $row['sales'] ?? 0,
                    null,
                    null,
                    null,
                    null,
                ];

            case 'transaction_heading':

                return [
                    'No.',
                    'Date / Transaction Number',
                    'Source / Cashier',
                    'Branch / Customer or Reseller',
                    'Items',
                    'Total Qty',
                    'Discount',
                    'Grand Total',
                ];

            case 'transaction':

                $sourceCashier = $row['source'] ?? '-';

                if (($row['source'] ?? '') === 'POS') {
                    $sourceCashier .= ' / ' . ($row['cashier'] ?? '-');
                }

                $partner = ($row['partner_type'] ?? '') === 'Reseller'
                    ? 'Reseller'
                    : 'Customer';

                $branchCustomer =
                    ($row['branch'] ?? '-') .
                    ' / ' .
                    $partner .
                    ': ' .
                    ($row['customer'] ?? '-');

                return [
                    $row['number'] ?? null,

                    ($row['date'] ?? '-') .
                    ' / ' .
                    ($row['transaction_number'] ?? '-'),

                    $sourceCashier,

                    $branchCustomer,

                    $row['items'] ?? 0,

                    $row['total_qty'] ?? 0,

                    $row['discount'] ?? 0,

                    $row['grand_total'] ?? 0,
                ];

            default:

                return array_fill(0, 8, null);
        }
    }
}