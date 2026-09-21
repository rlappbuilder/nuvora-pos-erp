<?php

namespace App\Exports\Resellers\Reports;

use App\Services\Consignment\ConsignmentReportService;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsignmentReportExport implements
    FromArray,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    public function __construct(
        protected array $filters = []
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Array
    |--------------------------------------------------------------------------
    */

    public function array(): array
    {
        $service =
            app(ConsignmentReportService::class);

        $report =
            $service->getReport(
                $this->filters
            );

        return match (
            $report['type']
            ?? 'all_activity'
        ) {

            'consignment_out' =>
                $this->consignmentOut($report),

            'sales_settlement' =>
                $this->salesSettlement($report),

            'receivable_payment' =>
                $this->receivablePayment($report),

            'consignment_return' =>
                $this->consignmentReturn($report),

            'stock_position' =>
                $this->stockPosition($report),

            'receivable' =>
                $this->receivable($report),

            'sales_analysis' =>
                $this->salesAnalysis($report),

            'profit_analysis' =>
                $this->profitAnalysis($report),

            default =>
                $this->allActivity($report),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    protected function header(
        array $report
    ): array {

        return [

            ['NUVORA ERP'],

            [
                strtoupper(
                    $report['title']
                    ?? 'CONSIGNMENT REPORT'
                ),
            ],

            [],

            [
                'Report Type',
                $report['title']
                    ?? 'All Activity',
            ],

            [
                'Period',
                ($this->filters['date_from'] ?? '-')
                . ' - '
                . ($this->filters['date_to'] ?? '-'),
            ],

            [
                'Reseller',
                $this->getResellerName(
                    $report
                ),
            ],

            [
                'Branch',
                $this->getBranchName(
                    $report
                ),
            ],

            [],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Reseller Name
    |--------------------------------------------------------------------------
    */

    protected function getResellerName(
        array $report
    ): string {

        if (
            !empty(
                $this->filters['reseller_id']
            )
        ) {

            return collect(
                $report['rows'] ?? []
            )
                ->pluck('reseller')
                ->filter()
                ->unique()
                ->first()
                ?? 'Selected Reseller';
        }

        return 'All Reseller';
    }


    /*
    |--------------------------------------------------------------------------
    | Branch Name
    |--------------------------------------------------------------------------
    */

    protected function getBranchName(
        array $report
    ): string {

        if (
            !empty(
                $this->filters['branch_id']
            )
        ) {

            return collect(
                $report['rows'] ?? []
            )
                ->pluck('branch')
                ->filter()
                ->unique()
                ->first()
                ?? 'Selected Branch';
        }

        return 'All Branch';
    }


    /*
    |--------------------------------------------------------------------------
    | Consignment Out
    |--------------------------------------------------------------------------
    */

    protected function consignmentOut(
        array $report
    ): array {

        $rows =
            $this->header($report);

        $rows[] = [
            'CONSIGNMENT OUT',
        ];

        $rows[] = [
            'Date',
            'Number',
            'Reseller',
            'Branch',
            'Warehouse',
            'Product',
            'Variant',
            'Unit',
            'Qty',
            'Price',
            'Total',
        ];

        foreach (
            $report['rows'] ?? []
            as $row
        ) {

            $rows[] = [
                $row['date'] ?? '',
                $row['number'] ?? '',
                $row['reseller'] ?? '',
                $row['branch'] ?? '',
                $row['warehouse'] ?? '',
                $row['product'] ?? '',
                $row['variant'] ?? '',
                $row['unit'] ?? '',
                $row['qty'] ?? 0,
                $row['unit_price'] ?? 0,
                $row['total'] ?? 0,
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            collect(
                $report['rows'] ?? []
            )->sum('qty'),
            '',
            collect(
                $report['rows'] ?? []
            )->sum('total'),
        ];

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Sales Settlement
    |--------------------------------------------------------------------------
    */

    protected function salesSettlement(
        array $report
    ): array {

        $rows =
            $this->header($report);

        $rows[] = [
            'SALES SETTLEMENT',
        ];

        $rows[] = [
            'Date',
            'Number',
            'Reseller',
            'Branch',
            'Product',
            'Variant',
            'Unit',
            'Qty',
            'Price',
            'Sales',
        ];

        foreach (
            $report['rows'] ?? []
            as $row
        ) {

            $rows[] = [
                $row['date'] ?? '',
                $row['number'] ?? '',
                $row['reseller'] ?? '',
                $row['branch'] ?? '',
                $row['product'] ?? '',
                $row['variant'] ?? '',
                $row['unit'] ?? '',
                $row['qty'] ?? 0,
                $row['unit_price'] ?? 0,
                $row['sales'] ?? 0,
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            '',
            '',
            collect(
                $report['rows'] ?? []
            )->sum('qty'),
            '',
            collect(
                $report['rows'] ?? []
            )->sum('sales'),
        ];

        $rows[] = [];

        $rows[] = [
            'SUMMARY',
        ];

        $rows[] = [
            'Documents',
            'Qty',
            'Sales',
            'Payment',
            'Receivable',
        ];

        $rows[] = [
            $report['summary']['documents'] ?? 0,
            $report['summary']['qty'] ?? 0,
            $report['summary']['sales'] ?? 0,
            $report['summary']['payment'] ?? 0,
            $report['summary']['receivable'] ?? 0,
        ];

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Receivable Payment
    |--------------------------------------------------------------------------
    */

    protected function receivablePayment(
        array $report
    ): array {

        $rows =
            $this->header($report);

        $rows[] = [
            'RECEIVABLE PAYMENT',
        ];

        $rows[] = [
            'Date',
            'Number',
            'Reseller',
            'Branch',
            'Settlement',
            'Payment Method',
            'Payment Account',
            'Payment',
            'Settlement Amount',
            'Previous Paid',
            'Previous Outstanding',
            'Outstanding After',
        ];

        foreach (
            $report['rows'] ?? []
            as $row
        ) {

            $rows[] = [
                $row['date'] ?? '',
                $row['number'] ?? '',
                $row['reseller'] ?? '',
                $row['branch'] ?? '',
                $row['settlement_number'] ?? '',
                $row['payment_method'] ?? '',
                $row['payment_account'] ?? '',
                $row['payment'] ?? 0,
                $row['settlement_amount'] ?? 0,
                $row['previous_paid'] ?? 0,
                $row['previous_outstanding'] ?? 0,
                $row['outstanding_after'] ?? 0,
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            '',
            '',
            collect(
                $report['rows'] ?? []
            )->sum('payment'),
            '',
            '',
            '',
            '',
        ];

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Consignment Return
    |--------------------------------------------------------------------------
    */

    protected function consignmentReturn(
        array $report
    ): array {

        $rows =
            $this->header($report);

        $rows[] = [
            'CONSIGNMENT RETURN',
        ];

        $rows[] = [
            'Date',
            'Number',
            'Reseller',
            'Branch',
            'Warehouse',
            'Product',
            'Variant',
            'Unit',
            'Qty',
            'Unit Cost',
            'Total Cost',
            'Remarks',
        ];

        foreach (
            $report['rows'] ?? []
            as $row
        ) {

            $rows[] = [
                $row['date'] ?? '',
                $row['number'] ?? '',
                $row['reseller'] ?? '',
                $row['branch'] ?? '',
                $row['warehouse'] ?? '',
                $row['product'] ?? '',
                $row['variant'] ?? '',
                $row['unit'] ?? '',
                $row['qty'] ?? 0,
                $row['unit_cost'] ?? 0,
                $row['total_cost'] ?? 0,
                $row['remarks'] ?? '',
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            collect(
                $report['rows'] ?? []
            )->sum('qty'),
            '',
            collect(
                $report['rows'] ?? []
            )->sum('total_cost'),
            '',
        ];

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Stock Position
    |--------------------------------------------------------------------------
    */

    protected function stockPosition(
        array $report
    ): array {

        $rows =
            $this->header($report);

        $rows[] = [
            'STOCK POSITION',
        ];

        $rows[] = [
            'Reseller',
            'Branch',
            'Product',
            'Variant',
            'Unit',
            'On Hand',
            'Available',
            'Consignment Price',
            'Stock Value',
        ];

        foreach (
            $report['rows'] ?? []
            as $row
        ) {

            $rows[] = [
                $row['reseller'] ?? '',
                $row['branch'] ?? '',
                $row['product'] ?? '',
                $row['variant'] ?? '',
                $row['unit'] ?? '',
                $row['on_hand'] ?? 0,
                $row['available'] ?? 0,
                $row['consignment_price'] ?? 0,
                $row['stock_value'] ?? 0,
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            collect(
                $report['rows'] ?? []
            )->sum('on_hand'),
            collect(
                $report['rows'] ?? []
            )->sum('available'),
            '',
            collect(
                $report['rows'] ?? []
            )->sum('stock_value'),
        ];

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Receivable
    |--------------------------------------------------------------------------
    */

    protected function receivable(
        array $report
    ): array {

        $rows =
            $this->header($report);

        $rows[] = [
            'RECEIVABLE',
        ];

        $rows[] = [
            'Date',
            'Type',
            'Number',
            'Reseller',
            'Debit',
            'Credit',
            'Balance',
        ];

        foreach (
            $report['rows'] ?? []
            as $row
        ) {

            $rows[] = [
                $row['date'] ?? '',
                $row['type'] ?? '',
                $row['number'] ?? '',
                $row['reseller'] ?? '',
                $row['debit'] ?? 0,
                $row['credit'] ?? 0,
                $row['balance'] ?? 0,
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            collect(
                $report['rows'] ?? []
            )->sum('debit'),
            collect(
                $report['rows'] ?? []
            )->sum('credit'),
            $report['summary']['outstanding'] ?? 0,
        ];

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Sales Analysis
    |--------------------------------------------------------------------------
    */

    protected function salesAnalysis(
        array $report
    ): array {

        $rows =
            $this->header($report);

        $rows[] = [
            'SALES ANALYSIS',
        ];

        $rows[] = [
            'Reseller',
            'Sold',
            'Sales',
            'HPP Nuvora',
            'Gross Profit',
            'Margin %',
        ];

        foreach (
            $report['rows'] ?? []
            as $row
        ) {

            $rows[] = [
                $row['reseller'] ?? '',
                $row['qty'] ?? 0,
                $row['sales'] ?? 0,
                $row['hpp'] ?? 0,
                $row['profit'] ?? 0,
                $row['margin'] ?? 0,
            ];
        }

        $rows[] = [
            'TOTAL',
            $report['summary']['qty'] ?? 0,
            $report['summary']['sales'] ?? 0,
            $report['summary']['hpp'] ?? 0,
            $report['summary']['profit'] ?? 0,
            $report['summary']['margin'] ?? 0,
        ];

        $rows[] = [];

        $rows[] = [
            'PRODUCT DETAIL',
        ];

        $rows[] = [
            'Reseller',
            'Product',
            'Variant',
            'Unit',
            'Sold',
            'Price',
            'Sales',
            'HPP',
            'Unit HPP',
            'Profit',
            'Margin %',
        ];

        foreach (
            $report['rows'] ?? []
            as $row
        ) {

            foreach (
                $row['details'] ?? []
                as $detail
            ) {

                $rows[] = [
                    $row['reseller'] ?? '',
                    $detail['product'] ?? '',
                    $detail['variant'] ?? '',
                    $detail['unit'] ?? '',
                    $detail['qty'] ?? 0,
                    $detail['unit_price'] ?? 0,
                    $detail['sales'] ?? 0,
                    $detail['hpp'] ?? 0,
                    $detail['unit_hpp'] ?? 0,
                    $detail['profit'] ?? 0,
                    $detail['margin'] ?? 0,
                ];
            }
        }

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Profit Analysis
    |--------------------------------------------------------------------------
    */

    protected function profitAnalysis(
        array $report
    ): array {

        $rows =
            $this->header($report);

        $rows[] = [
            'PROFIT ANALYSIS',
        ];

        $rows[] = [
            'Date',
            'Settlement',
            'Reseller',
            'Branch',
            'Product',
            'Variant',
            'Unit',
            'Qty',
            'Sales',
            'HPP',
            'Unit HPP',
            'Price',
            'Profit',
            'Margin %',
        ];

        foreach (
            $report['rows'] ?? []
            as $row
        ) {

            $rows[] = [
                $row['date'] ?? '',
                $row['number'] ?? '',
                $row['reseller'] ?? '',
                $row['branch'] ?? '',
                $row['product'] ?? '',
                $row['variant'] ?? '',
                $row['unit'] ?? '',
                $row['qty'] ?? 0,
                $row['sales'] ?? 0,
                $row['hpp'] ?? 0,
                $row['unit_hpp'] ?? 0,
                $row['unit_price'] ?? 0,
                $row['profit'] ?? 0,
                $row['margin'] ?? 0,
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            '',
            '',
            $report['summary']['qty'] ?? 0,
            $report['summary']['sales'] ?? 0,
            $report['summary']['hpp'] ?? 0,
            '',
            '',
            $report['summary']['profit'] ?? 0,
            $report['summary']['margin'] ?? 0,
        ];

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | All Activity
    |--------------------------------------------------------------------------
    */

    protected function allActivity(
        array $report
    ): array {

        $rows =
            $this->header($report);

        $rows[] = [
            'ALL ACTIVITY',
        ];

        $rows[] = [
            'Date',
            'Activity',
            'Number',
            'Reseller',
            'Product',
            'Unit',
            'Qty',
            'Amount',
        ];

        foreach (
            $report['rows'] ?? []
            as $row
        ) {

            $rows[] = [
                $row['date'] ?? '',
                $row['activity'] ?? '',
                $row['number'] ?? '',
                $row['reseller'] ?? '',
                $row['product'] ?? '',
                $row['unit'] ?? '',
                $row['qty_label'] ?? '',
                $row['amount'] ?? 0,
            ];
        }

        $rows[] = [];

        $rows[] = [
            'SUMMARY',
        ];

        $rows[] = [
            'Consignment Out',
            'Sold',
            'Return',
            'Payment',
        ];

        $rows[] = [
            $report['summary']['consignment_out_qty'] ?? 0,
            $report['summary']['sold_qty'] ?? 0,
            $report['summary']['return_qty'] ?? 0,
            $report['summary']['payment_amount'] ?? 0,
        ];

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Styles
    |--------------------------------------------------------------------------
    */

    public function styles(
        Worksheet $sheet
    ) {

        return [

            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
            ],

            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    public function registerEvents(): array
    {
        return [

            AfterSheet::class =>
                function (
                    AfterSheet $event
                ) {

                    $sheet =
                        $event
                            ->sheet
                            ->getDelegate();

                    $highestRow =
                        $sheet
                            ->getHighestRow();

                    $highestColumn =
                        $sheet
                            ->getHighestColumn();


                    /*
                    |--------------------------------------------------------------------------
                    | Bold Section Headers
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        range(
                            1,
                            $highestRow
                        ) as $row
                    ) {

                        $value =
                            $sheet
                                ->getCell(
                                    "A{$row}"
                                )
                                ->getValue();

                        if (
                            in_array(
                                $value,
                                [
                                    'ALL ACTIVITY',
                                    'CONSIGNMENT OUT',
                                    'SALES SETTLEMENT',
                                    'RECEIVABLE PAYMENT',
                                    'CONSIGNMENT RETURN',
                                    'STOCK POSITION',
                                    'RECEIVABLE',
                                    'SALES ANALYSIS',
                                    'PROFIT ANALYSIS',
                                    'PRODUCT DETAIL',
                                    'SUMMARY',
                                    'TOTAL',
                                ],
                                true
                            )
                        ) {

                            $sheet
                                ->getStyle(
                                    "A{$row}:{$highestColumn}{$row}"
                                )
                                ->getFont()
                                ->setBold(true);
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Number Format
                    |--------------------------------------------------------------------------
                    */

                    $sheet
                        ->getStyle(
                            "D1:{$highestColumn}{$highestRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '#,##0.00'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Freeze
                    |--------------------------------------------------------------------------
                    */

                    $sheet->freezePane('A10');
                },
        ];
    }
}