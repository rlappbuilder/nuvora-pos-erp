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
    $colorId = $this->route('color')?->id;

    return [

        'code' => [
            'nullable',
            'max:50',
            'unique:colors,code,' . $colorId,
        ],

        'name' => [
            'required',
            'string',
            'max:255',
        ],

        'hex_color' => [
            'nullable',
            'string',
            'max:20',
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