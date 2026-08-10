<?php

namespace App\Http\Requests\Inventory\OpeningStock;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpeningStockRequest extends FormRequest
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
        | Identity
        |--------------------------------------------------------------------------
        */

            'branch_id' => [
            'required',
            'exists:branches,id',
        ],

        'warehouse_id' => [
            'required',
            'exists:warehouses,id',
        ],

        /*
        |--------------------------------------------------------------------------
        | Document
        |--------------------------------------------------------------------------
        */

        'transaction_date' => [
            'required',
            'date',
        ],

        'description' => [
            'nullable',
            'string',
            'max:500',
        ],

        /*
        |--------------------------------------------------------------------------
        | Details
        |--------------------------------------------------------------------------
        */

        'details' => [
            'required',
            'array',
            'min:1',
        ],

        'details.*.product_variant_id' => [
            'required',
            'exists:product_variants,id',
        ],

        'details.*.unit_id' => [
            'required',
            'exists:units,id',
        ],

        'details.*.qty' => [
            'required',
            'numeric',
            'gt:0',
        ],

        'details.*.unit_cost' => [
            'required',
            'numeric',
            'min:0',
        ],

        'details.*.description' => [
            'nullable',
            'string',
            'max:500',
        ],

    ];
}

    public function attributes(): array
{
    return [

        'company_id' => 'Company',

        'branch_id' => 'Branch',

        'warehouse_id' => 'Warehouse',

        'transaction_date' => 'Transaction Date',

        'description' => 'Description',

        'details' => 'Opening Stock Details',

        'details.*.product_variant_id' =>
            'Product Variant',

        'details.*.unit_id' =>
            'Unit',

        'details.*.qty' =>
            'Quantity',

        'details.*.unit_cost' =>
            'Unit Cost',

        'details.*.description' =>
            'Detail Description',

    ];
}

    public function messages(): array
    {
        return [];
    }
}