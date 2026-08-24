<?php

namespace App\Http\Requests\Purchasing\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
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

            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'purchase_request_id' => [
                'nullable',
                'integer',
                'exists:purchase_request_headers,id',
            ],

            'order_date' => [
                'required',
                'date',
            ],

            'required_date' => [
                'nullable',
                'date',
                'after_or_equal:order_date',
            ],

            'description' => [
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

            'details.*.discount_rate' => [
                'nullable',
                'numeric',
                'gte:0',
                'lte:100',
            ],

            'details.*.tax_rate' => [
                'nullable',
                'numeric',
                'gte:0',
                'lte:100',
            ],

            'details.*.description' => [
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

            'supplier_id.required' =>
                'Supplier is required.',

            'supplier_id.exists' =>
                'Selected supplier is invalid.',

            'purchase_request_id.exists' =>
                'Selected purchase request is invalid.',

            'order_date.required' =>
                'Order date is required.',

            'required_date.after_or_equal' =>
                'Required date must be on or after order date.',

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

            'details.*.qty.required' =>
                'Quantity is required.',

            'details.*.qty.gt' =>
                'Quantity must be greater than zero.',

            'details.*.unit_price.required' =>
                'Unit price is required.',

            'details.*.unit_price.gte' =>
                'Unit price cannot be negative.',

            'details.*.discount_rate.gte' =>
                'Discount rate cannot be negative.',

            'details.*.discount_rate.lte' =>
                'Discount rate cannot exceed 100%.',

            'details.*.tax_rate.gte' =>
                'Tax rate cannot be negative.',

            'details.*.tax_rate.lte' =>
                'Tax rate cannot exceed 100%.',

        ];
    }
}