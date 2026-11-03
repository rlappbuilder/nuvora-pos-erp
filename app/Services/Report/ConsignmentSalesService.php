<?php

namespace App\Services\Report;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ConsignmentSalesService
{
    /**
     * Generate Consignment Sales Report.
     */
    public function generate(
        array $filters,
        bool $paginate = true,
        bool $includeComparison = true
    ): array {

        $companyId = (int) $filters['company_id'];

        $branchIds = $filters['branch_ids'] ?? [];

        $dateFrom = $filters['date_from'] ?? null;

        $dateTo = $filters['date_to'] ?? null;

        $resellerId = $filters['reseller_id'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Get Consignment Rows
        |--------------------------------------------------------------------------
        */

        $rows = $this->getConsignmentRows(
            $companyId,
            $branchIds,
            $dateFrom,
            $dateTo,
            $resellerId
        );

        /*
        |--------------------------------------------------------------------------
        | Aggregate By Reseller
        |--------------------------------------------------------------------------
        */

        $resellerTotals = [];

        $transactionIds = [];

        $transactionsPerReseller = [];

        foreach ($rows as $row) {

            $id = (int) $row->reseller_id;

            if (!isset($resellerTotals[$id])) {

                $resellerTotals[$id] = [

                    'reseller_id' => $id,

                    'reseller_name' =>
                        $row->reseller_name ?? 'Unknown Reseller',

                    'total_transactions' => 0,

                    'total_qty' => 0,

                    'gross_sales' => 0,

                    'cogs' => 0,

                    'gross_profit' => 0,

                    'margin' => 0,

                    'sales_contribution' => 0,
                ];
            }

            $resellerTotals[$id]['total_qty'] +=
                (float) $row->qty_sold;

            $resellerTotals[$id]['gross_sales'] +=
                (float) $row->total_amount;

            $resellerTotals[$id]['cogs'] +=
                (float) $row->total_cost;

            /*
            |--------------------------------------------------------------------------
            | Unique Transactions
            |--------------------------------------------------------------------------
            */

            $transactionKey =
                $id . '-' . $row->settlement_header_id;

            $transactionIds[$transactionKey] = true;

            $transactionsPerReseller[$id][
                $row->settlement_header_id
            ] = true;
        }

        /*
        |--------------------------------------------------------------------------
        | Build Reseller Collection
        |--------------------------------------------------------------------------
        */

        $resellers = collect($resellerTotals)

            ->map(function ($row) use ($transactionsPerReseller) {

                $id = $row['reseller_id'];

                $row['total_transactions'] = count(
                    $transactionsPerReseller[$id] ?? []
                );

                $row['gross_profit'] =
                    $row['gross_sales'] - $row['cogs'];

                $row['margin'] =
                    $row['gross_sales'] > 0
                        ? (
                            $row['gross_profit'] /
                            $row['gross_sales']
                        ) * 100
                        : 0;

                return $row;
            })

            ->sortByDesc('gross_sales')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Overall Totals
        |--------------------------------------------------------------------------
        */

        $totals = [

            'total_resellers' =>
                $resellers->count(),

            'total_transactions' =>
                count($transactionIds),

            'total_qty' =>
                $resellers->sum('total_qty'),

            'gross_sales' =>
                round($resellers->sum('gross_sales'), 2),

            'cogs' =>
                round($resellers->sum('cogs'), 2),

            'gross_profit' =>
                round($resellers->sum('gross_profit'), 2),
        ];

        $totals['margin'] =
            $totals['gross_sales'] > 0
                ? (
                    $totals['gross_profit'] /
                    $totals['gross_sales']
                ) * 100
                : 0;

        /*
        |--------------------------------------------------------------------------
        | Sales Contribution
        |--------------------------------------------------------------------------
        |
        | Calculate using all reseller data before pagination.
        |
        */

        $resellers = $resellers->map(function ($row) use ($totals) {

            $row['sales_contribution'] =
                $totals['gross_sales'] > 0
                    ? (
                        $row['gross_sales'] /
                        $totals['gross_sales']
                    ) * 100
                    : 0;

            return $row;
        });

        $totals['sales_contribution'] =
            $totals['gross_sales'] > 0 ? 100 : 0;

        /*
        |--------------------------------------------------------------------------
        | Chart Data
        |--------------------------------------------------------------------------
        */

        $chart = $resellers
            ->map(fn ($row) => [

                'reseller_id' =>
                    $row['reseller_id'],

                'reseller_name' =>
                    $row['reseller_name'],

                'gross_sales' =>
                    (float) $row['gross_sales'],

                'gross_profit' =>
                    (float) $row['gross_profit'],

            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $resultResellers = $resellers;

        if ($paginate) {

            $perPage = 25;

            $currentPage =
                LengthAwarePaginator::resolveCurrentPage();

            $resultResellers = new LengthAwarePaginator(

                $resellers
                    ->forPage($currentPage, $perPage)
                    ->values(),

                $resellers->count(),

                $perPage,

                $currentPage,

                [
                    'path' =>
                        LengthAwarePaginator::resolveCurrentPath(),

                    'query' =>
                        request()->query(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [

            'total_resellers' =>
                $totals['total_resellers'],

            'total_transactions' =>
                $totals['total_transactions'],

            'total_items' =>
                $totals['total_qty'],

            'gross_sales' =>
                $totals['gross_sales'],

            'cogs' =>
                $totals['cogs'],

            'gross_profit' =>
                $totals['gross_profit'],

            'margin' =>
                $totals['margin'],
        ];

        /*
        |--------------------------------------------------------------------------
        | Previous Period Comparison
        |--------------------------------------------------------------------------
        */

        $comparison = null;

        if ($includeComparison) {

            $comparison = $this->generateComparison(
                $filters,
                $summary
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Return Report
        |--------------------------------------------------------------------------
        */

        return [

            'filters' =>
                $filters,

            'summary' =>
                $summary,

            'comparison' =>
                $comparison,

            'chart' =>
                $chart,

            'resellers' =>
                $resultResellers,

            'totals' =>
                $totals,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Consignment Query
    |--------------------------------------------------------------------------
    */

    protected function getConsignmentRows(
        int $companyId,
        array $branchIds,
        ?string $dateFrom,
        ?string $dateTo,
        $resellerId = null
    ) {

        /*
        |--------------------------------------------------------------------------
        | Aggregate Inventory Movement COGS
        |--------------------------------------------------------------------------
        */

        $movementCosts = DB::table('inventory_movements')

            ->select([

                'reference_id',

                'product_variant_id',

                'unit_id',

            ])

            ->selectRaw(
                'SUM(total_cost) as total_cost'
            )

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'reference_type',
                'SETTLEMENT'
            )

            ->where(
                'qty_out',
                '>',
                0
            )

            ->groupBy([

                'reference_id',

                'product_variant_id',

                'unit_id',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Settlement Query
        |--------------------------------------------------------------------------
        */

        $query = DB::table('settlement_details as details')

            ->join(
                'settlement_headers as settlements',
                'settlements.id',
                '=',
                'details.settlement_header_id'
            )

            ->join(
                'resellers',
                'resellers.id',
                '=',
                'settlements.reseller_id'
            )

            ->leftJoinSub(
                $movementCosts,
                'movement_costs',
                function ($join) {

                    $join->on(
                        'movement_costs.reference_id',
                        '=',
                        'settlements.id'
                    )

                    ->on(
                        'movement_costs.product_variant_id',
                        '=',
                        'details.product_variant_id'
                    )

                    ->on(
                        'movement_costs.unit_id',
                        '=',
                        'details.unit_id'
                    );
                }
            )

            ->where(
                'settlements.company_id',
                $companyId
            )

            ->where(
                'settlements.status',
                'Posted'
            );

        /*
        |--------------------------------------------------------------------------
        | Branch Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($branchIds)) {

            $query->whereIn(
                'settlements.branch_id',
                $branchIds
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */

        if ($dateFrom) {

            $query->whereDate(
                'settlements.settlement_date',
                '>=',
                $dateFrom
            );
        }

        if ($dateTo) {

            $query->whereDate(
                'settlements.settlement_date',
                '<=',
                $dateTo
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Reseller Filter
        |--------------------------------------------------------------------------
        */

        if ($resellerId) {

            $query->where(
                'settlements.reseller_id',
                $resellerId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Select Data
        |--------------------------------------------------------------------------
        */

        return $query

            ->select([

                'settlements.id as settlement_header_id',

                'settlements.reseller_id',

                'resellers.name as reseller_name',

                'details.product_variant_id',

                'details.unit_id',

                'details.qty_sold',

                'details.total_amount',

            ])

            ->selectRaw(
                'COALESCE(movement_costs.total_cost, 0) as total_cost'
            )

            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Previous Period Comparison
    |--------------------------------------------------------------------------
    */

    protected function generateComparison(
        array $filters,
        array $currentSummary
    ): array {

        $dateFrom = Carbon::parse(
            $filters['date_from']
        )->startOfDay();

        $dateTo = Carbon::parse(
            $filters['date_to']
        )->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Calculate Period Duration
        |--------------------------------------------------------------------------
        */

        $periodDays = $dateFrom->diffInDays($dateTo) + 1;

        /*
        |--------------------------------------------------------------------------
        | Previous Period
        |--------------------------------------------------------------------------
        */

        $previousDateTo = $dateFrom->copy()
            ->subDay();

        $previousDateFrom = $previousDateTo->copy()
            ->subDays($periodDays - 1);

        /*
        |--------------------------------------------------------------------------
        | Previous Period Filters
        |--------------------------------------------------------------------------
        */

        $previousFilters = $filters;

        $previousFilters['date_from'] =
            $previousDateFrom->toDateString();

        $previousFilters['date_to'] =
            $previousDateTo->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Generate Previous Report
        |--------------------------------------------------------------------------
        |
        | Third parameter false prevents recursive comparison.
        |
        */

        $previousReport = $this->generate(
            $previousFilters,
            false,
            false
        );

        $previousSummary = $previousReport['summary'];

        /*
        |--------------------------------------------------------------------------
        | KPI Comparison
        |--------------------------------------------------------------------------
        */

        $kpiKeys = [

            'total_resellers',

            'total_transactions',

            'total_items',

            'gross_sales',

            'cogs',

            'gross_profit',

        ];

        $comparison = [];

        foreach ($kpiKeys as $key) {

            $current = (float) (
                $currentSummary[$key] ?? 0
            );

            $previous = (float) (
                $previousSummary[$key] ?? 0
            );

            $comparison[$key] = $this->calculatePercentageChange(
                $current,
                $previous
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Period Label
        |--------------------------------------------------------------------------
        */

        if ($periodDays === 1) {

            $periodLabel = 'Previous Day';

        } elseif ($periodDays === 7) {

            $periodLabel = 'Previous Week';

        } else {

            $periodLabel = 'Previous Period';
        }

        /*
        |--------------------------------------------------------------------------
        | Return Comparison
        |--------------------------------------------------------------------------
        */

        return [

            'period_days' =>
                $periodDays,

            'period_label' =>
                $periodLabel,

            'current_date_from' =>
                $dateFrom->toDateString(),

            'current_date_to' =>
                $dateTo->toDateString(),

            'previous_date_from' =>
                $previousDateFrom->toDateString(),

            'previous_date_to' =>
                $previousDateTo->toDateString(),

            'metrics' =>
                $comparison,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Percentage Change
    |--------------------------------------------------------------------------
    */

    protected function calculatePercentageChange(
        float $current,
        float $previous
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Both Periods Are Zero
        |--------------------------------------------------------------------------
        */

        if ($current == 0 && $previous == 0) {

            return [

                'current' => 0,

                'previous' => 0,

                'percentage' => null,

                'direction' => 'unchanged',

                'status' => 'no_data',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Previous Period Is Zero
        |--------------------------------------------------------------------------
        */

        if ($previous == 0 && $current > 0) {

            return [

                'current' => $current,

                'previous' => 0,

                'percentage' => null,

                'direction' => 'up',

                'status' => 'new',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate Percentage
        |--------------------------------------------------------------------------
        */

        $percentage = (
            ($current - $previous) /
            abs($previous)
        ) * 100;

        $percentage = round($percentage, 2);

        /*
        |--------------------------------------------------------------------------
        | Determine Direction
        |--------------------------------------------------------------------------
        */

        if ($current > $previous) {

            $direction = 'up';

            $status = 'changed';

        } elseif ($current < $previous) {

            $direction = 'down';

            $status = 'changed';

        } else {

            $direction = 'unchanged';

            $status = 'unchanged';
        }

        return [

            'current' =>
                $current,

            'previous' =>
                $previous,

            'percentage' =>
                $percentage,

            'direction' =>
                $direction,

            'status' =>
                $status,
        ];
    }
}