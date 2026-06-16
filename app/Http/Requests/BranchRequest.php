<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [

            'company_id' => [

                'required',

                'exists:companies,id'

            ],

            'name' => [

                'required',

                'max:255'

            ],

            'manager_name' => [

                'nullable',

                'max:255'

            ],

            'phone' => [

                'nullable',

                'max:50'

            ],

            'email' => [

                'nullable',

                'email',

                'max:255'

            ],

            'address' => [

                'nullable'

            ],

            'city' => [

                'nullable',

                'max:255'

            ],

            'province' => [

                'nullable',

                'max:255'

            ],

            'is_head_office' => [

                'required',

                'boolean'

            ],

            'status' => [

                'required',

                'boolean'

            ],

        ];
    }
}