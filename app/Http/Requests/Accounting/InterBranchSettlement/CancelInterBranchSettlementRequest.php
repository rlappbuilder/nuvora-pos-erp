<?php

namespace App\Http\Requests\Accounting\InterBranchSettlement;

use Illuminate\Foundation\Http\FormRequest;

class CancelInterBranchSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancelled_reason' => [
                'required',
                'string',
                'min:3',
                'max:1000',
            ],
        ];
    }
}