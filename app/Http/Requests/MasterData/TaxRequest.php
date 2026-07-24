<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $taxId = $this->route('tax')?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('taxes', 'code')->ignore($taxId),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'type' => [
                'required',
                Rule::in([
                    'Percentage',
                    'Fixed',
                ]),
            ],

            'rate' => [
                'required',
                'numeric',
                'min:0',
            ],

            'is_default' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'Code',
            'name' => 'Name',
            'type' => 'Type',
            'rate' => 'Rate',
            'is_default' => 'Default Tax',
            'is_active' => 'Active Status',
            'description' => 'Description',
        ];
    }
}