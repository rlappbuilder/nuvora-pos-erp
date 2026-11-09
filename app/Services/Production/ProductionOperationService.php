<?php

namespace App\Services\Production;

use App\Models\Production\ProductionOperation;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Support\Facades\DB;

class ProductionOperationService
{
    public function __construct(
        protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        array $data
    ): ProductionOperation {

        return DB::transaction(function () use ($data) {

            $data['code'] = $this->codeGeneratorService
                ->next('production_operation');

            return ProductionOperation::create($data);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        ProductionOperation $operation,
        array $data
    ): ProductionOperation {

        return DB::transaction(function () use (
            $operation,
            $data
        ) {

            // Generated code is immutable.
            unset($data['code']);

            $operation->update($data);

            return $operation;

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ProductionOperation $operation
    ): void {

        $operation->delete();

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

            $operations = ProductionOperation::query()
                ->whereIn('id', $ids)
                ->get();

            foreach ($operations as $operation) {

                $this->destroy($operation);

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

            ProductionOperation::query()
                ->whereIn('id', $ids)
                ->update([
                    'is_active' => true,
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

            ProductionOperation::query()
                ->whereIn('id', $ids)
                ->update([
                    'is_active' => false,
                ]);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Find
    |--------------------------------------------------------------------------
    */

    public function find(
        ProductionOperation $operation
    ): ProductionOperation {

        return $operation->load([
            'company',
            'creator',
            'updater',
            'deleter',
        ]);

    }
}