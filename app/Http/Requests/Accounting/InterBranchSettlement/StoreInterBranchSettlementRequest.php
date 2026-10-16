<?php

namespace App\Http\Requests\Accounting\InterBranchSettlement;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterBranchSettlementRequest extends FormRequest
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
            'settlement_date.required' => 'Tanggal settlement wajib diisi.',
            'from_branch_id.required' => 'Cabang pembayar wajib dipilih.',
            'to_branch_id.required' => 'Cabang penerima wajib dipilih.',
            'to_branch_id.different' => 'Cabang pembayar dan penerima harus berbeda.',
            'from_account_id.required' => 'Rekening pembayaran wajib dipilih.',
            'amount.required' => 'Total settlement wajib diisi.',
            'amount.gt' => 'Total settlement harus lebih besar dari nol.',
            'details.required' => 'Pilih minimal satu transaksi Fund Transfer.',
            'details.min' => 'Pilih minimal satu transaksi Fund Transfer.',
            'details.*.fund_transfer_header_id.distinct' => 'Transaksi Fund Transfer tidak boleh dipilih dua kali.',
            'details.*.amount.required' => 'Nominal settlement setiap transaksi wajib diisi.',
            'details.*.amount.gt' => 'Nominal settlement setiap transaksi harus lebih besar dari nol.',
        ];
    }
}