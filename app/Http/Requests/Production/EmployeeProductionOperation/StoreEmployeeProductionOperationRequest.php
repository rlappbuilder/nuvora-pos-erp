<?php

namespace App\Http\Requests\Production\EmployeeProductionOperation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeProductionOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'production_operation_id' => [
                'required',
                'integer',
                Rule::exists('production_operations', 'id')
                    ->where('company_id', auth()->user()->company_id),
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
            'production_operation_id' => 'production operation',
            'is_active' => 'status',
        ];
    }
}