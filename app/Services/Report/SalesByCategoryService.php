<?php

namespace App\Services\Report;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalesByCategoryService
{
    /**
     * Generate Sales by Category Report.
     */
    public function generate(
        array $filters,
        bool $paginate = true
    ): array {

        $companyId = (int) $filters['company_id'];

        $branchIds = $filters['branch_ids'] ?? [];

        $dateFrom = $filters['date_from'] ?? null;

        $dateTo = $filters['date_to'] ?? null;

        $source = $filters['source'] ?? 'all';

        /*
        |--------------------------------------------------------------------------
        | Category Filters
        |--------------------------------------------------------------------------
        */

        $categoryIds = collect(
            $filters['category_ids']
                ?? (
                    isset($filters['category_id'])
                        ? [$filters['category_id']]
                        : []
                )
        )
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $categoryTotals = [];

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
                $dateTo,
                $categoryIds
            );

            foreach ($posRows as $row) {

                $id = $row->category_id ?? 0;

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

                $this->addCategoryTotal(
                    $categoryTotals,
                    $id,
                    $row->category_name,
                    (float) $row->qty,
                    $lineGross,
                    $totalDiscount,
                    $netSales,
                    (float) $row->total_cost
                );

                $transactionIds[
                    'POS-' . $row->pos_sale_id
                ] = true;
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
                $categoryIds
            );

            foreach ($consignmentRows as $row) {

                $id = $row->category_id ?? 0;

                $grossSales = (float) $row->total_amount;

                $qty = (float) $row->qty_sold;

                $cogs = (float) $row->total_cost;

                $this->addCategoryTotal(
                    $categoryTotals,
                    $id,
                    $row->category_name,
                    $qty,
                    $grossSales,
                    0,
                    $grossSales,
                    $cogs
                );

                $transactionIds[
                    'CONSIGNMENT-' . $row->settlement_header_id
                ] = true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Build Category Collection
        |--------------------------------------------------------------------------
        */

        $categories = collect($categoryTotals)
            ->map(function ($row) {

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
            ->sortBy('category_name')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Overall Totals
        |--------------------------------------------------------------------------
        */

        $totals = [

            'total_categories' =>
                $categories->count(),

            'total_transactions' =>
                count($transactionIds),

            'total_qty' =>
                $categories->sum('total_qty'),

            'gross_sales' =>
                round(
                    $categories->sum('gross_sales'),
                    2
                ),

            'discount' =>
                round(
                    $categories->sum('discount'),
                    2
                ),

            'net_sales' =>
                round(
                    $categories->sum('net_sales'),
                    2
                ),

            'cogs' =>
                round(
                    $categories->sum('cogs'),
                    2
                ),

            'gross_profit' =>
                round(
                    $categories->sum('gross_profit'),
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
        | Pagination
        |--------------------------------------------------------------------------
        */

        $resultCategories = $categories;

        if ($paginate) {

            $perPage = 25;

            $currentPage =
                LengthAwarePaginator::resolveCurrentPage();

            $resultCategories = new LengthAwarePaginator(

                $categories
                    ->forPage($currentPage, $perPage)
                    ->values(),

                $categories->count(),

                $perPage,

                $currentPage,

                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),

                    'query' => request()->query(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Return Report
        |--------------------------------------------------------------------------
        */

        return [

            'filters' => $filters,

            'summary' => [

                'total_categories' =>
                    $totals['total_categories'],

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
            ],

            'categories' =>
                $resultCategories,

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
        array $categoryIds
    ) {

        /*
        |--------------------------------------------------------------------------
        | Calculate Total Line Net Per Sale
        |--------------------------------------------------------------------------
        |
        | Must include ALL categories in the transaction.
        | Category filtering must not affect header discount allocation.
        |
        | GREATEST(0, ...) follows PosTransactionService:
        | lineSubtotal = max(0, qty * unitPrice - discount).
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

            ->leftJoin(
                'categories',
                'categories.id',
                '=',
                'products.category_id'
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
        | Multiple Category Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($categoryIds)) {

            $query->whereIn(
                'products.category_id',
                $categoryIds
            );
        }

        return $query

            ->select([

                'sales.id as pos_sale_id',

                'products.category_id',

                'categories.name as category_name',

                'details.qty',

                'details.unit_price',

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
        array $categoryIds
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

            ->leftJoin(
                'categories',
                'categories.id',
                '=',
                'products.category_id'
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
        | Multiple Category Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($categoryIds)) {

            $query->whereIn(
                'products.category_id',
                $categoryIds
            );
        }

        return $query

            ->select([

                'settlements.id as settlement_header_id',

                'products.category_id',

                'categories.name as category_name',

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
    | Add Category Aggregate
    |--------------------------------------------------------------------------
    */

    protected function addCategoryTotal(
        array &$totals,
        int $categoryId,
        ?string $categoryName,
        float $qty,
        float $grossSales,
        float $discount,
        float $netSales,
        float $cogs
    ): void {

        if (!isset($totals[$categoryId])) {

            $totals[$categoryId] = [

                'category_id' =>
                    $categoryId ?: null,

                'category_name' =>
                    $categoryName ?: 'Uncategorized',

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

        $totals[$categoryId]['total_qty'] += $qty;

        $totals[$categoryId]['gross_sales'] += $grossSales;

        $totals[$categoryId]['discount'] += $discount;

        $totals[$categoryId]['net_sales'] += $netSales;

        $totals[$categoryId]['cogs'] += $cogs;
    }
}