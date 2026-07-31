<?php

namespace App\Http\Requests\Product\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
                'nullable',
                'string',
                'max:30',
                'unique:products,code',
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                'unique:products,sku',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,slug',
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
    public function attributes(): array
{
    return [
        'category_id'    => 'category',
        'brand_id'       => 'brand',
        'unit_id'        => 'unit',
        'product_type'   => 'product type',
        'track_stock'    => 'track stock',
        'is_sellable'    => 'sellable',
        'is_purchasable' => 'purchasable',
        'minimum_stock'  => 'minimum stock',
        'is_active'      => 'status',
    ];
}
}