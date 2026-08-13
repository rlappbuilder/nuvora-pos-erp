<?php

namespace App\Http\Requests\Inventory\InventoryAdjustment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryAdjustmentRequest extends FormRequest
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
            | Header
            |--------------------------------------------------------------------------
            */

            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
            ],

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
                'min:0',
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

            'company_id.required' =>
                'Company is required.',

            'branch_id.required' =>
                'Branch is required.',

            'warehouse_id.required' =>
                'Warehouse is required.',

            'transaction_date.required' =>
                'Transaction date is required.',

            'details.required' =>
                'At least one adjustment detail is required.',

            'details.min' =>
                'At least one adjustment detail is required.',

            'details.*.product_variant_id.required' =>
                'Product variant is required.',

            'details.*.product_variant_id.exists' =>
                'Selected product variant does not exist.',

            'details.*.unit_id.required' =>
                'Unit is required.',

            'details.*.unit_id.exists' =>
                'Selected unit does not exist.',

            'details.*.actual_qty.required' =>
                'Actual quantity is required.',

            'details.*.actual_qty.numeric' =>
                'Actual quantity must be a number.',

            'details.*.actual_qty.min' =>
                'Actual quantity cannot be negative.',

        ];
    }
}