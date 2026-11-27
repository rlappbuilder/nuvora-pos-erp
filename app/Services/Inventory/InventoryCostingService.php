<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryMovement;
use App\Models\Product\ProductVariantUnit;
use RuntimeException;

class InventoryCostingService
{
    private const QTY_SCALE = 6;
    private const COST_SCALE = 6;
    private const VALUE_SCALE = 12;
    private const MONEY_SCALE = 2;

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

        $movements = InventoryMovement::query()
            ->where('product_variant_id', $productVariantId)
            ->where('branch_id', $branchId)
            ->where('warehouse_id', $warehouseId)
            ->where('unit_id', $unitId)
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate(
                    'transaction_date',
                    '<=',
                    $dateTo
                );
            })
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Running Balance
        |--------------------------------------------------------------------------
        */

        $onHand = '0.000000';
        $stockValue = '0';
        $totalCogs = '0';

        /*
        |--------------------------------------------------------------------------
        | Process Movements
        |--------------------------------------------------------------------------
        */

        foreach ($movements as $movement) {

            /*
            |--------------------------------------------------------------------------
            | Conversion
            |--------------------------------------------------------------------------
            */

            $conversionFactor = $this->getConversionFactor(
                (int) $movement->product_variant_id,
                (int) $movement->unit_id
            );

            /*
            |--------------------------------------------------------------------------
            | Quantities
            |--------------------------------------------------------------------------
            */

            $qtyIn = bcmul(
                $this->decimal($movement->qty_in, self::QTY_SCALE),
                $conversionFactor,
                self::QTY_SCALE
            );

            $qtyOut = bcmul(
                $this->decimal($movement->qty_out, self::QTY_SCALE),
                $conversionFactor,
                self::QTY_SCALE
            );

            /*
            |--------------------------------------------------------------------------
            | Cost
            |--------------------------------------------------------------------------
            */

            $unitCost = $this->decimal(
                $movement->unit_cost,
                self::COST_SCALE
            );

            $unitCostBase = bcdiv(
                $unitCost,
                $conversionFactor,
                self::COST_SCALE
            );

            /*
            |--------------------------------------------------------------------------
            | Average Cost Before Movement
            |--------------------------------------------------------------------------
            */

            $averageCostBefore = bccomp(
                $onHand,
                '0',
                self::QTY_SCALE
            ) > 0
                ? bcdiv(
                    $stockValue,
                    $onHand,
                    self::COST_SCALE
                )
                : '0.000000';

            /*
            |--------------------------------------------------------------------------
            | IN
            |--------------------------------------------------------------------------
            */

            if (bccomp($qtyIn, '0', self::QTY_SCALE) > 0) {

                $incomingValue = bcmul(
                    $qtyIn,
                    $unitCostBase,
                    self::VALUE_SCALE
                );

                $stockValue = bcadd(
                    $stockValue,
                    $incomingValue,
                    self::VALUE_SCALE
                );

                $onHand = bcadd(
                    $onHand,
                    $qtyIn,
                    self::QTY_SCALE
                );
            }

            /*
            |--------------------------------------------------------------------------
            | OUT
            |--------------------------------------------------------------------------
            */

            if (bccomp($qtyOut, '0', self::QTY_SCALE) > 0) {

                if (
                    bccomp(
                        $onHand,
                        $qtyOut,
                        self::QTY_SCALE
                    ) < 0
                ) {
                    throw new RuntimeException(
                        "Insufficient inventory quantity for Weighted Average. "
                        . "Movement ID: {$movement->id}."
                    );
                }

                $outCost = bcmul(
                    $qtyOut,
                    $averageCostBefore,
                    self::VALUE_SCALE
                );

                $totalCogs = bcadd(
                    $totalCogs,
                    $outCost,
                    self::VALUE_SCALE
                );

                $stockValue = bcsub(
                    $stockValue,
                    $outCost,
                    self::VALUE_SCALE
                );

                $onHand = bcsub(
                    $onHand,
                    $qtyOut,
                    self::QTY_SCALE
                );

                if (
                    bccomp(
                        $stockValue,
                        '0',
                        self::VALUE_SCALE
                    ) < 0
                ) {
                    throw new RuntimeException(
                        "Negative stock value detected in Weighted Average. "
                        . "Movement ID: {$movement->id}."
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Closing Average Cost
        |--------------------------------------------------------------------------
        */

        $averageCost = bccomp(
            $onHand,
            '0',
            self::QTY_SCALE
        ) > 0
            ? bcdiv(
                $stockValue,
                $onHand,
                self::COST_SCALE
            )
            : '0.000000';

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [

            'method' => 'WEIGHTED_AVERAGE',

            'on_hand_qty' => $onHand,

            'average_cost' => $averageCost,

            'stock_value' => $this->roundBc(
                $stockValue,
                self::MONEY_SCALE
            ),

            'cogs' => $this->roundBc(
                $totalCogs,
                self::MONEY_SCALE
            ),

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

        $movements = InventoryMovement::query()
            ->where('product_variant_id', $productVariantId)
            ->where('branch_id', $branchId)
            ->where('warehouse_id', $warehouseId)
            ->where('unit_id', $unitId)
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate(
                    'transaction_date',
                    '<=',
                    $dateTo
                );
            })
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Layers
        |--------------------------------------------------------------------------
        */

        $layers = collect();

        $totalCogs = '0';

        /*
        |--------------------------------------------------------------------------
        | Process Movements
        |--------------------------------------------------------------------------
        */

        foreach ($movements as $movement) {

            /*
            |--------------------------------------------------------------------------
            | Conversion
            |--------------------------------------------------------------------------
            */

            $conversionFactor = $this->getConversionFactor(
                (int) $movement->product_variant_id,
                (int) $movement->unit_id
            );

            /*
            |--------------------------------------------------------------------------
            | Quantities
            |--------------------------------------------------------------------------
            */

            $qtyIn = $this->decimal(
                $movement->qty_in,
                self::QTY_SCALE
            );

            $qtyOut = $this->decimal(
                $movement->qty_out,
                self::QTY_SCALE
            );

            $qtyInBase = bcmul(
                $qtyIn,
                $conversionFactor,
                self::QTY_SCALE
            );

            $qtyOutBase = bcmul(
                $qtyOut,
                $conversionFactor,
                self::QTY_SCALE
            );

            /*
            |--------------------------------------------------------------------------
            | Cost
            |--------------------------------------------------------------------------
            */

            $unitCost = $this->decimal(
                $movement->unit_cost,
                self::COST_SCALE
            );

            $unitCostBase = bcdiv(
                $unitCost,
                $conversionFactor,
                self::COST_SCALE
            );

            /*
            |--------------------------------------------------------------------------
            | IN → Create Layer
            |--------------------------------------------------------------------------
            */

            if (bccomp($qtyInBase, '0', self::QTY_SCALE) > 0) {

                $layers->push([

                    'movement_id' => $movement->id,

                    'date' => $movement->transaction_date,

                    'unit_id' => $movement->unit_id,

                    'conversion_factor' => $conversionFactor,

                    'qty' => $qtyInBase,

                    'unit_cost' => $unitCostBase,

                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | OUT → Consume Layer
            |--------------------------------------------------------------------------
            */

            if (bccomp($qtyOutBase, '0', self::QTY_SCALE) > 0) {

                $remaining = $qtyOutBase;

                while (
                    bccomp(
                        $remaining,
                        '0',
                        self::QTY_SCALE
                    ) > 0
                ) {

                    if ($layers->isEmpty()) {

                        throw new RuntimeException(
                            "Insufficient costing layers for {$method}. "
                            . "Movement ID: {$movement->id}."
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Select Layer
                    |--------------------------------------------------------------------------
                    */

                  $layerIndex = $method === 'FIFO'
                    ? $layers->keys()->first()
                    : $layers->keys()->last();

                    $layer = $layers->get($layerIndex);

                    if (
                        !is_array($layer)
                        || !isset($layer['qty'], $layer['unit_cost'])
                    ) {
                        throw new RuntimeException(
                            "Invalid costing layer detected. Movement ID: {$movement->id}."
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Consume Quantity
                    |--------------------------------------------------------------------------
                    */

                    $consume = bccomp(
                        $remaining,
                        $layer['qty'],
                        self::QTY_SCALE
                    ) <= 0
                        ? $remaining
                        : $layer['qty'];

                    /*
                    |--------------------------------------------------------------------------
                    | Calculate COGS
                    |--------------------------------------------------------------------------
                    */

                    $outCost = bcmul(
                        $consume,
                        $layer['unit_cost'],
                        self::VALUE_SCALE
                    );

                    $totalCogs = bcadd(
                        $totalCogs,
                        $outCost,
                        self::VALUE_SCALE
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Update Remaining Quantity
                    |--------------------------------------------------------------------------
                    */

                    $layer['qty'] = bcsub(
                        $layer['qty'],
                        $consume,
                        self::QTY_SCALE
                    );

                    $remaining = bcsub(
                        $remaining,
                        $consume,
                        self::QTY_SCALE
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Update Layer
                    |--------------------------------------------------------------------------
                    */

                    if (
                        bccomp(
                            $layer['qty'],
                            '0',
                            self::QTY_SCALE
                        ) === 0
                    ) {

                        $layers->forget($layerIndex);

                    } else {

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

        $layers = $layers->values();

        /*
        |--------------------------------------------------------------------------
        | Closing Stock & Stock Value
        |--------------------------------------------------------------------------
        */

        $onHand = '0.000000';

        $stockValue = '0';

        foreach ($layers as $layer) {

            $onHand = bcadd(
                $onHand,
                $layer['qty'],
                self::QTY_SCALE
            );

            $layerValue = bcmul(
                $layer['qty'],
                $layer['unit_cost'],
                self::VALUE_SCALE
            );

            $stockValue = bcadd(
                $stockValue,
                $layerValue,
                self::VALUE_SCALE
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Average Cost
        |--------------------------------------------------------------------------
        */

        $averageCost = bccomp(
            $onHand,
            '0',
            self::QTY_SCALE
        ) > 0
            ? bcdiv(
                $stockValue,
                $onHand,
                self::COST_SCALE
            )
            : '0.000000';

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [

            'method' => $method,

            'on_hand_qty' => $onHand,

            'average_cost' => $averageCost,

            'stock_value' => $this->roundBc(
                $stockValue,
                self::MONEY_SCALE
            ),

            'cogs' => $this->roundBc(
                $totalCogs,
                self::MONEY_SCALE
            ),

            'layers' => $layers->all(),

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

        $method = strtoupper(trim($method));

        return match ($method) {

            'FIFO' => $this->calculateFifo(
                productVariantId: $productVariantId,
                branchId: $branchId,
                warehouseId: $warehouseId,
                unitId: $unitId,
                dateTo: $dateTo
            ),

            'LIFO' => $this->calculateLifo(
                productVariantId: $productVariantId,
                branchId: $branchId,
                warehouseId: $warehouseId,
                unitId: $unitId,
                dateTo: $dateTo
            ),

            'WEIGHTED_AVERAGE',
            'WEIGHTED AVERAGE',
            'AVERAGE',
            'WA' => $this->calculateWeightedAverage(
                productVariantId: $productVariantId,
                branchId: $branchId,
                warehouseId: $warehouseId,
                unitId: $unitId,
                dateTo: $dateTo
            ),

            default => throw new \InvalidArgumentException(
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
    ): string {

        $variantUnit = ProductVariantUnit::query()
            ->where('product_variant_id', $productVariantId)
            ->where('unit_id', $unitId)
            ->where('is_active', true)
            ->first();

        $conversionFactor = $this->decimal(
            $variantUnit?->conversion_factor ?? '1',
            self::QTY_SCALE
        );

        if (
            bccomp(
                $conversionFactor,
                '0',
                self::QTY_SCALE
            ) <= 0
        ) {
            throw new RuntimeException(
                "Invalid conversion factor for Product Variant ID: "
                . "{$productVariantId}, Unit ID: {$unitId}."
            );
        }

        return $conversionFactor;
    }

    /*
    |--------------------------------------------------------------------------
    | Decimal Normalization
    |--------------------------------------------------------------------------
    */

    private function decimal(
        mixed $value,
        int $scale
    ): string {

        return bcadd(
            (string) ($value ?? '0'),
            '0',
            $scale
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BCMath Rounding
    |--------------------------------------------------------------------------
    */

    private function roundBc(
        string $value,
        int $scale = 2
    ): string {

        $negative = str_starts_with($value, '-');

        $absoluteValue = $negative
            ? substr($value, 1)
            : $value;

        $truncated = bcadd(
            $absoluteValue,
            '0',
            $scale + 1
        );

        [$integer, $fraction] = array_pad(
            explode('.', $truncated, 2),
            2,
            ''
        );

        $fraction = str_pad(
            $fraction,
            $scale + 1,
            '0'
        );

        $result = $integer . '.' . substr(
            $fraction,
            0,
            $scale
        );

        if ((int) $fraction[$scale] >= 5) {

            $increment = '0.'
                . str_repeat('0', max(0, $scale - 1))
                . '1';

            $result = bcadd(
                $result,
                $increment,
                $scale
            );
        }

        return $negative
            && bccomp($result, '0', $scale) !== 0
                ? '-' . $result
                : $result;
    }
}