<?php

namespace App\Http\Requests\Production\Bom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\DB;

class StoreProductionBomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = auth()->user()->company_id;

        return [

            /*
            |--------------------------------------------------------------------------
            | BOM Identity
            |--------------------------------------------------------------------------
            */

            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')
                    ->where('company_id', $companyId),
            ],

            'product_variant_id' => [
                'required',
                'integer',
                Rule::exists('product_variants', 'id')
                    ->where('is_active', true),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'bom_type' => [
                'required',
                Rule::in([
                    'standard',
                    'alternative',
                ]),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Output
            |--------------------------------------------------------------------------
            */

            'output_qty' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'output_unit_id' => [
                'required',
                'integer',
                'exists:units,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Effective Period
            |--------------------------------------------------------------------------
            */

            'effective_from' => [
                'nullable',
                'date',
            ],

            'effective_until' => [
                'nullable',
                'date',
                'after_or_equal:effective_from',
            ],

            /*
            |--------------------------------------------------------------------------
            | Materials
            |--------------------------------------------------------------------------
            */

            'materials' => [
                'required',
                'array',
                'min:1',
            ],

            'materials.*.component_variant_id' => [
                'required',
                'integer',
                Rule::exists('product_variants', 'id')
                    ->where('is_active', true),
            ],

            'materials.*.unit_id' => [
                'required',
                'integer',
                'exists:units,id',
            ],

            'materials.*.qty' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'materials.*.scrap_percent' => [
                'required',
                'numeric',
                'min:0',
                'max:99.9999',
            ],

            'materials.*.operation_sequence' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'materials.*.sequence' => [
                'required',
                'integer',
                'min:1',
                'distinct',
            ],

            'materials.*.notes' => [
                'nullable',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | Production Operations
            |--------------------------------------------------------------------------
            */

            'operations' => [
                'sometimes',
                'array',
            ],

            'operations.*.production_operation_id' => [
                'required',
                'integer',
                Rule::exists('production_operations', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'operations.*.sequence' => [
                'required',
                'integer',
                'min:1',
                'distinct',
            ],

            'operations.*.planned_duration_minutes' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'operations.*.instructions' => [
                'nullable',
                'string',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Additional Validation
    |--------------------------------------------------------------------------
    */

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {

            $productVariantId = $this->input('product_variant_id');

            /*
            |--------------------------------------------------------------------------
            | Output Unit
            |--------------------------------------------------------------------------
            */

            if (
                $productVariantId &&
                $this->filled('output_unit_id')
            ) {
                $validOutputUnit = DB::table('product_variant_units')
                    ->where('product_variant_id', $productVariantId)
                    ->where('unit_id', $this->input('output_unit_id'))
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->exists();

                if (!$validOutputUnit) {
                    $validator->errors()->add(
                        'output_unit_id',
                        'Satuan hasil tidak terdaftar atau tidak aktif pada Product Variant.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Material Validation
            |--------------------------------------------------------------------------
            */

            $materials = $this->input('materials', []);

            foreach ($materials as $index => $material) {

                $componentVariantId =
                    $material['component_variant_id'] ?? null;

                $unitId = $material['unit_id'] ?? null;

                /*
                | Prevent direct self-reference
                */

                if (
                    $productVariantId &&
                    $componentVariantId &&
                    (int) $productVariantId === (int) $componentVariantId
                ) {
                    $validator->errors()->add(
                        "materials.{$index}.component_variant_id",
                        'Product Variant hasil tidak boleh menjadi komponennya sendiri.'
                    );
                }

                /*
                | Component Unit Validation
                */

                if ($componentVariantId && $unitId) {

                    $validUnit = DB::table('product_variant_units')
                        ->where('product_variant_id', $componentVariantId)
                        ->where('unit_id', $unitId)
                        ->where('is_active', true)
                        ->whereNull('deleted_at')
                        ->exists();

                    if (!$validUnit) {
                        $validator->errors()->add(
                            "materials.{$index}.unit_id",
                            'Satuan tidak terdaftar atau tidak aktif pada Product Variant komponen.'
                        );
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Material Operation Sequence Validation
            |--------------------------------------------------------------------------
            */

            $operations = $this->input('operations', []);

            $operationSequences = collect($operations)
                ->pluck('sequence')
                ->map(fn ($sequence) => (int) $sequence)
                ->all();

            foreach ($materials as $index => $material) {

                $operationSequence =
                    $material['operation_sequence'] ?? null;

                if (
                    $operationSequence !== null &&
                    !in_array(
                        (int) $operationSequence,
                        $operationSequences,
                        true
                    )
                ) {
                    $validator->errors()->add(
                        "materials.{$index}.operation_sequence",
                        'Tahapan operasi yang dipilih tidak ditemukan dalam routing BOM.'
                    );
                }
            }
        });
    }
}