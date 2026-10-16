<?php

namespace App\Http\Requests\Accounting\InterBranchSettlement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInterBranchSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settlement_date' => [
                'required',
                'date',
            ],

            'from_branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            'to_branch_id' => [
                'required',
                'integer',
                'different:from_branch_id',
                'exists:branches,id',
            ],

            'from_account_id' => [
                'required',
                'integer',
                'exists:chart_of_accounts,id',
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'details' => [
                'required',
                'array',
                'min:1',
            ],

            'details.*.fund_transfer_header_id' => [
                'required',
                'integer',
                'distinct',
                'exists:fund_transfer_headers,id',
            ],

            'details.*.amount' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'to_branch_id.different' => 'Cabang pembayar dan penerima harus berbeda.',
            'details.required' => 'Pilih minimal satu transaksi Fund Transfer.',
            'details.min' => 'Pilih minimal satu transaksi Fund Transfer.',
            'details.*.fund_transfer_header_id.distinct' => 'Transaksi Fund Transfer tidak boleh dipilih dua kali.',
        ];
    }
}