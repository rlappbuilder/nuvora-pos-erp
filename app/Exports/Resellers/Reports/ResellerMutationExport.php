<?php

namespace App\Exports\Resellers\Reports;

use App\Services\Consignment\ResellerMutationService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResellerMutationExport implements
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
        $service =
            app(ResellerMutationService::class);

        $report =
            $service->getReport(
                $this->filters
            );

        $rows = [];

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        $rows[] = ['NUVORA ERP'];

        $rows[] = ['RESELLER MUTATION'];

        $rows[] = [];

        $rows[] = [
            'Reseller',
            $report['reseller']['name']
                ?? 'Selected Reseller',
        ];

        $rows[] = [
            'Period',
            ($this->filters['date_from'] ?? '-')
            . ' - '
            . ($this->filters['date_to'] ?? '-'),
        ];

        $rows[] = [
            'Branch',
            $report['branch']['name']
                ?? 'All Branch',
        ];

        $rows[] = [];

        /*
        |--------------------------------------------------------------------------
        | Mutation
        |--------------------------------------------------------------------------
        */

        $rows[] = ['RESELLER MUTATION'];

        $rows[] = [
            'Date',
            'Reference',
            'Description',
            'In',
            'Out',
            'Price',
            'Debit',
            'Credit',
            'Balance Qty',
            'Balance Value',
        ];

        foreach ($report['rows'] ?? [] as $row) {

            $rows[] = [
                $row['date'] ?? '',
                $row['reference'] ?? '',
                $row['description'] ?? '',
                $row['in'] ?? 0,
                $row['out'] ?? 0,
                $row['price'] ?? 0,
                $row['debit'] ?? 0,
                $row['credit'] ?? 0,
                $row['balance_qty'] ?? 0,
                $row['balance_value'] ?? 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Ending Balance
        |--------------------------------------------------------------------------
        */

        $lastRow =
            collect(
                $report['rows'] ?? []
            )->last();

        $rows[] = [
            'ENDING BALANCE',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $lastRow['balance_qty'] ?? 0,
            $lastRow['balance_value'] ?? 0,
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

                $sheet =
                    $event->sheet->getDelegate();

                $highestRow =
                    $sheet->getHighestRow();

                $highestColumn =
                    $sheet->getHighestColumn();

                /*
                |--------------------------------------------------------------------------
                | Bold Section Headers
                |--------------------------------------------------------------------------
                */

                foreach (
                    range(1, $highestRow)
                    as $row
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
                                'RESELLER MUTATION',
                                'ENDING BALANCE',
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