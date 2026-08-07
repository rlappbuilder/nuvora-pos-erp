<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductVariantPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'branch_id' => [

                'required',

                'exists:branches,id',

            ],

            'product_variant_id' => [

                'required',

                'exists:product_variants,id',

            ],

            'unit_id' => [

                'required',

                'exists:units,id',

            ],

            'price_type_id' => [

                'required',

                'exists:price_types,id',

            ],

            'last_purchase_price' => [

                'required',

                'numeric',

                'min:0',

            ],

            'selling_price' => [

                'required',

                'numeric',

                'min:0',

            ],

            'effective_from' => [

                'required',

                'date',

            ],

            'effective_until' => [

                'nullable',

                'date',

                'after_or_equal:effective_from',

            ],

            'is_active' => [

                'required',

                'boolean',

            ],

            'description' => [

                'nullable',

                'string',

                'max:1000',

            ],

        ];

    }

    public function attributes(): array
    {
        return [

            'branch_id' => 'Branch',

            'product_variant_id' => 'Product Variant',

            'unit_id' => 'Unit',

            'price_type_id' => 'Price Type',

            'last_purchase_price' => 'Last Purchase Price',

            'selling_price' => 'Selling Price',

            'effective_from' => 'Effective From',

            'effective_until' => 'Effective Until',

        ];
    }
}