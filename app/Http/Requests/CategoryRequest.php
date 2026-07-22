<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
            'string',
            'max:255'
        ],

        'description' => [
            'nullable',
            'string'
        ],

        'is_active' => [
            'required',
            'boolean'
        ],

        ];
    }
}