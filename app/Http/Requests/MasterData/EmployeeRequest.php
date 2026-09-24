<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'position' => [
                'nullable',
                'string',
                'max:255',
            ],

            'join_date' => [
                'nullable',
                'date',
            ],

            'status' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'Employee name wajib diisi.',

            'name.max' =>
                'Employee name maksimal 255 karakter.',

            'phone.max' =>
                'Phone maksimal 50 karakter.',

            'email.email' =>
                'Format email tidak valid.',

            'email.max' =>
                'Email maksimal 255 karakter.',

            'position.max' =>
                'Position maksimal 255 karakter.',

            'join_date.date' =>
                'Format join date tidak valid.',

            'status.required' =>
                'Status wajib dipilih.',

            'status.boolean' =>
                'Status tidak valid.',
        ];
    }
}