<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class BulkActivateProductVariantPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'ids' => [

                'required',

                'array',

                'min:1',

            ],

            'ids.*' => [

                'exists:product_variant_prices,id',

            ],

        ];
    }
}