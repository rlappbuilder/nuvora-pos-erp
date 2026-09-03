<?php

namespace App\Http\Requests\Accounting\AccountingJournal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountingJournalRequest extends FormRequest
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
            | Company
            |--------------------------------------------------------------------------
            */

            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
            ],


            /*
            |--------------------------------------------------------------------------
            | Journal
            |--------------------------------------------------------------------------
            */

            'code' => [
                'required',
                'string',
                'max:30',
            ],

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'type' => [
                'required',
                Rule::in([
                    'General',
                    'Sales',
                    'Purchase',
                    //'Inventory',
                    'Cash',
                    'Bank',
                    'Adjustment',
                    'Opening',
                ]),
            ],


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'is_active' => [
                'required',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Journal Accounts
            |--------------------------------------------------------------------------
            */

            'accounts' => [
                'nullable',
                'array',
            ],

            'accounts.*.account_id' => [
                'required',
                'integer',
                'exists:chart_of_accounts,id',
            ],

            'accounts.*.role' => [
                'required',
                'string',
                'max:50',
            ],

            'accounts.*.is_default' => [
                'boolean',
            ],
            /*
            |--------------------------------------------------------------------------
            | Information
            |--------------------------------------------------------------------------
            */

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

        ];
    }


    public function messages(): array
    {
        return [

            'company_id.required' =>
                'Company is required.',

            'company_id.exists' =>
                'Selected company is invalid.',


            'code.required' =>
                'Journal code is required.',

            'code.max' =>
                'Journal code may not exceed 30 characters.',


            'name.required' =>
                'Journal name is required.',

            'name.max' =>
                'Journal name may not exceed 100 characters.',


            'type.required' =>
                'Journal type is required.',

            'type.in' =>
                'Selected journal type is invalid.',


            'is_active.required' =>
                'Status is required.',

            'is_active.boolean' =>
                'Status must be valid.',

        ];
    }
}