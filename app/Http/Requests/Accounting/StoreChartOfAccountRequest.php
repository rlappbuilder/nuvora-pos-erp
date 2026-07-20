<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChartOfAccountRequest extends FormRequest
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
            'company_id' => [
                'required',
                'exists:companies,id',
            ],

            'parent_id' => [
                'nullable',
                'exists:chart_of_accounts,id',
            ],

            'account_category_id' => [
                'required',
                'exists:account_categories,id',
            ],

            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('chart_of_accounts')
                    ->where(fn ($query) => $query->where('company_id', $this->company_id)),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'normal_balance' => [
                'required',
                Rule::in(['Debit', 'Credit']),
            ],

            'is_header' => [
                'sometimes',
                'boolean',
            ],

            'is_posting' => [
                'sometimes',
                'boolean',
            ],

            'opening_balance' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'company_id'          => 'Company',
            'parent_id'           => 'Parent Account',
            'account_category_id' => 'Account Category',
            'code'                => 'Account Code',
            'name'                => 'Account Name',
            'normal_balance'      => 'Normal Balance',
            'is_header'           => 'Header Account',
            'is_posting'          => 'Posting Account',
            'opening_balance'     => 'Opening Balance',
            'is_active'           => 'Status',
            'description'         => 'Description',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'The account code has already been used for this company.',
        ];
    }
}