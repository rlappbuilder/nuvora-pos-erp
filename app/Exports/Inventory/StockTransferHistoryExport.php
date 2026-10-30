<?php

namespace App\Exports\Inventory;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockTransferHistoryExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    public function __construct(
        protected Collection $transfers,
        protected string $direction
    ) {
    }

    public function collection(): Collection
    {
        return $this->transfers;
    }

    public function headings(): array
    {
        return [
            'Transfer Number',
            'Transaction Date',
            'Source Branch',
            'Destination Branch',
            'Source Warehouse',
            'Destination Warehouse',
            'Items',
            'Total Quantity',
            'Total Amount',
            'Status',
            'Description',
        ];
    }

    public function map($transfer): array
    {
        return [
            $transfer->number,
            $transfer->transaction_date,
            $transfer->fromBranch?->name,
            $transfer->toBranch?->name,
            $transfer->fromWarehouse?->name,
            $transfer->toWarehouse?->name,
            $transfer->details_count,
            $transfer->total_qty,
            $transfer->total_cost,
            $transfer->status,
            $transfer->description,
        ];
    }
}