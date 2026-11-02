<?php

namespace App\Services\Report;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SalesSummaryService
{
    /**
     * Generate Sales Summary.
     */
    public function generate(array $filters): array
    {
        $companyId = (int) $filters['company_id'];

        $branchIds = $filters['branch_ids'];

        $dateFrom = $filters['date_from'];

        $dateTo = $filters['date_to'];

        /*
        |--------------------------------------------------------------------------
        | POS Sales Query
        |--------------------------------------------------------------------------
        */

        $posQuery = DB::table('pos_sales')
            ->where('company_id', $companyId)
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'posted')
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo);

        /*
        |--------------------------------------------------------------------------
        | POS Filters
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['cashier_id'])) {
            $posQuery->whereExists(function ($query) use ($filters) {
                $query->selectRaw('1')
                    ->from('cashier_sessions')
                    ->whereColumn(
                        'cashier_sessions.id',
                        'pos_sales.cashier_session_id'
                    )
                    ->where(
                        'cashier_sessions.user_id',
                        $filters['cashier_id']
                    );
            });
        }

        if (!empty($filters['customer_id'])) {
            $posQuery->where(
                'customer_id',
                $filters['customer_id']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | POS Header Statistics
        |--------------------------------------------------------------------------
        */

        $posStatistics = (clone $posQuery)
            ->selectRaw('
                COUNT(*) as total_transactions,
                COALESCE(SUM(grand_total), 0) as total_revenue,
                COALESCE(SUM(discount_amount), 0) as header_discount
            ')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | POS Details Statistics
        |--------------------------------------------------------------------------
        */

        $posSaleIds = (clone $posQuery)
            ->select('id');

        $posDetails = DB::table('pos_sale_details')
            ->whereIn('pos_sale_id', $posSaleIds)
            ->selectRaw('
                COALESCE(SUM(qty), 0) as total_items,
                COALESCE(SUM(discount_amount), 0) as detail_discount,
                COALESCE(SUM(total_cost), 0) as total_cogs
            ')
            ->first();

        $posRevenue = (float) $posStatistics->total_revenue;

        $posTransactions = (int) $posStatistics->total_transactions;

        $posItems = (float) $posDetails->total_items;

        $posDiscount =
            (float) $posStatistics->header_discount
            +
            (float) $posDetails->detail_discount;

        $posCogs = (float) $posDetails->total_cogs;

        /*
        |--------------------------------------------------------------------------
        | Consignment Settlement Query
        |--------------------------------------------------------------------------
        */

        $settlementQuery = DB::table('settlement_headers')
            ->where('company_id', $companyId)
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'Posted')
            ->whereDate('settlement_date', '>=', $dateFrom)
            ->whereDate('settlement_date', '<=', $dateTo);

        /*
        |--------------------------------------------------------------------------
        | Consignment Header Statistics
        |--------------------------------------------------------------------------
        */

        $consignmentTransactions = (int) (clone $settlementQuery)
            ->count();

        $consignmentRevenue = (float) (clone $settlementQuery)
            ->sum('grand_total');

        $settlementIds = (clone $settlementQuery)
            ->select('id');

        /*
        |--------------------------------------------------------------------------
        | Consignment Details
        |--------------------------------------------------------------------------
        */

        $settlementDetails = DB::table('settlement_details')
            ->whereIn(
                'settlement_header_id',
                $settlementIds
            )
            ->selectRaw('
                COALESCE(SUM(qty_sold), 0) as total_items
            ')
            ->first();

        $consignmentItems = (float) $settlementDetails->total_items;

        /*
        |--------------------------------------------------------------------------
        | Consignment COGS
        |--------------------------------------------------------------------------
        |
        | HPP comes from original Inventory Movement.
        | Do not recalculate using current ProductStock.
        |
        */

        $consignmentCogs = DB::table('inventory_movements')
            ->where('company_id', $companyId)
            ->whereIn('branch_id', $branchIds)
            ->where('reference_type', 'SETTLEMENT')
            ->where('qty_out', '>', 0)
            ->whereIn('reference_id', $settlementIds)
            ->sum('total_cost');

        $consignmentCogs = (float) $consignmentCogs;

        /*
        |--------------------------------------------------------------------------
        | Combined Statistics
        |--------------------------------------------------------------------------
        */

        $totalRevenue =
            $posRevenue +
            $consignmentRevenue;

        $totalTransactions =
            $posTransactions +
            $consignmentTransactions;

        $totalItems =
            $posItems +
            $consignmentItems;

        $totalCogs =
            $posCogs +
            $consignmentCogs;

        $totalDiscount = $posDiscount;

        $grossProfit =
            $totalRevenue -
            $totalCogs;

        $grossProfitMargin = $totalRevenue > 0
            ? ($grossProfit / $totalRevenue) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Daily Sales Trend
        |--------------------------------------------------------------------------
        */

        $posTrend = DB::table('pos_sales')
            ->where('company_id', $companyId)
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'posted')
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->when(!empty($filters['cashier_id']), function ($query) use ($filters) {
                $query->whereExists(function ($subQuery) use ($filters) {
                    $subQuery->selectRaw('1')
                        ->from('cashier_sessions')
                        ->whereColumn(
                            'cashier_sessions.id',
                            'pos_sales.cashier_session_id'
                        )
                        ->where(
                            'cashier_sessions.user_id',
                            $filters['cashier_id']
                        );
                });
            })
            ->when(!empty($filters['customer_id']), function ($query) use ($filters) {
                $query->where(
                    'customer_id',
                    $filters['customer_id']
                );
            })
            ->selectRaw("
                DATE(sale_date) as date,
                SUM(grand_total) as revenue
            ")
            ->groupByRaw('DATE(sale_date)')
            ->get();

        $consignmentTrend = DB::table('settlement_headers')
            ->where('company_id', $companyId)
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'Posted')
            ->whereDate('settlement_date', '>=', $dateFrom)
            ->whereDate('settlement_date', '<=', $dateTo)
            ->selectRaw("
                DATE(settlement_date) as date,
                SUM(grand_total) as revenue
            ")
            ->groupByRaw('DATE(settlement_date)')
            ->get();

        $trendMap = [];

        foreach ($posTrend as $row) {
            $trendMap[$row->date] = [
                'date' => $row->date,
                'pos' => (float) $row->revenue,
                'consignment' => 0,
            ];
        }

        foreach ($consignmentTrend as $row) {
            if (!isset($trendMap[$row->date])) {
                $trendMap[$row->date] = [
                    'date' => $row->date,
                    'pos' => 0,
                    'consignment' => 0,
                ];
            }

            $trendMap[$row->date]['consignment'] =
                (float) $row->revenue;
        }

        /*
        |--------------------------------------------------------------------------
        | Fill Missing Dates
        |--------------------------------------------------------------------------
        */

        $trend = [];

        $period = Carbon::parse($dateFrom)->startOfDay();

        $endDate = Carbon::parse($dateTo)->startOfDay();

        while ($period->lte($endDate)) {
            $date = $period->toDateString();

            $pos = $trendMap[$date]['pos'] ?? 0;

            $consignment =
                $trendMap[$date]['consignment'] ?? 0;

            $trend[] = [
                'date' => $date,
                'pos' => $pos,
                'consignment' => $consignment,
                'revenue' => $pos + $consignment,
            ];

            $period->addDay();
        }

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],

            'summary' => [
                'total_revenue' => round($totalRevenue, 2),
                'total_transactions' => $totalTransactions,
                'total_items' => round($totalItems, 2),
                'total_discount' => round($totalDiscount, 2),
                'total_cogs' => round($totalCogs, 2),
                'gross_profit' => round($grossProfit, 2),
                'gross_profit_margin' => round(
                    $grossProfitMargin,
                    2
                ),
            ],

            'sources' => [
                'pos' => [
                    'transactions' => $posTransactions,
                    'revenue' => round($posRevenue, 2),
                    'items' => round($posItems, 2),
                    'discount' => round($posDiscount, 2),
                    'cogs' => round($posCogs, 2),
                ],

                'consignment' => [
                    'transactions' => $consignmentTransactions,
                    'revenue' => round($consignmentRevenue, 2),
                    'items' => round($consignmentItems, 2),
                    'cogs' => round($consignmentCogs, 2),
                ],
            ],

            'sales_trend' => $trend,
        ];
    }
}