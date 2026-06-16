<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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

            'company_name' => [

                'required',

                'max:255'

            ],

            'legal_name' => [

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

            'website' => [

                'nullable',

                'max:255'

            ],

            'tax_number' => [

                'nullable',

                'max:255'

            ],

            'director_name' => [

                'nullable',

                'max:255'

            ],

          'logo' => [

                'nullable',

                'image',

                'mimes:jpg,jpeg,png',

                'max:2048'

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

            'postal_code' => [

                'nullable',

                'max:20'

            ],

            'status' => [

                'required',

                'boolean'

            ],

        ];
    }
}