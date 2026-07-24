<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $currency = $this->route('currency');

        return [
            'code' => [
                'required',
                'string',
                'min:3',
                'max:3',
                Rule::unique('currencies', 'code')->ignore($currency),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'symbol' => [
                'required',
                'string',
                'max:10',
            ],

            'decimal_places' => [
                'required',
                'integer',
                'min:0',
                'max:8',
            ],

            'exchange_rate' => [
                'required',
                'numeric',
                'min:0',
            ],

            'is_base_currency' => [
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

    /**
     * Prepare the data for validation.
     */
protected function prepareForValidation(): void
{
    $this->merge([
        'code' => strtoupper($this->code),

        'is_base_currency' => $this->boolean('is_base_currency'),
        'is_active' => $this->boolean('is_active'),
    ]);
}
}