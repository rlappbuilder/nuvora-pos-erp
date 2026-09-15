<?php

namespace App\Exports\Resellers\Reports;

use App\Models\Reseller\ConsignmentOut\ConsignmentOut;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsignmentOutExport implements
    FromArray,
    WithHeadings,
    WithColumnWidths,
    WithStyles
{
    protected ConsignmentOut $consignmentOut;

    public function __construct(ConsignmentOut $consignmentOut)
    {
        $this->consignmentOut = $consignmentOut;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->consignmentOut->details as $index => $detail) {
            $rows[] = [
                $index + 1,
                $detail->variant?->product?->name ?? '-',
                $detail->variant?->sku ?? '-',
                $detail->qty ?? 0,
                $detail->unit?->name ?? '-',
                $detail->unit_price ?? 0,
                $detail->total_price ?? 0,
            ];
        }

        $grandTotal = $this->consignmentOut->details->sum(
            fn ($detail) => (float) ($detail->total_price ?? 0)
        );

        $rows[] = [
            '',
            '',
            '',
            '',
            '',
            'Grand Total',
            $grandTotal,
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Product',
            'SKU',
            'Jml',
            'Unit',
            'Harga',
            'Total',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 35,
            'C' => 20,
            'D' => 12,
            'E' => 15,
            'F' => 18,
            'G' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
            $lastRow => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}