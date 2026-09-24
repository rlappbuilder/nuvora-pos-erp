<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'employee_id' => [
                'required',
                'integer',
                'exists:employees,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'name')
                    ->ignore($user?->id),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user?->id),
            ],

            'password' => [
                $this->isMethod('post')
                    ? 'required'
                    : 'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
            ],

            'role' => [
                'required',
                'string',
                'exists:roles,name',
            ],

            'branch_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'branch_ids.*' => [
                'integer',
                'distinct',
                'exists:branches,id',
            ],

            'default_branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' =>
                'Employee wajib dipilih.',

            'employee_id.exists' =>
                'Employee tidak ditemukan.',

            'name.required' =>
                'Username wajib diisi.',

            'name.regex' =>
                'Username hanya boleh mengandung huruf, angka, titik, underscore, dan tanda minus.',

            'name.unique' =>
                'Username sudah digunakan.',

            'email.required' =>
                'Email wajib diisi.',

            'email.email' =>
                'Format email tidak valid.',

            'email.unique' =>
                'Email sudah digunakan.',

            'password.required' =>
                'Password wajib diisi.',

            'password.min' =>
                'Password minimal 8 karakter.',

            'password.confirmed' =>
                'Konfirmasi password tidak cocok.',

            'company_id.required' =>
                'Company wajib dipilih.',

            'company_id.exists' =>
                'Company tidak ditemukan.',

            'role.required' =>
                'Role wajib dipilih.',

            'role.exists' =>
                'Role tidak ditemukan.',

            'branch_ids.required' =>
                'Cabang wajib dipilih.',

            'branch_ids.min' =>
                'Minimal satu cabang harus dipilih.',

            'branch_ids.*.exists' =>
                'Cabang tidak ditemukan.',

            'default_branch_id.required' =>
                'Default branch wajib dipilih.',

            'default_branch_id.exists' =>
                'Default branch tidak ditemukan.',
        ];
    }
}