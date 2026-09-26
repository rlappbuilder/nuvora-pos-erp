<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class CashierSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],

            'cash_account_id' => [
                'required',
                'integer',
                'exists:chart_of_accounts,id',
            ],

            'opening_balance' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' =>
                'Warehouse wajib dipilih.',

            'warehouse_id.integer' =>
                'Warehouse tidak valid.',

            'warehouse_id.exists' =>
                'Warehouse tidak ditemukan.',

            'cash_account_id.required' =>
                'Cash account wajib dipilih.',

            'cash_account_id.integer' =>
                'Cash account tidak valid.',

            'cash_account_id.exists' =>
                'Cash account tidak ditemukan.',

            'opening_balance.required' =>
                'Opening balance wajib diisi.',

            'opening_balance.numeric' =>
                'Opening balance harus berupa angka.',

            'opening_balance.min' =>
                'Opening balance tidak boleh kurang dari 0.',
        ];
    }
}