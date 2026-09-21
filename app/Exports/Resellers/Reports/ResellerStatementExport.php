<?php

namespace App\Exports\Resellers\Reports;

use App\Services\Consignment\ResellerStatementService;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResellerStatementExport implements
    FromArray,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    public function __construct(
        protected array $filters = []
    ) {
    }

    public function array(): array
    {
        $service = app(ResellerStatementService::class);

        $statement = $service->getPrintStatement(
            $this->filters
        );

        $rows = [];

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        $rows[] = ['NUVORA ERP'];
        $rows[] = ['RESELLER STATEMENT'];
        $rows[] = [];

        $rows[] = [
            'Reseller',
            $statement['reseller']['name']
                ?? 'All Reseller',
        ];

        $rows[] = [
            'Period',
            ($this->filters['date_from'] ?? '-')
            . ' - '
            . ($this->filters['date_to'] ?? '-'),
        ];

        $rows[] = [
            'Branch',
            $statement['branch']['name']
                ?? 'All Branch',
        ];

        $rows[] = [];

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $rows[] = ['SUMMARY'];

        $rows[] = [
            'Current Stock',
            'Stock Value',
            'Sold',
            'Sales',
            'Payment',
            'Outstanding',
        ];

        $rows[] = [
            $statement['summary']['current_stock'] ?? 0,
            $statement['summary']['stock_value'] ?? 0,
            $statement['summary']['sold'] ?? 0,
            $statement['summary']['sales'] ?? 0,
            $statement['summary']['payment'] ?? 0,
            $statement['summary']['outstanding'] ?? 0,
        ];

        $rows[] = [];

        /*
        |--------------------------------------------------------------------------
        | Stock Position
        |--------------------------------------------------------------------------
        */

        $rows[] = ['STOCK POSITION'];

        $rows[] = [
            'Product',
            'Variant',
            'Unit',
            'Opening',
            'Consignment',
            'Sold',
            'Return',
            'Closing',
            'Price',
            'Stock Value',
        ];

        foreach ($statement['stock'] ?? [] as $row) {
            $rows[] = [
                $row['product'],
                $row['variant'] ?? '',
                $row['unit'] ?? '',
                $row['opening'],
                $row['consignment'],
                $row['sold'],
                $row['return'],
                $row['closing'],
                $row['price'],
                $row['stock_value'],
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            '',
            collect($statement['stock'] ?? [])
                ->sum('opening'),
            collect($statement['stock'] ?? [])
                ->sum('consignment'),
            collect($statement['stock'] ?? [])
                ->sum('sold'),
            collect($statement['stock'] ?? [])
                ->sum('return'),
            collect($statement['stock'] ?? [])
                ->sum('closing'),
            '',
            collect($statement['stock'] ?? [])
                ->sum('stock_value'),
        ];

        $rows[] = [];

        /*
        |--------------------------------------------------------------------------
        | Sales & Payment
        |--------------------------------------------------------------------------
        */

        $rows[] = ['SALES & PAYMENT'];

        $rows[] = [
            'Product',
            'Variant',
            'Unit',
            'Sold',
            'Price',
            'Sales Total',
            'Payment',
            'Outstanding',
        ];

        foreach ($statement['sales'] ?? [] as $row) {
            $rows[] = [
                $row['product'],
                $row['variant'] ?? '',
                $row['unit'] ?? '',
                $row['sold'],
                $row['price'],
                $row['sales_total'],
                $row['payment'],
                $row['outstanding'],
            ];
        }

        $rows[] = [
            'TOTAL',
            '',
            '',
            collect($statement['sales'] ?? [])
                ->sum('sold'),
            '',
            collect($statement['sales'] ?? [])
                ->sum('sales_total'),
            collect($statement['sales'] ?? [])
                ->sum('payment'),
            collect($statement['sales'] ?? [])
                ->sum('outstanding'),
        ];

        $rows[] = [];

        $rows[] = [
            'Statement Basis',
            'Only Posted transactions are included.',
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                /*
                |--------------------------------------------------------------------------
                | Bold Section Headers
                |--------------------------------------------------------------------------
                */

                foreach (range(1, $highestRow) as $row) {

                    $value = $sheet
                        ->getCell("A{$row}")
                        ->getValue();

                    if (in_array($value, [
                        'SUMMARY',
                        'STOCK POSITION',
                        'SALES & PAYMENT',
                        'TOTAL',
                    ], true)) {

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
                    ->getStyle("D1:{$highestColumn}{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

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