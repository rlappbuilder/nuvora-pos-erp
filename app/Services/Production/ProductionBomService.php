<?php

namespace App\Services\Production;

use App\Models\Production\ProductionBom;
use App\Models\Production\ProductionBomVersion;
use App\Models\Production\ProductionBomOperation;
use App\Models\Production\ProductionBomMaterial;
use App\Models\Production\ProductionOperation;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ProductionBomService
{
    public function __construct(
        protected CodeGeneratorService $codeGeneratorService,
        protected DocumentActivityService $documentActivityService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Create BOM
    |--------------------------------------------------------------------------
    */

    public function createBom(
        array $data
    ): ProductionBom {

        return DB::transaction(function () use ($data) {

            $companyId = auth()->user()->company_id;
            $userId = auth()->id();

            $this->validateBranch(
                $data['branch_id'],
                $companyId
            );

            $this->validateProductVariant(
                $data['product_variant_id']
            );

            $this->validateOutputUnit(
                $data['product_variant_id'],
                $data['output_unit_id']
            );

            $this->validateMaterials(
                $data['materials'],
                $companyId
            );

            $this->assertNoCircularReference(
                $data['product_variant_id'],
                $data['materials'],
                $companyId
            );

            $bom = ProductionBom::create([
                'company_id' => $companyId,
                'branch_id' => $data['branch_id'],
                'product_variant_id' => $data['product_variant_id'],
                'code' => $this->codeGeneratorService->next(
                    'production_bom'
                ),
                'name' => $data['name'],
                'bom_type' => $data['bom_type'],
                'description' => $data['description'] ?? null,
                'is_active' => false,
                'created_by' => $userId,
            ]);

            $version = ProductionBomVersion::create([
                'production_bom_id' => $bom->id,
                'version' => 1,
                'output_qty' => $data['output_qty'],
                'output_unit_id' => $data['output_unit_id'],
                'effective_from' => $data['effective_from'] ?? null,
                'effective_until' => $data['effective_until'] ?? null,
                'status' => 'draft',
                'revision_notes' => $data['revision_notes'] ?? null,
                'created_by' => $userId,
            ]);

            $this->storeOperations(
                $version,
                $data['operations'] ?? []
            );

            $this->storeMaterials(
                $version,
                $data['materials']
            );

            $this->documentActivityService->record(
                $bom,
                'CREATED',
                null,
                'Draft',
                'Production BOM created.',
                [
                    'version' => $version->version,
                ]
            );

            return $this->getBomDetail($bom);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Update Draft
    |--------------------------------------------------------------------------
    */

    public function updateDraft(
        ProductionBom $bom,
        array $data
    ): ProductionBom {

        return DB::transaction(function () use (
            $bom,
            $data
        ) {

            $bom = ProductionBom::query()
                ->whereKey($bom->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateBomOwnership($bom);

            $version = $bom->versions()
                ->where('status', 'draft')
                ->orderByDesc('version')
                ->lockForUpdate()
                ->first();

            if (! $version) {
                throw new RuntimeException(
                    'No draft version is available for this BOM.'
                );
            }

            $this->assertVersionNotUsed($version);

            $this->validateOutputUnit(
                $bom->product_variant_id,
                $data['output_unit_id']
            );

            $this->validateMaterials(
                $data['materials'],
                $bom->company_id
            );

            $this->assertNoCircularReference(
                $bom->product_variant_id,
                $data['materials'],
                $bom->company_id,
                $version->id
            );

            $bom->update([
                'name' => $data['name'],
                'bom_type' => $data['bom_type'],
                'description' => $data['description'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            $version->update([
                'output_qty' => $data['output_qty'],
                'output_unit_id' => $data['output_unit_id'],
                'effective_from' => $data['effective_from'] ?? null,
                'effective_until' => $data['effective_until'] ?? null,
                'revision_notes' => $data['revision_notes'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            // Hapus material lebih dahulu karena memiliki
            // foreign key ke operation.
            $version->materials()->delete();

            $version->operations()->delete();

            $this->storeOperations(
                $version,
                $data['operations'] ?? []
            );

            $this->storeMaterials(
                $version,
                $data['materials']
            );

            $this->documentActivityService->record(
                $bom,
                'UPDATED',
                'Draft',
                'Draft',
                'Production BOM draft updated.',
                [
                    'version' => $version->version,
                ]
            );

            return $this->getBomDetail($bom);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Create Revision
    |--------------------------------------------------------------------------
    */

    public function createRevision(
        ProductionBom $bom,
        array $data = []
    ): ProductionBomVersion {

        return DB::transaction(function () use (
            $bom,
            $data
        ) {

            $bom = ProductionBom::query()
                ->whereKey($bom->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateBomOwnership($bom);

            $sourceVersion = $bom->versions()
                ->orderByDesc('version')
                ->lockForUpdate()
                ->first();

            if (! $sourceVersion) {
                throw new RuntimeException(
                    'The BOM does not have a previous version to revise.'
                );
            }

            $this->assertVersionNotUsed($sourceVersion);

            $newVersionNumber = (
                $bom->versions()->max('version') ?? 0
            ) + 1;

            $newVersion = ProductionBomVersion::create([
                'production_bom_id' => $bom->id,
                'version' => $newVersionNumber,
                'output_qty' => $sourceVersion->output_qty,
                'output_unit_id' => $sourceVersion->output_unit_id,
                'effective_from' => $sourceVersion->effective_from,
                'effective_until' => $sourceVersion->effective_until,
                'status' => 'draft',
                'revision_notes' => $data['revision_notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $operationMap = [];

            foreach ($sourceVersion->operations as $operation) {

                $newOperation = ProductionBomOperation::create([
                    'production_bom_version_id' => $newVersion->id,
                    'production_operation_id' => $operation->production_operation_id,
                    'sequence' => $operation->sequence,
                    'planned_duration_minutes' => $operation->planned_duration_minutes,
                    'instructions' => $operation->instructions,
                ]);

                $operationMap[$operation->id] = $newOperation->id;

            }

            foreach ($sourceVersion->materials as $material) {

                ProductionBomMaterial::create([
                    'production_bom_version_id' => $newVersion->id,
                    'component_variant_id' => $material->component_variant_id,
                    'unit_id' => $material->unit_id,
                    'qty' => $material->qty,
                    'scrap_percent' => $material->scrap_percent,
                    'production_bom_operation_id' =>
                        $material->production_bom_operation_id
                            ? ($operationMap[$material->production_bom_operation_id] ?? null)
                            : null,
                    'sequence' => $material->sequence,
                    'notes' => $material->notes,
                ]);

            }

            $bomStatus = $bom->is_active
                ? 'Active'
                : 'Inactive';

            $this->documentActivityService->record(
                $bom,
                'REVISION_CREATED',
                $bomStatus,
                $bomStatus,
                'New Production BOM revision created.',
                [
                    'previous_version' => $sourceVersion->version,
                    'new_version' => $newVersion->version,
                ]
            );

            return $newVersion->fresh([
                'productionBom',
                'outputUnit',
                'operations.productionOperation',
                'materials.componentVariant.product',
                'materials.unit',
                'materials.productionBomOperation',
            ]);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Activate Version
    |--------------------------------------------------------------------------
    */

    public function activateVersion(
        ProductionBomVersion $version
    ): ProductionBomVersion {

        return DB::transaction(function () use ($version) {

            $version = ProductionBomVersion::query()
                ->whereKey($version->id)
                ->lockForUpdate()
                ->firstOrFail();

            $bom = ProductionBom::query()
                ->whereKey($version->production_bom_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateBomOwnership($bom);

            if ($version->status !== 'draft') {
                throw new RuntimeException(
                    'Only a draft BOM version can be activated.'
                );
            }

            if ($version->materials()->count() === 0) {
                throw new RuntimeException(
                    'A BOM version must contain at least one material.'
                );
            }

            if ($version->operations()->count() === 0) {
                throw new RuntimeException(
                    'A BOM version must contain at least one production operation.'
                );
            }

            $oldStatus = $bom->is_active
                ? 'Active'
                : 'Inactive';

            $previousActiveVersion = $bom->versions()
                ->where('status', 'active')
                ->first();

            $version->update([
                'status' => 'inactive',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            $bom->versions()
                ->where('id', '!=', $version->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'inactive',
                    'updated_by' => auth()->id(),
                ]);

            $version->update([
                'status' => 'active',
                'updated_by' => auth()->id(),
            ]);

            $bom->update([
                'is_active' => true,
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $bom,
                'VERSION_ACTIVATED',
                $oldStatus,
                'Active',
                'Production BOM version activated.',
                [
                    'previous_version' => $previousActiveVersion?->version,
                    'activated_version' => $version->version,
                ]
            );

            return $version->fresh([
                'productionBom',
                'outputUnit',
                'approver',
                'operations.productionOperation',
                'materials.componentVariant.product',
                'materials.unit',
                'materials.productionBomOperation',
            ]);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Deactivate BOM
    |--------------------------------------------------------------------------
    */

    public function deactivateBom(
        ProductionBom $bom
    ): ProductionBom {

        return DB::transaction(function () use ($bom) {

            $bom = ProductionBom::query()
                ->whereKey($bom->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateBomOwnership($bom);

            $oldStatus = $bom->is_active
                ? 'Active'
                : 'Inactive';

            // Deactivate current active version.
            $bom->versions()
                ->where('status', 'active')
                ->update([
                    'status' => 'inactive',
                    'updated_by' => auth()->id(),
                ]);

            // Deactivate BOM header.
            $bom->update([
                'is_active' => false,
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $bom,
                'DEACTIVATED',
                $oldStatus,
                'Inactive',
                'Production BOM deactivated.'
            );

            return $this->getBomDetail($bom);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Delete Draft
    |--------------------------------------------------------------------------
    */

    public function deleteDraft(
        ProductionBom $bom
    ): void {

        DB::transaction(function () use ($bom) {

            $bom = ProductionBom::query()
                ->whereKey($bom->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateBomOwnership($bom);

            $versions = $bom->versions()
                ->lockForUpdate()
                ->get();

            if ($versions->isEmpty()) {
                throw new RuntimeException(
                    'The BOM does not contain a draft version.'
                );
            }

            foreach ($versions as $version) {

                if ($version->status !== 'draft') {
                    throw new RuntimeException(
                        'A BOM containing an active or inactive version cannot be deleted as a draft.'
                    );
                }

                $this->assertVersionNotUsed($version);

            }

            $this->documentActivityService->record(
                $bom,
                'DELETED',
                'Draft',
                'Deleted',
                'Production BOM deleted.'
            );

            foreach ($versions as $version) {

                $version->materials()->delete();

                $version->operations()->delete();

                $version->delete();

            }

            $bom->update([
                'deleted_by' => auth()->id(),
            ]);

            $bom->delete();

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Find / Detail
    |--------------------------------------------------------------------------
    */

    public function getBomDetail(
        ProductionBom $bom
    ): ProductionBom {

        return $bom->load([
            'company',
            'branch',
            'productVariant.product',
            'versions.outputUnit',
            'versions.approver',
            'versions.creator',
            'versions.updater',
            'versions.operations.productionOperation',
            'versions.materials.componentVariant.product',
            'versions.materials.unit',
            'versions.materials.productionBomOperation',
            'activities.performer',
            'creator',
            'updater',
            'deleter',
        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Store Operations
    |--------------------------------------------------------------------------
    */

    protected function storeOperations(
        ProductionBomVersion $version,
        array $operations
    ): void {

        $companyId = $version->productionBom->company_id;

        foreach ($operations as $operation) {

            $this->validateProductionOperation(
                $operation['production_operation_id'],
                $companyId
            );

            ProductionBomOperation::create([
                'production_bom_version_id' => $version->id,
                'production_operation_id' => $operation['production_operation_id'],
                'sequence' => $operation['sequence'],
                'planned_duration_minutes' =>
                    $operation['planned_duration_minutes'] ?? null,
                'instructions' => $operation['instructions'] ?? null,
            ]);

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Store Materials
    |--------------------------------------------------------------------------
    */

    protected function storeMaterials(
        ProductionBomVersion $version,
        array $materials
    ): void {

        $operations = $version->operations()
            ->pluck('id', 'sequence');

        foreach ($materials as $material) {

            $operationId = null;

            if (! empty($material['operation_sequence'])) {

                $operationId = $operations->get(
                    $material['operation_sequence']
                );

                if (! $operationId) {
                    throw new RuntimeException(
                        'The selected material operation sequence does not exist.'
                    );
                }

            }

            ProductionBomMaterial::create([
                'production_bom_version_id' => $version->id,
                'component_variant_id' => $material['component_variant_id'],
                'unit_id' => $material['unit_id'],
                'qty' => $material['qty'],
                'scrap_percent' => $material['scrap_percent'] ?? 0,
                'production_bom_operation_id' => $operationId,
                'sequence' => $material['sequence'] ?? 0,
                'notes' => $material['notes'] ?? null,
            ]);

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Validation Helpers
    |--------------------------------------------------------------------------
    */

    protected function validateBomOwnership(
        ProductionBom $bom
    ): void {

        $companyId = auth()->user()->company_id;

        if ((int) $bom->company_id !== (int) $companyId) {
            throw new RuntimeException(
                'You are not authorized to access this BOM.'
            );
        }

    }

    protected function validateBranch(
        int $branchId,
        int $companyId
    ): void {

        $exists = DB::table('branches')
            ->where('id', $branchId)
            ->where('company_id', $companyId)
            ->exists();

        if (! $exists) {
            throw new RuntimeException(
                'The selected branch does not belong to this company.'
            );
        }

    }

    protected function validateProductVariant(
        int $variantId
    ): void {

        $query = DB::table('product_variants')
            ->where('id', $variantId)
            ->where('is_active', true);

        if (Schema::hasColumn('product_variants', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (! $query->exists()) {
            throw new RuntimeException(
                'The selected product variant is not active or does not exist.'
            );
        }

    }

    protected function validateOutputUnit(
        int $variantId,
        int $unitId
    ): void {

        $exists = DB::table('product_variant_units')
            ->where('product_variant_id', $variantId)
            ->where('unit_id', $unitId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->exists();

        if (! $exists) {
            throw new RuntimeException(
                'The output unit is not registered as an active unit for this product variant.'
            );
        }

    }

    protected function validateMaterials(
        array $materials,
        int $companyId
    ): void {

        foreach ($materials as $material) {

            $this->validateProductVariant(
                $material['component_variant_id']
            );

            $this->validateOutputUnit(
                $material['component_variant_id'],
                $material['unit_id']
            );

            $this->validateScrapPercentage(
                $material['scrap_percent'] ?? 0
            );

        }

    }

    protected function validateScrapPercentage(
        float|int|string $scrapPercent
    ): void {

        if (
            ! is_numeric($scrapPercent) ||
            $scrapPercent < 0 ||
            $scrapPercent >= 100
        ) {
            throw new RuntimeException(
                'Material scrap percentage must be between 0 and less than 100.'
            );
        }

    }

    protected function validateProductionOperation(
        int $operationId,
        int $companyId
    ): void {

        $exists = ProductionOperation::query()
            ->where('id', $operationId)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            throw new RuntimeException(
                'The selected production operation is inactive or does not belong to this company.'
            );
        }

    }

    /*
    |--------------------------------------------------------------------------
    | Circular BOM Validation
    |--------------------------------------------------------------------------
    */

    protected function assertNoCircularReference(
        int $outputVariantId,
        array $materials,
        int $companyId,
        ?int $currentVersionId = null
    ): void {

        foreach ($materials as $material) {

            $componentVariantId = (int) $material['component_variant_id'];

            if ($componentVariantId === $outputVariantId) {
                throw new RuntimeException(
                    'A BOM cannot use its own output product variant as a material.'
                );
            }

            $visited = [];

            if (
                $this->variantContainsOutput(
                    $componentVariantId,
                    $outputVariantId,
                    $companyId,
                    $visited,
                    $currentVersionId
                )
            ) {
                throw new RuntimeException(
                    'Circular BOM reference detected. The component already depends on this output product variant.'
                );
            }

        }

    }

    protected function variantContainsOutput(
        int $variantId,
        int $targetVariantId,
        int $companyId,
        array &$visited,
        ?int $currentVersionId = null
    ): bool {

        if ($variantId === $targetVariantId) {
            return true;
        }

        if (in_array($variantId, $visited, true)) {
            return false;
        }

        $visited[] = $variantId;

        $versions = ProductionBomVersion::query()
            ->whereIn('status', ['active', 'draft'])
            ->where('id', '!=', $currentVersionId)
            ->whereHas('productionBom', function ($query) use (
                $variantId,
                $companyId
            ) {
                $query
                    ->where('company_id', $companyId)
                    ->where('product_variant_id', $variantId)
                    ->whereNull('deleted_at');
            })
            ->with('materials')
            ->get();

        foreach ($versions as $version) {

            foreach ($version->materials as $material) {

                if (
                    $this->variantContainsOutput(
                        (int) $material->component_variant_id,
                        $targetVariantId,
                        $companyId,
                        $visited,
                        $currentVersionId
                    )
                ) {
                    return true;
                }

            }

        }

        return false;

    }

    /*
    |--------------------------------------------------------------------------
    | Production Order Usage Protection
    |--------------------------------------------------------------------------
    */

    protected function assertVersionNotUsed(
        ProductionBomVersion $version
    ): void {

        if (
            Schema::hasTable('production_orders') &&
            Schema::hasColumn(
                'production_orders',
                'production_bom_version_id'
            )
        ) {

            $used = DB::table('production_orders')
                ->where(
                    'production_bom_version_id',
                    $version->id
                )
                ->exists();

            if ($used) {
                throw new RuntimeException(
                    'This BOM version has already been used by a production order and cannot be modified or deleted.'
                );
            }

        }

    }

}