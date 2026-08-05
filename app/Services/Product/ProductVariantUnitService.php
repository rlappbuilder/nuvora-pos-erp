<?php

namespace App\Services\Product;

use App\Models\Product\ProductVariantUnit;
use App\Models\MasterData\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductVariantUnitService
{
    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
    array $data
): ProductVariantUnit
{
    return DB::transaction(function () use ($data) {

        $this->validateDuplicateUnit($data);

        if ($data['is_base']) {

            $this->resetBaseUnit(
                $data['product_variant_id']
            );

            $data['conversion_factor'] = 1;

        }

        if ($data['is_default']) {

            $this->resetDefaultUnit(
                $data['product_variant_id']
            );

        }

        return ProductVariantUnit::create($data);

    });
}

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        ProductVariantUnit $variantUnit,
        array $data
    ): ProductVariantUnit
    {
        return DB::transaction(function () use ($variantUnit, $data) {

            $this->validateDuplicateUnit(
                $data,
                $variantUnit->id
            );

            if ($data['is_base']) {

                $this->resetBaseUnit(
                    $data['product_variant_id']
                );

                $data['conversion_factor'] = 1;

            }

            if ($data['is_default']) {

                $this->resetDefaultUnit(
                    $data['product_variant_id']
                );

            }
            if (
                !$data['is_active']
            ) {

                $this->ensureCanDeactivate(
                    $variantUnit
                );

            }
            $variantUnit->update($data);

            return $variantUnit;

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ProductVariantUnit $variantUnit
    ): void
    {
        if ($variantUnit->is_base) {

            throw new \Exception(
                'Base Unit cannot be deleted.'
            );

        }

        if ($variantUnit->is_default) {

            throw new \Exception(
                'Default Unit cannot be deleted.'
            );

        }

        $variantUnit->delete();
    }
    public function bulkDelete(
            array $ids
        ): void
        {
            DB::transaction(function () use ($ids) {

                $variantUnits = ProductVariantUnit::query()
                    ->whereIn('id', $ids)
                    ->get();

                foreach ($variantUnits as $variantUnit) {

                    $this->destroy($variantUnit);

                }

            });
        }
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function validateDuplicateUnit(
        array $data,
        ?int $ignoreId = null
    ): void
    {
        $exists = ProductVariantUnit::query()

            ->where(
                'product_variant_id',
                $data['product_variant_id']
            )

            ->where(
                'unit_id',
                $data['unit_id']
            )

            ->when(
                $ignoreId,
                fn ($query) => $query->where(
                    'id',
                    '!=',
                    $ignoreId
                )
            )

            ->exists();

        if ($exists) {

            throw new \Exception(
                'Unit already exists for this Product Variant.'
            );

        }
    }

    protected function resetBaseUnit(
        int $variantId
    ): void
    {
        ProductVariantUnit::query()

            ->where(
                'product_variant_id',
                $variantId
            )

            ->update([

                'is_base' => false,

            ]);
    }

    protected function resetDefaultUnit(
        int $variantId
    ): void
    {
        ProductVariantUnit::query()

            ->where(
                'product_variant_id',
                $variantId
            )

            ->update([

                'is_default' => false,

            ]);
    }
    public function bulkActivate(
    array $ids
): void
{
    DB::transaction(function () use ($ids) {

        ProductVariantUnit::query()

            ->whereIn('id', $ids)

            ->update([

                'is_active' => true,

            ]);

    });
}
public function bulkDeactivate(
    array $ids
): void
{
    DB::transaction(function () use ($ids) {

        $variantUnits = ProductVariantUnit::query()

            ->whereIn('id', $ids)

            ->get();

        foreach ($variantUnits as $variantUnit) {

            $this->ensureCanDeactivate(
                $variantUnit
            );

        }

        ProductVariantUnit::query()

            ->whereIn('id', $ids)

            ->update([

                'is_active' => false,

            ]);

    });
}
protected function ensureCanDeactivate(
    ProductVariantUnit $variantUnit
): void
{
    if ($variantUnit->is_base) {

        throw new \Exception(
            'Base Unit cannot be deactivated.'
        );

    }

    if ($variantUnit->is_default) {

        throw new \Exception(
            'Default Unit cannot be deactivated.'
        );

    }
}
public function getAvailableUnits(
    int $productVariantId,
    ?int $currentUnitId = null
): Collection
{
            $usedUnitIds = ProductVariantUnit::query()

            ->where(
                'product_variant_id',
                $productVariantId
            )

            ->when(

                $currentUnitId,

                fn ($query) => $query->where(
                    'unit_id',
                    '!=',
                    $currentUnitId
                )

            )

            ->pluck('unit_id');

    return Unit::query()

        ->active()

        ->whereNotIn(
            'id',
            $usedUnitIds
        )

        ->orderBy('name')

        ->get()

        ->map(function ($unit) {

            return [

                'id' => $unit->id,

                'name' => $unit->name,

                'label' => $unit->name,

            ];

        });
}
public function find(
    ProductVariantUnit $variantUnit
): ProductVariantUnit
{
    return $variantUnit->load([

        'variant.product',

        'unit',

    ]);
}
}