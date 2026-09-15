<?php

namespace App\Http\Requests\Reseller\ConsignmentOut;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsignmentOutRequest extends FormRequest
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

            'reseller_id' => [
                'required',
                'integer',
                'exists:resellers,id',
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


            'reseller_id.required' =>
                'Reseller is required.',

            'reseller_id.exists' =>
                'Selected reseller is invalid.',


            /*
            |--------------------------------------------------------------------------
            | Document
            |--------------------------------------------------------------------------
            */

            'transaction_date.required' =>
                'Transaction date is required.',

            'transaction_date.date' =>
                'Transaction date must be a valid date.',


            'reference_number.max' =>
                'Reference number may not exceed 100 characters.',


            /*
            |--------------------------------------------------------------------------
            | Details
            |--------------------------------------------------------------------------
            */

            'details.required' =>
                'Consignment details are required.',

            'details.array' =>
                'Consignment details must be a valid list.',

            'details.min' =>
                'At least one consignment item is required.',


            'details.*.product_variant_id.required' =>
                'Product variant is required.',

            'details.*.product_variant_id.exists' =>
                'Selected product variant is invalid.',


            'details.*.unit_id.required' =>
                'Unit is required.',

            'details.*.unit_id.exists' =>
                'Selected unit is invalid.',


            'details.*.qty.required' =>
                'Consignment quantity is required.',

            'details.*.qty.numeric' =>
                'Consignment quantity must be a number.',

            'details.*.qty.gt' =>
                'Consignment quantity must be greater than zero.',


            'details.*.unit_price.required' =>
                'Consignment price is required.',

            'details.*.unit_price.numeric' =>
                'Consignment price must be a number.',

            'details.*.unit_price.gte' =>
                'Consignment price cannot be negative.',
        ];
    }
}