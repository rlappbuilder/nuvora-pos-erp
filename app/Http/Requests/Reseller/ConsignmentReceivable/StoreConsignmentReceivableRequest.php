<?php

namespace App\Http\Requests\Reseller\ConsignmentReceivable;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsignmentReceivableRequest extends FormRequest
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

            'reseller_id' => [
                'required',
                'integer',
                'exists:resellers,id',
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

            'details.*.settlement_header_id' => [
                'required',
                'integer',
                'exists:settlement_headers,id',
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

            'reseller_id.required' =>
                'Reseller is required.',

            'reseller_id.exists' =>
                'Selected reseller is invalid.',

            'payment_date.required' =>
                'Payment date is required.',

            'payment_method.required' =>
                'Payment method is required.',

            'payment_account_id.required' =>
                'Payment account is required.',

            'payment_account_id.exists' =>
                'Selected payment account is invalid.',

            'details.required' =>
                'At least one settlement is required.',

            'details.min' =>
                'At least one settlement is required.',

            'details.*.settlement_header_id.required' =>
                'Settlement is required.',

            'details.*.settlement_header_id.exists' =>
                'Selected settlement is invalid.',

            'details.*.payment_amount.required' =>
                'Payment amount is required.',

            'details.*.payment_amount.gt' =>
                'Payment amount must be greater than zero.',

        ];
    }
}