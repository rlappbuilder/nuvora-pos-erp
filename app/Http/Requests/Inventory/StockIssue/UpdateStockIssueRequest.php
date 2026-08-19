<?php

namespace App\Http\Requests\Inventory\StockIssue;

use App\Enums\Inventory\StockIssueType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockIssueRequest extends FormRequest
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

            'issue_type' => [
                'required',
                'string',
                'max:50',
                Rule::in(
                    StockIssueType::values()
                ),
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

            'branch_id.required' =>
                'Branch is required.',

            'branch_id.exists' =>
                'Selected branch is invalid.',

            'warehouse_id.required' =>
                'Warehouse is required.',

            'warehouse_id.exists' =>
                'Selected warehouse is invalid.',

            'transaction_date.required' =>
                'Transaction date is required.',

            'transaction_date.date' =>
                'Transaction date must be a valid date.',

            'issue_type.required' =>
                'Issue type is required.',

            'issue_type.in' =>
                'Selected issue type is invalid.',

            'description.max' =>
                'Description cannot exceed 1000 characters.',

            'details.required' =>
                'Issue details are required.',

            'details.array' =>
                'Issue details must be a valid list.',

            'details.min' =>
                'At least one issue item is required.',

            'details.*.product_variant_id.required' =>
                'Product variant is required.',

            'details.*.product_variant_id.exists' =>
                'Selected product variant is invalid.',

            'details.*.unit_id.required' =>
                'Unit is required.',

            'details.*.unit_id.exists' =>
                'Selected unit is invalid.',

            'details.*.qty.required' =>
                'Issue quantity is required.',

            'details.*.qty.numeric' =>
                'Issue quantity must be a number.',

            'details.*.qty.gt' =>
                'Issue quantity must be greater than zero.',

            'details.*.description.max' =>
                'Detail description cannot exceed 1000 characters.',

        ];
    }
}