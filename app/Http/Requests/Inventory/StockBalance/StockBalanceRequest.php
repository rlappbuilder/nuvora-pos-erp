<?php

namespace App\Http\Requests\Inventory\StockBalance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],


            /*
            |--------------------------------------------------------------------------
            | Location
            |--------------------------------------------------------------------------
            */

            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],

            'warehouse_id' => [
                'nullable',
                'integer',
                'exists:warehouses,id',
            ],


            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            'product_variant_id' => [
                'nullable',
                'integer',
                'exists:product_variants,id',
            ],

            'unit_id' => [
                'nullable',
                'integer',
                'exists:units,id',
            ],


            /*
            |--------------------------------------------------------------------------
            | Price Type
            |--------------------------------------------------------------------------
            */

            'price_type_id' => [
                'nullable',
                'integer',
                'exists:price_types,id',
            ],


            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],


            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */

            'sort_by' => [
                'nullable',
                'string',
                Rule::in([
                    'id',
                    'on_hand_qty',
                    'reserved_qty',
                    'available_qty',
                    'average_cost',
                ]),
            ],

            'sort_direction' => [
                'nullable',
                'string',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],

        ];
    }


    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            'search.string' =>
                'Search must be valid text.',

            'search.max' =>
                'Search cannot exceed 255 characters.',


            /*
            |--------------------------------------------------------------------------
            | Location
            |--------------------------------------------------------------------------
            */

            'branch_id.integer' =>
                'Branch must be a valid number.',

            'branch_id.exists' =>
                'Selected branch is invalid.',


            'warehouse_id.integer' =>
                'Warehouse must be a valid number.',

            'warehouse_id.exists' =>
                'Selected warehouse is invalid.',


            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            'product_variant_id.integer' =>
                'Product variant must be a valid number.',

            'product_variant_id.exists' =>
                'Selected product variant is invalid.',


            'unit_id.integer' =>
                'Unit must be a valid number.',

            'unit_id.exists' =>
                'Selected unit is invalid.',


            /*
            |--------------------------------------------------------------------------
            | Price Type
            |--------------------------------------------------------------------------
            */

            'price_type_id.integer' =>
                'Price type must be a valid number.',

            'price_type_id.exists' =>
                'Selected price type is invalid.',


            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            'per_page.integer' =>
                'Per page must be a valid number.',

            'per_page.min' =>
                'Per page must be at least 1.',

            'per_page.max' =>
                'Per page cannot exceed 100.',


            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */

            'sort_by.in' =>
                'Selected sort field is invalid.',

            'sort_direction.in' =>
                'Sort direction must be asc or desc.',

        ];
    }
}