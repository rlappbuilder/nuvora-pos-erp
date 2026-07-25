<?php

namespace App\Http\Requests\Product\ProductAttribute;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProductAttributeUpdateRequest extends FormRequest
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
            'name' => trim((string) $this->name),
            'display_name' => trim((string) $this->display_name),
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
      $productAttribute = $this->route('product_attribute');

        $productAttributeId = is_object($productAttribute)
            ? $productAttribute->id
            : $productAttribute;

        return [
            'company_id' => ['nullable', 'exists:companies,id'],

            'code' => [
                'required',
                'string',
                'max:30',
               Rule::unique('product_attributes', 'code')
                ->ignore($productAttributeId),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_attributes', 'name')
                ->ignore($productAttributeId),
            ],

            'display_name' => [
                'required',
                'string',
                'max:100',
            ],

            'input_type' => [
                'required',
                Rule::in([
                    'Select',
                    'Radio',
                    'Button',
                    'Color',
                    'Text',
                ]),
            ],

            'is_required' => [
                'boolean',
            ],

            'is_variant' => [
                'boolean',
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
            'code.unique' => 'Code Ready In use.',
            'name.unique' => 'Name ready in use.',
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'company_id'   => 'Company',
            'code'         => 'Code',
            'name'         => 'Name',
            'display_name' => 'Display Name',
            'input_type'   => 'Input Type',
            'is_required'  => 'Required',
            'is_variant'   => 'Variant',
            'sort_order'   => 'Sort Order',
            'description'  => 'Description',
            'is_active'    => 'Status',
        ];
    }
}