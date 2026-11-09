<?php

namespace App\Http\Requests\Production\ProductionOperation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductionOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:100',
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

            'is_active' => [
                'required',
                'boolean',
            ],

        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'operation name',
            'description' => 'description',
            'sort_order' => 'sort order',
            'is_active' => 'status',
        ];
    }
}