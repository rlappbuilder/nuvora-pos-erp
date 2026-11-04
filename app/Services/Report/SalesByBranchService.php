<?php

namespace App\Services\Report;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SalesByBranchService
{
    /**
     * Generate Sales by Branch Report.
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

        $source = $filters['source'] ?? 'all';

        $branchTotals = [];

        $transactionIds = [];

        /*
        |--------------------------------------------------------------------------
        | POS SALES
        |--------------------------------------------------------------------------
        */

        if (in_array($source, ['all', 'pos'], true)) {

            $posRows = $this->getPosRows(
                $companyId,
                $branchIds,
                $dateFrom,
                $dateTo
            );

            foreach ($posRows as $row) {

                $branchId = (int) $row->branch_id;

                $lineGross = (float) $row->line_gross;

                $itemDiscount = (float) $row->item_discount;

                $lineNet = (float) $row->line_net;

                $saleNetBeforeHeaderDiscount =
                    (float) $row->sale_net_before_header_discount;

                $headerDiscount =
                    (float) $row->header_discount;

                $allocatedHeaderDiscount = 0;

                if ($saleNetBeforeHeaderDiscount > 0) {

                    $allocatedHeaderDiscount =
                        $headerDiscount *
                        (
                            $lineNet /
                            $saleNetBeforeHeaderDiscount
                        );
                }

                $totalDiscount =
                    $itemDiscount +
                    $allocatedHeaderDiscount;

                $netSales =
                    $lineGross -
                    $totalDiscount;

                $transactionKey =
                    'POS-' . $row->pos_sale_id;

                $transactionIds[$transactionKey] = true;

                $this->addBranchRow(
                    $branchTotals,
                    $branchId,
                    $row->branch_code,
                    $row->branch_name,
                    'pos',
                    null,
                    null,
                    'POS Sales',
                    $transactionKey,
                    (float) $row->qty,
                    $lineGross,
                    $totalDiscount,
                    $netSales,
                    (float) $row->total_cost
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CONSIGNMENT SALES
        |--------------------------------------------------------------------------
        */

        if (in_array($source, ['all', 'consignment'], true)) {

            $consignmentRows = $this->getConsignmentRows(
                $companyId,
                $branchIds,
                $dateFrom,
                $dateTo
            );

            foreach ($consignmentRows as $row) {

                $branchId = (int) $row->branch_id;

                $resellerId = (int) $row->reseller_id;

                $grossSales = (float) $row->total_amount;

                $qty = (float) $row->qty_sold;

                $cogs = (float) $row->total_cost;

                $transactionKey =
                    'CONSIGNMENT-' . $row->settlement_header_id;

                $transactionIds[$transactionKey] = true;

                $this->addBranchRow(
                    $branchTotals,
                    $branchId,
                    $row->branch_code,
                    $row->branch_name,
                    'consignment',
                    $resellerId,
                    $row->reseller_code,
                    $row->reseller_name,
                    $transactionKey,
                    $qty,
                    $grossSales,
                    0,
                    $grossSales,
                    $cogs
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | BUILD BRANCH COLLECTION
        |--------------------------------------------------------------------------
        */

        $branches = collect($branchTotals)

            ->map(function ($branch) {

                /*
                |--------------------------------------------------------------------------
                | Keep Raw Transaction IDs For Subtotal Calculation
                |--------------------------------------------------------------------------
                */

                $rawSalesRows = collect($branch['sales_rows']);

                /*
                |--------------------------------------------------------------------------
                | Calculate Branch Subtotal
                |--------------------------------------------------------------------------
                */

                $subtotal = $this->calculateTotals(
                    $rawSalesRows,
                    true
                );

                /*
                |--------------------------------------------------------------------------
                | Build Display Sales Rows
                |--------------------------------------------------------------------------
                */

                $salesRows = $rawSalesRows

                    ->map(function ($row) {

                        $row['total_transactions'] =
                            count($row['transaction_ids']);

                        $row['gross_profit'] =
                            round(
                                $row['net_sales'] - $row['cogs'],
                                2
                            );

                        $row['margin'] =
                            $row['net_sales'] > 0
                                ? (
                                    $row['gross_profit'] /
                                    $row['net_sales']
                                ) * 100
                                : 0;

                        /*
                        |--------------------------------------------------------------------------
                        | Match Frontend Field Names
                        |--------------------------------------------------------------------------
                        */

                        $row['source'] =
                            $row['source_type'];

                        $row['reseller_name'] =
                            $row['source_type'] === 'consignment'
                                ? $row['name']
                                : null;

                        unset(
                            $row['transaction_ids'],
                            $row['source_type'],
                            $row['name']
                        );

                        return $row;
                    })

                    ->sort(function ($a, $b) {

                        /*
                        |--------------------------------------------------------------------------
                        | POS First
                        |--------------------------------------------------------------------------
                        */

                        if ($a['source'] !== $b['source']) {

                            if ($a['source'] === 'pos') {
                                return -1;
                            }

                            if ($b['source'] === 'pos') {
                                return 1;
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Consignment: Net Sales DESC
                        |--------------------------------------------------------------------------
                        */

                        $salesComparison =
                            $b['net_sales'] <=> $a['net_sales'];

                        if ($salesComparison !== 0) {
                            return $salesComparison;
                        }

                        return strcasecmp(
                            $a['reseller_name'] ?? '',
                            $b['reseller_name'] ?? ''
                        );
                    })

                    ->values();

                return [

                    'branch_id' =>
                        $branch['branch_id'],

                    'branch_code' =>
                        $branch['branch_code'],

                    'branch_name' =>
                        $branch['branch_name'],

                    'sales_rows' =>
                        $salesRows,

                    'subtotal' =>
                        $subtotal,
                ];
            })

            /*
            |--------------------------------------------------------------------------
            | Branch Sorting: Net Sales DESC
            |--------------------------------------------------------------------------
            */

            ->sort(function ($a, $b) {

                $salesComparison =
                    $b['subtotal']['net_sales']
                    <=>
                    $a['subtotal']['net_sales'];

                if ($salesComparison !== 0) {
                    return $salesComparison;
                }

                $grossComparison =
                    $b['subtotal']['gross_sales']
                    <=>
                    $a['subtotal']['gross_sales'];

                if ($grossComparison !== 0) {
                    return $grossComparison;
                }

                return strcasecmp(
                    $a['branch_name'],
                    $b['branch_name']
                );
            })

            ->values();

        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL
        |--------------------------------------------------------------------------
        */

        $totals = [

            'total_branches' =>
                $branches->count(),

            'total_transactions' =>
                count($transactionIds),

            'total_qty' =>
                $branches->sum(
                    fn ($branch) =>
                        $branch['subtotal']['total_qty']
                ),

            'gross_sales' =>
                round(
                    $branches->sum(
                        fn ($branch) =>
                            $branch['subtotal']['gross_sales']
                    ),
                    2
                ),

            'discount' =>
                round(
                    $branches->sum(
                        fn ($branch) =>
                            $branch['subtotal']['discount']
                    ),
                    2
                ),

            'net_sales' =>
                round(
                    $branches->sum(
                        fn ($branch) =>
                            $branch['subtotal']['net_sales']
                    ),
                    2
                ),

            'cogs' =>
                round(
                    $branches->sum(
                        fn ($branch) =>
                            $branch['subtotal']['cogs']
                    ),
                    2
                ),

            'gross_profit' =>
                round(
                    $branches->sum(
                        fn ($branch) =>
                            $branch['subtotal']['gross_profit']
                    ),
                    2
                ),
        ];

        $totals['margin'] =
            $totals['net_sales'] > 0
                ? (
                    $totals['gross_profit'] /
                    $totals['net_sales']
                ) * 100
                : 0;

        /*
        |--------------------------------------------------------------------------
        | CHART DATA
        |--------------------------------------------------------------------------
        |
        | One POS row and one Consignment row per branch.
        | Consignment transactions are aggregated across all resellers.
        | Chart is generated before pagination.
        |
        */

        $chart = $branches

            ->flatMap(function ($branch) {

                $rows = collect($branch['sales_rows']);

                $chartRows = collect();

                /*
                |--------------------------------------------------------------------------
                | POS Chart Row
                |--------------------------------------------------------------------------
                */

                $posRow = $rows->first(
                    fn ($row) => $row['source'] === 'pos'
                );

                if ($posRow) {

                    $chartRows->push([

                        'branch_id' =>
                            $branch['branch_id'],

                        'branch_name' =>
                            $branch['branch_name'],

                        'source' =>
                            'pos',

                        'label' =>
                            $branch['branch_name'] . ' - POS Sales',

                        'net_sales' =>
                              (float) $posRow['net_sales'],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Consignment Chart Row
                |--------------------------------------------------------------------------
                |
                | All reseller transactions are combined per branch.
                |
                */

                $consignmentRows = $rows->filter(
                    fn ($row) => $row['source'] === 'consignment'
                );

                if ($consignmentRows->isNotEmpty()) {

                    $chartRows->push([

                        'branch_id' =>
                            $branch['branch_id'],

                        'branch_name' =>
                            $branch['branch_name'],

                        'source' =>
                            'consignment',

                        'label' =>
                            $branch['branch_name'] . ' - Consignment',

                        'net_sales' =>
                            (float) $consignmentRows->sum(
                                'net_sales'
                            ),
                    ]);
                }

                return $chartRows;
            })

            ->values();

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $resultBranches = $branches;

        if ($paginate) {

            $perPage = 25;

            $currentPage =
                LengthAwarePaginator::resolveCurrentPage();

            $resultBranches = new LengthAwarePaginator(

                $branches
                    ->forPage($currentPage, $perPage)
                    ->values(),

                $branches->count(),

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
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $summary = [

            'total_branches' =>
                $totals['total_branches'],

            'total_transactions' =>
                $totals['total_transactions'],

            'total_items' =>
                $totals['total_qty'],

            'gross_sales' =>
                $totals['gross_sales'],

            'discount' =>
                $totals['discount'],

            'net_sales' =>
                $totals['net_sales'],

            'cogs' =>
                $totals['cogs'],

            'gross_profit' =>
                $totals['gross_profit'],

            'margin' =>
                $totals['margin'],
        ];

        /*
        |--------------------------------------------------------------------------
        | PREVIOUS PERIOD COMPARISON
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
        | RETURN REPORT
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

            'branch_rows' =>
                $resultBranches,

            'totals' =>
                $totals,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | POS QUERY
    |--------------------------------------------------------------------------
    */

    protected function getPosRows(
        int $companyId,
        array $branchIds,
        ?string $dateFrom,
        ?string $dateTo
    ) {

        $saleLineTotals = DB::table('pos_sale_details')

            ->select('pos_sale_id')

            ->selectRaw(
                'SUM(GREATEST(0, (qty * unit_price) - discount_amount)) as sale_net_before_header_discount'
            )

            ->groupBy('pos_sale_id');

        $query = DB::table('pos_sale_details as details')

            ->join(
                'pos_sales as sales',
                'sales.id',
                '=',
                'details.pos_sale_id'
            )

            ->join(
                'branches',
                'branches.id',
                '=',
                'sales.branch_id'
            )

            ->joinSub(
                $saleLineTotals,
                'sale_totals',
                function ($join) {

                    $join->on(
                        'sale_totals.pos_sale_id',
                        '=',
                        'sales.id'
                    );
                }
            )

            ->where(
                'sales.company_id',
                $companyId
            )

            ->where(
                'sales.status',
                'posted'
            );

        if (!empty($branchIds)) {

            $query->whereIn(
                'sales.branch_id',
                $branchIds
            );
        }

        if ($dateFrom) {

            $query->whereDate(
                'sales.sale_date',
                '>=',
                $dateFrom
            );
        }

        if ($dateTo) {

            $query->whereDate(
                'sales.sale_date',
                '<=',
                $dateTo
            );
        }

        return $query

            ->select([

                'sales.id as pos_sale_id',

                'sales.branch_id',

                'branches.code as branch_code',

                'branches.name as branch_name',

                'details.qty',

                'details.discount_amount as item_discount',

                'details.total_cost',

                'sales.discount_amount as header_discount',

                'sale_totals.sale_net_before_header_discount',

            ])

            ->selectRaw(
                '(details.qty * details.unit_price) as line_gross'
            )

            ->selectRaw(
                'GREATEST(0, (details.qty * details.unit_price) - details.discount_amount) as line_net'
            )

            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | CONSIGNMENT QUERY
    |--------------------------------------------------------------------------
    */

    protected function getConsignmentRows(
        int $companyId,
        array $branchIds,
        ?string $dateFrom,
        ?string $dateTo
    ) {

        $movementCosts = DB::table('inventory_movements')

            ->select([

                'reference_id',

                'branch_id',

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

                'branch_id',

                'product_variant_id',

                'unit_id',

            ]);

        $query = DB::table('settlement_details as details')

            ->join(
                'settlement_headers as settlements',
                'settlements.id',
                '=',
                'details.settlement_header_id'
            )

            ->join(
                'branches',
                'branches.id',
                '=',
                'settlements.branch_id'
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
                        'movement_costs.branch_id',
                        '=',
                        'settlements.branch_id'
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

        if (!empty($branchIds)) {

            $query->whereIn(
                'settlements.branch_id',
                $branchIds
            );
        }

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

        return $query

            ->select([

                'settlements.id as settlement_header_id',

                'settlements.branch_id',

                'settlements.reseller_id',

                'branches.code as branch_code',

                'branches.name as branch_name',

                'resellers.reseller_code',

                'resellers.name as reseller_name',

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
    | ADD BRANCH AGGREGATE
    |--------------------------------------------------------------------------
    */

    protected function addBranchRow(
        array &$branches,
        int $branchId,
        ?string $branchCode,
        ?string $branchName,
        string $sourceType,
        ?int $resellerId,
        ?string $resellerCode,
        ?string $resellerName,
        string $transactionKey,
        float $qty,
        float $grossSales,
        float $discount,
        float $netSales,
        float $cogs
    ): void {

        if (!isset($branches[$branchId])) {

            $branches[$branchId] = [

                'branch_id' =>
                    $branchId,

                'branch_code' =>
                    $branchCode,

                'branch_name' =>
                    $branchName,

                'sales_rows' =>
                    [],
            ];
        }

        if ($sourceType === 'pos') {

            $rowKey = 'POS';

            $rowName = 'POS Sales';

        } else {

            $rowKey = 'RESELLER-' . $resellerId;

            $rowName = $resellerName;
        }

        if (!isset($branches[$branchId]['sales_rows'][$rowKey])) {

            $branches[$branchId]['sales_rows'][$rowKey] = [

                'source_type' =>
                    $sourceType,

                'reseller_id' =>
                    $resellerId,

                'reseller_code' =>
                    $resellerCode,

                'name' =>
                    $rowName,

                'total_qty' =>
                    0,

                'gross_sales' =>
                    0,

                'discount' =>
                    0,

                'net_sales' =>
                    0,

                'cogs' =>
                    0,

                'transaction_ids' =>
                    [],
            ];
        }

        $row = &$branches[$branchId]['sales_rows'][$rowKey];

        $row['total_qty'] += $qty;

        $row['gross_sales'] += $grossSales;

        $row['discount'] += $discount;

        $row['net_sales'] += $netSales;

        $row['cogs'] += $cogs;

        $row['transaction_ids'][$transactionKey] = true;

        unset($row);
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE TOTALS
    |--------------------------------------------------------------------------
    */

    protected function calculateTotals(
        $rows,
        bool $includeTransactions = true
    ): array {

        $transactionIds = [];

        foreach ($rows as $row) {

            foreach ($row['transaction_ids'] ?? [] as $key => $value) {

                $transactionIds[$key] = true;
            }
        }

        $totals = [

            'total_transactions' =>
                $includeTransactions
                    ? count($transactionIds)
                    : 0,

            'total_qty' =>
                $rows->sum('total_qty'),

            'gross_sales' =>
                round($rows->sum('gross_sales'), 2),

            'discount' =>
                round($rows->sum('discount'), 2),

            'net_sales' =>
                round($rows->sum('net_sales'), 2),

            'cogs' =>
                round($rows->sum('cogs'), 2),

            'gross_profit' =>
                round(
                    $rows->sum('net_sales') -
                    $rows->sum('cogs'),
                    2
                ),
        ];

        $totals['margin'] =
            $totals['net_sales'] > 0
                ? (
                    $totals['gross_profit'] /
                    $totals['net_sales']
                ) * 100
                : 0;

        return $totals;
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE PREVIOUS PERIOD COMPARISON
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

        $periodDays = $dateFrom->diffInDays($dateTo) + 1;

        $previousDateTo = $dateFrom->copy()
            ->subDay();

        $previousDateFrom = $previousDateTo->copy()
            ->subDays($periodDays - 1);

        $previousFilters = $filters;

        $previousFilters['date_from'] =
            $previousDateFrom->toDateString();

        $previousFilters['date_to'] =
            $previousDateTo->toDateString();

        $previousReport = $this->generate(
            $previousFilters,
            false,
            false
        );

        $previousSummary = $previousReport['summary'];

        $kpiKeys = [

            'total_branches',

            'total_transactions',

            'total_items',

            'gross_sales',

            'discount',

            'net_sales',

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

        if ($periodDays === 1) {

            $periodLabel = 'Previous Day';

        } elseif ($periodDays === 7) {

            $periodLabel = 'Previous Week';

        } else {

            $periodLabel = 'Previous Period';
        }

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
    | CALCULATE PERCENTAGE CHANGE
    |--------------------------------------------------------------------------
    */

    protected function calculatePercentageChange(
        float $current,
        float $previous
    ): array {

        if ($current == 0 && $previous == 0) {

            return [

                'current' => 0,

                'previous' => 0,

                'percentage' => null,

                'direction' => 'unchanged',

                'status' => 'no_data',
            ];
        }

        if ($previous == 0 && $current > 0) {

            return [

                'current' => $current,

                'previous' => 0,

                'percentage' => null,

                'direction' => 'up',

                'status' => 'new',
            ];
        }

        $percentage = (
            ($current - $previous) /
            abs($previous)
        ) * 100;

        $percentage = round($percentage, 2);

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