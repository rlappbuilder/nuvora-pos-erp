<?php

namespace App\Http\Requests\Product\ProductAttributeValue;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductAttributeValueStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => trim((string) $this->code),
            'value' => trim((string) $this->value),
            'display_value' => trim((string) $this->display_value),
            'color_code' => $this->color_code
                ? trim((string) $this->color_code)
                : null,
            'description' => $this->description
                ? trim((string) $this->description)
                : null,
        ]);
    }

    /**
     * Get the validation rules.
     */
    public function rules(): array
    {
        return [

            'company_id' => [
                'nullable',
                'exists:companies,id',
            ],

            'product_attribute_id' => [
                'required',
                'exists:product_attributes,id',
            ],

            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('product_attribute_values')
                    ->where(fn ($query) => $query->where(
                        'product_attribute_id',
                        $this->product_attribute_id
                    )),
            ],

            'value' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_attribute_values')
                    ->where(fn ($query) => $query->where(
                        'product_attribute_id',
                        $this->product_attribute_id
                    )),
            ],

            'display_value' => [
                'required',
                'string',
                'max:100',
            ],

            'color_code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'boolean',
            ],

        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Code already exists for this Product Attribute.',
            'value.unique' => 'Value already exists for this Product Attribute.',
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'company_id' => 'Company',
            'product_attribute_id' => 'Product Attribute',
            'code' => 'Code',
            'value' => 'Value',
            'display_value' => 'Display Value',
            'color_code' => 'Color Code',
            'sort_order' => 'Sort Order',
            'description' => 'Description',
            'is_active' => 'Status',
        ];
    }
}