<?php

namespace App\Http\Requests\Accounting\FundTransfer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFundTransferRequest extends FormRequest
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
            | Source
            |--------------------------------------------------------------------------
            */

            'from_branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],

            'from_account_id' => [
                'required',
                'integer',
                'exists:chart_of_accounts,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Destination
            |--------------------------------------------------------------------------
            */

            'to_branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
                'different:from_branch_id',
            ],

            'to_account_id' => [
                'required',
                'integer',
                'exists:chart_of_accounts,id',
                'different:from_account_id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Transfer Information
            |--------------------------------------------------------------------------
            */

            'transfer_date' => [
                'required',
                'date',
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'transfer_mode' => [
                'required',
                'in:receivable,allocation',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'from_branch_id.exists' =>
                'Selected source branch is invalid.',

            'from_account_id.required' =>
                'Source account is required.',

            'from_account_id.exists' =>
                'Selected source account is invalid.',

            'to_branch_id.exists' =>
                'Selected destination branch is invalid.',

            'to_branch_id.different' =>
                'Source and destination branches must be different.',

            'to_account_id.required' =>
                'Destination account is required.',

            'to_account_id.exists' =>
                'Selected destination account is invalid.',

            'to_account_id.different' =>
                'Source and destination accounts must be different.',

            'transfer_date.required' =>
                'Transfer date is required.',

            'transfer_date.date' =>
                'Transfer date is invalid.',

            'amount.required' =>
                'Transfer amount is required.',

            'amount.numeric' =>
                'Transfer amount must be numeric.',

            'amount.gt' =>
                'Transfer amount must be greater than zero.',

            'amount.decimal' =>
                'Transfer amount may contain a maximum of two decimal places.',

            'description.max' =>
                'Description may not exceed 1000 characters.',

        ];
    }
}