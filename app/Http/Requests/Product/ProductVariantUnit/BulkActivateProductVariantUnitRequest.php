<?php

namespace App\Http\Requests\Product\ProductVariantUnit;

use Illuminate\Foundation\Http\FormRequest;

class BulkActivateProductVariantUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'integer',
                'exists:product_variant_units,id',
            ],

        ];
    }

    public function attributes(): array
    {
        return [

            'ids' => 'Product Variant Units',

        ];
    }
}