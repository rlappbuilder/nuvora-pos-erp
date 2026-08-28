<?php

namespace App\Http\Requests\Purchasing\PurchaseReturn;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseReturnRequest extends FormRequest
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

            'goods_receipt_id' => [
                'required',
                'integer',
                'exists:goods_receipt_headers,id',
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

            'details.*.goods_receipt_detail_id' => [
                'required',
                'integer',
                'exists:goods_receipt_details,id',
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

            'details.*.received_qty' => [
                'required',
                'numeric',
                'gte:0',
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

            'goods_receipt_id.required' =>
                'Goods receipt is required.',

            'goods_receipt_id.exists' =>
                'Selected goods receipt is invalid.',

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

            'details.*.goods_receipt_detail_id.required' =>
                'Goods receipt detail is required.',

            'details.*.goods_receipt_detail_id.exists' =>
                'Selected goods receipt detail is invalid.',

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

            'details.*.received_qty.required' =>
                'Received quantity is required.',

            'details.*.received_qty.gte' =>
                'Received quantity cannot be negative.',

            'details.*.returned_qty.required' =>
                'Returned quantity is required.',

            'details.*.returned_qty.gt' =>
                'Returned quantity must be greater than zero.',

        ];
    }
}