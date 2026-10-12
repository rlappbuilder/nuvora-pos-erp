<?php

namespace App\Http\Requests\Accounting\FundTransfer;

use Illuminate\Foundation\Http\FormRequest;

class RejectFundTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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