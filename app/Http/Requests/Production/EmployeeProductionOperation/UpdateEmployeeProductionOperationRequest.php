<?php

namespace App\Http\Requests\Production\EmployeeProductionOperation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeProductionOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'is_active' => 'status',
        ];
    }
}