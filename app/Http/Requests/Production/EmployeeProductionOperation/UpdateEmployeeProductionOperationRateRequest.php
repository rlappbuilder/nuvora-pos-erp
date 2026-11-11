<?php

namespace App\Http\Requests\Production\EmployeeProductionOperation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeProductionOperationRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wage_rate' => [
                'required',
                'numeric',
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
            'wage_rate' => 'wage rate',
            'is_active' => 'status',
        ];
    }
}