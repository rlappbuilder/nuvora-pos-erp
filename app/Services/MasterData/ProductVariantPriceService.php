<?php

namespace App\Services\MasterData;

use App\Models\MasterData\ProductVariantPrice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class ProductVariantPriceService
{
    public function store(
    array $data
        ): ProductVariantPrice
        {
            return DB::transaction(function () use ($data) {

                $this->validateDuplicate($data);

                $this->validateEffectivePeriod($data);

                $this->closePreviousPrice($data);

                return ProductVariantPrice::create($data);

            });
        }

    public function update(
    ProductVariantPrice $price,
    array $data
): ProductVariantPrice
{
    return DB::transaction(function () use (
        $price,
        $data
    ) {

        $this->validateDuplicate(
            $data,
            $price->id
        );

        $this->validateEffectivePeriod(
            $data,
            $price->id
        );

        $price->update($data);

        return $price->fresh();

    });
}

    public function delete(
    ProductVariantPrice $price,
    ?int $userId = null
): void
{
    DB::transaction(function () use (
        $price,
        $userId
    ) {

        $price->update([
            'deleted_by' => $userId,
        ]);

        $price->delete();

    });
}

   public function bulkDelete(
    array $ids,
    ?int $userId = null
        ): void
        {
            DB::transaction(function () use (
                $ids,
                $userId
            ) {

                ProductVariantPrice::whereIn(
                    'id',
                    $ids
                )->update([
                    'deleted_by' => $userId,
                ]);

                ProductVariantPrice::whereIn(
                    'id',
                    $ids
                )->delete();

            });
        }

    public function bulkActivate(
        array $ids
    ): void
    {
        ProductVariantPrice::whereIn(
            'id',
            $ids
        )->update([

            'is_active' => true,

        ]);
    }

    public function bulkDeactivate(
        array $ids
    ): void
    {
        ProductVariantPrice::whereIn(
            'id',
            $ids
        )->update([

            'is_active' => false,

        ]);
    }

    protected function validateDuplicate(
        array $data,
        ?int $ignoreId = null
    ): void
    {
        $query = ProductVariantPrice::query()

            ->where(
                'branch_id',
                $data['branch_id']
            )

            ->where(
                'product_variant_id',
                $data['product_variant_id']
            )

            ->where(
                'unit_id',
                $data['unit_id']
            )

            ->where(
                'price_type_id',
                $data['price_type_id']
            )

            ->where(
                'effective_from',
                $data['effective_from']
            );

        if ($ignoreId) {

            $query->where(
                'id',
                '!=',
                $ignoreId
            );

        }

        if ($query->exists()) {

            throw new \Exception(
                'Price already exists for the selected Branch, Variant, Unit, Price Type and Effective Date.'
            );

        }
    }

protected function validateEffectivePeriod(
    array $data,
    ?int $ignoreId = null
): void
{
    if (
        empty($data['effective_from']) ||
        empty($data['effective_until'])
    ) {

        return;

    }

    $query = ProductVariantPrice::query()

        ->where(
            'branch_id',
            $data['branch_id']
        )

        ->where(
            'product_variant_id',
            $data['product_variant_id']
        )

        ->where(
            'unit_id',
            $data['unit_id']
        )

        ->where(
            'price_type_id',
            $data['price_type_id']
        )

        ->where(function ($query) use ($data) {

            $query

                ->whereBetween(
                    'effective_from',
                    [
                        $data['effective_from'],
                        $data['effective_until'],
                    ]
                )

                ->orWhereBetween(
                    'effective_until',
                    [
                        $data['effective_from'],
                        $data['effective_until'],
                    ]
                )

                ->orWhere(function ($query) use ($data) {

                    $query

                        ->where(
                            'effective_from',
                            '<=',
                            $data['effective_from']
                        )

                        ->where(
                            'effective_until',
                            '>=',
                            $data['effective_until']
                        );

                });

        });

        if ($ignoreId) {

            $query->where(
                'id',
                '!=',
                $ignoreId
            );

        }

        if ($query->exists()) {

            throw new \Exception(
                'The effective period overlaps with an existing price.'
            );

        }
    }
    protected function closePreviousPrice(array $data): void
{
    ProductVariantPrice::query()

        ->where('branch_id', $data['branch_id'])

        ->where('product_variant_id', $data['product_variant_id'])

        ->where('unit_id', $data['unit_id'])

        ->where('price_type_id', $data['price_type_id'])

        ->where(
            'effective_from',
            '<',
            $data['effective_from']
        )

        ->whereNull('effective_until')

        ->update([

            'effective_until' => Carbon::parse(
                $data['effective_from']
            )->subDay(),

        ]);

}
public function history(
    ProductVariantPrice $price
)
{
    return ProductVariantPrice::query()

        ->with([

            'branch:id,name',

            'variant.product:id,code,name',

            'unit:id,name',

            'priceType:id,name',

        ])

        ->where(
            'branch_id',
            $price->branch_id
        )

        ->where(
            'product_variant_id',
            $price->product_variant_id
        )

        ->where(
            'unit_id',
            $price->unit_id
        )

        ->where(
            'price_type_id',
            $price->price_type_id
        )

        ->orderByDesc(
            'effective_from'
        )

        ->get();
}
}