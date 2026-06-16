<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SizeRequest extends FormRequest
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

            'sort_order' => [

                'required',

                'integer',

                'min:0'

            ],

            'status' => [

                'required',

                'boolean'

            ],

        ];
    }
}