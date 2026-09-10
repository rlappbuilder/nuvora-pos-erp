<?php

namespace App\Exports\Purchasing\Reports;

use App\Services\Purchasing\Reports\SupplierStatementService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SupplierStatementExport implements FromArray, WithHeadings
{
    protected SupplierStatementService $supplierStatementService;

    protected array $filters;

    public function __construct(
        SupplierStatementService $supplierStatementService,
        array $filters
    ) {
        $this->supplierStatementService = $supplierStatementService;
        $this->filters = $filters;
    }

    public function array(): array
    {
        $report = $this->supplierStatementService
            ->getStatement($this->filters);

        $rows = [];

        /*
        |--------------------------------------------------------------------------
        | Opening Balance
        |--------------------------------------------------------------------------
        */

        $rows[] = [
            'Opening Balance',
            null,
            null,
            null,
            null,
            $report['opening_balance'] ?? 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        */

        foreach ($report['rows'] ?? [] as $row) {
            $rows[] = [
                $row['date'] ?? null,
                $row['document'] ?? null,
                $row['type'] ?? null,
                $row['debit'] ?? 0,
                $row['credit'] ?? 0,
                $row['balance'] ?? 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Period Total
        |--------------------------------------------------------------------------
        */

        $rows[] = [
            'Period Total',
            null,
            null,
            $report['totals']['debit'] ?? 0,
            $report['totals']['credit'] ?? 0,
            null,
        ];

        /*
        |--------------------------------------------------------------------------
        | Closing Balance
        |--------------------------------------------------------------------------
        */

        $rows[] = [
            'Closing Balance',
            null,
            null,
            null,
            null,
            $report['totals']['closing_balance'] ?? 0,
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Document',
            'Type',
            'Debit',
            'Credit',
            'Balance',
        ];
    }
}