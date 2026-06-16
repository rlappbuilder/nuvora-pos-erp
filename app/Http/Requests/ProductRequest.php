<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'category_id' => [

                'required',

                'exists:categories,id'

            ],

            'brand_id' => [

                'nullable',

                'exists:brands,id'

            ],

            'barcode' => [

                'nullable',

                'max:100'

            ],
            'image' => [

                    'nullable',

                    'image',

                    'mimes:jpg,jpeg,png,webp',

                    'max:2048'

                ],

                'product_type' => [

                    'required',

                    'in:PRODUCT,SERVICE'

                ],

            'name' => [

                'required',

                'max:255'

            ],

            'description' => [

                'nullable'

            ],

            'unit' => [

                'required',

                'max:50'

            ],

            'cost_price' => [

                'required',

                'numeric',

                'min:0'

            ],

            'selling_price' => [

                'required',

                'numeric',

                'min:0'

            ],

            'minimum_stock' => [

                'nullable',

                'integer',

                'min:0'

            ],

            'status' => [

                'required',

                'boolean'

            ],

        ];
    }
}