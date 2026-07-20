<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
{
    return [

        'code' => [

            'required',

            'string',

            'max:10',

            Rule::unique('account_groups', 'code')
                ->ignore($this->route('account_group')),

        ],

        'name' => [

            'required',

            'string',

            'max:150',

        ],

        'description' => [

            'nullable',

            'string',

        ],

        'sort_order' => [

            'nullable',

            'integer',

            'min:0',

        ],

        'status' => [

            'required',

            'boolean',

        ],

    ];
}
    public function messages(): array
    {
        return [

            'code.required' => 'Account Group Code is required.',

            'code.unique' => 'Account Group Code already exists.',

            'name.required' => 'Account Group Name is required.',

            'status.required' => 'Status is required.',

        ];
    }
}