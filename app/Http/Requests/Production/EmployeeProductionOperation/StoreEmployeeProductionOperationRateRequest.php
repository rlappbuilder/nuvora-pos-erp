<?php

namespace App\Http\Requests\Production\EmployeeProductionOperation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeProductionOperationRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => [
                'required',
                'integer',
                Rule::exists('product_variants', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

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
            'product_variant_id' => 'product variant',
            'wage_rate' => 'wage rate',
            'is_active' => 'status',
        ];
    }
}