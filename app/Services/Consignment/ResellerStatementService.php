<?php

namespace App\Services\Consignment;

use App\Models\Inventory\InventoryMovement;
use App\Models\Reseller\ConsignmentReceivable\ConsignmentReceivableHeader;
use App\Models\Reseller\ConsignmentReturn\ConsignmentReturnHeader;
use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader;
use Illuminate\Support\Collection;
use App\Models\Reseller\ResellerPrice;
use App\Models\Inventory\ProductStock;

class ResellerStatementService
{
    /*
    |--------------------------------------------------------------------------
    | Statement
    |--------------------------------------------------------------------------
    */

    public function getStatement(array $filters = []): array
    {
        $resellerId = !empty($filters['reseller_id'])
            ? (int) $filters['reseller_id']
            : null;

        $branchId = !empty($filters['branch_id'])
            ? (int) $filters['branch_id']
            : null;

        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Stock
        |--------------------------------------------------------------------------
        */

        $stock = $this->buildStockStatement(
            resellerId: $resellerId,
            branchId: $branchId,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
        );

        /*
        |--------------------------------------------------------------------------
        | Settlement
        |--------------------------------------------------------------------------
        */

        $settlements = $this->getSettlements(
            resellerId: $resellerId,
            branchId: $branchId,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
        );

        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        $payments = $this->getPayments(
            resellerId: $resellerId,
            branchId: $branchId,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
        );

        /*
        |--------------------------------------------------------------------------
        | Receivable
        |--------------------------------------------------------------------------
        */

        $receivable = $this->buildReceivableStatement(
            resellerId: $resellerId,
            branchId: $branchId,
            dateFrom: $dateFrom,
            settlements: $settlements,
            payments: $payments,
        );

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [

            /*
            |--------------------------------------------------------------------------
            | Overview
            |--------------------------------------------------------------------------
            */

          'overview' => [

                'stock' => [

                    'opening_stock' =>
                        $stock['overview']['opening'],

                    'consignment_out' =>
                        $stock['overview']['consignment_out'],

                    'sold' =>
                        $stock['overview']['sold'],

                    'return' =>
                        $stock['overview']['return'],

                    'closing_stock' =>
                        $stock['overview']['closing'],

                ],

              'receivable' => [

                'opening_ar' =>
                    $receivable['overview']['opening_ar'],

                'new_settlement' =>
                    $receivable['overview']['new_settlement'],

                'payment' =>
                    $receivable['overview']['payment'],

                'outstanding' =>
                    $receivable['overview']['outstanding'],

            ],

            ],
            /*
            |--------------------------------------------------------------------------
            | Stock
            |--------------------------------------------------------------------------
            */

            'stock' => [

                'movements' =>
                    $stock['movements'],

                'details' =>
                    $stock['details'],

            ],

            /*
            |--------------------------------------------------------------------------
            | Receivable
            |--------------------------------------------------------------------------
            */

            'receivable' => [

                'ledger' =>
                    $receivable['ledger'],

            ],
            /*
            |--------------------------------------------------------------------------
            | Settlement
            |--------------------------------------------------------------------------
            */

            'settlements' =>
                $settlements,

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            'payments' =>
                $payments,

            /*
            |--------------------------------------------------------------------------
            | Statistics
            |--------------------------------------------------------------------------
            */

            'statistics' => [

                'opening_stock' =>
                    $stock['overview']['opening'],

                'consignment_out' =>
                    $stock['overview']['consignment_out'],

                'sold' =>
                    $stock['overview']['sold'],

                'return' =>
                    $stock['overview']['return'],

                'closing_stock' =>
                    $stock['overview']['closing'],

                'opening_receivable' =>
                    $receivable['overview']['opening_ar'],

                'settlement' =>
                    $receivable['overview']['settlement'],

                'payment' =>
                    $receivable['overview']['payment'],

                'outstanding' =>
                    $receivable['overview']['outstanding'],

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Stock Statement
    |--------------------------------------------------------------------------
    */

    protected function buildStockStatement(
        ?int $resellerId,
        ?int $branchId,
        ?string $dateFrom,
        ?string $dateTo,
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Opening Movements
        |--------------------------------------------------------------------------
        */

        $openingMovements = $dateFrom
            ? $this->getInventoryMovements(
                resellerId: $resellerId,
                branchId: $branchId,
                dateFrom: null,
                dateTo: $this->previousDate($dateFrom),
            )
            : collect();

        /*
        |--------------------------------------------------------------------------
        | Period Movements
        |--------------------------------------------------------------------------
        */

        $periodMovements =
            $this->getInventoryMovements(
                resellerId: $resellerId,
                branchId: $branchId,
                dateFrom: $dateFrom,
                dateTo: $dateTo,
            );


        /*
        |--------------------------------------------------------------------------
        | Opening By Stock Key
        |--------------------------------------------------------------------------
        */

        $openingByKey =
            $this->calculateOpeningByKey(
                $openingMovements
            );


        /*
        |--------------------------------------------------------------------------
        | Period Rows
        |--------------------------------------------------------------------------
        */

        $movementRows =
            $this->mapMovementRows(
                $periodMovements
            );


        /*
        |--------------------------------------------------------------------------
        | Stock Details
        |--------------------------------------------------------------------------
        */

        $details =
            $this->buildStockDetails(
                $openingByKey,
                $periodMovements
            );


        /*
        |--------------------------------------------------------------------------
        | Overview
        |--------------------------------------------------------------------------
        */

        $opening =
            $details->sum('opening');

        $consignmentOut =
            $details->sum('consignment_out');

        $sold =
            $details->sum('sold');

        $return =
            $details->sum('return');

       $closing =
    ProductStock::query()
        ->where(
            'reseller_id',
            $resellerId
        )
        ->when(
            $branchId,
            fn ($query) =>
                $query->where(
                    'branch_id',
                    $branchId
                )
        )
        ->sum('on_hand_qty');


        return [

            'overview' => [

                'opening' =>
                    round($opening, 2),

                'consignment_out' =>
                    round($consignmentOut, 2),

                'sold' =>
                    round($sold, 2),

                'return' =>
                    round($return, 2),

                'closing' =>
                    round($closing, 2),

            ],

            'movements' =>
                $movementRows,

            'details' =>
                $details,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Inventory Movements
    |--------------------------------------------------------------------------
    */

    protected function getInventoryMovements(
        ?int $resellerId,
        ?int $branchId,
        ?string $dateFrom,
        ?string $dateTo,
    ): Collection {

        return InventoryMovement::query()

            ->with([
                'variant.product',
                'unit',
                'branch',
                'warehouse',
                'reseller',
            ])

            ->whereNotNull('reseller_id')

            ->when(
                $resellerId,
                fn ($query) =>
                    $query->where(
                        'reseller_id',
                        $resellerId
                    )
            )

            ->when(
                $branchId,
                fn ($query) =>
                    $query->where(
                        'branch_id',
                        $branchId
                    )
            )

            ->when(
                $dateFrom,
                fn ($query) =>
                    $query->whereDate(
                        'transaction_date',
                        '>=',
                        $dateFrom
                    )
            )

            ->when(
                $dateTo,
                fn ($query) =>
                    $query->whereDate(
                        'transaction_date',
                        '<=',
                        $dateTo
                    )
            )

            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | Previous Date
    |--------------------------------------------------------------------------
    */

    protected function previousDate(
        string $date
    ): string {

        return date(
            'Y-m-d',
            strtotime(
                $date . ' -1 day'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Opening By Key
    |--------------------------------------------------------------------------
    */

    protected function calculateOpeningByKey(
        Collection $movements
    ): Collection {

        return $movements

            ->groupBy(
                fn ($movement) =>
                    $this->stockKey(
                        $movement
                    )
            )

            ->map(
                function ($rows) {

                    $first =
                        $rows->first();

                    $qtyIn =
                        $rows->sum(
                            fn ($movement) =>
                                (float)
                                $movement->qty_in
                        );

                    $qtyOut =
                        $rows->sum(
                            fn ($movement) =>
                                (float)
                                $movement->qty_out
                        );

                    return [

                        'key' =>
                            $this->stockKey(
                                $first
                            ),

                        'opening' =>
                            $qtyIn - $qtyOut,

                    ];
                }
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Stock Key
    |--------------------------------------------------------------------------
    */

    protected function stockKey(
        $movement
    ): string {

        return implode(
            '-',
            [

                $movement->reseller_id,

                $movement->branch_id,

                $movement->warehouse_id,

                $movement->product_variant_id,

                $movement->unit_id,

            ]
        );
    }


   /*
|--------------------------------------------------------------------------
| Movement Rows
|--------------------------------------------------------------------------
*/

protected function mapMovementRows(
    Collection $movements
): Collection {

    $balance = 0;

    return $movements
        ->map(
            function ($movement) use (&$balance) {

                $qtyIn =
                    (float) $movement->qty_in;

                $qtyOut =
                    (float) $movement->qty_out;

                $balance +=
                    $qtyIn - $qtyOut;

                return [

                    'id' =>
                        $movement->id,

                    'date' =>
                        $movement
                            ->transaction_date
                            ?->format('Y-m-d'),

                    'reference_type' =>
                        $movement->reference_type,

                    'reference_number' =>
                        $movement->reference_number,

                    'description' =>
                        $movement->description,

                    'reseller' => [

                        'id' =>
                            $movement
                                ->reseller
                                ?->id,

                        'code' =>
                            $movement
                                ->reseller
                                ?->reseller_code,

                        'name' =>
                            $movement
                                ->reseller
                                ?->name,

                    ],

                    'branch' => [

                        'id' =>
                            $movement
                                ->branch
                                ?->id,

                        'name' =>
                            $movement
                                ->branch
                                ?->name,

                    ],

                    'warehouse' => [

                        'id' =>
                            $movement
                                ->warehouse
                                ?->id,

                        'name' =>
                            $movement
                                ->warehouse
                                ?->name,

                    ],

                    'product_variant_id' =>
                        $movement
                            ->product_variant_id,

                    'product' => [

                        'id' =>
                            $movement
                                ->variant
                                ?->product
                                ?->id,

                        'name' =>
                            $movement
                                ->variant
                                ?->product
                                ?->name,

                    ],

                    'variant' => [

                        'id' =>
                            $movement
                                ->variant
                                ?->id,

                        'sku' =>
                            $movement
                                ->variant
                                ?->sku,

                        'name' =>
                            $movement
                                ->variant
                                ?->name,

                    ],

                    'unit' => [

                        'id' =>
                            $movement
                                ->unit
                                ?->id,

                        'name' =>
                            $movement
                                ->unit
                                ?->name,

                    ],

                    'qty_in' =>
                        $qtyIn,

                    'qty_out' =>
                        $qtyOut,

                    'balance' =>
                        $balance,

                    'unit_cost' =>
                        (float)
                        $movement->unit_cost,

                    'total_cost' =>
                        (float)
                        $movement->total_cost,

                ];
            }
        )
        ->values();
}

    /*
    |--------------------------------------------------------------------------
    | Stock Details
    |--------------------------------------------------------------------------
    */

    protected function buildStockDetails(
        Collection $openingByKey,
        Collection $periodMovements
    ): Collection {

        $periodByKey =
            $periodMovements
                ->groupBy(
                    fn ($movement) =>
                        $this->stockKey(
                            $movement
                        )
                );


        $keys =
            $openingByKey
                ->keys()
                ->merge(
                    $periodByKey->keys()
                )
                ->unique()
                ->values();


        return $keys
            ->map(
                function ($key) use (
                    $openingByKey,
                    $periodByKey
                ) {

                    $rows =
                        $periodByKey
                            ->get(
                                $key,
                                collect()
                            );

                    $first =
                        $rows->first();


                    /*
                    |--------------------------------------------------------------------------
                    | Opening
                    |--------------------------------------------------------------------------
                    */

                    $opening =
                        (float)
                        (
                            $openingByKey
                                ->get(
                                    $key,
                                    [
                                        'opening' => 0,
                                    ]
                                )['opening']
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Movement Classification
                    |--------------------------------------------------------------------------
                    */

                    $consignmentOut =
                        $rows
                            ->filter(
                                fn ($movement) =>
                                    $movement->reference_type ===
                                    'CONSIGNMENT_OUT'
                            )
                            ->sum(
                                fn ($movement) =>
                                    (float)
                                    $movement->qty_in
                            );


                    $sold =
                        $rows
                            ->filter(
                                fn ($movement) =>
                                    $movement->reference_type ===
                                    'CONSIGNMENT_SETTLEMENT'
                            )
                            ->sum(
                                fn ($movement) =>
                                    (float)
                                    $movement->qty_out
                            );


                    $return =
                        $rows
                            ->filter(
                                fn ($movement) =>
                                    $movement->reference_type ===
                                    'CONSIGNMENT_RETURN'
                            )
                            ->sum(
                                fn ($movement) =>
                                    (float)
                                    $movement->qty_out
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | Other Movement
                    |--------------------------------------------------------------------------
                    */

                    $otherIn =
                        $rows->sum(
                            fn ($movement) =>
                                $movement->reference_type !==
                                'CONSIGNMENT_OUT'
                                    ? (float)
                                      $movement->qty_in
                                    : 0
                        );


                    $otherOut =
                        $rows->sum(
                            fn ($movement) =>
                                ! in_array(
                                    $movement->reference_type,
                                    [
                                        'CONSIGNMENT_SETTLEMENT',
                                        'CONSIGNMENT_RETURN',
                                    ],
                                    true
                                )
                                    ? (float)
                                      $movement->qty_out
                                    : 0
                        );


                    $closing =
                        $opening
                        + $rows->sum(
                            fn ($movement) =>
                                (float)
                                $movement->qty_in
                                -
                                (float)
                                $movement->qty_out
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | If no Period Movement
                    |--------------------------------------------------------------------------
                    */

                    if (!$first) {

                        return [

                            'key' =>
                                $key,

                            'product_variant_id' =>
                                null,

                            'product' =>
                                null,

                            'variant' =>
                                null,

                            'unit' =>
                                null,

                            'reseller' =>
                                null,

                            'branch' =>
                                null,

                            'warehouse' =>
                                null,

                            'opening' =>
                                round($opening, 2),

                            'consignment_out' =>
                                0,

                            'sold' =>
                                0,

                            'return' =>
                                0,

                            'other_in' =>
                                0,

                            'other_out' =>
                                0,

                            'closing' =>
                                round($closing, 2),

                        ];
                    }


                    return [

                        'key' =>
                            $key,

                        'product_variant_id' =>
                            $first->product_variant_id,

                        'product' => [

                            'id' =>
                                $first
                                    ->variant
                                    ?->product
                                    ?->id,

                            'name' =>
                                $first
                                    ->variant
                                    ?->product
                                    ?->name,

                        ],

                        'variant' => [

                            'id' =>
                                $first
                                    ->variant
                                    ?->id,

                            'sku' =>
                                $first
                                    ->variant
                                    ?->sku,

                            'name' =>
                                $first
                                    ->variant
                                    ?->name,

                        ],

                        'unit' => [

                            'id' =>
                                $first
                                    ->unit
                                    ?->id,

                            'name' =>
                                $first
                                    ->unit
                                    ?->name,

                        ],

                        'reseller' => [

                            'id' =>
                                $first
                                    ->reseller
                                    ?->id,

                            'code' =>
                                $first
                                    ->reseller
                                    ?->reseller_code,

                            'name' =>
                                $first
                                    ->reseller
                                    ?->name,

                        ],

                        'branch' => [

                            'id' =>
                                $first
                                    ->branch
                                    ?->id,

                            'name' =>
                                $first
                                    ->branch
                                    ?->name,

                        ],

                        'warehouse' => [

                            'id' =>
                                $first
                                    ->warehouse
                                    ?->id,

                            'name' =>
                                $first
                                    ->warehouse
                                    ?->name,

                        ],

                        'opening' =>
                            round($opening, 2),

                        'consignment_out' =>
                            round($consignmentOut, 2),

                        'sold' =>
                            round($sold, 2),

                        'return' =>
                            round($return, 2),

                        'other_in' =>
                            round($otherIn, 2),

                        'other_out' =>
                            round($otherOut, 2),

                        'closing' =>
                            round($closing, 2),

                    ];
                }
            )
            ->filter(
                fn ($row) =>
                    $row['opening'] != 0
                    || $row['consignment_out'] != 0
                    || $row['sold'] != 0
                    || $row['return'] != 0
                    || $row['other_in'] != 0
                    || $row['other_out'] != 0
                    || $row['closing'] != 0
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Settlements
    |--------------------------------------------------------------------------
    */

    protected function getSettlements(
        ?int $resellerId,
        ?int $branchId,
        ?string $dateFrom,
        ?string $dateTo,
    ): Collection {

        return ConsignmentSettlementHeader::query()

            ->with([
                'reseller',
                'branch',
                'warehouse',
                'details.variant.product',
                'details.unit',
            ])

            ->where(
                'status',
                'Posted'
            )

            ->when(
                $resellerId,
                fn ($query) =>
                    $query->where(
                        'reseller_id',
                        $resellerId
                    )
            )

            ->when(
                $branchId,
                fn ($query) =>
                    $query->where(
                        'branch_id',
                        $branchId
                    )
            )

            ->when(
                $dateFrom,
                fn ($query) =>
                    $query->whereDate(
                        'settlement_date',
                        '>=',
                        $dateFrom
                    )
            )

            ->when(
                $dateTo,
                fn ($query) =>
                    $query->whereDate(
                        'settlement_date',
                        '<=',
                        $dateTo
                    )
            )

            ->orderBy(
                'settlement_date'
            )

            ->orderBy(
                'id'
            )

            ->get()

            ->map(
                function ($settlement) {

                    return [

                        'id' =>
                            $settlement->id,

                        'date' =>
                            $settlement
                                ->settlement_date
                                ?->format('Y-m-d'),

                        'settlement_number' =>
                            $settlement
                                ->settlement_number,

                        'period_from' =>
                            $settlement
                                ->period_from
                                ?->format('Y-m-d'),

                        'period_to' =>
                            $settlement
                                ->period_to
                                ?->format('Y-m-d'),

                        'reseller' => [

                            'id' =>
                                $settlement
                                    ->reseller
                                    ?->id,

                            'name' =>
                                $settlement
                                    ->reseller
                                    ?->name,

                            'code' =>
                                $settlement
                                    ->reseller
                                    ?->reseller_code,

                        ],

                        'branch' => [

                            'id' =>
                                $settlement
                                    ->branch
                                    ?->id,

                            'name' =>
                                $settlement
                                    ->branch
                                    ?->name,

                        ],

                        'warehouse' => [

                            'id' =>
                                $settlement
                                    ->warehouse
                                    ?->id,

                            'name' =>
                                $settlement
                                    ->warehouse
                                    ?->name,

                        ],

                        'qty_sold' =>
                            $settlement
                                ->details
                                ->sum(
                                    fn ($detail) =>
                                        (float)
                                        $detail->qty_sold
                                ),

                        'sales_amount' =>
                            (float)
                            $settlement
                                ->grand_total,

                        'paid' =>
                            (float)
                            $settlement
                                ->payment_amount,

                        'receivable' =>
                            (float)
                            $settlement
                                ->receivable_amount,

                        'payment_status' =>
                            $settlement
                                ->payment_status,

                    ];
                }
            )

            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    protected function getPayments(
        ?int $resellerId,
        ?int $branchId,
        ?string $dateFrom,
        ?string $dateTo,
    ): Collection {

        return ConsignmentReceivableHeader::query()

            ->with([
                'reseller',
                'branch',
                'details.settlement',
            ])

            ->where(
                'status',
                'Posted'
            )

            ->when(
                $resellerId,
                fn ($query) =>
                    $query->where(
                        'reseller_id',
                        $resellerId
                    )
            )

            ->when(
                $branchId,
                fn ($query) =>
                    $query->where(
                        'branch_id',
                        $branchId
                    )
            )

            ->when(
                $dateFrom,
                fn ($query) =>
                    $query->whereDate(
                        'payment_date',
                        '>=',
                        $dateFrom
                    )
            )

            ->when(
                $dateTo,
                fn ($query) =>
                    $query->whereDate(
                        'payment_date',
                        '<=',
                        $dateTo
                    )
            )

            ->orderBy(
                'payment_date'
            )

            ->orderBy(
                'id'
            )

            ->get()

            ->map(
                function ($payment) {

                    return [

                        'id' =>
                            $payment->id,

                        'date' =>
                            $payment
                                ->payment_date
                                ?->format('Y-m-d'),

                        'payment_number' =>
                            $payment->number,

                        'reseller' => [

                            'id' =>
                                $payment
                                    ->reseller
                                    ?->id,

                            'name' =>
                                $payment
                                    ->reseller
                                    ?->name,

                            'code' =>
                                $payment
                                    ->reseller
                                    ?->reseller_code,

                        ],

                        'branch' => [

                            'id' =>
                                $payment
                                    ->branch
                                    ?->id,

                            'name' =>
                                $payment
                                    ->branch
                                    ?->name,

                        ],

                        'payment_method' =>
                            $payment
                                ->payment_method,

                        'amount' =>
                            (float)
                            $payment
                                ->total_amount,

                      'details' =>
                        $payment
                            ->details
                            ->map(
                                fn ($detail) => [

                                    'settlement_id' =>
                                        $detail
                                            ->settlement_header_id,

                                    'settlement_number' =>
                                        $detail
                                            ->settlement
                                            ?->settlement_number,

                                    'payment_amount' =>
                                        (float)
                                        $detail
                                            ->payment_amount,

                                ]
                            )
                            ->values(),

                    ];
                }
            )

            ->values();
    }


protected function buildReceivableStatement(
    ?int $resellerId,
    ?int $branchId,
    ?string $dateFrom,
    Collection $settlements,
    Collection $payments,
): array {

    $opening =
        $this->getOpeningReceivable(
            resellerId: $resellerId,
            branchId: $branchId,
            dateFrom: $dateFrom,
        );


    $settlementAmount =
        $settlements->sum(
            'receivable'
        );


    $paymentAmount =
        $payments->sum(
            'amount'
        );


    $outstanding =
        $opening
        + $settlementAmount
        - $paymentAmount;


    /*
    |--------------------------------------------------------------------------
    | Receivable Ledger
    |--------------------------------------------------------------------------
    */

    $ledger = collect();


    /*
    |--------------------------------------------------------------------------
    | Settlement → Debit AR
    |--------------------------------------------------------------------------
    */

    foreach ($settlements as $settlement) {

        $receivable =
            (float)
            ($settlement['receivable'] ?? 0);

        if ($receivable == 0) {
            continue;
        }

        $ledger->push([

            'id' =>
                'settlement-' .
                $settlement['id'],

            'date' =>
                $settlement['date'],

            'document' =>
                $settlement['settlement_number'],

            'reference_type' =>
                'SETTLEMENT',

            'description' =>
                'Consignment settlement ' .
                $settlement['settlement_number'],

            'debit' =>
                round($receivable, 2),

            'credit' =>
                0,

            'balance' =>
                0,

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Payment → Credit AR
    |--------------------------------------------------------------------------
    */

    foreach ($payments as $payment) {

        $ledger->push([

            'id' =>
                'payment-' .
                $payment['id'],

            'date' =>
                $payment['date'],

            'document' =>
                $payment['payment_number'],

            'reference_type' =>
                'RECEIVABLE_PAYMENT',

            'description' =>
                'Receivable payment ' .
                $payment['payment_number'],

            'debit' =>
                0,

            'credit' =>
                round(
                    (float)
                    $payment['amount'],
                    2
                ),

            'balance' =>
                0,

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Sort Ledger
    |--------------------------------------------------------------------------
    */

    $ledger =
        $ledger
            ->sortBy([
                ['date', 'asc'],
                ['id', 'asc'],
            ])
            ->values();


    /*
    |--------------------------------------------------------------------------
    | Running AR Balance
    |--------------------------------------------------------------------------
    */

    $runningBalance =
        (float) $opening;


    $ledger =
        $ledger
            ->map(
                function ($row) use (
                    &$runningBalance
                ) {

                    $runningBalance +=
                        (float)
                        $row['debit'];

                    $runningBalance -=
                        (float)
                        $row['credit'];

                    $row['balance'] =
                        round(
                            $runningBalance,
                            2
                        );

                    return $row;

                }
            )
            ->values();


    return [

        'overview' => [

            'opening_ar' =>
                round($opening, 2),

            'new_settlement' =>
                round($settlementAmount, 2),

            'settlement' =>
                round($settlementAmount, 2),

            'payment' =>
                round($paymentAmount, 2),

            'outstanding' =>
                round($outstanding, 2),

        ],

        'ledger' =>
            $ledger,

    ];
}
protected function getOpeningReceivable(
    ?int $resellerId,
    ?int $branchId,
    ?string $dateFrom
): float {
    if (!$dateFrom) {
        return 0.0;
    }

    $settlementQuery = ConsignmentSettlementHeader::query()
        ->where('status', 'Posted')
        ->whereDate('settlement_date', '<', $dateFrom);

    if ($resellerId) {
        $settlementQuery->where('reseller_id', $resellerId);
    }

    if ($branchId) {
        $settlementQuery->where('branch_id', $branchId);
    }

    $settlementReceivable = (float) $settlementQuery
        ->sum('receivable_amount');

    $paymentQuery = ConsignmentReceivableHeader::query()
        ->where('status', 'Posted')
        ->whereDate('payment_date', '<', $dateFrom);

    if ($resellerId) {
        $paymentQuery->where('reseller_id', $resellerId);
    }

    if ($branchId) {
        $paymentQuery->where('branch_id', $branchId);
    }

    $payments = (float) $paymentQuery->sum('total_amount');

    return round($settlementReceivable - $payments, 2);
}
public function getPrintStatement(array $filters): array
{
    $resellerId = $filters['reseller_id'] ?? null;
    $branchId   = $filters['branch_id'] ?? null;
    $dateFrom   = $filters['date_from'] ?? null;
    $dateTo     = $filters['date_to'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | Posted Settlements
    |--------------------------------------------------------------------------
    */

    $settlements = \App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader::query()
        ->where('status', 'Posted')
        ->when(
            $resellerId,
            fn ($q) => $q->where('reseller_id', $resellerId)
        )
        ->when(
            $branchId,
            fn ($q) => $q->where('branch_id', $branchId)
        )
        ->when(
            $dateFrom,
            fn ($q) => $q->whereDate(
                'settlement_date',
                '>=',
                $dateFrom
            )
        )
        ->when(
            $dateTo,
            fn ($q) => $q->whereDate(
                'settlement_date',
                '<=',
                $dateTo
            )
        )
        ->with([
            'reseller',
            'branch',
            'details.variant.product',
            'details.unit',
        ])
        ->orderBy('settlement_date')
        ->orderBy('id')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Posted Receivable Payments
    |--------------------------------------------------------------------------
    */

    $payments = \App\Models\Reseller\ConsignmentReceivable\ConsignmentReceivableHeader::query()
        ->where('status', 'Posted')
        ->when(
            $resellerId,
            fn ($q) => $q->where('reseller_id', $resellerId)
        )
        ->when(
            $branchId,
            fn ($q) => $q->where('branch_id', $branchId)
        )
        ->when(
            $dateFrom,
            fn ($q) => $q->whereDate(
                'payment_date',
                '>=',
                $dateFrom
            )
        )
        ->when(
            $dateTo,
            fn ($q) => $q->whereDate(
                'payment_date',
                '<=',
                $dateTo
            )
        )
        ->with([
            'details',
        ])
        ->orderBy('payment_date')
        ->orderBy('id')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Stock Movements
    |--------------------------------------------------------------------------
    */

    $openingMovements = \App\Models\Inventory\InventoryMovement::query()
        ->whereNotNull('reseller_id')
        ->when(
            $resellerId,
            fn ($q) => $q->where('reseller_id', $resellerId)
        )
        ->when(
            $branchId,
            fn ($q) => $q->where('branch_id', $branchId)
        )
        ->when(
            $dateFrom,
            fn ($q) => $q->whereDate(
                'transaction_date',
                '<',
                $dateFrom
            )
        )
        ->with([
            'variant.product',
            'unit',
            'reseller',
            'branch',
        ])
        ->orderBy('transaction_date')
        ->orderBy('id')
        ->get();

    $periodMovements = \App\Models\Inventory\InventoryMovement::query()
        ->whereNotNull('reseller_id')
        ->when(
            $resellerId,
            fn ($q) => $q->where('reseller_id', $resellerId)
        )
        ->when(
            $branchId,
            fn ($q) => $q->where('branch_id', $branchId)
        )
        ->when(
            $dateFrom,
            fn ($q) => $q->whereDate(
                'transaction_date',
                '>=',
                $dateFrom
            )
        )
        ->when(
            $dateTo,
            fn ($q) => $q->whereDate(
                'transaction_date',
                '<=',
                $dateTo
            )
        )
        ->with([
            'variant.product',
            'unit',
            'reseller',
            'branch',
        ])
        ->orderBy('transaction_date')
        ->orderBy('id')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Opening Stock By Product
    |--------------------------------------------------------------------------
    */

    $openingByKey = [];

    foreach ($openingMovements as $movement) {

        $key = implode('|', [
            $movement->reseller_id,
            $movement->branch_id,
            $movement->product_variant_id,
            $movement->unit_id,
        ]);

        $openingByKey[$key] =
            ($openingByKey[$key] ?? 0)
            + (float) $movement->qty_in
            - (float) $movement->qty_out;
    }

    /*
    |--------------------------------------------------------------------------
    | Posted Sold Qty By Product
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Sold is taken from Posted Settlement Detail,
    | NOT from InventoryMovement.
    |--------------------------------------------------------------------------
    */

    $postedSoldByKey = [];

    foreach ($settlements as $settlement) {

        foreach ($settlement->details as $detail) {

            $key = implode('|', [
                $settlement->reseller_id,
                $settlement->branch_id,
                $detail->product_variant_id,
                $detail->unit_id,
            ]);

            $postedSoldByKey[$key] =
                ($postedSoldByKey[$key] ?? 0)
                + (float) $detail->qty_sold;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Stock Position
    |--------------------------------------------------------------------------
    */

    $stockRows = [];

    foreach ($periodMovements as $movement) {

        $key = implode('|', [
            $movement->reseller_id,
            $movement->branch_id,
            $movement->product_variant_id,
            $movement->unit_id,
        ]);

        if (!isset($stockRows[$key])) {

            $stockRows[$key] = [
                'branch_id' => $movement->branch_id,
                'product_variant_id' =>
                    $movement->product_variant_id,
                'unit_id' => $movement->unit_id,

                'product_id' =>
                    $movement->variant?->product_id,

                'product' =>
                    $movement->variant?->product?->name
                    ?? '-',

                'variant' =>
                    $movement->variant?->name
                    ?? $movement->variant?->code
                    ?? null,

                'unit' =>
                    $movement->unit?->name
                    ?? '-',

                'opening' =>
                    (float) (
                        $openingByKey[$key] ?? 0
                    ),

                'consignment' => 0,
                'sold' => 0,
                'return' => 0,
                'other_in' => 0,
                'other_out' => 0,
            ];
        }

        $qtyIn  = (float) $movement->qty_in;
        $qtyOut = (float) $movement->qty_out;

        switch ($movement->reference_type) {

            case 'CONSIGNMENT_OUT':

                $stockRows[$key]['consignment'] += $qtyIn;

                break;

            case 'CONSIGNMENT_RETURN':

                $stockRows[$key]['return'] += $qtyOut;

                break;

            default:

                /*
                |--------------------------------------------------------------------------
                | Do NOT use settlement qty_out as Sold.
                |
                | Sold will be replaced from Posted Settlement Details below.
                |--------------------------------------------------------------------------
                */

                if (
                    !in_array(
                        $movement->reference_type,
                        [
                            'CONSIGNMENT_SETTLEMENT',
                            'SETTLEMENT',
                        ],
                        true
                    )
                ) {
                    $stockRows[$key]['other_in'] += $qtyIn;
                    $stockRows[$key]['other_out'] += $qtyOut;
                }

                break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Add Opening-Only Products
    |--------------------------------------------------------------------------
    */

    foreach ($openingMovements as $movement) {

        $key = implode('|', [
            $movement->reseller_id,
            $movement->branch_id,
            $movement->product_variant_id,
            $movement->unit_id,
        ]);

        if (isset($stockRows[$key])) {
            continue;
        }

        $stockRows[$key] = [
            'branch_id' => $movement->branch_id,
            'product_variant_id' =>
                $movement->product_variant_id,
            'unit_id' => $movement->unit_id,

            'product_id' =>
                $movement->variant?->product_id,

            'product' =>
                $movement->variant?->product?->name
                ?? '-',

            'variant' =>
                $movement->variant?->name
                ?? $movement->variant?->code
                ?? null,

            'unit' =>
                $movement->unit?->name
                ?? '-',

            'opening' =>
                (float) (
                    $openingByKey[$key] ?? 0
                ),

            'consignment' => 0,
            'sold' => 0,
            'return' => 0,
            'other_in' => 0,
            'other_out' => 0,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Posted Settlement Sold Qty
    |--------------------------------------------------------------------------
    */

    foreach ($stockRows as $key => &$row) {

        $row['sold'] =
            (float) (
                $postedSoldByKey[$key] ?? 0
            );
    }

    unset($row);

    /*
    |--------------------------------------------------------------------------
    | Current Nuvora Consignment Price
    |--------------------------------------------------------------------------
    */

    $prices = collect();

    if ($resellerId) {

        $prices = \App\Models\Reseller\ResellerPrice::query()
            ->where('reseller_id', $resellerId)
            ->get()
            ->keyBy('product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Closing + Stock Value
    |--------------------------------------------------------------------------
    */

    $stockRows = collect($stockRows)
        ->map(function ($row) use ($prices) {

            $closing =
                $row['opening']
                + $row['consignment']
                + $row['other_in']
                - $row['sold']
                - $row['return']
                - $row['other_out'];

            $price = 0;

            if (
                $row['product_id']
                && isset($prices[$row['product_id']])
            ) {
                $price =
                    (float) $prices[$row['product_id']]->price;
            }

            $row['closing'] = $closing;
            $row['price'] = $price;

            $row['stock_value'] =
                $closing * $price;

            return $row;
        })
        ->filter(function ($row) {

            return
                abs((float) $row['opening']) > 0.000001
                || abs((float) $row['consignment']) > 0.000001
                || abs((float) $row['sold']) > 0.000001
                || abs((float) $row['return']) > 0.000001
                || abs((float) $row['closing']) > 0.000001;
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Payment By Settlement
    |--------------------------------------------------------------------------
    */

    $receivablePaymentBySettlement = [];

    foreach ($payments as $payment) {

        foreach ($payment->details as $detail) {

            $settlementId =
                $detail->settlement_header_id;

            if (!$settlementId) {
                continue;
            }

            $receivablePaymentBySettlement[$settlementId] =
                ($receivablePaymentBySettlement[$settlementId] ?? 0)
                + (float) $detail->payment_amount;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Sales & Payment By Product
    |--------------------------------------------------------------------------
    */

    $salesRows = [];

    foreach ($settlements as $settlement) {

        /*
        |--------------------------------------------------------------------------
        | Payment directly recorded on Settlement
        |--------------------------------------------------------------------------
        */

        $directPayment =
            (float) $settlement->payment_amount;

        /*
        |--------------------------------------------------------------------------
        | Payment through Receivable
        |--------------------------------------------------------------------------
        */

        $receivablePayment =
            (float) (
                $receivablePaymentBySettlement[
                    $settlement->id
                ] ?? 0
            );

        /*
        |--------------------------------------------------------------------------
        | Total Payment Applied To This Settlement
        |--------------------------------------------------------------------------
        */

        $settlementPayment =
            $directPayment
            + $receivablePayment;

        $detailsTotal =
            (float) $settlement->details->sum(
                fn ($detail) =>
                    (float) $detail->total_amount
            );

        foreach ($settlement->details as $detail) {

            $productId =
                $detail->variant?->product_id;

            $salesAmount =
                (float) $detail->total_amount;

            /*
            |--------------------------------------------------------------------------
            | Allocate Payment Proportionally
            |--------------------------------------------------------------------------
            */

            $allocatedPayment = 0;

            if (
                $detailsTotal > 0
                && $settlementPayment > 0
            ) {
                $allocatedPayment =
                    $settlementPayment
                    * (
                        $salesAmount
                        / $detailsTotal
                    );
            }

            $key = implode('|', [
                $productId ?? 0,
                $detail->product_variant_id ?? 0,
                $detail->unit_id ?? 0,
            ]);

            if (!isset($salesRows[$key])) {

                $salesRows[$key] = [
                    'product_id' =>
                        $productId,

                    'product' =>
                        $detail->variant?->product?->name
                        ?? '-',

                    'variant' =>
                        $detail->variant?->name
                        ?? $detail->variant?->code
                        ?? null,

                    'unit' =>
                        $detail->unit?->name
                        ?? '-',

                    'sold' => 0,

                    'price' =>
                        (float) $detail->unit_price,

                    'sales_total' => 0,

                    'payment' => 0,

                    'outstanding' => 0,
                ];
            }

            $salesRows[$key]['sold'] +=
                (float) $detail->qty_sold;

            $salesRows[$key]['sales_total'] +=
                $salesAmount;

            $salesRows[$key]['payment'] +=
                $allocatedPayment;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Final Sales Rows
    |--------------------------------------------------------------------------
    */

    $salesRows = collect($salesRows)
        ->map(function ($row) {

            $row['outstanding'] =
                max(
                    0,
                    $row['sales_total']
                    - $row['payment']
                );

            return $row;
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    $currentStock =
        (float) $stockRows->sum('closing');

    $stockValue =
        (float) $stockRows->sum('stock_value');

    $sold =
        (float) $salesRows->sum('sold');

    $sales =
        (float) $salesRows->sum('sales_total');

    $payment =
        (float) $salesRows->sum('payment');

    $outstanding =
        max(
            0,
            $sales - $payment
        );

    /*
    |--------------------------------------------------------------------------
    | Reseller / Branch
    |--------------------------------------------------------------------------
    */

    $reseller = $resellerId
        ? \App\Models\Reseller\Reseller::find(
            $resellerId
        )
        : null;

    $branch = $branchId
        ? \App\Models\MasterData\Branch::find(
            $branchId
        )
        : null;

    /*
    |--------------------------------------------------------------------------
    | Final Print Data
    |--------------------------------------------------------------------------
    */

    return [

        'reseller' => $reseller
            ? [
                'id' =>
                    $reseller->id,

                'name' =>
                    $reseller->name,

                'code' =>
                    $reseller->reseller_code,
            ]
            : null,

        'branch' => $branch
            ? [
                'id' =>
                    $branch->id,

                'name' =>
                    $branch->name,
            ]
            : null,

        'filters' => [
            'date_from' =>
                $dateFrom,

            'date_to' =>
                $dateTo,

            'reseller_id' =>
                $resellerId,

            'branch_id' =>
                $branchId,
        ],

        'summary' => [

            'current_stock' =>
                $currentStock,

            'stock_value' =>
                $stockValue,

            'sold' =>
                $sold,

            'sales' =>
                $sales,

            'payment' =>
                $payment,

            'outstanding' =>
                $outstanding,
        ],

        'stock' =>
            $stockRows->values()->all(),

        'sales' =>
            $salesRows->values()->all(),
    ];
}
}