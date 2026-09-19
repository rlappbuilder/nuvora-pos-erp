<?php

namespace App\Services\Consignment;

use App\Models\Reseller\ResellerStockPriceLayer;
use Illuminate\Support\Collection;
//use Illuminate\Support\Facades\DB;
use RuntimeException;

class ResellerStockPriceLayerService
{
    /**
     * Allocate reseller stock price layers using FIFO.
     *
     * Returns the allocated layers with quantities and values.
     */
    public function allocate(
        int $companyId,
        int $branchId,
        int $warehouseId,
        int $resellerId,
        int $productVariantId,
        int $unitId,
        float $qty
    ): Collection {

        if ($qty <= 0) {
            throw new RuntimeException(
                'Allocation quantity must be greater than zero.'
            );
        }

        $remainingQty = $qty;

        $layers = ResellerStockPriceLayer::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('warehouse_id', $warehouseId)
            ->where('reseller_id', $resellerId)
            ->where('product_variant_id', $productVariantId)
            ->where('unit_id', $unitId)
            ->where('status', 'Open')
            ->where('remaining_qty', '>', 0)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($layers->isEmpty()) {
            throw new RuntimeException(
                'No available reseller stock price layer found.'
            );
        }

        $allocations = collect();

        foreach ($layers as $layer) {

            if ($remainingQty <= 0) {
                break;
            }

            $availableQty = (float) $layer->remaining_qty;

            $allocatedQty = min(
                $remainingQty,
                $availableQty
            );

            if ($allocatedQty <= 0) {
                continue;
            }

            $unitPrice = (float) $layer->unit_price;

            $allocatedValue =
                $allocatedQty * $unitPrice;

            $newRemainingQty =
                $availableQty - $allocatedQty;

            $layer->remaining_qty =
                round($newRemainingQty, 6);

            $layer->status =
                $newRemainingQty <= 0
                    ? 'Exhausted'
                    : 'Open';

            $layer->save();

            $allocations->push([
                'layer_id' =>
                    $layer->id,

                'consignment_out_id' =>
                    $layer->consignment_out_id,

                'consignment_out_detail_id' =>
                    $layer->consignment_out_detail_id,

                'unit_price' =>
                    round($unitPrice, 2),

                'qty' =>
                    round($allocatedQty, 6),

                'total_value' =>
                    round($allocatedValue, 2),
            ]);

            $remainingQty -= $allocatedQty;
        }

        if ($remainingQty > 0.000001) {

            throw new RuntimeException(
                'Insufficient reseller price layer stock for the requested quantity.'
            );
        }

        return $allocations;
    }
}