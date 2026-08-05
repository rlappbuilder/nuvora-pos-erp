<?php

namespace App\Services\Product;

use Illuminate\Support\Facades\DB;
use App\Data\Product\VariantGeneratorData;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Product\ProductVariantValue;
class VariantGeneratorService
{
    

    /*
    |--------------------------------------------------------------------------
    | Public Methods
    |--------------------------------------------------------------------------
    */

    public function generate(
    VariantGeneratorData $data
): void
{
    DB::transaction(function () use ($data) {

        if ($data->attributes->isEmpty()) {

            $this->storeDefaultVariant($data);

            return;
        }

        $combinations = $this->generateCombinations(
            $data->attributes->all()
        );

        $this->storeVariants(
            data: $data,
            combinations: $combinations
        );

    });
}
    /*
    |--------------------------------------------------------------------------
    | Generate
    |--------------------------------------------------------------------------
    */

    private function generateCombinations(
    array $attributes
): array
{
    $combinations = [[]];

    foreach ($attributes as $attribute) {

        $newCombinations = [];

        foreach ($combinations as $combination) {

            foreach ($attribute->values as $value) {

                $newCombinations[] = [
                    ...$combination,
                    $value,
                ];

            }

        }

        $combinations = $newCombinations;
    }

    return $combinations;
}

    private function generateVariantName(
    array $values
        ): string
        {
            return collect($values)
                ->pluck('name')
                ->implode(' / ');
        }

private function generateVariantSku(
    Product $product,
    int $sequence
): string
{
    return sprintf(
        '%s-%03d',
        $product->code,
        $sequence
    );
}
public function regenerate(
    VariantGeneratorData $data
): void
{
    DB::transaction(function () use ($data) {

        ProductVariant::where(
            'product_id',
            $data->product->id
        )
        ->get()
        ->each(function ($variant) {

            $variant->forceDelete();

        });

        $this->generate($data);

    });
}
    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

  private function storeVariants(
    VariantGeneratorData $data,
    array $combinations
): void
{
    $sequence = 1;

    foreach ($combinations as $values) {

        $variant = ProductVariant::create([

            'product_id' => $data->product->id,

            'sku' => $this->generateVariantSku(
                $data->product,
                $sequence
            ),

            'name' => $this->generateVariantName(
                $values
            ),

            'is_default' => false,

            'is_active' => true,

            'sort_order' => $sequence,

            'created_by' => $data->userId,

        ]);

        $this->storeVariantValues(
            $variant,
            $values
        );

        $sequence++;
    }
}

    private function storeVariantValues(
    ProductVariant $variant,
    array $values
    ): void
    {
        foreach ($values as $value) {

            ProductVariantValue::create([

                'product_variant_id' => $variant->id,

                'product_attribute_id' => $value->product_attribute_id,

                'product_attribute_value_id' => $value->id,

            ]);

        }
    }

    private function storeDefaultVariant(
    VariantGeneratorData $data
): void
{
    $variant = ProductVariant::create([

   // 'company_id' => $data->product->company_id,

    'product_id' => $data->product->id,

    'sku' => $this->generateVariantSku(
        $data->product,
        1
    ),

    'name' => 'Default',

    'is_default' => true,

    'is_active' => true,

    'sort_order' => 1,

    'created_by' => $data->userId,

]);

//dd($variant);
}
public function preview(
    Product $product
): array
{
    $attributes = $product
        ->attributes()
        ->with([
            'values' => function ($query) {

                $query->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');

            },
        ])
        ->where('is_variant', true)
        ->where('is_active', true)
        ->get();
//dd($attributes);
    if ($attributes->isEmpty()) {

        return [

            'attributes' => [],

            'variants' => [

                [
                    'name' => 'Default',
                ],

            ],

            'summary' => [

                'attributes' => 0,

                'values' => [],

                'total' => 1,

            ],

        ];

    }

    $combinations = $this->generateCombinations(
        $attributes->all()
    );

    return [

        'attributes' => $attributes
            ->map(function ($attribute) {

                return [

                    'id' => $attribute->id,

                    'name' => $attribute->display_name,

                    'values_count' => $attribute->values->count(),

                ];

            })
            ->values(),

        'variants' => collect($combinations)
            ->map(function ($values) {

                return [

                    'name' => $this->generateVariantName(
                        $values
                    ),

                ];

            })
            ->values(),

        'summary' => [

            'attributes' => $attributes->count(),

            'values' => $attributes
                ->pluck('values')
                ->map(fn ($values) => $values->count())
                ->values(),

            'total' => count($combinations),

        ],

    ];
}
}