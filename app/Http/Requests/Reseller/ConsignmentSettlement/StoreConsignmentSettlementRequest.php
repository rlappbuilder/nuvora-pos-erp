<?php

namespace App\Http\Requests\Reseller\ConsignmentSettlement;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsignmentSettlementRequest extends FormRequest
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

            'settlement_date' => [
                'required',
                'date',
            ],

            'period_from' => [
                'required',
                'date',
            ],

            'period_to' => [
                'required',
                'date',
                'after_or_equal:period_from',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],

            'payment_amount' => [
                'required',
                'numeric',
                'gte:0',
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

            'details.*.qty_sold' => [
                'required',
                'numeric',
                'gt:0',
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

            'settlement_date.required' =>
                'Settlement date is required.',

            'period_from.required' =>
                'Period from is required.',

            'period_to.required' =>
                'Period to is required.',

            'period_to.after_or_equal' =>
                'Period to must be on or after period from.',

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

            'details.*.qty_sold.required' =>
                'Quantity sold is required.',

            'details.*.qty_sold.gt' =>
                'Quantity sold must be greater than zero.',

        ];
    }
}