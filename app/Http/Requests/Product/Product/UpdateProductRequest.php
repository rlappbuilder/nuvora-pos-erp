<?php

namespace App\Http\Requests\Product\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
                'exists:categories,id',
            ],

            'brand_id' => [
                'nullable',
                'exists:brands,id',
            ],

            'unit_id' => [
                'required',
                'exists:units,id',
            ],

          'code' => [
                    'required',
                    Rule::unique('products', 'code')
                        ->ignore($this->route('product')->id),
                ],

            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($this->route('product')),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

                'product_type' => [
                'required',
                Rule::in(['PRODUCT', 'SERVICE']),
            ],

            'track_stock' => [
                'required',
                'boolean',
            ],

            'is_sellable' => [
                'required',
                'boolean',
            ],

            'is_purchasable' => [
                'required',
                'boolean',
            ],

            'minimum_stock' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

        ];
    }
}