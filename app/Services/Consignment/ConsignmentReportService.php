<?php

namespace App\Services\Consignment;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Models\Reseller\Reseller;
use App\Models\Reseller\ResellerPrice;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\InventoryMovement;

use App\Models\Reseller\ConsignmentOut\ConsignmentOut;
use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader;
use App\Models\Reseller\ConsignmentReceivable\ConsignmentReceivableHeader;
use App\Models\Reseller\ConsignmentReturn\ConsignmentReturnHeader;

class ConsignmentReportService
{
    /*
    |--------------------------------------------------------------------------
    | Main Report
    |--------------------------------------------------------------------------
    */

    public function getReport(array $filters = []): array
    {
        $reportType = $filters['report_type'] ?? 'all_activity';

        return match ($reportType) {

            'consignment_out' =>
                $this->getConsignmentOutReport($filters),

            'sales_settlement' =>
                $this->getSalesSettlementReport($filters),

            'receivable_payment' =>
                $this->getReceivablePaymentReport($filters),

            'consignment_return' =>
                $this->getConsignmentReturnReport($filters),

            'stock_position' =>
                $this->getStockPositionReport($filters),

            'receivable' =>
                $this->getReceivableReport($filters),

            'sales_analysis' =>
                $this->getSalesAnalysisReport($filters),

            'profit_analysis' =>
                $this->getProfitAnalysisReport($filters),

            default =>
                $this->getAllActivityReport($filters),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Consignment Out
    |--------------------------------------------------------------------------
    */

    protected function getConsignmentOutReport(array $filters): array
    {
        $query = ConsignmentOut::query()
            ->with([
                'reseller',
                'branch',
                'warehouse',
                'details.variant.product',
                'details.unit',
            ])
            ->where('status', 'Posted');

        $this->applyHeaderFilters(
            $query,
            $filters,
            'transaction_date'
        );

        $documents = $query
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $rows = collect();

        foreach ($documents as $document) {

            foreach ($document->details as $detail) {

                $rows->push([
                    'date' => optional($document->transaction_date)
                        ->format('Y-m-d'),

                    'number' =>
                        $document->consignment_out_number,

                    'reseller_id' =>
                        $document->reseller_id,

                    'reseller' =>
                        optional($document->reseller)->name,

                    'branch_id' =>
                        $document->branch_id,

                    'branch' =>
                        optional($document->branch)->name,

                    'warehouse' =>
                        optional($document->warehouse)->name,

                    'product_variant_id' =>
                        $detail->product_variant_id,

                    'product' =>
                        optional($detail->variant?->product)->name
                        ?? optional($detail->variant)->name,

                    'variant' =>
                        optional($detail->variant)->name,

                    'unit' =>
                        optional($detail->unit)->name,

                    'qty' =>
                        (float) $detail->qty,

                    'unit_price' =>
                        (float) $detail->unit_price,

                    'total' =>
                        (float) $detail->total_price,
                ]);
            }
        }

        return [
            'type' => 'consignment_out',
            'title' => 'Consignment Out',
            'rows' => $rows->values(),
            'summary' => [
                'documents' => $documents->count(),
                'qty' => $rows->sum('qty'),
                'total' => $rows->sum('total'),
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Sales Settlement
    |--------------------------------------------------------------------------
    */

    protected function getSalesSettlementReport(array $filters): array
    {
        $query = ConsignmentSettlementHeader::query()
            ->with([
                'reseller',
                'branch',
                'details.variant.product',
                'details.unit',
            ])
            ->where('status', 'Posted');

        $this->applyHeaderFilters(
            $query,
            $filters,
            'settlement_date'
        );

        $documents = $query
            ->orderBy('settlement_date')
            ->orderBy('id')
            ->get();

        $rows = collect();

        foreach ($documents as $document) {

            foreach ($document->details as $detail) {

                $rows->push([
                    'date' => optional($document->settlement_date)
                        ->format('Y-m-d'),

                    'number' =>
                        $document->settlement_number,

                    'reseller_id' =>
                        $document->reseller_id,

                    'reseller' =>
                        optional($document->reseller)->name,

                    'branch_id' =>
                        $document->branch_id,

                    'branch' =>
                        optional($document->branch)->name,

                    'product_variant_id' =>
                        $detail->product_variant_id,

                    'product' =>
                        optional($detail->variant?->product)->name
                        ?? optional($detail->variant)->name,

                    'variant' =>
                        optional($detail->variant)->name,

                    'unit' =>
                        optional($detail->unit)->name,

                    'qty' =>
                        (float) $detail->qty_sold,

                    'unit_price' =>
                        (float) $detail->unit_price,

                    'sales' =>
                        (float) $detail->total_amount,
                ]);
            }
        }

        return [
            'type' => 'sales_settlement',
            'title' => 'Sales Settlement',
            'rows' => $rows->values(),
            'summary' => [
                'documents' => $documents->count(),
                'qty' => $rows->sum('qty'),
                'sales' => $rows->sum('sales'),
                'payment' => $documents->sum(
                    fn ($document) =>
                        (float) $document->payment_amount
                ),
                'receivable' => $documents->sum(
                    fn ($document) =>
                        (float) $document->receivable_amount
                ),
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Receivable Payment
    |--------------------------------------------------------------------------
    */

    protected function getReceivablePaymentReport(array $filters): array
    {
        $query = ConsignmentReceivableHeader::query()
            ->with([
                'reseller',
                'branch',
                'paymentAccount',
                'details.settlement',
            ])
            ->where('status', 'Posted');

        $this->applyHeaderFilters(
            $query,
            $filters,
            'payment_date'
        );

        $documents = $query
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        $rows = collect();

        foreach ($documents as $document) {

            foreach ($document->details as $detail) {

                $settlement = $detail->settlement;

                $rows->push([
                    'date' => optional($document->payment_date)
                        ->format('Y-m-d'),

                    'number' =>
                        $document->number,

                    'reseller_id' =>
                        $document->reseller_id,

                    'reseller' =>
                        optional($document->reseller)->name,

                    'branch_id' =>
                        $document->branch_id,

                    'branch' =>
                        optional($document->branch)->name,

                    'settlement_number' =>
                        optional($settlement)->settlement_number,

                    'payment_method' =>
                        $document->payment_method,

                    'payment_account' =>
                        optional($document->paymentAccount)->name,

                    'payment' =>
                        (float) $detail->payment_amount,

                    'settlement_amount' =>
                        (float) $detail->settlement_amount,

                    'previous_paid' =>
                        (float) $detail->previous_paid_amount,

                    'previous_outstanding' =>
                        (float) $detail->previous_outstanding_amount,

                    'outstanding_after' =>
                        max(
                            0,
                            (float) $detail->previous_outstanding_amount
                            - (float) $detail->payment_amount
                        ),
                ]);
            }
        }

        return [
            'type' => 'receivable_payment',
            'title' => 'Receivable Payment',
            'rows' => $rows->values(),
            'summary' => [
                'documents' => $documents->count(),
                'payment' => $rows->sum('payment'),
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Consignment Return
    |--------------------------------------------------------------------------
    */

    protected function getConsignmentReturnReport(array $filters): array
    {
        $query = ConsignmentReturnHeader::query()
            ->with([
                'reseller',
                'branch',
                'warehouse',
                'details.variant.product',
                'details.unit',
            ])
            ->where('status', 'Posted');

        $this->applyHeaderFilters(
            $query,
            $filters,
            'return_date'
        );

        $documents = $query
            ->orderBy('return_date')
            ->orderBy('id')
            ->get();

        $rows = collect();

        foreach ($documents as $document) {

            foreach ($document->details as $detail) {

                $rows->push([
                    'date' => optional($document->return_date)
                        ->format('Y-m-d'),

                    'number' =>
                        $document->return_number,

                    'reseller_id' =>
                        $document->reseller_id,

                    'reseller' =>
                        optional($document->reseller)->name,

                    'branch_id' =>
                        $document->branch_id,

                    'branch' =>
                        optional($document->branch)->name,

                    'warehouse' =>
                        optional($document->warehouse)->name,

                    'product_variant_id' =>
                        $detail->product_variant_id,

                    'product' =>
                        optional($detail->variant?->product)->name
                        ?? optional($detail->variant)->name,

                    'variant' =>
                        optional($detail->variant)->name,

                    'unit' =>
                        optional($detail->unit)->name,

                    'qty' =>
                        (float) $detail->returned_qty,

                    'unit_cost' =>
                        (float) $detail->unit_cost,

                    'total_cost' =>
                        (float) $detail->total_cost,

                    'remarks' =>
                        $detail->remarks,
                ]);
            }
        }

        return [
            'type' => 'consignment_return',
            'title' => 'Consignment Return',
            'rows' => $rows->values(),
            'summary' => [
                'documents' => $documents->count(),
                'qty' => $rows->sum('qty'),
                'total_cost' => $rows->sum('total_cost'),
            ],
        ];
    }


  /*
|--------------------------------------------------------------------------
| Stock Position
|--------------------------------------------------------------------------
*/

protected function getStockPositionReport(array $filters): array
{
    $query = ProductStock::query()
        ->with([
            'reseller',
            'branch',
            'variant.product',
            'unit',
        ])
        ->whereNotNull('reseller_id');

    $this->applyStockFilters(
        $query,
        $filters
    );

    $stocks = $query
        ->orderBy('reseller_id')
        ->orderBy('product_variant_id')
        ->orderBy('unit_id')
        ->get();

    $resellerIds = $stocks
        ->pluck('reseller_id')
        ->filter()
        ->unique()
        ->values();

    $productIds = $stocks
        ->map(
            fn ($stock) =>
                optional($stock->variant)->product_id
        )
        ->filter()
        ->unique()
        ->values();

    $prices = ResellerPrice::query()
        ->whereIn('reseller_id', $resellerIds)
        ->whereIn('product_id', $productIds)
        ->get()
        ->keyBy(
            fn ($price) =>
                $price->reseller_id . '|' . $price->product_id
        );

    $rows = $stocks->map(
        function ($stock) use ($prices) {

            $productId =
                optional($stock->variant)->product_id;

            $price =
                $prices->get(
                    $stock->reseller_id . '|' . $productId
                );

            $consignmentPrice =
                $price
                    ? (float) $price->price
                    : 0;

            $qty =
                (float) $stock->on_hand_qty;

            return [
                'reseller_id' =>
                    $stock->reseller_id,

                'reseller' =>
                    optional($stock->reseller)->name,

                'branch_id' =>
                    $stock->branch_id,

                'branch' =>
                    optional($stock->branch)->name,

                'product_variant_id' =>
                    $stock->product_variant_id,

                'product' =>
                    optional($stock->variant?->product)->name
                    ?? optional($stock->variant)->name,

                'variant' =>
                    optional($stock->variant)->name,

                'unit' =>
                    optional($stock->unit)->name,

                'on_hand' =>
                    $qty,

                'available' =>
                    (float) $stock->available_qty,

                'consignment_price' =>
                    $consignmentPrice,

                'stock_value' =>
                    round(
                        $qty * $consignmentPrice,
                        2
                    ),
            ];
        }
    );

    return [
        'type' => 'stock_position',

        'title' => 'Stock Position',

        'rows' =>
            $rows->values(),

        'summary' => [
            'products' =>
                $rows->count(),

            'qty' =>
                $rows->sum('on_hand'),

            'stock_value' =>
                $rows->sum('stock_value'),
        ],
    ];
}


    /*
    |--------------------------------------------------------------------------
    | Receivable
    |--------------------------------------------------------------------------
    */

    protected function getReceivableReport(array $filters): array
    {
        $settlementQuery = ConsignmentSettlementHeader::query()
            ->with([
                'reseller',
            ])
            ->where('status', 'Posted');

        $paymentQuery = ConsignmentReceivableHeader::query()
            ->with([
                'reseller',
            ])
            ->where('status', 'Posted');

        $this->applyHeaderFilters(
            $settlementQuery,
            $filters,
            'settlement_date'
        );

        $this->applyHeaderFilters(
            $paymentQuery,
            $filters,
            'payment_date'
        );

        $settlements = $settlementQuery
            ->orderBy('settlement_date')
            ->orderBy('id')
            ->get();

        $payments = $paymentQuery
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        $rows = collect();

        foreach ($settlements as $settlement) {

            $rows->push([
                'date' =>
                    optional($settlement->settlement_date)
                        ->format('Y-m-d'),

                'type' =>
                    'Settlement',

                'number' =>
                    $settlement->settlement_number,

                'reseller_id' =>
                    $settlement->reseller_id,

                'reseller' =>
                    optional($settlement->reseller)->name,

                'debit' =>
                    (float) $settlement->receivable_amount,

                'credit' =>
                    0,

                'balance' =>
                    0,
            ]);
        }

        foreach ($payments as $payment) {

            $rows->push([
                'date' =>
                    optional($payment->payment_date)
                        ->format('Y-m-d'),

                'type' =>
                    'Payment',

                'number' =>
                    $payment->number,

                'reseller_id' =>
                    $payment->reseller_id,

                'reseller' =>
                    optional($payment->reseller)->name,

                'debit' =>
                    0,

                'credit' =>
                    (float) $payment->total_amount,

                'balance' =>
                    0,
            ]);
        }

        $rows = $rows
            ->sortBy([
                ['date', 'asc'],
                ['type', 'asc'],
            ])
            ->values();

        $balance = 0;

        $rows = $rows->map(
            function ($row) use (&$balance) {

                $balance +=
                    (float) $row['debit']
                    - (float) $row['credit'];

                $row['balance'] =
                    round($balance, 2);

                return $row;
            }
        );

        return [
            'type' => 'receivable',
            'title' => 'Receivable',
            'rows' => $rows,
            'summary' => [
                'debit' =>
                    $rows->sum('debit'),

                'credit' =>
                    $rows->sum('credit'),

                'outstanding' =>
                    $balance,
            ],
        ];
    }


    protected function getSalesAnalysisReport(array $filters): array
{
    $query = ConsignmentSettlementHeader::query()
        ->with([
            'reseller',
            'branch',
            'details.variant.product',
            'details.unit',
            'movements',
        ])
        ->where('status', 'Posted');

    $this->applyHeaderFilters(
        $query,
        $filters,
        'settlement_date'
    );

    $settlements = $query
        ->orderBy('settlement_date')
        ->orderBy('id')
        ->get();

    $detailRows = $this->buildProfitRows($settlements);

    /*
    |--------------------------------------------------------------------------
    | Group By Reseller
    |--------------------------------------------------------------------------
    */

    $rows = $detailRows
        ->groupBy('reseller_id')
        ->map(function (Collection $items) {

            $sales = $items->sum('sales');
            $hpp = $items->sum('hpp');
            $profit = $items->sum('profit');
            $qty = $items->sum('qty');

            /*
            |--------------------------------------------------------------------------
            | Product Detail
            |--------------------------------------------------------------------------
            */

            $details = $items
                ->groupBy(function ($item) {
                    return
                        $item['product_variant_id']
                        . '|'
                        . ($item['unit_id'] ?? 0);
                })
                ->map(function (Collection $products) {

                    $productSales =
                        $products->sum('sales');

                    $productHpp =
                        $products->sum('hpp');

                    $productProfit =
                        $products->sum('profit');

                    $productQty =
                        $products->sum('qty');

                    $first =
                        $products->first();

                    return [
                        'product_variant_id' =>
                            $first['product_variant_id'],

                        'product' =>
                            $first['product'],

                        'variant' =>
                            $first['variant'],

                        'unit' =>
                            $first['unit'],

                        'qty' =>
                            round($productQty, 2),

                        'unit_price' =>
                            $productQty > 0
                                ? round(
                                    $productSales / $productQty,
                                    2
                                )
                                : 0,

                        'sales' =>
                            round($productSales, 2),

                        'hpp' =>
                            round($productHpp, 2),

                        'unit_hpp' =>
                            $productQty > 0
                                ? round(
                                    $productHpp / $productQty,
                                    2
                                )
                                : 0,

                        'profit' =>
                            round($productProfit, 2),

                        'margin' =>
                            $this->calculateMargin(
                                $productSales,
                                $productProfit
                            ),
                    ];
                })
                ->sortByDesc('sales')
                ->values();

            return [
                'reseller_id' =>
                    $items->first()['reseller_id'],

                'reseller' =>
                    $items->first()['reseller'],

                'qty' =>
                    round($qty, 2),

                'sales' =>
                    round($sales, 2),

                'hpp' =>
                    round($hpp, 2),

                'profit' =>
                    round($profit, 2),

                'margin' =>
                    $this->calculateMargin(
                        $sales,
                        $profit
                    ),

                'details' =>
                    $details,
            ];
        })
        ->sortByDesc('sales')
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    $totalQty =
        $rows->sum('qty');

    $totalSales =
        $rows->sum('sales');

    $totalHpp =
        $rows->sum('hpp');

    $totalProfit =
        $rows->sum('profit');

    return [
        'type' =>
            'sales_analysis',

        'title' =>
            'Sales Analysis',

        'rows' =>
            $rows,

        'summary' => [
            'resellers' =>
                $rows->count(),

            'qty' =>
                round($totalQty, 2),

            'sales' =>
                round($totalSales, 2),

            'hpp' =>
                round($totalHpp, 2),

            'profit' =>
                round($totalProfit, 2),

            'margin' =>
                $this->calculateMargin(
                    $totalSales,
                    $totalProfit
                ),
        ],
    ];
}


    /*
    |--------------------------------------------------------------------------
    | Profit Analysis
    |--------------------------------------------------------------------------
    */

    protected function getProfitAnalysisReport(array $filters): array
    {
        $query = ConsignmentSettlementHeader::query()
            ->with([
                'reseller',
                'branch',
                'details.variant.product',
                'details.unit',
                'movements',
            ])
            ->where('status', 'Posted');

        $this->applyHeaderFilters(
            $query,
            $filters,
            'settlement_date'
        );

        $settlements = $query
            ->orderBy('settlement_date')
            ->orderBy('id')
            ->get();

        $rows = $this->buildProfitRows($settlements);

        return [
            'type' => 'profit_analysis',
            'title' => 'Profit Analysis',
            'rows' => $rows->values(),
            'summary' => [
                'qty' =>
                    $rows->sum('qty'),

                'sales' =>
                    $rows->sum('sales'),

                'hpp' =>
                    $rows->sum('hpp'),

                'profit' =>
                    $rows->sum('profit'),

                'margin' =>
                    $this->calculateMargin(
                        $rows->sum('sales'),
                        $rows->sum('profit')
                    ),
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Build Profit Rows
    |--------------------------------------------------------------------------
    */

    protected function buildProfitRows(
        Collection $settlements
    ): Collection {

        $rows = collect();

        foreach ($settlements as $settlement) {

            foreach ($settlement->details as $detail) {

                $sales =
                    (float) $detail->total_amount;

                $qty =
                    (float) $detail->qty_sold;

                $hpp =
                    0;

                $movement =
                    $settlement->movements
                        ->first(
                            function ($movement) use ($detail) {

                                return
                                    (int) $movement->product_variant_id
                                        === (int) $detail->product_variant_id
                                    &&
                                    (int) $movement->unit_id
                                        === (int) $detail->unit_id
                                    &&
                                    $movement->qty_out > 0;
                            }
                        );

                if ($movement) {

                    $hpp =
                        (float) $movement->total_cost;
                }

                $profit =
                    $sales - $hpp;

                $rows->push([

                    'date' =>
                        optional(
                            $settlement->settlement_date
                        )->format('Y-m-d'),

                    'number' =>
                        $settlement->settlement_number,

                    'reseller_id' =>
                        $settlement->reseller_id,

                    'reseller' =>
                        optional(
                            $settlement->reseller
                        )->name,

                    'branch_id' =>
                        $settlement->branch_id,

                    'branch' =>
                        optional(
                            $settlement->branch
                        )->name,

                    'product_variant_id' =>
                        $detail->product_variant_id,

                    'unit_id' =>
                        $detail->unit_id,

                    'product' =>
                        optional(
                            $detail->variant?->product
                        )->name
                        ??
                        optional(
                            $detail->variant
                        )->name,

                    'variant' =>
                        optional(
                            $detail->variant
                        )->name,

                    'unit' =>
                        optional(
                            $detail->unit
                        )->name,

                    'qty' =>
                        $qty,

                    'sales' =>
                        round($sales, 2),

                    'hpp' =>
                        round($hpp, 2),

                    'unit_hpp' =>
                        $qty > 0
                            ? round($hpp / $qty, 2)
                            : 0,

                    'unit_price' =>
                        $qty > 0
                            ? round($sales / $qty, 2)
                            : 0,

                    'profit' =>
                        round($profit, 2),

                    'margin' =>
                        $this->calculateMargin(
                            $sales,
                            $profit
                        ),
                ]);
            }
        }

        return $rows;
    }

/*
|--------------------------------------------------------------------------
| All Activity
|--------------------------------------------------------------------------
*/

protected function getAllActivityReport(array $filters): array
{
    $rows = collect();

    // ============================================================
    // CONSIGNMENT OUT
    // ============================================================
    $out = $this->getConsignmentOutReport($filters);

    foreach ($out['rows'] as $row) {
        $rows->push([
            'date' => $row['date'],
            'activity' => 'Consignment Out',
            'number' => $row['number'],
            'reseller_id' => $row['reseller_id'],
            'reseller' => $row['reseller'],
            'product' => $row['product'],
            'unit' => $row['unit'],

            'qty' => $row['qty'],
            'qty_label' => $row['qty'] . ' Out',

            'amount' => $row['total'],
        ]);
    }

    // ============================================================
    // SALES SETTLEMENT
    // ============================================================
    $sales = $this->getSalesSettlementReport($filters);

    foreach ($sales['rows'] as $row) {
        $rows->push([
            'date' => $row['date'],
            'activity' => 'Sales Settlement',
            'number' => $row['number'],
            'reseller_id' => $row['reseller_id'],
            'reseller' => $row['reseller'],
            'product' => $row['product'],
            'unit' => $row['unit'],

            'qty' => $row['qty'],
            'qty_label' => $row['qty'] . ' Sold',

            'amount' => $row['sales'],
        ]);
    }

    // ============================================================
    // RECEIVABLE PAYMENT
    // ============================================================
    $payments = $this->getReceivablePaymentReport($filters);

    foreach ($payments['rows'] as $row) {
        $rows->push([
            'date' => $row['date'],
            'activity' => 'Receivable Payment',
            'number' => $row['number'],
            'reseller_id' => $row['reseller_id'],
            'reseller' => $row['reseller'],
            'product' => null,
            'unit' => null,

            'qty' => null,
            'qty_label' => null,

            'amount' => $row['payment'],
        ]);
    }

    // ============================================================
    // CONSIGNMENT RETURN
    // ============================================================
    $returns = $this->getConsignmentReturnReport($filters);

    foreach ($returns['rows'] as $row) {
        $rows->push([
            'date' => $row['date'],
            'activity' => 'Consignment Return',
            'number' => $row['number'],
            'reseller_id' => $row['reseller_id'],
            'reseller' => $row['reseller'],
            'product' => $row['product'],
            'unit' => $row['unit'],

            'qty' => $row['qty'],
            'qty_label' => $row['qty'] . ' Return',

            'amount' => $row['total_cost'],
        ]);
    }

    $rows = $rows
        ->sortBy([
            ['date', 'asc'],
            ['activity', 'asc'],
            ['number', 'asc'],
        ])
        ->values();

    // ============================================================
    // SUMMARY
    // ============================================================

    return [
        'type' => 'all_activity',
        'title' => 'All Activity',
        'rows' => $rows,

        'summary' => [
            'rows' => $rows->count(),

            'consignment_out_qty' => $rows
                ->where('activity', 'Consignment Out')
                ->sum('qty'),

            'sold_qty' => $rows
                ->where('activity', 'Sales Settlement')
                ->sum('qty'),

            'return_qty' => $rows
                ->where('activity', 'Consignment Return')
                ->sum('qty'),

            'payment_amount' => $rows
                ->where('activity', 'Receivable Payment')
                ->sum('amount'),
        ],
    ];
}


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    protected function applyHeaderFilters(
        $query,
        array $filters,
        string $dateColumn
    ): void {

        if (
            !empty($filters['reseller_id'])
        ) {
            $query->where(
                'reseller_id',
                $filters['reseller_id']
            );
        }

        if (
            !empty($filters['branch_id'])
        ) {
            $query->where(
                'branch_id',
                $filters['branch_id']
            );
        }

        if (
            !empty($filters['date_from'])
        ) {
            $query->whereDate(
                $dateColumn,
                '>=',
                $filters['date_from']
            );
        }

        if (
            !empty($filters['date_to'])
        ) {
            $query->whereDate(
                $dateColumn,
                '<=',
                $filters['date_to']
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Stock Filters
    |--------------------------------------------------------------------------
    */

    protected function applyStockFilters(
        $query,
        array $filters
    ): void {

        if (
            !empty($filters['reseller_id'])
        ) {
            $query->where(
                'reseller_id',
                $filters['reseller_id']
            );
        }

        if (
            !empty($filters['branch_id'])
        ) {
            $query->where(
                'branch_id',
                $filters['branch_id']
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Margin
    |--------------------------------------------------------------------------
    */

    protected function calculateMargin(
        float $sales,
        float $profit
    ): float {

        if ($sales <= 0) {
            return 0;
        }

        return round(
            ($profit / $sales) * 100,
            2
        );
    }
}