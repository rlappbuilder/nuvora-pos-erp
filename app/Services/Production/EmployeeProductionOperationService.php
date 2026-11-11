<?php

namespace App\Services\Production;

use App\Models\Production\EmployeeProductionOperation;
use App\Models\Production\EmployeeProductionOperationRate;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EmployeeProductionOperationService
{
    /*
    |--------------------------------------------------------------------------
    | Store Assignment
    |--------------------------------------------------------------------------
    */

    public function store(
        array $data
    ): EmployeeProductionOperation {

        return DB::transaction(function () use ($data) {

            $existing = EmployeeProductionOperation::withTrashed()
                ->where('company_id', $data['company_id'])
                ->where('employee_id', $data['employee_id'])
                ->where(
                    'production_operation_id',
                    $data['production_operation_id']
                )
                ->lockForUpdate()
                ->first();

            if ($existing) {

                if (! $existing->trashed()) {
                    throw new RuntimeException(
                        'This production operation is already assigned to this employee.'
                    );
                }

                $existing->restore();

                $existing->update([
                    'wage_rate' => 0,
                    'is_active' => $data['is_active'],
                    'updated_by' => auth()->id(),
                    'deleted_by' => null,
                ]);

                return $existing->fresh([
                    'company',
                    'employee',
                    'productionOperation',
                    'creator',
                    'updater',
                    'deleter',
                    'rates.productVariant.product',
                ]);
            }

            // Parent assignment tidak lagi menyimpan tarif upah.
            unset($data['wage_rate']);

            $data['wage_rate'] = 0;

            $assignment = EmployeeProductionOperation::create($data);

            return $assignment->fresh([
                'company',
                'employee',
                'productionOperation',
                'creator',
                'updater',
                'deleter',
                'rates.productVariant.product',
            ]);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Update Assignment
    |--------------------------------------------------------------------------
    */

    public function update(
        EmployeeProductionOperation $employeeProductionOperation,
        array $data
    ): EmployeeProductionOperation {

        return DB::transaction(function () use (
            $employeeProductionOperation,
            $data
        ) {

            // Tarif tidak boleh diubah melalui parent assignment.
            unset(
                $data['wage_rate'],
                $data['company_id'],
                $data['employee_id'],
                $data['production_operation_id']
            );

            $data['updated_by'] = auth()->id();

            $employeeProductionOperation->update($data);

            return $employeeProductionOperation->fresh([
                'company',
                'employee',
                'productionOperation',
                'creator',
                'updater',
                'deleter',
                'rates.productVariant.product',
            ]);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Destroy Assignment
    |--------------------------------------------------------------------------
    */

    public function destroy(
        EmployeeProductionOperation $employeeProductionOperation
    ): void {

        DB::transaction(function () use (
            $employeeProductionOperation
        ) {

            $employeeProductionOperation->update([
                'deleted_by' => auth()->id(),
            ]);

            $employeeProductionOperation->delete();

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Store Product Variant Rate
    |--------------------------------------------------------------------------
    */

    public function storeRate(
        EmployeeProductionOperation $employeeProductionOperation,
        array $data
    ): EmployeeProductionOperationRate {

        return DB::transaction(function () use (
            $employeeProductionOperation,
            $data
        ) {

            $existing = EmployeeProductionOperationRate::withTrashed()
                ->where(
                    'employee_production_operation_id',
                    $employeeProductionOperation->id
                )
                ->where(
                    'product_variant_id',
                    $data['product_variant_id']
                )
                ->lockForUpdate()
                ->first();

            if ($existing) {

                if (! $existing->trashed()) {
                    throw new RuntimeException(
                        'A wage rate for this product variant already exists.'
                    );
                }

                $existing->restore();

                $existing->update([
                    'wage_rate' => $data['wage_rate'],
                    'is_active' => $data['is_active'],
                    'updated_by' => auth()->id(),
                    'deleted_by' => null,
                ]);

                return $existing->fresh([
                    'employeeProductionOperation',
                    'productVariant.product',
                    'creator',
                    'updater',
                    'deleter',
                ]);
            }

            $data['employee_production_operation_id'] =
                $employeeProductionOperation->id;

            $data['created_by'] = auth()->id();

            $rate = EmployeeProductionOperationRate::create($data);

            return $rate->load([
                'employeeProductionOperation',
                'productVariant.product',
                'creator',
                'updater',
                'deleter',
            ]);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Update Product Variant Rate
    |--------------------------------------------------------------------------
    */

    public function updateRate(
        EmployeeProductionOperationRate $rate,
        array $data
    ): EmployeeProductionOperationRate {

        return DB::transaction(function () use (
            $rate,
            $data
        ) {

            // Assignment dan Product Variant tidak boleh
            // dipindahkan melalui proses update tarif.
            unset(
                $data['employee_production_operation_id'],
                $data['product_variant_id'],
                $data['created_by']
            );

            $data['updated_by'] = auth()->id();

            $rate->update($data);

            return $rate->fresh([
                'employeeProductionOperation',
                'productVariant.product',
                'creator',
                'updater',
                'deleter',
            ]);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Destroy Product Variant Rate
    |--------------------------------------------------------------------------
    */

    public function destroyRate(
        EmployeeProductionOperationRate $rate
    ): void {

        DB::transaction(function () use ($rate) {

            $rate->update([
                'deleted_by' => auth()->id(),
            ]);

            $rate->delete();

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */

    public function bulkDelete(
        array $ids
    ): void {

        DB::transaction(function () use ($ids) {

            $employeeProductionOperations = EmployeeProductionOperation::query()
                ->whereIn('id', $ids)
                ->get();

            foreach ($employeeProductionOperations as $employeeProductionOperation) {

                $this->destroy($employeeProductionOperation);

            }

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Activate
    |--------------------------------------------------------------------------
    */

    public function bulkActivate(
        array $ids
    ): void {

        DB::transaction(function () use ($ids) {

            EmployeeProductionOperation::query()
                ->whereIn('id', $ids)
                ->update([
                    'is_active' => true,
                    'updated_by' => auth()->id(),
                ]);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Deactivate
    |--------------------------------------------------------------------------
    */

    public function bulkDeactivate(
        array $ids
    ): void {

        DB::transaction(function () use ($ids) {

            EmployeeProductionOperation::query()
                ->whereIn('id', $ids)
                ->update([
                    'is_active' => false,
                    'updated_by' => auth()->id(),
                ]);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Find
    |--------------------------------------------------------------------------
    */

    public function find(
        EmployeeProductionOperation $employeeProductionOperation
    ): EmployeeProductionOperation {

        return $employeeProductionOperation->load([
            'company',
            'employee',
            'productionOperation',
            'creator',
            'updater',
            'deleter',
            'rates.productVariant.product',
        ]);

    }
}