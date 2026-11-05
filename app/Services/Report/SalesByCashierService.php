<?php

namespace App\Services\Report;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class SalesByCashierService
{
    /**
     * Generate Sales by Cashier report.
     */
    public function generate(
        array $filters,
        bool $paginate = true,
        bool $includeComparison = true
    ): array {
        $companyId = $filters['company_id'];

        $branchIds = $filters['branch_ids'] ?? [];
        $cashierIds = $filters['cashier_ids'] ?? [];

        $source = $filters['source'] ?? 'all';

        $dateFrom = $filters['date_from'];
        $dateTo = $filters['date_to'];

        $rows = collect();

        /*
        |--------------------------------------------------------------------------
        | POS SALES
        |--------------------------------------------------------------------------
        */

        if (in_array($source, ['all', 'pos'], true)) {
            $posRows = $this->getPosRows(
                $companyId,
                $branchIds,
                $cashierIds,
                $dateFrom,
                $dateTo
            );

            $rows = $rows->merge($posRows);
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

            $rows = $rows->merge($consignmentRows);
        }

        /*
        |--------------------------------------------------------------------------
        | SORT ROWS
        |--------------------------------------------------------------------------
        */

        $rows = $rows
            ->sort(function ($a, $b) {
                $branchComparison = strcasecmp(
                    $a['branch_name'],
                    $b['branch_name']
                );

                if ($branchComparison !== 0) {
                    return $branchComparison;
                }

                if ($a['source_type'] !== $b['source_type']) {
                    return $a['source_type'] === 'POS' ? -1 : 1;
                }

                return strcasecmp(
                    $a['cashier_name'],
                    $b['cashier_name']
                );
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | BRANCH TOTALS
        |--------------------------------------------------------------------------
        */

        $branchRows = $rows
            ->groupBy('branch_id')
            ->map(function (Collection $branchItems) {
                $first = $branchItems->first();

                $branchTotal = $this->calculateTotals($branchItems);

                $items = $branchItems
                    ->map(function ($item) {
                        unset($item['transaction_ids']);

                        return $item;
                    })
                    ->values();

                return [
                    'branch_id' => $first['branch_id'],
                    'branch_name' => $first['branch_name'],
                    'rows' => $items,
                    'subtotal' => $branchTotal,
                ];
            })
            ->sort(function ($a, $b) {
                $salesComparison =
                    $b['subtotal']['net_sales']
                    <=>
                    $a['subtotal']['net_sales'];

                if ($salesComparison !== 0) {
                    return $salesComparison;
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

        $totals = $this->calculateTotals($rows);

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total_cashiers' => $rows
                ->where('source_type', 'POS')
                ->pluck('cashier_id')
                ->filter()
                ->unique()
                ->count(),

            'total_transactions' => $totals['total_transactions'],
            'total_items' => $totals['total_qty'],
            'gross_sales' => $totals['gross_sales'],
            'discount' => $totals['discount'],
            'net_sales' => $totals['net_sales'],
            'cogs' => $totals['cogs'],
            'gross_profit' => $totals['gross_profit'],
            'margin' => $totals['margin'],
        ];

        /*
        |--------------------------------------------------------------------------
        | CHART - TOP 10 CASHIERS
        |--------------------------------------------------------------------------
        */

        $chart = $this->generateChart($rows);

        /*
        |--------------------------------------------------------------------------
        | COMPARISON
        |--------------------------------------------------------------------------
        */

        $comparison = null;

        if ($includeComparison) {
            $comparison = $this->generateComparison($filters);
        }

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        if ($paginate) {
            $perPage = 25;

            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            $paginatedBranches = new LengthAwarePaginator(
                $branchRows->forPage($currentPage, $perPage)->values(),
                $branchRows->count(),
                $perPage,
                $currentPage,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => request()->query(),
                ]
            );
        } else {
            $paginatedBranches = $branchRows;
        }

        return [
            'filters' => $filters,
            'summary' => $summary,
            'comparison' => $comparison,
            'chart' => $chart,
            'branch_rows' => $paginatedBranches,
            'totals' => $totals,
        ];
    }

    /**
     * Retrieve POS sales grouped by branch and cashier.
     */
    private function getPosRows(
        int $companyId,
        array $branchIds,
        array $cashierIds,
        string $dateFrom,
        string $dateTo
    ): Collection {
        $saleLineTotals = DB::table('pos_sale_details')
            ->select(
                'pos_sale_id',
                DB::raw('SUM((qty * unit_price) - discount_amount) AS sale_net_before_header_discount')
            )
            ->groupBy('pos_sale_id');

        $query = DB::table('pos_sale_details as details')
            ->join('pos_sales as sales', 'sales.id', '=', 'details.pos_sale_id')
            ->join(
                'cashier_sessions as sessions',
                'sessions.id',
                '=',
                'sales.cashier_session_id'
            )
            ->leftJoin('users', 'users.id', '=', 'sessions.user_id')
            ->join('branches', 'branches.id', '=', 'sales.branch_id')
            ->leftJoinSub(
                $saleLineTotals,
                'saleLineTotals',
                function ($join) {
                    $join->on(
                        'saleLineTotals.pos_sale_id',
                        '=',
                        'sales.id'
                    );
                }
            )
            ->where('sales.company_id', $companyId)
            ->where('sales.status', 'posted')
            ->whereBetween(
                DB::raw('DATE(sales.sale_date)'),
                [$dateFrom, $dateTo]
            );

        if (!empty($branchIds)) {
            $query->whereIn('sales.branch_id', $branchIds);
        }

        if (!empty($cashierIds)) {
            $query->whereIn('sessions.user_id', $cashierIds);
        }

        $details = $query
            ->select(
                'sales.id as sale_id',
                'sales.sale_number',
                'sales.branch_id',
                'branches.name as branch_name',
                'sessions.user_id as cashier_id',
                'users.name as cashier_name',
                'sales.discount_amount as header_discount',
                'details.qty',
                'details.unit_price',
                'details.discount_amount as item_discount',
                'details.total_cost',
                'saleLineTotals.sale_net_before_header_discount'
            )
            ->get();

        return $details
            ->groupBy(function ($item) {
                return $item->branch_id . '-' . $item->cashier_id;
            })
            ->map(function (Collection $items) {
                $first = $items->first();

                $transactionIds = $items
                    ->pluck('sale_id')
                    ->unique()
                    ->values();

                $sales = $items->groupBy('sale_id');

                $grossSales = 0;
                $itemDiscount = 0;
                $headerDiscount = 0;
                $totalQty = 0;
                $cogs = 0;

                foreach ($sales as $saleItems) {
                    $sale = $saleItems->first();

                    $saleGross = $saleItems->sum(function ($detail) {
                        return (float) $detail->qty
                            * (float) $detail->unit_price;
                    });

                    $saleItemDiscount = $saleItems->sum(
                        'item_discount'
                    );

                    $saleNetBeforeHeaderDiscount =
                        (float) $sale->sale_net_before_header_discount;

                    $saleHeaderDiscount =
                        (float) $sale->header_discount;

                    $grossSales += $saleGross;
                    $itemDiscount += $saleItemDiscount;

                    $headerDiscount += $saleNetBeforeHeaderDiscount > 0
                        ? $saleHeaderDiscount
                        : 0;

                    $totalQty += $saleItems->sum('qty');
                    $cogs += $saleItems->sum('total_cost');
                }

                /*
                 * Allocate header discount proportionally
                 * to the cashier's grouped sales.
                 */
                $allocatedHeaderDiscount = 0;

                foreach ($sales as $saleItems) {
                    $sale = $saleItems->first();

                    $saleNetBeforeHeaderDiscount =
                        (float) $sale->sale_net_before_header_discount;

                    if ($saleNetBeforeHeaderDiscount <= 0) {
                        continue;
                    }

                    $saleHeaderDiscount =
                        (float) $sale->header_discount;

                    $cashierSaleNet = $saleItems->sum(function ($detail) {
                        return (
                            (float) $detail->qty
                            * (float) $detail->unit_price
                        ) - (float) $detail->item_discount;
                    });

                    $allocatedHeaderDiscount +=
                        $saleHeaderDiscount
                        * (
                            $cashierSaleNet
                            / $saleNetBeforeHeaderDiscount
                        );
                }

                $discount = $itemDiscount + $allocatedHeaderDiscount;

                $netSales = $grossSales - $discount;

                $grossProfit = $netSales - $cogs;

                return [
                    'branch_id' => $first->branch_id,
                    'branch_name' => $first->branch_name,

                    'source_type' => 'POS',

                    'cashier_id' => $first->cashier_id,
                    'cashier_name' => $first->cashier_name
                        ?? 'Unknown Cashier',

                    'total_transactions' => $transactionIds->count(),
                    'total_qty' => (float) $totalQty,

                    'gross_sales' => (float) $grossSales,
                    'discount' => (float) $discount,
                    'net_sales' => (float) $netSales,

                    'cogs' => (float) $cogs,
                    'gross_profit' => (float) $grossProfit,

                    'margin' => $netSales > 0
                        ? ($grossProfit / $netSales) * 100
                        : 0,

                    'transaction_ids' => $transactionIds
                        ->map(fn ($id) => 'POS-' . $id)
                        ->all(),
                ];
            })
            ->values();
    }

    /**
     * Retrieve consignment settlements grouped by branch.
     *
     * Consignment is not attributed to POS cashiers.
     */
    private function getConsignmentRows(
        int $companyId,
        array $branchIds,
        string $dateFrom,
        string $dateTo
    ): Collection {
        $movementCosts = DB::table('inventory_movements')
            ->select(
                'reference_id',
                'branch_id',
                'product_variant_id',
                'unit_id',
                DB::raw('SUM(total_cost) as total_cost')
            )
            ->where('company_id', $companyId)
            ->where('reference_type', 'SETTLEMENT')
            ->where('qty_out', '>', 0)
            ->groupBy(
                'reference_id',
                'branch_id',
                'product_variant_id',
                'unit_id'
            );

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
            ->leftJoinSub(
                $movementCosts,
                'movementCosts',
                function ($join) {
                    $join->on(
                        'movementCosts.reference_id',
                        '=',
                        'settlements.id'
                    )
                    ->on(
                        'movementCosts.branch_id',
                        '=',
                        'settlements.branch_id'
                    )
                    ->on(
                        'movementCosts.product_variant_id',
                        '=',
                        'details.product_variant_id'
                    )
                    ->on(
                        'movementCosts.unit_id',
                        '=',
                        'details.unit_id'
                    );
                }
            )
            ->where('settlements.company_id', $companyId)
            ->where('settlements.status', 'Posted')
            ->whereBetween(
                'settlements.settlement_date',
                [$dateFrom, $dateTo]
            );

        if (!empty($branchIds)) {
            $query->whereIn('settlements.branch_id', $branchIds);
        }

        $details = $query
            ->select(
                'settlements.id as settlement_id',
                'settlements.branch_id',
                'branches.name as branch_name',
                'details.qty_sold',
                'details.total_amount',
                DB::raw('COALESCE(movementCosts.total_cost, 0) as total_cost')
            )
            ->get();

        return $details
            ->groupBy('branch_id')
            ->map(function (Collection $items) {
                $first = $items->first();

                $transactionIds = $items
                    ->pluck('settlement_id')
                    ->unique()
                    ->values();

                $grossSales = (float) $items->sum('total_amount');
                $totalQty = (float) $items->sum('qty_sold');
                $cogs = (float) $items->sum('total_cost');

                $netSales = $grossSales;
                $grossProfit = $netSales - $cogs;

                return [
                    'branch_id' => $first->branch_id,
                    'branch_name' => $first->branch_name,

                    'source_type' => 'Consignment',

                    'cashier_id' => null,
                    'cashier_name' => 'Consignment (No Cashier)',

                    'total_transactions' => $transactionIds->count(),
                    'total_qty' => $totalQty,

                    'gross_sales' => $grossSales,
                    'discount' => 0,
                    'net_sales' => $netSales,

                    'cogs' => $cogs,
                    'gross_profit' => $grossProfit,

                    'margin' => $netSales > 0
                        ? ($grossProfit / $netSales) * 100
                        : 0,

                    'transaction_ids' => $transactionIds
                        ->map(fn ($id) => 'CONSIGNMENT-' . $id)
                        ->all(),
                ];
            })
            ->values();
    }

    /**
     * Calculate totals from rows.
     */
    private function calculateTotals(Collection $rows): array
    {
        $transactionIds = $rows
            ->flatMap(function ($row) {
                return $row['transaction_ids'] ?? [];
            })
            ->unique();

        $grossSales = (float) $rows->sum('gross_sales');
        $discount = (float) $rows->sum('discount');
        $netSales = (float) $rows->sum('net_sales');
        $cogs = (float) $rows->sum('cogs');
        $grossProfit = $netSales - $cogs;

        return [
            'total_transactions' => $transactionIds->count(),
            'total_qty' => (float) $rows->sum('total_qty'),

            'gross_sales' => $grossSales,
            'discount' => $discount,
            'net_sales' => $netSales,

            'cogs' => $cogs,
            'gross_profit' => $grossProfit,

            'margin' => $netSales > 0
                ? ($grossProfit / $netSales) * 100
                : 0,
        ];
    }

    /**
     * Generate Top 10 cashier chart.
     *
     * Cashier sales are combined across all branches.
     * Consignment remains a separate category.
     */
    private function generateChart(Collection $rows): array
    {
        $cashierRows = $rows
            ->where('source_type', 'POS')
            ->groupBy('cashier_id')
            ->map(function (Collection $items) {
                $totals = $this->calculateTotals($items);

                return [
                    'cashier_id' => $items->first()['cashier_id'],
                    'cashier_name' => $items->first()['cashier_name'],
                    'gross_sales' => $totals['gross_sales'],
                    'gross_profit' => $totals['gross_profit'],
                ];
            })
            ->sort(function ($a, $b) {
                $salesComparison =
                    $b['gross_sales'] <=> $a['gross_sales'];

                if ($salesComparison !== 0) {
                    return $salesComparison;
                }

                return $b['gross_profit'] <=> $a['gross_profit'];
            })
            ->take(10)
            ->values();

        $consignment = $rows->where(
            'source_type',
            'Consignment'
        );

        $chartRows = $cashierRows->map(function ($item) {
            return [
                'label' => $item['cashier_name'],
                'gross_sales' => $item['gross_sales'],
                'gross_profit' => $item['gross_profit'],
            ];
        });

        if ($consignment->isNotEmpty()) {
            $consignmentTotals = $this->calculateTotals(
                $consignment
            );

            $chartRows->push([
                'label' => 'Consignment',
                'gross_sales' => $consignmentTotals['gross_sales'],
                'gross_profit' => $consignmentTotals['gross_profit'],
            ]);
        }

        return [
            'labels' => $chartRows->pluck('label')->values()->all(),

            'gross_sales' => $chartRows
                ->pluck('gross_sales')
                ->values()
                ->all(),

            'gross_profit' => $chartRows
                ->pluck('gross_profit')
                ->values()
                ->all(),

            'data' => $chartRows->values()->all(),
        ];
    }

    /**
     * Generate comparison with previous period.
     */
    private function generateComparison(array $filters): array
    {
        $dateFrom = \Carbon\Carbon::parse(
            $filters['date_from']
        );

        $dateTo = \Carbon\Carbon::parse(
            $filters['date_to']
        );

        $periodDays = $dateFrom->diffInDays($dateTo) + 1;

        $previousDateTo = $dateFrom->copy()->subDay();

        $previousDateFrom = $previousDateTo
            ->copy()
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

        $currentRows = $this->generate(
            $filters,
            false,
            false
        );

        $current = $currentRows['summary'];
        $previous = $previousReport['summary'];

        $metrics = [];

        $keys = [
            'total_cashiers',
            'total_transactions',
            'total_items',
            'gross_sales',
            'discount',
            'net_sales',
            'cogs',
            'gross_profit',
        ];

        foreach ($keys as $key) {
            $metrics[$key] = $this->calculatePercentageChange(
                $current[$key] ?? 0,
                $previous[$key] ?? 0
            );
        }

        return [
            'period_days' => $periodDays,

            'period_label' => $periodDays . ' days',

            'current_period' => [
                'from' => $dateFrom->toDateString(),
                'to' => $dateTo->toDateString(),
            ],

            'previous_period' => [
                'from' => $previousDateFrom->toDateString(),
                'to' => $previousDateTo->toDateString(),
            ],

            'metrics' => $metrics,
        ];
    }

    /**
     * Calculate percentage change.
     */
    private function calculatePercentageChange(
        float|int $current,
        float|int $previous
    ): array {
        if ($current == 0 && $previous == 0) {
            return [
                'percentage' => null,
                'direction' => 'unchanged',
                'status' => 'no_data',
            ];
        }

        if ($previous == 0 && $current > 0) {
            return [
                'percentage' => null,
                'direction' => 'up',
                'status' => 'new',
            ];
        }

        $percentage = (
            ($current - $previous)
            / abs($previous)
        ) * 100;

        $percentage = round($percentage, 2);

        return [
            'percentage' => abs($percentage),

            'direction' => $percentage > 0
                ? 'up'
                : ($percentage < 0 ? 'down' : 'unchanged'),

            'status' => $percentage == 0
                ? 'unchanged'
                : 'changed',
        ];
    }
}