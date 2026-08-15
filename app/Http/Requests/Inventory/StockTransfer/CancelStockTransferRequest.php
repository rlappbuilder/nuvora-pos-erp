<?php

namespace App\Http\Requests\Inventory\StockTransfer;

use Illuminate\Foundation\Http\FormRequest;

class CancelStockTransferRequest extends FormRequest
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
                'Cancellation reason must be a valid text.',

            'reason.max' =>
                'Cancellation reason may not exceed 1000 characters.',

        ];
    }
}