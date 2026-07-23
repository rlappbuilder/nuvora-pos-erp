<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
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
        'max:50',
    ],

    'name' => [
        'required',
        'string',
        'max:255',
    ],

    'symbol' => [
        'required',
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