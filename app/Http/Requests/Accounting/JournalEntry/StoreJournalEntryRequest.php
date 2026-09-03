<?php

namespace App\Http\Requests\Accounting\JournalEntry;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
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

            'accounting_journal_id' => [
                'required',
                'integer',
                'exists:accounting_journals,id',
            ],

            'fiscal_year_id' => [
                'required',
                'integer',
                'exists:fiscal_years,id',
            ],

            'accounting_period_id' => [
                'required',
                'integer',
                'exists:accounting_periods,id',
            ],

            'entry_date' => [
                'required',
                'date',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],


            /*
            |--------------------------------------------------------------------------
            | Lines
            |--------------------------------------------------------------------------
            */

            'lines' => [
                'required',
                'array',
                'min:1',
            ],

            'lines.*.account_id' => [
                'required',
                'integer',
                'exists:chart_of_accounts,id',
            ],

            'lines.*.description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'lines.*.debit' => [
                'required',
                'numeric',
                'min:0',
            ],

            'lines.*.credit' => [
                'required',
                'numeric',
                'min:0',
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
                'Selected branch does not exist.',

            'accounting_journal_id.required' =>
                'Accounting journal is required.',

            'accounting_journal_id.exists' =>
                'Selected accounting journal does not exist.',

            'fiscal_year_id.required' =>
                'Fiscal year is required.',

            'fiscal_year_id.exists' =>
                'Selected fiscal year does not exist.',

            'accounting_period_id.required' =>
                'Accounting period is required.',

            'accounting_period_id.exists' =>
                'Selected accounting period does not exist.',

            'entry_date.required' =>
                'Entry date is required.',

            'reference.max' =>
                'Reference cannot exceed 100 characters.',

            'description.max' =>
                'Description cannot exceed 1000 characters.',


            /*
            |--------------------------------------------------------------------------
            | Lines
            |--------------------------------------------------------------------------
            */

            'lines.required' =>
                'At least one journal entry line is required.',

            'lines.min' =>
                'At least one journal entry line is required.',

            'lines.*.account_id.required' =>
                'Account is required.',

            'lines.*.account_id.exists' =>
                'Selected account does not exist.',

            'lines.*.description.max' =>
                'Line description cannot exceed 1000 characters.',

            'lines.*.debit.required' =>
                'Debit is required.',

            'lines.*.debit.numeric' =>
                'Debit must be a number.',

            'lines.*.debit.min' =>
                'Debit cannot be negative.',

            'lines.*.credit.required' =>
                'Credit is required.',

            'lines.*.credit.numeric' =>
                'Credit must be a number.',

            'lines.*.credit.min' =>
                'Credit cannot be negative.',

        ];
    }
}