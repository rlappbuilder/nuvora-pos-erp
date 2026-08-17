<?php

namespace App\Http\Requests\Inventory\StockTransfer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockTransferRequest extends FormRequest
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
            
            

            'from_branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            'from_warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],

            'to_branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            'to_warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
                'different:from_warehouse_id',
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

            'details.*.qty' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'details.*.unit_cost' => [
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

            'from_branch_id.required' =>
                'Source branch is required.',

            'from_branch_id.exists' =>
                'Source branch is invalid.',


            'from_warehouse_id.required' =>
                'Source warehouse is required.',

            'from_warehouse_id.exists' =>
                'Source warehouse is invalid.',


            'to_branch_id.required' =>
                'Destination branch is required.',

            'to_branch_id.exists' =>
                'Destination branch is invalid.',


            'to_warehouse_id.required' =>
                'Destination warehouse is required.',

            'to_warehouse_id.exists' =>
                'Destination warehouse is invalid.',

            'to_warehouse_id.different' =>
                'Destination warehouse must be different from source warehouse.',


            'transaction_date.required' =>
                'Transaction date is required.',

            'transaction_date.date' =>
                'Transaction date must be a valid date.',


            'details.required' =>
                'Transfer details are required.',

            'details.array' =>
                'Transfer details must be a valid list.',

            'details.min' =>
                'At least one transfer item is required.',


            'details.*.product_variant_id.required' =>
                'Product variant is required.',

            'details.*.product_variant_id.exists' =>
                'Selected product variant is invalid.',


            'details.*.unit_id.required' =>
                'Unit is required.',

            'details.*.unit_id.exists' =>
                'Selected unit is invalid.',


            'details.*.qty.required' =>
                'Transfer quantity is required.',

            'details.*.qty.numeric' =>
                'Transfer quantity must be a number.',

            'details.*.qty.gt' =>
                'Transfer quantity must be greater than zero.',


            'details.*.unit_cost.required' =>
                'Unit cost is required.',

            'details.*.unit_cost.numeric' =>
                'Unit cost must be a number.',

            'details.*.unit_cost.gte' =>
                'Unit cost cannot be negative.',

        ];
    }
}