<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryMovement;
use Illuminate\Support\Collection;

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

        $baseQuery =
            InventoryMovement::query()
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

$openingQuery =
    clone $baseQuery;


if ($dateFrom) {

    $openingQuery->whereDate(
        'transaction_date',
        '<',
        $dateFrom
    );

}


$openingMovements =
    $openingQuery
        ->orderBy(
            'transaction_date'
        )
        ->orderBy(
            'id'
        )
        ->get();


$openingQtyIn =
    $openingMovements->sum(
        fn ($movement) =>
            (float)
            $movement->qty_in
    );


$openingQtyOut =
    $openingMovements->sum(
        fn ($movement) =>
            (float)
            $movement->qty_out
    );


/*
|--------------------------------------------------------------------------
| Opening Balance
|--------------------------------------------------------------------------
*/

$openingBalance =
    $dateFrom
        ? (
            $openingQtyIn
            -
            $openingQtyOut
        )
        : 0;


/*
|--------------------------------------------------------------------------
| Current Period Movements
|--------------------------------------------------------------------------
*/

$movementQuery =
    clone $baseQuery;


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


$movements =
    $movementQuery
        ->orderBy(
            'transaction_date'
        )
        ->orderBy(
            'id'
        )
        ->get();


/*
|--------------------------------------------------------------------------
| Build Rows
|--------------------------------------------------------------------------
*/

$balance =
    $openingBalance;


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
            0,

        'qty_in' =>
            0,

        'qty_out' =>
            0,

        'balance_qty' =>
            $balance,

        'unit_id' =>
            $unitId,

        'unit_cost' =>
            0,

        'total_cost' =>
            0,

    ]);

}


/*
|--------------------------------------------------------------------------
| Movement Rows
|--------------------------------------------------------------------------
*/

foreach (
    $movements
    as $movement
) {

    $openingQty =
        $balance;


    $qtyIn =
        (float)
        $movement->qty_in;


    $qtyOut =
        (float)
        $movement->qty_out;


    $balance =
        $openingQty
        +
        $qtyIn
        -
        $qtyOut;


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
            (float)
            $movement->unit_cost,

        'total_cost' =>
            (float)
            $movement->total_cost,

    ]);

}

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [

            'opening_qty' =>
                $openingBalance,

            'total_qty_in' =>
                $movements->sum(
                    fn ($movement) =>
                        (float)
                        $movement->qty_in
                ),

            'total_qty_out' =>
                $movements->sum(
                    fn ($movement) =>
                        (float)
                        $movement->qty_out
                ),

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
            | Jadi urutan tampilan harus sama dengan urutan
            | perhitungan balance.
            |
            */

            $sortedRows =
                $rows->values();
        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage =
            max(
                1,
                (int) $perPage
            );


        $page =
            max(
                1,
                (int) $page
            );


        $total =
            $sortedRows->count();


        $lastPage =
            max(
                1,
                (int) ceil(
                    $total / $perPage
                )
            );


        $page =
            min(
                $page,
                $lastPage
            );


        $items =
            $sortedRows
                ->slice(
                    (
                        $page - 1
                    )
                    *
                    $perPage,
                    $perPage
                )
                ->values();


        $from =
            $total > 0
                ? (
                    (
                        $page - 1
                    )
                    *
                    $perPage
                )
                + 1
                : null;


        $to =
            $total > 0
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