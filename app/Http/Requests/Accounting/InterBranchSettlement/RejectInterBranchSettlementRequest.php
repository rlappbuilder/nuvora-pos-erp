<?php

namespace App\Http\Requests\Accounting\InterBranchSettlement;

use Illuminate\Foundation\Http\FormRequest;

class RejectInterBranchSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejected_reason' => [
                'required',
                'string',
                'min:3',
                'max:1000',
            ],
        ];
    }
}