<?php

namespace App\Http\Requests\Purchasing\PurchasePayment;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchasePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
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

            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'payment_date' => [
                'required',
                'date',
            ],

            'payment_method' => [
                'required',
                'string',
                'max:30',
            ],

            'payment_account_id' => [
                'required',
                'integer',
                'exists:chart_of_accounts,id',
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

            'details.*.purchase_invoice_header_id' => [
                'required',
                'integer',
                'exists:purchase_invoice_headers,id',
            ],

            'details.*.payment_amount' => [
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

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [

            'branch_id.required' =>
                'Branch is required.',

            'branch_id.exists' =>
                'Selected branch is invalid.',

            'supplier_id.required' =>
                'Supplier is required.',

            'supplier_id.exists' =>
                'Selected supplier is invalid.',

            'payment_date.required' =>
                'Payment date is required.',

            'payment_method.required' =>
                'Payment method is required.',

            'payment_account_id.required' =>
                'Payment account is required.',

            'payment_account_id.exists' =>
                'Selected payment account is invalid.',

            'details.required' =>
                'At least one purchase invoice is required.',

            'details.min' =>
                'At least one purchase invoice is required.',

            'details.*.purchase_invoice_header_id.required' =>
                'Purchase invoice is required.',

            'details.*.purchase_invoice_header_id.exists' =>
                'Selected purchase invoice is invalid.',

            'details.*.payment_amount.required' =>
                'Payment amount is required.',

            'details.*.payment_amount.gt' =>
                'Payment amount must be greater than zero.',

        ];
    }
}