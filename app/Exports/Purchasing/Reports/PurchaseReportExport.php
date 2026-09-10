<?php

namespace App\Exports\Purchasing\Reports;

use App\Services\Purchasing\Reports\PurchaseReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseReportExport implements FromArray, WithHeadings
{
    /*
    |--------------------------------------------------------------------------
    | Service
    |--------------------------------------------------------------------------
    */

    protected PurchaseReportService $service;

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    protected array $filters;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        PurchaseReportService $service,
        array $filters
    ) {
        $this->service = $service;

        $this->filters = $filters;
    }

    /*
    |--------------------------------------------------------------------------
    | Array
    |--------------------------------------------------------------------------
    */

    public function array(): array
    {
        $report =
            $this->service
                ->getReport($this->filters);

        $rows = [];

        foreach ($report['rows'] as $row) {

            $rows[] = [
                $row['date'],
                $row['document'],
                $row['supplier_name'],
                $row['branch_name'],
                $row['type'],
                $row['amount'],
                $row['status'],
            ];
        }

        return $rows;
    }

    /*
    |--------------------------------------------------------------------------
    | Headings
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        return [
            'Date',
            'Document',
            'Supplier',
            'Branch',
            'Type',
            'Amount',
            'Status',
        ];
    }
}