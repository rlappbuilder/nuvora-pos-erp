<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class RejectCashierDepositRequest extends FormRequest
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
                'min:3',
                'max:1000',
            ],

        ];
    }

    public function attributes(): array
    {
        return [

            'reason' =>
                'Rejection Reason',

        ];
    }
}