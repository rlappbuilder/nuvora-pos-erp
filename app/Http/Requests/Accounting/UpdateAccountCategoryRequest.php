<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountCategoryRequest extends FormRequest
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

        'account_type_id' => [

            'required',

            'exists:account_types,id',

        ],

        'code' => [

        'required',

        'string',

        'max:10',

        Rule::unique('account_categories', 'code')
            ->ignore($this->route('account_category')),

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
