<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryMovement;

class StockCardService
{
    /*
    |--------------------------------------------------------------------------
    | Get Stock Card
    |--------------------------------------------------------------------------
    */

    public function getStockCard(
        int $productVariantId,
        int $branchId,
        int $warehouseId,
        int $unitId,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $sortBy = 'date',
        ?string $sortDirection = 'desc',
        int $perPage = 25,
        int $page = 1
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Base Movement Query
        |--------------------------------------------------------------------------
        */

        $baseQuery = InventoryMovement::query()
            ->where(
                'product_variant_id',
                $productVariantId
            )
            ->where(
                'branch_id',
                $branchId
            )
            ->where(
                'warehouse_id',
                $warehouseId
            )
            ->where(
                'unit_id',
                $unitId
            );

        /*
        |--------------------------------------------------------------------------
        | Opening Balance
        |--------------------------------------------------------------------------
        */

        $openingQuery = clone $baseQuery;

        if ($dateFrom) {
            $openingQuery->whereDate(
                'transaction_date',
                '<',
                $dateFrom
            );
        }

        $openingMovements = $openingQuery
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $openingQtyIn = '0.000000';
        $openingQtyOut = '0.000000';

        foreach ($openingMovements as $movement) {

            $openingQtyIn = bcadd(
                $openingQtyIn,
                (string) ($movement->qty_in ?? '0'),
                6
            );

            $openingQtyOut = bcadd(
                $openingQtyOut,
                (string) ($movement->qty_out ?? '0'),
                6
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Opening Balance
        |--------------------------------------------------------------------------
        */

        $openingBalance = $dateFrom
            ? bcsub(
                $openingQtyIn,
                $openingQtyOut,
                6
            )
            : '0.000000';

        /*
        |--------------------------------------------------------------------------
        | Current Period Movements
        |--------------------------------------------------------------------------
        */

        $movementQuery = clone $baseQuery;

        $movementQuery
            ->when(
                $dateFrom,
                function ($query) use ($dateFrom) {

                    $query->whereDate(
                        'transaction_date',
                        '>=',
                        $dateFrom
                    );

                }
            )
            ->when(
                $dateTo,
                function ($query) use ($dateTo) {

                    $query->whereDate(
                        'transaction_date',
                        '<=',
                        $dateTo
                    );

                }
            );

        $movements = $movementQuery
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Build Rows
        |--------------------------------------------------------------------------
        */

        $balance = $openingBalance;

        $rows = collect();

        /*
        |--------------------------------------------------------------------------
        | Opening Row
        |--------------------------------------------------------------------------
        */

        if ($dateFrom) {

            $rows->push([

                'id' =>
                    null,

                'date' =>
                    $dateFrom,

                'reference_type' =>
                    'OPENING_BALANCE',

                'reference_number' =>
                    null,

                'description' =>
                    'Saldo awal',

                'opening_qty' =>
                    '0.000000',

                'qty_in' =>
                    '0.000000',

                'qty_out' =>
                    '0.000000',

                'balance_qty' =>
                    $balance,

                'unit_id' =>
                    $unitId,

                'unit_cost' =>
                    '0.000000',

                'total_cost' =>
                    '0.00',

            ]);

        }

        /*
        |--------------------------------------------------------------------------
        | Movement Rows
        |--------------------------------------------------------------------------
        */

        foreach ($movements as $movement) {

            $openingQty = $balance;

            $qtyIn = bcadd(
                (string) ($movement->qty_in ?? '0'),
                '0',
                6
            );

            $qtyOut = bcadd(
                (string) ($movement->qty_out ?? '0'),
                '0',
                6
            );

            $balance = bcadd(
                bcsub(
                    $openingQty,
                    $qtyOut,
                    6
                ),
                $qtyIn,
                6
            );

            $rows->push([

                'id' =>
                    $movement->id,

                'date' =>
                    $movement->transaction_date
                        ?->format('Y-m-d'),

                'reference_type' =>
                    $movement->reference_type,

                'reference_number' =>
                    $movement->reference_number,

                'description' =>
                    $movement->description,

                'opening_qty' =>
                    $openingQty,

                'qty_in' =>
                    $qtyIn,

                'qty_out' =>
                    $qtyOut,

                'balance_qty' =>
                    $balance,

                'unit_id' =>
                    $movement->unit_id,

                'unit_cost' =>
                    bcadd(
                        (string) ($movement->unit_cost ?? '0'),
                        '0',
                        6
                    ),

                'total_cost' =>
                    bcadd(
                        (string) ($movement->total_cost ?? '0'),
                        '0',
                        2
                    ),

            ]);

        }

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $totalQtyIn = '0.000000';
        $totalQtyOut = '0.000000';

        foreach ($movements as $movement) {

            $totalQtyIn = bcadd(
                $totalQtyIn,
                (string) ($movement->qty_in ?? '0'),
                6
            );

            $totalQtyOut = bcadd(
                $totalQtyOut,
                (string) ($movement->qty_out ?? '0'),
                6
            );

        }

        $summary = [

            'opening_qty' =>
                $openingBalance,

            'total_qty_in' =>
                $totalQtyIn,

            'total_qty_out' =>
                $totalQtyOut,

            'closing_qty' =>
                $balance,

        ];

        /*
        |--------------------------------------------------------------------------
        | Display Order
        |--------------------------------------------------------------------------
        |
        | Stock card selalu ditampilkan chronological.
        |
        | Running balance dihitung berdasarkan:
        |
        | transaction_date ASC
        | id ASC
        |
        | Parameter sortBy dan sortDirection tetap dipertahankan
        | untuk kompatibilitas pemanggil method.
        |
        */

        $sortedRows = $rows->values();

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = max(
            1,
            (int) $perPage
        );

        $page = max(
            1,
            (int) $page
        );

        $total = $sortedRows->count();

        $lastPage = max(
            1,
            (int) ceil(
                $total / $perPage
            )
        );

        $page = min(
            $page,
            $lastPage
        );

        $items = $sortedRows
            ->slice(
                ($page - 1) * $perPage,
                $perPage
            )
            ->values();

        $from = $total > 0
            ? (($page - 1) * $perPage) + 1
            : null;

        $to = $total > 0
            ? $from + $items->count() - 1
            : null;

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [

            'summary' =>
                $summary,

            'data' =>
                $items->all(),

            'current_page' =>
                $page,

            'last_page' =>
                $lastPage,

            'per_page' =>
                $perPage,

            'total' =>
                $total,

            'from' =>
                $from,

            'to' =>
                $to,

        ];

    }
}