<?php

namespace App\Http\Requests\Inventory\StockOpname;

use Illuminate\Foundation\Http\FormRequest;

class CancelStockOpnameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

            'reason' => [
                'required',
                'string',
                'max:1000',
            ],

        ];
    }


    public function messages(): array
    {
        return [

            'reason.required' =>
                'Cancellation reason is required.',

            'reason.string' =>
                'Cancellation reason must be valid text.',

            'reason.max' =>
                'Cancellation reason cannot exceed 1000 characters.',

        ];
    }
}