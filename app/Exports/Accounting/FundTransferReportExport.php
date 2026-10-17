<?php

namespace App\Exports\Accounting;

use App\Services\Accounting\FundTransferReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FundTransferReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    protected string $mode;

    protected array $filters;

    public function __construct(string $mode, array $filters = [])
    {
        $this->mode = $mode;
        $this->filters = $filters;
    }

    public function collection()
    {
        $request = Request::create('/', 'GET', $this->filters);

        return app(FundTransferReportService::class)
            ->getExportData($this->mode, $request);
    }

    public function headings(): array
    {
        return [
            'Transfer Number',
            'Transfer Date',
            'From Branch',
            'To Branch',
            'Transfer Amount',
            'Settled Amount',
            'Outstanding Amount',
            'Status',
        ];
    }

    public function map($transfer): array
    {
        return [
            $transfer->transfer_number,
            $transfer->transfer_date
                ? $transfer->transfer_date->format('Y-m-d')
                : '',
            $transfer->fromBranch->name ?? '',
            $transfer->toBranch->name ?? '',
            (float) $transfer->amount,
            (float) ($transfer->settled_amount ?? 0),
            (float) ($transfer->outstanding_amount ?? 0),
            $transfer->status,
        ];
    }
}