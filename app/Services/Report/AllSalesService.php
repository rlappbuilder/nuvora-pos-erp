<?php

namespace App\Services\Report;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AllSalesService
{
    /**
     * Generate All Sales Report.
     *
     * $paginate = true:
     * Results page, 25 transactions per page.
     *
     * $paginate = false:
     * Return transaction query for streaming.
     */
    public function generate(
        array $filters,
        bool $paginate = true
    ): array {

        $companyId = (int) $filters['company_id'];

        $branchIds = array_map(
            'intval',
            $filters['branch_ids']
        );

        $dateFrom = $filters['date_from'];
        $dateTo = $filters['date_to'];

        $source = $filters['source'] ?? 'all';

        /*
        |--------------------------------------------------------------------------
        | Build POS Query
        |--------------------------------------------------------------------------
        */

        $posQuery = $this->buildPosQuery(
            $companyId,
            $branchIds,
            $dateFrom,
            $dateTo,
            $filters,
            $source
        );

        /*
        |--------------------------------------------------------------------------
        | Build Consignment Query
        |--------------------------------------------------------------------------
        */

        $consignmentQuery = $this->buildConsignmentQuery(
            $companyId,
            $branchIds,
            $dateFrom,
            $dateTo,
            $filters,
            $source
        );

        /*
        |--------------------------------------------------------------------------
        | Transaction Queries
        |--------------------------------------------------------------------------
        */

        $posRows = $this->buildPosRows($posQuery);

        $consignmentRows = $this->buildConsignmentRows(
            $consignmentQuery
        );

        $transactions = $posRows->unionAll($consignmentRows);

        $transactionQuery = DB::query()
            ->fromSub($transactions, 'transactions')
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_id');

        /*
        |--------------------------------------------------------------------------
        | Transaction Result
        |--------------------------------------------------------------------------
        */

        $transactionData = $paginate
            ? $transactionQuery->paginate(25)->withQueryString()
            : $transactionQuery;

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $posSummary = $this->getPosSummary($posQuery);

        $consignmentSummary = $this->getConsignmentSummary(
            $consignmentQuery
        );

        $totalTransactions =
            $posSummary['transactions'] +
            $consignmentSummary['transactions'];

        $totalItems =
            $posSummary['items'] +
            $consignmentSummary['items'];

        $totalSales =
            $posSummary['sales'] +
            $consignmentSummary['sales'];

        $totalDiscount = $posSummary['discount'];

        /*
        |--------------------------------------------------------------------------
        | Daily Sales Trend
        |--------------------------------------------------------------------------
        */

        $trend = $this->getSalesTrend(
            $posQuery,
            $consignmentQuery,
            $dateFrom,
            $dateTo
        );

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [

            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'source' => $source,
                'branch_ids' => $branchIds,
                'cashier_id' => $filters['cashier_id'] ?? null,
                'customer_id' => $filters['customer_id'] ?? null,
                'reseller_id' => $filters['reseller_id'] ?? null,
                'payment_method' => $filters['payment_method'] ?? null,
            ],

            'summary' => [
                'total_transactions' => $totalTransactions,
                'total_items' => round($totalItems, 2),
                'total_sales' => round($totalSales, 2),
                'total_discount' => round($totalDiscount, 2),
            ],

            'sources' => [

                'pos' => [
                    'transactions' => $posSummary['transactions'],
                    'sales' => round($posSummary['sales'], 2),
                    'items' => round($posSummary['items'], 2),
                    'discount' => round($posSummary['discount'], 2),
                ],

                'consignment' => [
                    'transactions' => $consignmentSummary['transactions'],
                    'sales' => round($consignmentSummary['sales'], 2),
                    'items' => round($consignmentSummary['items'], 2),
                    'discount' => 0,
                ],

            ],

            'sales_trend' => $trend,

            'transactions' => $transactionData,

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Build POS Query
    |--------------------------------------------------------------------------
    */

    private function buildPosQuery(
        int $companyId,
        array $branchIds,
        string $dateFrom,
        string $dateTo,
        array $filters,
        string $source
    ) {

        $query = DB::table('pos_sales')
            ->leftJoin(
                'cashier_sessions',
                'cashier_sessions.id',
                '=',
                'pos_sales.cashier_session_id'
            )
            ->leftJoin(
                'users',
                'users.id',
                '=',
                'cashier_sessions.user_id'
            )
            ->leftJoin(
                'customers',
                'customers.id',
                '=',
                'pos_sales.customer_id'
            )
            ->join(
                'branches',
                'branches.id',
                '=',
                'pos_sales.branch_id'
            )
            ->where('pos_sales.company_id', $companyId)
            ->whereIn('pos_sales.branch_id', $branchIds)
            ->where('pos_sales.status', 'posted')
            ->whereDate('pos_sales.sale_date', '>=', $dateFrom)
            ->whereDate('pos_sales.sale_date', '<=', $dateTo);

        if ($source === 'consignment') {
            $query->whereRaw('1 = 0');
        }

        if (
            in_array($source, ['all', 'pos'], true) &&
            !empty($filters['cashier_id'])
        ) {
            $query->where(
                'cashier_sessions.user_id',
                $filters['cashier_id']
            );
        }

        if (
            in_array($source, ['all', 'pos'], true) &&
            !empty($filters['customer_id'])
        ) {
            $query->where(
                'pos_sales.customer_id',
                $filters['customer_id']
            );
        }

        if (
            in_array($source, ['all', 'pos'], true) &&
            !empty($filters['payment_method'])
        ) {
            $query->whereExists(function ($subQuery) use ($filters) {

                $subQuery->selectRaw('1')
                    ->from('pos_sale_payments')
                    ->whereColumn(
                        'pos_sale_payments.pos_sale_id',
                        'pos_sales.id'
                    )
                    ->where(
                        'pos_sale_payments.payment_method',
                        $filters['payment_method']
                    );
            });
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Build Consignment Query
    |--------------------------------------------------------------------------
    */

    private function buildConsignmentQuery(
        int $companyId,
        array $branchIds,
        string $dateFrom,
        string $dateTo,
        array $filters,
        string $source
    ) {

        $query = DB::table('settlement_headers')
            ->leftJoin(
                'resellers',
                'resellers.id',
                '=',
                'settlement_headers.reseller_id'
            )
            ->join(
                'branches',
                'branches.id',
                '=',
                'settlement_headers.branch_id'
            )
            ->where('settlement_headers.company_id', $companyId)
            ->whereIn('settlement_headers.branch_id', $branchIds)
            ->where('settlement_headers.status', 'Posted')
            ->whereDate(
                'settlement_headers.settlement_date',
                '>=',
                $dateFrom
            )
            ->whereDate(
                'settlement_headers.settlement_date',
                '<=',
                $dateTo
            );

        if ($source === 'pos') {
            $query->whereRaw('1 = 0');
        }

        if (
            in_array($source, ['all', 'consignment'], true) &&
            !empty($filters['reseller_id'])
        ) {
            $query->where(
                'settlement_headers.reseller_id',
                $filters['reseller_id']
            );
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Build POS Rows
    |--------------------------------------------------------------------------
    */

    private function buildPosRows($query)
    {
        return (clone $query)->selectRaw("
            pos_sales.id as transaction_id,
            pos_sales.sale_date as transaction_date,
            pos_sales.sale_number as transaction_number,
            'POS' as source,
            COALESCE(users.name, '-') as cashier_name,
            branches.id as branch_id,
            branches.name as branch_name,
            COALESCE(customers.name, '-') as customer_name,
            'Customer' as partner_type,

            (
                SELECT COUNT(DISTINCT product_variant_id)
                FROM pos_sale_details
                WHERE pos_sale_details.pos_sale_id = pos_sales.id
            ) as total_items,

            (
                SELECT COALESCE(SUM(qty), 0)
                FROM pos_sale_details
                WHERE pos_sale_details.pos_sale_id = pos_sales.id
            ) as total_qty,

            (
                pos_sales.discount_amount +
                COALESCE((
                    SELECT SUM(discount_amount)
                    FROM pos_sale_details
                    WHERE pos_sale_details.pos_sale_id = pos_sales.id
                ), 0)
            ) as discount,

            pos_sales.grand_total as grand_total
        ");
    }

    /*
    |--------------------------------------------------------------------------
    | Build Consignment Rows
    |--------------------------------------------------------------------------
    */

    private function buildConsignmentRows($query)
    {
        return (clone $query)->selectRaw("
            settlement_headers.id as transaction_id,
            settlement_headers.settlement_date as transaction_date,
            settlement_headers.settlement_number as transaction_number,
            'Consignment' as source,
            '-' as cashier_name,
            branches.id as branch_id,
            branches.name as branch_name,
            COALESCE(resellers.name, '-') as customer_name,
            'Reseller' as partner_type,

            (
                SELECT COUNT(DISTINCT product_variant_id)
                FROM settlement_details
                WHERE settlement_details.settlement_header_id = settlement_headers.id
            ) as total_items,

            (
                SELECT COALESCE(SUM(qty_sold), 0)
                FROM settlement_details
                WHERE settlement_details.settlement_header_id = settlement_headers.id
            ) as total_qty,

            0 as discount,

            settlement_headers.grand_total as grand_total
        ");
    }

    /*
    |--------------------------------------------------------------------------
    | POS Summary
    |--------------------------------------------------------------------------
    */

    private function getPosSummary($query): array
    {
        $summary = (clone $query)
            ->selectRaw("
                COUNT(*) as total_transactions,
                COALESCE(SUM(pos_sales.grand_total), 0) as total_sales,
                COALESCE(SUM(pos_sales.discount_amount), 0) as header_discount
            ")
            ->first();

        $ids = (clone $query)->select('pos_sales.id');

        $details = DB::table('pos_sale_details')
            ->whereIn('pos_sale_id', $ids)
            ->selectRaw("
                COALESCE(SUM(qty), 0) as total_qty,
                COALESCE(SUM(discount_amount), 0) as detail_discount
            ")
            ->first();

        return [
            'transactions' => (int) $summary->total_transactions,
            'sales' => (float) $summary->total_sales,
            'items' => (float) $details->total_qty,
            'discount' =>
                (float) $summary->header_discount +
                (float) $details->detail_discount,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Consignment Summary
    |--------------------------------------------------------------------------
    */

    private function getConsignmentSummary($query): array
    {
        $summary = (clone $query)
            ->selectRaw("
                COUNT(*) as total_transactions,
                COALESCE(SUM(settlement_headers.grand_total), 0) as total_sales
            ")
            ->first();

        $ids = (clone $query)
            ->select('settlement_headers.id');

        $details = DB::table('settlement_details')
            ->whereIn('settlement_header_id', $ids)
            ->selectRaw("
                COALESCE(SUM(qty_sold), 0) as total_qty
            ")
            ->first();

        return [
            'transactions' => (int) $summary->total_transactions,
            'sales' => (float) $summary->total_sales,
            'items' => (float) $details->total_qty,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Sales Trend
    |--------------------------------------------------------------------------
    */

    private function getSalesTrend(
        $posQuery,
        $consignmentQuery,
        string $dateFrom,
        string $dateTo
    ): array {

        $posTrend = (clone $posQuery)
            ->selectRaw("
                DATE(pos_sales.sale_date) as date,
                COALESCE(SUM(pos_sales.grand_total), 0) as revenue
            ")
            ->groupByRaw('DATE(pos_sales.sale_date)')
            ->get();

        $consignmentTrend = (clone $consignmentQuery)
            ->selectRaw("
                DATE(settlement_headers.settlement_date) as date,
                COALESCE(SUM(settlement_headers.grand_total), 0) as revenue
            ")
            ->groupByRaw('DATE(settlement_headers.settlement_date)')
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
                'total' => $pos + $consignment,
            ];

            $period->addDay();
        }

        return $trend;
    }
}