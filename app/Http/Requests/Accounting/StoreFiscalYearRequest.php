<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFiscalYearRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    public function authorize(): bool
    {
        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */

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
            | Fiscal Year
            |--------------------------------------------------------------------------
            */

            'year' => [
                'required',
                'integer',
                'digits:4',
                'min:2000',
                'max:2100',

                Rule::unique('fiscal_years', 'year')
                    ->where(function ($query) {
                        return $query->where(
                            'company_id',
                            $this->company_id
                        );
                    })
                    ->whereNull('deleted_at'),
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


    /*
    |--------------------------------------------------------------------------
    | Validation Messages
    |--------------------------------------------------------------------------
    */

    public function messages(): array
    {
        return [

            'company_id.required' =>
                'Company is required.',

            'company_id.exists' =>
                'Selected company is invalid.',

            'year.required' =>
                'Fiscal year is required.',

            'year.digits' =>
                'Fiscal year must contain exactly 4 digits.',

            'year.min' =>
                'Fiscal year must be 2000 or later.',

            'year.max' =>
                'Fiscal year cannot be later than 2100.',

            'year.unique' =>
                'This fiscal year already exists for the selected company.',

            'description.max' =>
                'Description may not exceed 1000 characters.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function attributes(): array
    {
        return [

            'company_id' =>
                'company',

            'year' =>
                'fiscal year',

            'description' =>
                'description',

        ];
    }
}