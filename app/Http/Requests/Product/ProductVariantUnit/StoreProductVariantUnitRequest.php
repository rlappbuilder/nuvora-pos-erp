<?php

namespace App\Http\Requests\Product\ProductVariantUnit;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'product_variant_id' => [
                'required',
                'exists:product_variants,id',
            ],

            'unit_id' => [
                'required',
                'exists:units,id',
            ],

            'conversion_factor' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'is_base' => [
                'required',
                'boolean',
            ],

            'is_default' => [
                'required',
                'boolean',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

        ];
    }

    public function attributes(): array
    {
        return [

            'product_variant_id' => 'Product Variant',

            'unit_id' => 'Unit',

            'conversion_factor' => 'Conversion Factor',

            'is_base' => 'Base Unit',

            'is_default' => 'Default Unit',

            'is_active' => 'Status',

            'sort_order' => 'Sort Order',

        ];
    }
}