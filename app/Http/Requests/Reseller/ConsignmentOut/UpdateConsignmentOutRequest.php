<?php

namespace App\Http\Requests\Reseller\ConsignmentOut;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsignmentOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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

            'transaction_date' => [
                'required',
                'date',
            ],

            'reference_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],

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

            'details.*.unit_price' => [
                'required',
                'numeric',
                'gte:0',
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

            'reseller_id.required' =>
                'Reseller is required.',

            'reseller_id.exists' =>
                'Selected reseller is invalid.',

            'transaction_date.required' =>
                'Transaction date is required.',

            'transaction_date.date' =>
                'Transaction date must be a valid date.',

            'reference_number.max' =>
                'Reference number may not exceed 100 characters.',

            'remarks.max' =>
                'Remarks may not exceed 1000 characters.',

            'details.required' =>
                'At least one detail is required.',

            'details.min' =>
                'At least one detail is required.',

            'details.*.product_variant_id.required' =>
                'Product is required.',

            'details.*.product_variant_id.exists' =>
                'Selected product is invalid.',

            'details.*.unit_id.required' =>
                'Unit is required.',

            'details.*.unit_id.exists' =>
                'Selected unit is invalid.',

            'details.*.qty.required' =>
                'Quantity is required.',

            'details.*.qty.gt' =>
                'Quantity must be greater than 0.',

            'details.*.unit_price.required' =>
                'Consignment price is required.',

            'details.*.unit_price.gte' =>
                'Consignment price must be 0 or greater.',
        ];
    }
}