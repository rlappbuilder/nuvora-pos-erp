<?php

namespace App\Http\Requests\Purchasing\PurchaseInvoice;

use Illuminate\Foundation\Http\FormRequest;

class RejectPurchaseInvoiceRequest extends FormRequest
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

            /*
            |--------------------------------------------------------------------------
            | Rejection Reason
            |--------------------------------------------------------------------------
            */

            'reason' => [
                'required',
                'string',
                'min:3',
                'max:1000',
            ],

        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [

            'reason.required' =>
                'Rejection reason is required.',

            'reason.min' =>
                'Rejection reason must be at least 3 characters.',

            'reason.max' =>
                'Rejection reason may not exceed 1000 characters.',

        ];
    }
}