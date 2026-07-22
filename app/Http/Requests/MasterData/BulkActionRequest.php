<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionRequest extends FormRequest
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
        return [
            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'integer',
                'distinct',
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Please select at least one record.',
            'ids.array'    => 'Invalid selected records.',
            'ids.min'      => 'Please select at least one record.',
            'ids.*.integer'=> 'Invalid record selected.',
            'ids.*.distinct'=> 'Duplicate record detected.',
        ];
    }
}