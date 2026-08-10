<?php

namespace App\Http\Requests\Inventory\StockOpname;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockOpnameRequest extends FormRequest
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

            'company_id' => [
                'required',
                'exists:companies,id',
            ],

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

            'details.*.actual_qty' => [
                'required',
                'numeric',
                'gte:0',
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

            'details' => 'Stock Opname Details',

            'details.*.product_variant_id' =>
                'Product Variant',

            'details.*.unit_id' =>
                'Unit',

            'details.*.actual_qty' =>
                'Actual Quantity',

            'details.*.description' =>
                'Detail Description',

        ];
    }

    public function messages(): array
    {
        return [];
    }
}