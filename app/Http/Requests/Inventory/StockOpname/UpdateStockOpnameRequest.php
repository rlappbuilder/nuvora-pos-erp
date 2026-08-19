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
            | Location
            |--------------------------------------------------------------------------
            */

            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            'warehouse_id' => [
                'required',
                'integer',
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
                'max:1000',
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
                'integer',
                'exists:product_variants,id',
            ],

            'details.*.unit_id' => [
                'required',
                'integer',
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
                'max:1000',
            ],

        ];
    }


    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Location
            |--------------------------------------------------------------------------
            */

            'branch_id.required' =>
                'Branch is required.',

            'branch_id.exists' =>
                'Selected branch is invalid.',


            'warehouse_id.required' =>
                'Warehouse is required.',

            'warehouse_id.exists' =>
                'Selected warehouse is invalid.',


            /*
            |--------------------------------------------------------------------------
            | Document
            |--------------------------------------------------------------------------
            */

            'transaction_date.required' =>
                'Transaction date is required.',

            'transaction_date.date' =>
                'Transaction date must be a valid date.',


            'description.max' =>
                'Description cannot exceed 1000 characters.',


            /*
            |--------------------------------------------------------------------------
            | Details
            |--------------------------------------------------------------------------
            */

            'details.required' =>
                'Opname details are required.',

            'details.array' =>
                'Opname details must be a valid list.',

            'details.min' =>
                'At least one opname item is required.',


            'details.*.product_variant_id.required' =>
                'Product variant is required.',

            'details.*.product_variant_id.exists' =>
                'Selected product variant is invalid.',


            'details.*.unit_id.required' =>
                'Unit is required.',

            'details.*.unit_id.exists' =>
                'Selected unit is invalid.',


            'details.*.actual_qty.required' =>
                'Actual quantity is required.',

            'details.*.actual_qty.numeric' =>
                'Actual quantity must be a number.',

            'details.*.actual_qty.gte' =>
                'Actual quantity cannot be negative.',


            'details.*.description.max' =>
                'Detail description cannot exceed 1000 characters.',

        ];
    }
}