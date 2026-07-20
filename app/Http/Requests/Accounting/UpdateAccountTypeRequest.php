<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    

public function rules(): array
{
    return [

        'account_group_id' => [

            'required',

            'exists:account_groups,id',

        ],

        'code' => [

            'required',

            'string',

            'max:10',

            Rule::unique('account_types', 'code')
            ->ignore($this->route('account_type')),
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
}
