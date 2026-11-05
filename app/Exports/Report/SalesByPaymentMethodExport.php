<?php

namespace App\Exports\Report;

use Generator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesByPaymentMethodExport implements
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

        $paymentRows = $report['payment_rows'] ?? collect();

        $totals = $report['totals'] ?? [];

        $filters = $this->data['filters'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Report Information
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'title',
            'label' => 'SALES BY PAYMENT METHOD REPORT',
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
            'label' => 'Payment Method',
            'value' => $this->getPaymentMethodLabel($filters),
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
            'label' => 'Total Payment Methods',
            'value' => $summary['total_payment_methods'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Payment Transactions',
            'value' => $summary['payment_transactions'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Sales Transactions',
            'value' => $summary['sales_transactions'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Total Payment Amount',
            'value' => $summary['total_payment_amount'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'POS Payment Amount',
            'value' => $summary['pos_payment_amount'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Consignment Payment Amount',
            'value' => $summary['consignment_payment_amount'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Outstanding Receivables',
            'value' => $summary['outstanding_receivables'] ?? 0,
        ];

        yield [
            'type' => 'metric',
            'label' => 'Average Payment Amount',
            'value' => $summary['average_payment_amount'] ?? 0,
        ];

        yield [];

        /*
        |--------------------------------------------------------------------------
        | Payment Method Details
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'section',
            'label' => 'PAYMENT METHOD DETAILS',
        ];

        yield [
            'type' => 'table_heading',
        ];

        /*
        |--------------------------------------------------------------------------
        | Stream Payment Rows
        |--------------------------------------------------------------------------
        */

        $number = 1;

        $paymentRows = $paymentRows instanceof Collection
            ? $paymentRows
            : collect($paymentRows);

        foreach ($paymentRows as $paymentRow) {

            yield [
                'type' => 'payment',

                'number' => $number++,

                'payment_method' => data_get(
                    $paymentRow,
                    'payment_method_label',
                    data_get($paymentRow, 'payment_method', '-')
                ),

                'source' => match (
                    data_get($paymentRow, 'source_type', '')
                ) {
                    'POS' => 'POS',
                    'Consignment' => 'Consignment',
                    default => data_get($paymentRow, 'source_type', '-'),
                },

                'payment_transactions' =>
                    data_get($paymentRow, 'payment_transactions', 0),

                'sales_transactions' =>
                    data_get($paymentRow, 'sales_transactions', 0),

                'payment_amount' =>
                    data_get($paymentRow, 'payment_amount', 0),

                'percentage' =>
                    data_get($paymentRow, 'percentage', 0),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Grand Total
        |--------------------------------------------------------------------------
        */

        yield [
            'type' => 'total',

            'payment_transactions' =>
                $totals['payment_transactions'] ?? 0,

            'sales_transactions' =>
                $totals['sales_transactions'] ?? 0,

            'payment_amount' =>
                $totals['payment_amount'] ?? 0,

            'percentage' =>
                $totals['percentage'] ?? 100,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Method Label
    |--------------------------------------------------------------------------
    */

    protected function getPaymentMethodLabel(array $filters): string
    {
        $methods = $filters['payment_methods'] ?? [];

        if (empty($methods)) {
            return 'All Payment Methods';
        }

        if (! is_array($methods)) {
            $methods = [$methods];
        }

        return collect($methods)
            ->map(function ($method) {
                return match (strtolower((string) $method)) {
                    'cash' => 'Cash',
                    'qris' => 'QRIS',
                    'debit_card' => 'Debit Card',
                    'credit_card' => 'Credit Card',
                    'transfer' => 'Bank Transfer',
                    'e_wallet', 'ewallet' => 'E-Wallet',
                    default => ucwords(
                        str_replace('_', ' ', (string) $method)
                    ),
                };
            })
            ->implode(', ');
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
            'Payment Method',
            'Source',
            'Payment Transactions',
            'Sales Transactions',
            'Payment Amount',
            'Contribution (%)',
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

            case 'table_heading':

                return [
                    'No.',
                    'Payment Method',
                    'Source',
                    'Payment Transactions',
                    'Sales Transactions',
                    'Payment Amount',
                    'Contribution (%)',
                ];

            case 'payment':

                return [
                    $row['number'] ?? null,
                    $row['payment_method'] ?? '-',
                    $row['source'] ?? '-',
                    $row['payment_transactions'] ?? 0,
                    $row['sales_transactions'] ?? 0,
                    $row['payment_amount'] ?? 0,
                    $row['percentage'] ?? 0,
                ];

            case 'total':

                return [
                    null,
                    'GRAND TOTAL',
                    null,
                    $row['payment_transactions'] ?? 0,
                    $row['sales_transactions'] ?? 0,
                    $row['payment_amount'] ?? 0,
                    $row['percentage'] ?? 100,
                ];

            default:

                return array_fill(0, 7, null);
        }
    }
}