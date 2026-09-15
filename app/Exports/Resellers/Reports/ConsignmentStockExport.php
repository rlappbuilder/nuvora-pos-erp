<?php

namespace App\Exports\Resellers\Reports;

use App\Services\Inventory\StockBalanceService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsignmentStockExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function collection()
    {
        $service = app(StockBalanceService::class);

        return $service
            ->getConsignmentStock($this->filters)
            ->getCollection();
    }

    public function headings(): array
    {
        return [
            'No',
            'Reseller',
            'Branch',
            'Warehouse',
            'Product',
            'SKU',
            'Unit',
            'On Hand',
            'Available',
            'Average Cost',
            'Stock Value',
            'Consignment Price',
            'Consignment Value',
        ];
    }

    public function map($row): array
    {
        return [
            $row['id'] ?? '',
            data_get($row, 'reseller.name', '-'),
            data_get($row, 'branch.name', '-'),
            data_get($row, 'warehouse.name', '-'),
            data_get($row, 'product.name', data_get($row, 'variant.product.name', '-')),
            data_get($row, 'variant.sku', '-'),
            collect($row['units'] ?? [])
                ->pluck('unit_name')
                ->filter()
                ->implode(', '),
            $row['on_hand_qty'] ?? 0,
            $row['available_qty'] ?? 0,
            $row['average_cost'] ?? 0,
            $row['stock_value'] ?? 0,
            $row['consignment_price'] ?? 0,
            $row['consignment_value'] ?? 0,
        ];
    }

    public function styles(
        Worksheet $sheet
    ): array {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}