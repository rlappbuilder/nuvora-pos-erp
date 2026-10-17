<?php

namespace App\Services\Accounting;

use App\Models\Accounting\FundTransferHeader;
use App\Models\Accounting\InterBranchSettlementHeader;
use App\Models\Accounting\InterBranchSettlementDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FundTransferReportService
{
    /**
     * Get paginated report and summary.
     *
     * Supported modes:
     * - receivable
     * - payable
     * - allocation
     */
    public function getReport(string $mode, Request $request): array
    {
        $this->validateMode($mode);

        $filters = $this->getFilters($request);

        $query = $this->buildQuery($mode, $filters);

        $summary = $this->getSummary($query, $mode);

        $transfers = (clone $query)
            ->orderByDesc('fund_transfer_headers.transfer_date')
            ->orderByDesc('fund_transfer_headers.id')
            ->paginate($filters['per_page'])
            ->withQueryString();

        return [
            'data' => $transfers,
            'summary' => $summary,
            'filters' => $filters,
            'mode' => $mode,
        ];
    }

    /**
     * Get all report rows for export and print.
     */
    public function getExportData(string $mode, Request $request)
    {
        $this->validateMode($mode);

        $filters = $this->getFilters($request);

        return $this->buildQuery($mode, $filters)
            ->orderByDesc('fund_transfer_headers.transfer_date')
            ->orderByDesc('fund_transfer_headers.id')
            ->get();
    }

    /**
     * Get a single transfer and its settlement history.
     */
    public function getDetail(string $mode, int $fundTransferId): array
    {
        $this->validateMode($mode);

        $user = auth()->user();

        $companyId = $user->company_id ?? null;

        abort_if(!$companyId, 403, 'Company tidak ditemukan.');

        $query = $this->buildQuery($mode, [
            'search' => null,
            'from_branch_id' => null,
            'to_branch_id' => null,
            'date_from' => null,
            'date_to' => null,
        ]);

        $transfer = $query
            ->where('fund_transfer_headers.id', $fundTransferId)
            ->firstOrFail();

        $settlements = InterBranchSettlementHeader::query()
            ->where('company_id', $companyId)
            ->where('status', 'Posted')
            ->whereNull('deleted_at')
            ->whereHas('details', function (Builder $detailQuery) use ($fundTransferId) {
                $detailQuery->where(
                    'fund_transfer_header_id',
                    $fundTransferId
                );
            })
            ->with([
                'fromBranch',
                'toBranch',
                'fromAccount',
                'toAccount',
                'details' => function ($detailQuery) use ($fundTransferId) {
                    $detailQuery->where(
                        'fund_transfer_header_id',
                        $fundTransferId
                    );
                },
            ])
            ->orderBy('settlement_date')
            ->orderBy('id')
            ->get();

        return [
            'transfer' => $transfer,
            'settlements' => $settlements,
        ];
    }

    /**
     * Build the report query.
     */
    protected function buildQuery(string $mode, array $filters): Builder
    {
        $user = auth()->user();

        $companyId = $user->company_id ?? null;

        abort_if(!$companyId, 403, 'Company tidak ditemukan.');

        $query = FundTransferHeader::query()
            ->where('fund_transfer_headers.company_id', $companyId)
            ->where('fund_transfer_headers.status', 'Posted')
            ->whereNull('fund_transfer_headers.deleted_at')
            ->with([
                'fromBranch',
                'toBranch',
                'fromAccount',
                'toAccount',
                'journalEntry',
            ]);

        if ($mode === 'allocation') {
            $query->where('fund_transfer_headers.transfer_mode', 'allocation');

            $query->select('fund_transfer_headers.*')
                ->selectRaw('0 AS settled_amount')
                ->selectRaw(
                    'fund_transfer_headers.amount AS outstanding_amount'
                );
        } else {
            /*
             * Receivable and Payable are two perspectives
             * of the same posted Receivable transfer.
             *
             * The source transaction remains unchanged.
             * The UI can reverse the branch presentation
             * for the Payable perspective.
             */
            $query->where(
                'fund_transfer_headers.transfer_mode',
                'receivable'
            );

            $settledSubquery = $this->settledAmountSubquery();

            $query->select('fund_transfer_headers.*')
                ->selectSub(
                    clone $settledSubquery,
                    'settled_amount'
                )
                ->selectRaw(
                    'fund_transfer_headers.amount - COALESCE(('
                    . $settledSubquery->toSql()
                    . '), 0) AS outstanding_amount',
                    $settledSubquery->getBindings()
                );
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function (Builder $searchQuery) use ($search) {
                $searchQuery
                    ->where(
                        'fund_transfer_headers.transfer_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'fund_transfer_headers.description',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas('fromBranch', function (Builder $branchQuery) use ($search) {
                        $branchQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('toBranch', function (Builder $branchQuery) use ($search) {
                        $branchQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['from_branch_id'])) {
            $query->where(
                'fund_transfer_headers.from_branch_id',
                $filters['from_branch_id']
            );
        }

        if (!empty($filters['to_branch_id'])) {
            $query->where(
                'fund_transfer_headers.to_branch_id',
                $filters['to_branch_id']
            );
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'fund_transfer_headers.transfer_date',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'fund_transfer_headers.transfer_date',
                '<=',
                $filters['date_to']
            );
        }

        /*
         * Hide fully settled transfers from Receivable/Payable.
         * Allocation does not use settlement balances.
         */
        if (in_array($mode, ['receivable', 'payable'], true)) {
            $query->whereRaw(
                'fund_transfer_headers.amount > COALESCE(('
                . $this->settledAmountSubquery()->toSql()
                . '), 0)',
                $this->settledAmountSubquery()->getBindings()
            );
        }

        return $query;
    }

    /**
     * Subquery calculating settlements that are actually Posted.
     *
     * receipt_status is intentionally not used:
     * a Posted settlement reduces outstanding even if its
     * receipt workflow has not yet been completed.
     */
    protected function settledAmountSubquery()
    {
        return DB::table('inter_branch_settlement_details as settlement_details')
            ->join(
                'inter_branch_settlement_headers as settlement_headers',
                'settlement_headers.id',
                '=',
                'settlement_details.inter_branch_settlement_header_id'
            )
            ->selectRaw('COALESCE(SUM(settlement_details.amount), 0)')
            ->whereColumn(
                'settlement_details.fund_transfer_header_id',
                'fund_transfer_headers.id'
            )
            ->whereColumn(
                'settlement_headers.company_id',
                'fund_transfer_headers.company_id'
            )
            ->where('settlement_headers.status', 'Posted')
            ->whereNull('settlement_headers.deleted_at');
    }

    /**
     * Calculate summary using a derived table.
     *
     * This allows SQL to aggregate the computed aliases
     * settled_amount and outstanding_amount safely.
     */
    protected function getSummary(Builder $query, string $mode): array
    {
        $summaryQuery = (clone $query)
            ->reorder()
            ->toBase();

        $summary = DB::query()
            ->fromSub($summaryQuery, 'report_rows')
            ->selectRaw('COUNT(*) AS documents')
            ->selectRaw(
                'COALESCE(SUM(report_rows.amount), 0) AS transfer_amount'
            )
            ->selectRaw(
                'COALESCE(SUM(report_rows.settled_amount), 0) AS settled_amount'
            )
            ->selectRaw(
                'COALESCE(SUM(report_rows.outstanding_amount), 0) AS outstanding_amount'
            )
            ->first();

        return [
            'documents' => (int) ($summary->documents ?? 0),
            'transfer_amount' => (float) ($summary->transfer_amount ?? 0),
            'settled_amount' => (float) ($summary->settled_amount ?? 0),
            'outstanding_amount' => (float) ($summary->outstanding_amount ?? 0),
        ];
    }

    /**
     * Get supported filters from the request.
     */
   protected function getFilters(Request $request): array
{
    $perPage = (int) $request->input('per_page', 10);

    if (!in_array($perPage, [10, 25, 50, 100], true)) {
        $perPage = 10;
    }

    return [
        'search' => trim((string) $request->input('search', '')),
        'from_branch_id' => $request->input('from_branch_id'),
        'to_branch_id' => $request->input('to_branch_id'),
        'date_from' => $request->input('date_from'),
        'date_to' => $request->input('date_to'),
        'per_page' => $perPage,
    ];
}

    /**
     * Validate report mode.
     */
    protected function validateMode(string $mode): void
    {
        if (!in_array($mode, ['receivable', 'payable', 'allocation'], true)) {
            throw ValidationException::withMessages([
                'mode' => 'Mode laporan Fund Transfer tidak valid.',
            ]);
        }
    }
}