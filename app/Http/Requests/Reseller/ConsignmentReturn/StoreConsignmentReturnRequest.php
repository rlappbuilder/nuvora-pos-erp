<?php

namespace App\Http\Requests\Reseller\ConsignmentReturn;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsignmentReturnRequest extends FormRequest
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

            'reseller_id' => [
                'required',
                'integer',
                'exists:resellers,id',
            ],

            'settlement_header_id' => [
                'nullable',
                'integer',
                'exists:settlement_headers,id',
            ],

            'return_date' => [
                'required',
                'date',
            ],

            'remarks' => [
                'nullable',
                'string',
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

            'details.*.returned_qty' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'details.*.remarks' => [
                'nullable',
                'string',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Header
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

            'reseller_id.required' =>
                'Reseller is required.',

            'reseller_id.exists' =>
                'Selected reseller is invalid.',

            'settlement_header_id.exists' =>
                'Selected settlement is invalid.',

            'return_date.required' =>
                'Return date is required.',

            /*
            |--------------------------------------------------------------------------
            | Details
            |--------------------------------------------------------------------------
            */

            'details.required' =>
                'At least one product is required.',

            'details.min' =>
                'At least one product is required.',

            'details.*.product_variant_id.required' =>
                'Product variant is required.',

            'details.*.product_variant_id.exists' =>
                'Selected product variant is invalid.',

            'details.*.unit_id.required' =>
                'Unit is required.',

            'details.*.unit_id.exists' =>
                'Selected unit is invalid.',

            'details.*.returned_qty.required' =>
                'Returned quantity is required.',

            'details.*.returned_qty.gt' =>
                'Returned quantity must be greater than zero.',

        ];
    }
}