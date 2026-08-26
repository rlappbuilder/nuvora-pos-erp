<?php

namespace App\Http\Requests\Purchasing\GoodsReceive;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoodsReceiptRequest extends FormRequest
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

            'purchase_order_id' => [
                'required',
                'integer',
                'exists:purchase_order_headers,id',
            ],

            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],

            'receipt_date' => [
                'required',
                'date',
            ],

            'supplier_do_number' => [
                'nullable',
                'string',
                'max:255',
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

            'details.*.purchase_order_detail_id' => [
                'required',
                'integer',
                'exists:purchase_order_details,id',
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

            'details.*.ordered_qty' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'details.*.received_qty' => [
                'required',
                'numeric',
                'gte:0',
            ],

            'details.*.rejected_qty' => [
                'nullable',
                'numeric',
                'gte:0',
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

            'purchase_order_id.required' =>
                'Purchase order is required.',

            'purchase_order_id.exists' =>
                'Selected purchase order is invalid.',

            'supplier_id.required' =>
                'Supplier is required.',

            'supplier_id.exists' =>
                'Selected supplier is invalid.',

            'warehouse_id.required' =>
                'Warehouse is required.',

            'warehouse_id.exists' =>
                'Selected warehouse is invalid.',

            'receipt_date.required' =>
                'Receipt date is required.',

            'supplier_do_number.max' =>
                'Supplier DO number may not exceed 255 characters.',

            /*
            |--------------------------------------------------------------------------
            | Details
            |--------------------------------------------------------------------------
            */

            'details.required' =>
                'At least one product is required.',

            'details.min' =>
                'At least one product is required.',

            'details.*.purchase_order_detail_id.required' =>
                'Purchase order detail is required.',

            'details.*.purchase_order_detail_id.exists' =>
                'Selected purchase order detail is invalid.',

            'details.*.product_variant_id.required' =>
                'Product variant is required.',

            'details.*.product_variant_id.exists' =>
                'Selected product variant is invalid.',

            'details.*.unit_id.required' =>
                'Unit is required.',

            'details.*.unit_id.exists' =>
                'Selected unit is invalid.',

            'details.*.ordered_qty.required' =>
                'Ordered quantity is required.',

            'details.*.ordered_qty.gt' =>
                'Ordered quantity must be greater than zero.',

            'details.*.received_qty.required' =>
                'Received quantity is required.',

            'details.*.received_qty.gte' =>
                'Received quantity cannot be negative.',

            'details.*.rejected_qty.gte' =>
                'Rejected quantity cannot be negative.',

        ];
    }
}