<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ColorRequest extends FormRequest
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

            'name' => [

                'required',

                'max:255'

            ],

            'description' => [

                'nullable'

            ],

            'status' => [

                'required',

                'boolean'

            ],

        ];
    }
}