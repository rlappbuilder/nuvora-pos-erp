<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PosSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'details' => [
                'required',
                'array',
                'min:1',
            ],

            'details.*.product_variant_id' => [
                'required',
                'integer',
                'exists:product_variants,id',
            ],

            'details.*.unit_id' => [
                'required',
                'integer',
                'exists:units,id',
            ],

            'details.*.price_type_id' => [
                'required',
                'integer',
                'exists:price_types,id',
            ],

            'details.*.qty' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'details.*.discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'payments' => [
                'required',
                'array',
                'min:1',
            ],

            'payments.*.payment_method' => [
                'required',
                Rule::in([
                    'cash',
                    'qris',
                    'debit_card',
                    'transfer',
                    'e_wallet',
                ]),
            ],

            'payments.*.amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'payments.*.reference_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'payments.*.note' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'details.required' => 'Detail transaksi wajib diisi.',
            'details.min' => 'Minimal satu produk harus dipilih.',
            'details.*.qty.gt' => 'Qty harus lebih dari 0.',
            'payments.required' => 'Payment wajib diisi.',
            'payments.min' => 'Minimal satu payment harus dipilih.',
            'payments.*.amount.gt' => 'Nominal payment harus lebih dari 0.',
        ];
    }
}