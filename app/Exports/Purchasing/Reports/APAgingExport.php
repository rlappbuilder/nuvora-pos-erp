<?php

namespace App\Exports\Purchasing\Reports;

use App\Services\Purchasing\Reports\APAgingService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class APAgingExport implements FromArray, WithHeadings
{
    protected APAgingService $service;

    protected array $filters;

    public function __construct(
        APAgingService $service,
        array $filters
    ) {
        $this->service = $service;
        $this->filters = $filters;
    }

    public function array(): array
    {
        $report =
            $this->service
                ->getAging($this->filters);

        $rows = [];

        foreach ($report['rows'] as $row) {
            $rows[] = [
                $row['supplier_name'],
                $row['current'],
                $row['days_1_30'],
                $row['days_31_60'],
                $row['days_61_90'],
                $row['over_90'],
                $row['total'],
            ];
        }

        $rows[] = [
            'TOTAL',
            $report['totals']['current'],
            $report['totals']['days_1_30'],
            $report['totals']['days_31_60'],
            $report['totals']['days_61_90'],
            $report['totals']['over_90'],
            $report['totals']['total'],
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Supplier',
            'Current',
            '1–30 Days',
            '31–60 Days',
            '61–90 Days',
            '>90 Days',
            'Total',
        ];
    }
}