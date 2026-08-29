<?php

namespace App\Http\Requests\Purchasing\PurchaseInvoice;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseInvoiceRequest extends FormRequest
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

            'purchase_order_id' => [
                'required',
                'integer',
                'exists:purchase_order_headers,id',
            ],

            'goods_receipt_id' => [
                'required',
                'integer',
                'exists:goods_receipt_headers,id',
            ],

            'payment_term_id' => [
                'required',
                'integer',
                'exists:payment_terms,id',
            ],

            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
            ],

            'tax_id' => [
                'nullable',
                'integer',
                'exists:taxes,id',
            ],

            'invoice_number' => [
                'required',
                'string',
                'max:255',
            ],

            'invoice_date' => [
                'required',
                'date',
            ],

            'due_date' => [
                'required',
                'date',
                'after_or_equal:invoice_date',
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

            'details.*.goods_receipt_detail_id' => [
                'required',
                'integer',
                'exists:goods_receipt_details,id',
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

            'details.*.invoiced_qty' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'details.*.unit_price' => [
                'required',
                'numeric',
                'gte:0',
            ],

            'details.*.discount_amount' => [
                'nullable',
                'numeric',
                'gte:0',
            ],

            'details.*.tax_amount' => [
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

            'purchase_order_id.required' =>
                'Purchase order is required.',

            'purchase_order_id.exists' =>
                'Selected purchase order is invalid.',

            'goods_receipt_id.required' =>
                'Goods receipt is required.',

            'goods_receipt_id.exists' =>
                'Selected goods receipt is invalid.',

            'payment_term_id.required' =>
                'Payment term is required.',

            'payment_term_id.exists' =>
                'Selected payment term is invalid.',

            'currency_id.required' =>
                'Currency is required.',

            'currency_id.exists' =>
                'Selected currency is invalid.',

            'tax_id.exists' =>
                'Selected tax is invalid.',

            'invoice_number.required' =>
                'Supplier invoice number is required.',

            'invoice_date.required' =>
                'Invoice date is required.',

            'due_date.required' =>
                'Due date is required.',

            'due_date.after_or_equal' =>
                'Due date must be on or after invoice date.',

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

            'details.*.goods_receipt_detail_id.required' =>
                'Goods receipt detail is required.',

            'details.*.goods_receipt_detail_id.exists' =>
                'Selected goods receipt detail is invalid.',

            'details.*.product_variant_id.required' =>
                'Product variant is required.',

            'details.*.product_variant_id.exists' =>
                'Selected product variant is invalid.',

            'details.*.unit_id.required' =>
                'Unit is required.',

            'details.*.unit_id.exists' =>
                'Selected unit is invalid.',

            'details.*.invoiced_qty.required' =>
                'Invoice quantity is required.',

            'details.*.invoiced_qty.gt' =>
                'Invoice quantity must be greater than zero.',

            'details.*.unit_price.required' =>
                'Unit price is required.',

            'details.*.unit_price.gte' =>
                'Unit price cannot be negative.',

            'details.*.discount_amount.gte' =>
                'Discount amount cannot be negative.',

            'details.*.tax_amount.gte' =>
                'Tax amount cannot be negative.',

        ];
    }
}