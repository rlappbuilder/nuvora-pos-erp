<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryMovement;
use App\Models\Product\ProductVariantUnit;
use Illuminate\Support\Collection;

class InventoryCostingService
{
    /*
    |--------------------------------------------------------------------------
    | FIFO
    |--------------------------------------------------------------------------
    */

    public function calculateFifo(
        int $productVariantId,
        int $branchId,
        int $warehouseId,
        int $unitId,
        ?string $dateTo = null
    ): array {

        return $this->calculateLayers(
            productVariantId: $productVariantId,
            branchId: $branchId,
            warehouseId: $warehouseId,
            unitId: $unitId,
            dateTo: $dateTo,
            method: 'FIFO'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | LIFO
    |--------------------------------------------------------------------------
    */

    public function calculateLifo(
        int $productVariantId,
        int $branchId,
        int $warehouseId,
        int $unitId,
        ?string $dateTo = null
    ): array {

        return $this->calculateLayers(
            productVariantId: $productVariantId,
            branchId: $branchId,
            warehouseId: $warehouseId,
            unitId: $unitId,
            dateTo: $dateTo,
            method: 'LIFO'
        );

    }
            /*
            |--------------------------------------------------------------------------
            | Weighted Average
            |--------------------------------------------------------------------------
            */

            public function calculateWeightedAverage(
                int $productVariantId,
                int $branchId,
                int $warehouseId,
                int $unitId,
                ?string $dateTo = null
            ): array {

                /*
                |--------------------------------------------------------------------------
                | Movements
                |--------------------------------------------------------------------------
                */

                $movements =
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
                        )
                        ->orderBy(
                            'transaction_date'
                        )
                        ->orderBy(
                            'id'
                        )
                        ->get();


                /*
                |--------------------------------------------------------------------------
                | Running Balance
                |--------------------------------------------------------------------------
                */

                $onHand =
                    0;

                $stockValue =
                    0;

                $totalCogs =
                    0;


                /*
                |--------------------------------------------------------------------------
                | Process Movements
                |--------------------------------------------------------------------------
                */

                foreach (
                    $movements
                    as $movement
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Conversion
                    |--------------------------------------------------------------------------
                    */

                    $conversionFactor =
                        $this->getConversionFactor(
                            $movement->product_variant_id,
                            $movement->unit_id
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Quantity
                    |--------------------------------------------------------------------------
                    */

                    $qtyIn =
                        (
                            (float)
                            $movement->qty_in
                        )
                        *
                        $conversionFactor;


                    $qtyOut =
                        (
                            (float)
                            $movement->qty_out
                        )
                        *
                        $conversionFactor;


                    /*
                    |--------------------------------------------------------------------------
                    | Cost
                    |--------------------------------------------------------------------------
                    */

                    $unitCost =
                        (float)
                        $movement->unit_cost;


                    $unitCostBase =
                        $conversionFactor > 0
                            ? $unitCost / $conversionFactor
                            : $unitCost;


                    /*
                    |--------------------------------------------------------------------------
                    | Average Cost Before Movement
                    |--------------------------------------------------------------------------
                    */

                    $averageCostBefore =
                        $onHand > 0
                            ? $stockValue / $onHand
                            : 0;


                    /*
                    |--------------------------------------------------------------------------
                    | IN
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $qtyIn > 0
                    ) {

                        $stockValue +=
                            $qtyIn
                            *
                            $unitCostBase;


                        $onHand +=
                            $qtyIn;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | OUT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $qtyOut > 0
                    ) {

                        $outCost =
                            $qtyOut
                            *
                            $averageCostBefore;


                        $totalCogs +=
                            $outCost;


                        $stockValue -=
                            $outCost;


                        $onHand -=
                            $qtyOut;


                        /*
                        |--------------------------------------------------------------------------
                        | Protection
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $onHand < 0
                        ) {

                            $onHand =
                                0;

                        }


                        if (
                            $stockValue < 0
                        ) {

                            $stockValue =
                                0;

                        }

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | Closing Average Cost
                |--------------------------------------------------------------------------
                */

                $averageCost =
                    $onHand > 0
                        ? $stockValue / $onHand
                        : 0;


                /*
                |--------------------------------------------------------------------------
                | Return
                |--------------------------------------------------------------------------
                */

                return [

                    'method' =>
                        'WEIGHTED_AVERAGE',

                    'on_hand_qty' =>
                        $onHand,

                    'average_cost' =>
                        $averageCost,

                    'stock_value' =>
                        $stockValue,

                    'cogs' =>
                        $totalCogs,

                ];

            }

    /*
    |--------------------------------------------------------------------------
    | Layer Calculation
    |--------------------------------------------------------------------------
    */

    private function calculateLayers(
        int $productVariantId,
        int $branchId,
        int $warehouseId,
        int $unitId,
        ?string $dateTo,
        string $method
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Movements
        |--------------------------------------------------------------------------
        */

        $movements =
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
                )
                ->orderBy(
                    'transaction_date'
                )
                ->orderBy(
                    'id'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Layers
        |--------------------------------------------------------------------------
        */

        $layers =
            collect();


        $totalCogs =
            0;


        /*
        |--------------------------------------------------------------------------
        | Process Movements
        |--------------------------------------------------------------------------
        */

        foreach (
            $movements
            as $movement
        ) {

            /*
            |--------------------------------------------------------------------------
            | Conversion
            |--------------------------------------------------------------------------
            */

            $conversionFactor =
                $this->getConversionFactor(
                    $movement->product_variant_id,
                    $movement->unit_id
                );


            /*
            |--------------------------------------------------------------------------
            | Quantities
            |--------------------------------------------------------------------------
            */

            $qtyIn =
                (float)
                $movement->qty_in;

            $qtyOut =
                (float)
                $movement->qty_out;


            $qtyInBase =
                $qtyIn
                *
                $conversionFactor;


            $qtyOutBase =
                $qtyOut
                *
                $conversionFactor;


            /*
            |--------------------------------------------------------------------------
            | Cost
            |--------------------------------------------------------------------------
            */

            $unitCost =
                (float)
                $movement->unit_cost;


            $unitCostBase =
                $conversionFactor > 0
                    ? $unitCost / $conversionFactor
                    : $unitCost;


            /*
            |--------------------------------------------------------------------------
            | IN → Create Layer
            |--------------------------------------------------------------------------
            */

            if (
                $qtyInBase > 0
            ) {

                $layers->push([

                    'movement_id' =>
                        $movement->id,

                    'date' =>
                        $movement->transaction_date,

                    'unit_id' =>
                        $movement->unit_id,

                    'conversion_factor' =>
                        $conversionFactor,

                    'qty' =>
                        $qtyInBase,

                    'unit_cost' =>
                        $unitCostBase,

                ]);

            }


            /*
            |--------------------------------------------------------------------------
            | OUT → Consume Layer
            |--------------------------------------------------------------------------
            */

            if (
                $qtyOutBase > 0
            ) {

                $remaining =
                    $qtyOutBase;


                while (
                    $remaining > 0
                    &&
                    $layers->isNotEmpty()
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Select Layer
                    |--------------------------------------------------------------------------
                    */

                    $layerIndex =
                        $method === 'FIFO'
                            ? 0
                            : $layers->count() - 1;


                    $layer =
                        $layers->get(
                            $layerIndex
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Consume
                    |--------------------------------------------------------------------------
                    */

                    $consume =
                        min(
                            $remaining,
                            (float)
                            $layer['qty']
                        );


                    $totalCogs +=
                        $consume
                        *
                        (float)
                        $layer['unit_cost'];


                    /*
                    |--------------------------------------------------------------------------
                    | Remaining Layer
                    |--------------------------------------------------------------------------
                    */

                    $layer['qty'] =
                        (float)
                        $layer['qty']
                        -
                        $consume;


                    $remaining -=
                        $consume;


                    /*
                    |--------------------------------------------------------------------------
                    | Update Layer
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $layer['qty']
                        <= 0
                    ) {

                        $layers->forget(
                            $layerIndex
                        );

                    }

                    else {

                        $layers->put(
                            $layerIndex,
                            $layer
                        );

                    }

                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Layer Index
        |--------------------------------------------------------------------------
        */

        $layers =
            $layers
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Closing Stock
        |--------------------------------------------------------------------------
        */

        $onHand =
            $layers->sum(
                fn ($layer) =>
                    (float)
                    $layer['qty']
            );


        /*
        |--------------------------------------------------------------------------
        | Stock Value
        |--------------------------------------------------------------------------
        */

        $stockValue =
            $layers->sum(
                fn ($layer) =>
                    (
                        (float)
                        $layer['qty']
                    )
                    *
                    (
                        (float)
                        $layer['unit_cost']
                    )
            );


        /*
        |--------------------------------------------------------------------------
        | Average Cost
        |--------------------------------------------------------------------------
        */

        $averageCost =
            $onHand > 0
                ? $stockValue / $onHand
                : 0;


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [

            'method' =>
                $method,

            'on_hand_qty' =>
                $onHand,

            'average_cost' =>
                $averageCost,

            'stock_value' =>
                $stockValue,

            'cogs' =>
                $totalCogs,

            'layers' =>
                $layers->all(),

        ];

    }
/*
|--------------------------------------------------------------------------
| Calculate
|--------------------------------------------------------------------------
*/

public function calculate(
    int $productVariantId,
    int $branchId,
    int $warehouseId,
    int $unitId,
    string $method = 'WEIGHTED_AVERAGE',
    ?string $dateTo = null
): array {

    $method =
        strtoupper(
            trim($method)
        );


    return match ($method) {

        'FIFO' =>
            $this->calculateFifo(
                productVariantId: $productVariantId,
                branchId: $branchId,
                warehouseId: $warehouseId,
                unitId: $unitId,
                dateTo: $dateTo
            ),

        'LIFO' =>
            $this->calculateLifo(
                productVariantId: $productVariantId,
                branchId: $branchId,
                warehouseId: $warehouseId,
                unitId: $unitId,
                dateTo: $dateTo
            ),

        'WEIGHTED_AVERAGE',
        'WEIGHTED AVERAGE',
        'AVERAGE',
        'WA' =>
            $this->calculateWeightedAverage(
                productVariantId: $productVariantId,
                branchId: $branchId,
                warehouseId: $warehouseId,
                unitId: $unitId,
                dateTo: $dateTo
            ),

        default =>
            throw new \InvalidArgumentException(
                "Unsupported inventory costing method: {$method}"
            ),

    };

}

    /*
    |--------------------------------------------------------------------------
    | Conversion Factor
    |--------------------------------------------------------------------------
    */

    private function getConversionFactor(
        int $productVariantId,
        int $unitId
    ): float {

        $variantUnit =
            ProductVariantUnit::query()
                ->where(
                    'product_variant_id',
                    $productVariantId
                )
                ->where(
                    'unit_id',
                    $unitId
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();


        return (float) (
            $variantUnit?->conversion_factor
            ?? 1
        );

    }

}