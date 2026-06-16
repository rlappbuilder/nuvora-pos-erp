<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'branch_id' => [

                'required',

                'exists:branches,id'

            ],

            'name' => [

                'required',

                'max:255'

            ],

            'warehouse_type' => [

                'required'

            ],

            'pic_name' => [

                'nullable',

                'max:255'

            ],

            'phone' => [

                'nullable',

                'max:100'

            ],

            'email' => [

                'nullable',

                'email'

            ],

            'address' => [

                'nullable'

            ],

            'status' => [

                'required',

                'boolean'

            ],

        ];
    }
}