<?php

namespace App\Services\Report;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class SalesByPaymentMethodService
{
    /**
     * Generate Sales by Payment Method report.
     */
    public function generate(
        array $filters,
        bool $paginate = true,
        bool $includeComparison = true
    ): array {
        $companyId = (int) $filters['company_id'];

        $branchIds = $filters['branch_ids'] ?? [];
        $paymentMethods = $filters['payment_methods'] ?? [];

        $source = $filters['source'] ?? 'all';

        $dateFrom = $filters['date_from'];
        $dateTo = $filters['date_to'];

        /*
        |--------------------------------------------------------------------------
        | PAYMENT ROWS
        |--------------------------------------------------------------------------
        */

        $rows = collect();

        /*
        |--------------------------------------------------------------------------
        | POS PAYMENTS
        |--------------------------------------------------------------------------
        */

        if (in_array($source, ['all', 'pos'], true)) {
            $posRows = $this->getPosPaymentRows(
                $companyId,
                $branchIds,
                $paymentMethods,
                $dateFrom,
                $dateTo
            );

            $rows = $rows->merge($posRows);
        }

        /*
        |--------------------------------------------------------------------------
        | CONSIGNMENT PAYMENTS
        |--------------------------------------------------------------------------
        */

        if (in_array($source, ['all', 'consignment'], true)) {
            $consignmentRows = $this->getConsignmentPaymentRows(
                $companyId,
                $branchIds,
                $paymentMethods,
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
                $methodComparison = strcasecmp(
                    $a['payment_method_label'],
                    $b['payment_method_label']
                );

                if ($methodComparison !== 0) {
                    return $methodComparison;
                }

                if ($a['source_type'] !== $b['source_type']) {
                    return $a['source_type'] === 'POS' ? -1 : 1;
                }

                return 0;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | PAYMENT TOTALS
        |--------------------------------------------------------------------------
        */

        $paymentAmount = (float) $rows->sum('payment_amount');

        $posPaymentAmount = (float) $rows
            ->where('source_type', 'POS')
            ->sum('payment_amount');

        $consignmentPaymentAmount = (float) $rows
            ->where('source_type', 'Consignment')
            ->sum('payment_amount');

        /*
        |--------------------------------------------------------------------------
        | UNIQUE TRANSACTION COUNTS
        |--------------------------------------------------------------------------
        |
        | POS:
        | One POS sale can have multiple payment methods.
        | Count unique POS sale IDs globally.
        |
        | Consignment:
        | One receivable payment header can allocate payments
        | to multiple settlement headers.
        | Count unique payment headers globally.
        |
        */

        $posCounts = [
            'payment_transactions' => 0,
            'sales_transactions' => 0,
        ];

        $consignmentCounts = [
            'payment_transactions' => 0,
            'sales_transactions' => 0,
        ];

        if (in_array($source, ['all', 'pos'], true)) {
            $posCounts = $this->getPosTransactionCounts(
                $companyId,
                $branchIds,
                $paymentMethods,
                $dateFrom,
                $dateTo
            );
        }

        if (in_array($source, ['all', 'consignment'], true)) {
            $consignmentCounts = $this->getConsignmentTransactionCounts(
                $companyId,
                $branchIds,
                $paymentMethods,
                $dateFrom,
                $dateTo
            );
        }

        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL
        |--------------------------------------------------------------------------
        */

        $paymentTransactions =
            $posCounts['payment_transactions']
            + $consignmentCounts['payment_transactions'];

        $consignmentSalesTransactions = 0;

            if (in_array($source, ['all', 'consignment'], true)) {
                $consignmentSalesTransactions = $this->getUniqueConsignmentSalesTransactions(
                    $companyId,
                    $branchIds,
                    $paymentMethods,
                    $dateFrom,
                    $dateTo
                );
            }

            $salesTransactions =
                $posCounts['sales_transactions']
                + $consignmentSalesTransactions;

        $totals = [
            'payment_transactions' => $paymentTransactions,
            'sales_transactions' => $salesTransactions,

            'payment_amount' => $paymentAmount,

            'pos_payment_amount' => $posPaymentAmount,

            'consignment_payment_amount' => $consignmentPaymentAmount,

            'percentage' => $paymentAmount > 0
                ? 100
                : 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | OUTSTANDING RECEIVABLES
        |--------------------------------------------------------------------------
        |
        | This is the current outstanding balance of posted
        | settlements for the selected company and branches.
        |
        | It is intentionally independent of the payment date range
        | and payment method filter.
        |
        */

        $outstandingReceivables = 0;

        if (in_array($source, ['all', 'consignment'], true)) {
            $outstandingReceivables = $this->getOutstandingReceivables(
                $companyId,
                $branchIds
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total_payment_methods' => $rows
                ->pluck('payment_method')
                ->unique()
                ->count(),

            'payment_transactions' => $paymentTransactions,

            'sales_transactions' => $salesTransactions,

            'total_payment_amount' => $paymentAmount,

            'pos_payment_amount' => $posPaymentAmount,

            'consignment_payment_amount' => $consignmentPaymentAmount,

            'outstanding_receivables' => $outstandingReceivables,

            'average_payment_amount' => $paymentTransactions > 0
                ? $paymentAmount / $paymentTransactions
                : 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | ROW PERCENTAGES
        |--------------------------------------------------------------------------
        */

        $rows = $rows
            ->map(function ($row) use ($paymentAmount) {
                $row['percentage'] = $paymentAmount > 0
                    ? (
                        (float) $row['payment_amount']
                        / $paymentAmount
                    ) * 100
                    : 0;

                return $row;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | CHART
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

            $paginatedRows = new LengthAwarePaginator(
                $rows->forPage($currentPage, $perPage)->values(),
                $rows->count(),
                $perPage,
                $currentPage,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => request()->query(),
                ]
            );
        } else {
            $paginatedRows = $rows;
        }

        return [
            'filters' => $filters,

            'summary' => $summary,

            'comparison' => $comparison,

            'chart' => $chart,

            'payment_rows' => $paginatedRows,

            'totals' => $totals,
        ];
    }

    /**
     * Retrieve POS payment rows grouped by payment method.
     */
    private function getPosPaymentRows(
        int $companyId,
        array $branchIds,
        array $paymentMethods,
        string $dateFrom,
        string $dateTo
    ): Collection {
        $query = $this->buildPosPaymentQuery(
            $companyId,
            $branchIds,
            $paymentMethods,
            $dateFrom,
            $dateTo
        );

        $rows = $query
            ->select(
                'payments.payment_method',

                DB::raw(
                    'COUNT(DISTINCT sales.id) AS payment_transactions'
                ),

                DB::raw(
                    'COUNT(DISTINCT sales.id) AS sales_transactions'
                ),

                DB::raw(
                    'SUM(payments.amount) AS payment_amount'
                )
            )
            ->groupBy('payments.payment_method')
            ->get();

        return $rows->map(function ($item) {
            $method = (string) $item->payment_method;

            return [
                'payment_method' => $method,

                'payment_method_label' => $this->formatPaymentMethod(
                    $method
                ),

                'source_type' => 'POS',

                'payment_transactions' => (int) $item->payment_transactions,

                'sales_transactions' => (int) $item->sales_transactions,

                'payment_amount' => (float) $item->payment_amount,

                'percentage' => 0,
            ];
        })->values();
    }

    /**
     * Build the base POS payment query.
     */
    private function buildPosPaymentQuery(
        int $companyId,
        array $branchIds,
        array $paymentMethods,
        string $dateFrom,
        string $dateTo
    ) {
        $query = DB::table('pos_sale_payments as payments')
            ->join(
                'pos_sales as sales',
                'sales.id',
                '=',
                'payments.pos_sale_id'
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

        if (!empty($paymentMethods)) {
            $query->whereIn(
                'payments.payment_method',
                $paymentMethods
            );
        }

        return $query;
    }

    /**
     * Retrieve Consignment payment rows grouped by payment method.
     */
    private function getConsignmentPaymentRows(
        int $companyId,
        array $branchIds,
        array $paymentMethods,
        string $dateFrom,
        string $dateTo
    ): Collection {
        $query = $this->buildConsignmentPaymentQuery(
            $companyId,
            $branchIds,
            $paymentMethods,
            $dateFrom,
            $dateTo
        );

        $rows = $query
            ->select(
                'payments.payment_method',

                DB::raw(
                    'COUNT(DISTINCT payments.id) AS payment_transactions'
                ),

                DB::raw(
                    'COUNT(DISTINCT details.settlement_header_id) AS sales_transactions'
                ),

                DB::raw(
                    'SUM(details.payment_amount) AS payment_amount'
                )
            )
            ->groupBy('payments.payment_method')
            ->get();

        return $rows->map(function ($item) {
            $method = (string) $item->payment_method;

            return [
                'payment_method' => $method,

                'payment_method_label' => $this->formatPaymentMethod(
                    $method
                ),

                'source_type' => 'Consignment',

                'payment_transactions' => (int) $item->payment_transactions,

                'sales_transactions' => (int) $item->sales_transactions,

                'payment_amount' => (float) $item->payment_amount,

                'percentage' => 0,
            ];
        })->values();
    }

    /**
     * Build the base Consignment payment query.
     */
    private function buildConsignmentPaymentQuery(
        int $companyId,
        array $branchIds,
        array $paymentMethods,
        string $dateFrom,
        string $dateTo
    ) {
        $query = DB::table(
            'consignment_receivable_headers as payments'
        )
            ->join(
                'consignment_receivable_details as details',
                'details.consignment_receivable_header_id',
                '=',
                'payments.id'
            )
            ->join(
                'settlement_headers as settlements',
                'settlements.id',
                '=',
                'details.settlement_header_id'
            )
            ->where('payments.company_id', $companyId)
            ->where('payments.status', 'Posted')
            ->whereNull('payments.deleted_at')
            ->where('settlements.company_id', $companyId)
            ->where('settlements.status', 'Posted')
            ->whereNull('settlements.deleted_at')
            ->whereBetween(
                'payments.payment_date',
                [$dateFrom, $dateTo]
            );

        if (!empty($branchIds)) {
            $query->whereIn('payments.branch_id', $branchIds);
        }

        if (!empty($paymentMethods)) {
            $query->whereIn(
                'payments.payment_method',
                $paymentMethods
            );
        }

        return $query;
    }

    /**
     * Retrieve unique POS and Consignment transaction counts.
     */
    private function getPosTransactionCounts(
        int $companyId,
        array $branchIds,
        array $paymentMethods,
        string $dateFrom,
        string $dateTo
    ): array {
        $query = $this->buildPosPaymentQuery(
            $companyId,
            $branchIds,
            $paymentMethods,
            $dateFrom,
            $dateTo
        );

        $salesTransactions = (int) (clone $query)
            ->distinct()
            ->count('sales.id');

        return [
            'payment_transactions' => $salesTransactions,

            'sales_transactions' => $salesTransactions,
        ];
    }

    /**
     * Retrieve unique Consignment payment and settlement counts.
     */
    private function getConsignmentTransactionCounts(
        int $companyId,
        array $branchIds,
        array $paymentMethods,
        string $dateFrom,
        string $dateTo
    ): array {
        $query = $this->buildConsignmentPaymentQuery(
            $companyId,
            $branchIds,
            $paymentMethods,
            $dateFrom,
            $dateTo
        );

        $paymentTransactions = (int) (clone $query)
            ->distinct()
            ->count('payments.id');

        $salesTransactions = (int) (clone $query)
            ->distinct()
            ->count('details.settlement_header_id');

        return [
            'payment_transactions' => $paymentTransactions,

            'sales_transactions' => $salesTransactions,
        ];
    }

    /**
     * Retrieve current outstanding Consignment receivables.
     */
    private function getOutstandingReceivables(
        int $companyId,
        array $branchIds
    ): float {
        $query = DB::table('settlement_headers')
            ->where('company_id', $companyId)
            ->where('status', 'Posted')
            ->whereNull('deleted_at')
            ->where('receivable_amount', '>', 0);

        if (!empty($branchIds)) {
            $query->whereIn('branch_id', $branchIds);
        }

        return (float) $query->sum('receivable_amount');
    }

    /**
     * Generate payment amount chart.
     *
     * POS and Consignment are displayed as separate series.
     */
    private function generateChart(Collection $rows): array
    {
        $methods = $rows
            ->groupBy('payment_method')
            ->map(function (Collection $items) {
                return [
                    'payment_method' => $items->first()['payment_method'],

                    'label' => $items->first()['payment_method_label'],

                    'pos' => (float) $items
                        ->where('source_type', 'POS')
                        ->sum('payment_amount'),

                    'consignment' => (float) $items
                        ->where('source_type', 'Consignment')
                        ->sum('payment_amount'),
                ];
            })
            ->sort(function ($a, $b) {
                $totalA = $a['pos'] + $a['consignment'];
                $totalB = $b['pos'] + $b['consignment'];

                return $totalB <=> $totalA;
            })
            ->values();

        return [
            'labels' => $methods
                ->pluck('label')
                ->values()
                ->all(),

            'pos' => $methods
                ->pluck('pos')
                ->values()
                ->all(),

            'consignment' => $methods
                ->pluck('consignment')
                ->values()
                ->all(),

            'data' => $methods->all(),
        ];
    }

    /**
     * Format payment method labels.
     */
    private function formatPaymentMethod(string $method): string
    {
        $labels = [
            'cash' => 'Cash',
            'qris' => 'QRIS',
            'debit_card' => 'Debit Card',
            'credit_card' => 'Credit Card',
            'transfer' => 'Transfer',
            'e_wallet' => 'E-Wallet',
        ];

        $key = strtolower(trim($method));

        return $labels[$key]
            ?? ucwords(str_replace('_', ' ', $method));
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

        $periodDays = (int) $dateFrom->diffInDays($dateTo) + 1;

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

        $currentReport = $this->generate(
            $filters,
            false,
            false
        );

        $current = $currentReport['summary'];

        $previous = $previousReport['summary'];

        $metrics = [];

        $keys = [
            'total_payment_methods',
            'payment_transactions',
            'sales_transactions',
            'total_payment_amount',
            'pos_payment_amount',
            'consignment_payment_amount',
            'average_payment_amount',
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
    /**
 * Retrieve unique Consignment settlement counts.
 */
private function getUniqueConsignmentSalesTransactions(
    int $companyId,
    array $branchIds,
    array $paymentMethods,
    string $dateFrom,
    string $dateTo
): int {
    return (int) $this->buildConsignmentPaymentQuery(
        $companyId,
        $branchIds,
        $paymentMethods,
        $dateFrom,
        $dateTo
    )
        ->distinct()
        ->count('details.settlement_header_id');
}
}