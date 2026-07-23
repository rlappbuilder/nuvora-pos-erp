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

    'code' => [

        'required',

        'string',

        'max:50',

        'unique:sizes,code,' . $this->route('size')?->id,

    ],

    'name' => [

        'required',

        'string',

        'max:255',

    ],

    'sort_order' => [

        'nullable',

        'integer',

        'min:0',

    ],

    'description' => [

        'nullable',

        'string',

    ],

    'is_active' => [

        'required',

        'boolean',

    ],

];
    }
}