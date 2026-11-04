<?php

namespace App\Services\Report;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalesByProductService
{
    /**
     * Generate Sales by Product Report.
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

        /*
        |--------------------------------------------------------------------------
        | Product Filters
        |--------------------------------------------------------------------------
        */

        $productIds = collect(
            $filters['product_ids']
                ?? (
                    isset($filters['product_id'])
                        ? [$filters['product_id']]
                        : []
                )
        )
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $variantTotals = [];

        $transactionIds = [];

        $variantTransactionIds = [];

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
                $dateTo,
                $productIds
            );

            foreach ($posRows as $row) {

                $id = (int) $row->product_variant_id;

                $lineGross = (float) $row->line_gross;

                $itemDiscount = (float) $row->item_discount;

                $lineNet = (float) $row->line_net;

                $saleNetBeforeHeaderDiscount =
                    (float) $row->sale_net_before_header_discount;

                $headerDiscount =
                    (float) $row->header_discount;

                /*
                |--------------------------------------------------------------------------
                | Allocate Header Discount Proportionally
                |--------------------------------------------------------------------------
                */

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

                $this->addVariantTotal(
                    $variantTotals,
                    $id,
                    (int) $row->product_id,
                    $row->product_code,
                    $row->product_name,
                    $row->product_sku,
                    $row->variant_name,
                    $row->variant_sku,
                    (float) $row->qty,
                    $lineGross,
                    $totalDiscount,
                    $netSales,
                    (float) $row->total_cost
                );

                /*
                |--------------------------------------------------------------------------
                | Track Unique POS Transactions
                |--------------------------------------------------------------------------
                */

                $transactionKey = 'POS-' . $row->pos_sale_id;

                $transactionIds[$transactionKey] = true;

                $variantTransactionIds[$id][$transactionKey] = true;
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
                $dateTo,
                $productIds
            );

            foreach ($consignmentRows as $row) {

                $id = (int) $row->product_variant_id;

                $grossSales = (float) $row->total_amount;

                $qty = (float) $row->qty_sold;

                $cogs = (float) $row->total_cost;

                $this->addVariantTotal(
                    $variantTotals,
                    $id,
                    (int) $row->product_id,
                    $row->product_code,
                    $row->product_name,
                    $row->product_sku,
                    $row->variant_name,
                    $row->variant_sku,
                    $qty,
                    $grossSales,
                    0,
                    $grossSales,
                    $cogs
                );

                /*
                |--------------------------------------------------------------------------
                | Track Unique Consignment Transactions
                |--------------------------------------------------------------------------
                */

                $transactionKey =
                    'CONSIGNMENT-' . $row->settlement_header_id;

                $transactionIds[$transactionKey] = true;

                $variantTransactionIds[$id][$transactionKey] = true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Build Product Variant Collection
        |--------------------------------------------------------------------------
        */

        $productVariants = collect($variantTotals)

            ->map(function ($row) use ($variantTransactionIds) {

                $row['total_transactions'] = count(
                    $variantTransactionIds[
                        $row['product_variant_id']
                    ] ?? []
                );

                $row['gross_profit'] =
                    $row['net_sales'] -
                    $row['cogs'];

                $row['margin'] =
                    $row['net_sales'] > 0
                        ? (
                            $row['gross_profit'] /
                            $row['net_sales']
                        ) * 100
                        : 0;

                return $row;
            })

            ->sort(function ($a, $b) {

                /*
                |--------------------------------------------------------------------------
                | Primary: Total Qty DESC
                |--------------------------------------------------------------------------
                */

                $qtyComparison =
                    $b['total_qty'] <=> $a['total_qty'];

                if ($qtyComparison !== 0) {
                    return $qtyComparison;
                }

                /*
                |--------------------------------------------------------------------------
                | Secondary: Gross Sales DESC
                |--------------------------------------------------------------------------
                */

                $salesComparison =
                    $b['gross_sales'] <=> $a['gross_sales'];

                if ($salesComparison !== 0) {
                    return $salesComparison;
                }

                /*
                |--------------------------------------------------------------------------
                | Final: Product Name ASC
                |--------------------------------------------------------------------------
                */

                return strcasecmp(
                    $a['product_name'],
                    $b['product_name']
                );
            })

            ->values();

        /*
        |--------------------------------------------------------------------------
        | Overall Totals
        |--------------------------------------------------------------------------
        */

        $totals = [

            'total_products' =>
                $productVariants
                    ->pluck('product_id')
                    ->unique()
                    ->count(),

            'total_variants' =>
                $productVariants->count(),

            'total_transactions' =>
                count($transactionIds),

            'total_qty' =>
                $productVariants->sum('total_qty'),

            'gross_sales' =>
                round(
                    $productVariants->sum('gross_sales'),
                    2
                ),

            'discount' =>
                round(
                    $productVariants->sum('discount'),
                    2
                ),

            'net_sales' =>
                round(
                    $productVariants->sum('net_sales'),
                    2
                ),

            'cogs' =>
                round(
                    $productVariants->sum('cogs'),
                    2
                ),

            'gross_profit' =>
                round(
                    $productVariants->sum('gross_profit'),
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
        | Chart Data - Top 10 Products by Gross Sales
        |--------------------------------------------------------------------------
        |
        | Chart uses the complete collection before pagination.
        |
        */

        $chart = $productVariants

            ->sort(function ($a, $b) {

                $salesComparison =
                    $b['gross_sales'] <=> $a['gross_sales'];

                if ($salesComparison !== 0) {
                    return $salesComparison;
                }

                return $b['gross_profit'] <=> $a['gross_profit'];
            })

            ->take(10)

            ->map(fn ($row) => [

                'product_variant_id' =>
                    $row['product_variant_id'],

                'product_id' =>
                    $row['product_id'],

                'product_code' =>
                    $row['product_code'],

                'product_name' =>
                    $row['product_name'],

                'product_sku' =>
                    $row['product_sku'],

                'variant_name' =>
                    $row['variant_name'],

                'variant_sku' =>
                    $row['variant_sku'],

                'label' =>
                    trim(
                        $row['product_name'] .
                        ' ' .
                        $row['variant_name']
                    ),

                'total_transactions' =>
                    $row['total_transactions'],

                'total_qty' =>
                    (float) $row['total_qty'],

                'gross_sales' =>
                    (float) $row['gross_sales'],

                'gross_profit' =>
                    (float) $row['gross_profit'],

                'margin' =>
                    (float) $row['margin'],

            ])

            ->values();

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $resultVariants = $productVariants;

        if ($paginate) {

            $perPage = 25;

            $currentPage =
                LengthAwarePaginator::resolveCurrentPage();

            $resultVariants = new LengthAwarePaginator(

                $productVariants
                    ->forPage($currentPage, $perPage)
                    ->values(),

                $productVariants->count(),

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

            'total_products' =>
                $totals['total_products'],

            'total_variants' =>
                $totals['total_variants'],

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

            'product_variants' =>
                $resultVariants,

            'totals' =>
                $totals,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | POS Query
    |--------------------------------------------------------------------------
    */

    protected function getPosRows(
        int $companyId,
        array $branchIds,
        ?string $dateFrom,
        ?string $dateTo,
        array $productIds
    ) {

        /*
        |--------------------------------------------------------------------------
        | Calculate Total Line Net Per Sale
        |--------------------------------------------------------------------------
        |
        | Must include ALL products in the transaction.
        | Product filtering must not affect header discount allocation.
        |
        */

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
                'product_variants as variants',
                'variants.id',
                '=',
                'details.product_variant_id'
            )

            ->join(
                'products',
                'products.id',
                '=',
                'variants.product_id'
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

        /*
        |--------------------------------------------------------------------------
        | Product Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($productIds)) {

            $query->whereIn(
                'products.id',
                $productIds
            );
        }

        return $query

            ->select([

                'sales.id as pos_sale_id',

                'products.id as product_id',

                'products.code as product_code',

                'products.name as product_name',

                'products.sku as product_sku',

                'variants.id as product_variant_id',

                'variants.name as variant_name',

                'variants.sku as variant_sku',

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
    | Consignment Query
    |--------------------------------------------------------------------------
    */

    protected function getConsignmentRows(
        int $companyId,
        array $branchIds,
        ?string $dateFrom,
        ?string $dateTo,
        array $productIds
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

        $query = DB::table('settlement_details as details')

            ->join(
                'settlement_headers as settlements',
                'settlements.id',
                '=',
                'details.settlement_header_id'
            )

            ->join(
                'product_variants as variants',
                'variants.id',
                '=',
                'details.product_variant_id'
            )

            ->join(
                'products',
                'products.id',
                '=',
                'variants.product_id'
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

        /*
        |--------------------------------------------------------------------------
        | Product Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($productIds)) {

            $query->whereIn(
                'products.id',
                $productIds
            );
        }

        return $query

            ->select([

                'settlements.id as settlement_header_id',

                'products.id as product_id',

                'products.code as product_code',

                'products.name as product_name',

                'products.sku as product_sku',

                'variants.id as product_variant_id',

                'variants.name as variant_name',

                'variants.sku as variant_sku',

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

        $dateFrom = \Illuminate\Support\Carbon::parse(
            $filters['date_from']
        )->startOfDay();

        $dateTo = \Illuminate\Support\Carbon::parse(
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

            'total_products',

            'total_variants',

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

    /*
    |--------------------------------------------------------------------------
    | Add Product Variant Aggregate
    |--------------------------------------------------------------------------
    */

    protected function addVariantTotal(
        array &$totals,
        int $variantId,
        int $productId,
        ?string $productCode,
        ?string $productName,
        ?string $productSku,
        ?string $variantName,
        ?string $variantSku,
        float $qty,
        float $grossSales,
        float $discount,
        float $netSales,
        float $cogs
    ): void {

        if (!isset($totals[$variantId])) {

            $totals[$variantId] = [

                'product_variant_id' =>
                    $variantId,

                'product_id' =>
                    $productId,

                'product_code' =>
                    $productCode,

                'product_name' =>
                    $productName,

                'product_sku' =>
                    $productSku,

                'variant_name' =>
                    $variantName,

                'variant_sku' =>
                    $variantSku,

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

                'gross_profit' =>
                    0,

                'margin' =>
                    0,
            ];
        }

        $totals[$variantId]['total_qty'] += $qty;

        $totals[$variantId]['gross_sales'] += $grossSales;

        $totals[$variantId]['discount'] += $discount;

        $totals[$variantId]['net_sales'] += $netSales;

        $totals[$variantId]['cogs'] += $cogs;
    }
}