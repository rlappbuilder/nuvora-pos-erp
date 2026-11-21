<?php

namespace App\Services\Production;

use App\Models\Production\ProductionBom;
use App\Models\Production\ProductionBomVersion;
use App\Models\Production\ProductionOrder\ProductionOrderHeader;
use App\Services\Core\DocumentActivityService;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ProductionOrderService
{
    public function __construct(
        protected CodeGeneratorService $codeGeneratorService,
        protected DocumentActivityService $activityService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Create Production Order
    |--------------------------------------------------------------------------
    */

    public function create(array $data): ProductionOrderHeader
    {
        return DB::transaction(function () use ($data) {
            $companyId = auth()->user()->company_id;
            $userId = auth()->id();

            $branch = $this->validateBranch(
                (int) $data['branch_id'],
                (int) $companyId
            );

            $bom = ProductionBom::query()
                ->where('id', $data['production_bom_id'])
                ->where('company_id', $companyId)
                ->where('branch_id', $branch->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $version = ProductionBomVersion::query()
                ->where('id', $data['production_bom_version_id'])
                ->where('production_bom_id', $bom->id)
                ->where('status', 'active')
                ->with(['operations', 'materials'])
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateBomVersion(
                $version,
                $data['order_date']
            );

            $this->validateBomComponents($bom, $version);

            $this->validateWarehouse(
                (int) $data['source_warehouse_id'],
                (int) $branch->id
            );

            $this->validateWarehouse(
                (int) $data['destination_warehouse_id'],
                (int) $branch->id
            );

            $plannedQuantity = (float) $data['planned_quantity'];

            if ($plannedQuantity <= 0) {
                throw new RuntimeException(
                    'Planned quantity must be greater than zero.'
                );
            }

            if ((float) $version->output_qty <= 0) {
                throw new RuntimeException(
                    'BOM output quantity must be greater than zero.'
                );
            }

            $order = ProductionOrderHeader::create([
                'company_id' => $companyId,
                'branch_id' => $branch->id,
                'number' => $this->codeGeneratorService->next(
                    'production_order'
                ),
                'production_bom_id' => $bom->id,
                'production_bom_version_id' => $version->id,
                'source_warehouse_id' => $data['source_warehouse_id'],
                'destination_warehouse_id' => $data['destination_warehouse_id'],
                'order_date' => $data['order_date'],
                'planned_start_date' => $data['planned_start_date'] ?? null,
                'planned_end_date' => $data['planned_end_date'] ?? null,
                'status' => 'Draft',
                'description' => $data['description'] ?? null,
                'created_by' => $userId,
            ]);

            // Output detail snapshot.
            $order->details()->create([
                'product_variant_id' => $bom->product_variant_id,
                'unit_id' => $version->output_unit_id,
                'planned_quantity' => $plannedQuantity,
                'produced_quantity' => 0,
                'accepted_quantity' => 0,
                'rejected_quantity' => 0,
                'description' => null,
            ]);

            // Snapshot routing.
            $operationMap = [];

            foreach ($version->operations->sortBy('sequence') as $operation) {
                $orderOperation = $order->operations()->create([
                    'production_bom_operation_id' => $operation->id,
                    'production_operation_id' => $operation->production_operation_id,
                    'sequence' => $operation->sequence,
                    'planned_duration_minutes' => $operation->planned_duration_minutes,
                    'instructions' => $operation->instructions,
                ]);

                $operationMap[$operation->id] = $orderOperation->id;
            }

            // Snapshot material requirements.
            foreach ($version->materials->sortBy('sequence') as $material) {
                $scrapPercent = (float) $material->scrap_percent;

                $this->validateScrapPercentage($scrapPercent);

                $requiredQuantity = $this->calculateRequiredQuantity(
                    (float) $material->qty,
                    (float) $version->output_qty,
                    $plannedQuantity,
                    $scrapPercent
                );

                $orderOperationId = null;

                if ($material->production_bom_operation_id !== null) {
                    $orderOperationId = $operationMap[
                        $material->production_bom_operation_id
                    ] ?? null;

                    if (!$orderOperationId) {
                        throw new RuntimeException(
                            'BOM material references an invalid production operation.'
                        );
                    }
                }

                $order->materials()->create([
                    'production_bom_material_id' => $material->id,
                    'production_order_operation_id' => $orderOperationId,
                    'component_variant_id' => $material->component_variant_id,
                    'unit_id' => $material->unit_id,
                    'bom_qty' => $material->qty,
                    'scrap_percent' => $scrapPercent,
                    'required_quantity' => $requiredQuantity,
                    'issued_quantity' => 0,
                    'returned_quantity' => 0,
                    'sequence' => $material->sequence,
                    'notes' => $material->notes,
                ]);
            }

            $this->activityService->record(
                $order,
                'CREATED',
                null,
                'Draft',
                'Production Order created.',
                [
                    'production_bom_id' => $bom->id,
                    'production_bom_code' => $bom->code,
                    'production_bom_version_id' => $version->id,
                    'planned_quantity' => $plannedQuantity,
                ]
            );

            return $this->getOrderDetail($order->id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update Draft
    |--------------------------------------------------------------------------
    */

    public function updateDraft(
        ProductionOrderHeader $order,
        array $data
    ): ProductionOrderHeader {
        return DB::transaction(function () use ($order, $data) {
            $companyId = auth()->user()->company_id;
            $userId = auth()->id();

            $order = $this->lockOrder($order->id);

            if ($order->status !== 'Draft') {
                throw new RuntimeException(
                    'Only Draft Production Orders can be updated.'
                );
            }

            if ($order->results()->exists()) {
                throw new RuntimeException(
                    'Production Order cannot be updated because production results already exist.'
                );
            }

            $hasMaterialMovement = $order->materials()
                ->where(function ($query) {
                    $query->where('issued_quantity', '>', 0)
                        ->orWhere('returned_quantity', '>', 0);
                })
                ->exists();

            if ($hasMaterialMovement) {
                throw new RuntimeException(
                    'Production Order cannot be updated because material movements already exist.'
                );
            }

            $branch = $this->validateBranch(
                (int) $data['branch_id'],
                (int) $companyId
            );

            $bom = ProductionBom::query()
                ->where('id', $data['production_bom_id'])
                ->where('company_id', $companyId)
                ->where('branch_id', $branch->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $version = ProductionBomVersion::query()
                ->where('id', $data['production_bom_version_id'])
                ->where('production_bom_id', $bom->id)
                ->where('status', 'active')
                ->with(['operations', 'materials'])
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateBomVersion(
                $version,
                $data['order_date']
            );

            $this->validateBomComponents($bom, $version);

            $this->validateWarehouse(
                (int) $data['source_warehouse_id'],
                (int) $branch->id
            );

            $this->validateWarehouse(
                (int) $data['destination_warehouse_id'],
                (int) $branch->id
            );

            $plannedQuantity = (float) $data['planned_quantity'];

            if ($plannedQuantity <= 0) {
                throw new RuntimeException(
                    'Planned quantity must be greater than zero.'
                );
            }

            if ((float) $version->output_qty <= 0) {
                throw new RuntimeException(
                    'BOM output quantity must be greater than zero.'
                );
            }

            $order->update([
                'branch_id' => $branch->id,
                'production_bom_id' => $bom->id,
                'production_bom_version_id' => $version->id,
                'source_warehouse_id' => $data['source_warehouse_id'],
                'destination_warehouse_id' => $data['destination_warehouse_id'],
                'order_date' => $data['order_date'],
                'planned_start_date' => $data['planned_start_date'] ?? null,
                'planned_end_date' => $data['planned_end_date'] ?? null,
                'description' => $data['description'] ?? null,
                'updated_by' => $userId,
            ]);

            $detail = $order->details;

            if (!$detail) {
                throw new RuntimeException(
                    'Production Order detail was not found.'
                );
            }

            $detail->update([
                'product_variant_id' => $bom->product_variant_id,
                'unit_id' => $version->output_unit_id,
                'planned_quantity' => $plannedQuantity,
                'produced_quantity' => 0,
                'accepted_quantity' => 0,
                'rejected_quantity' => 0,
            ]);

            // Remove old snapshots.
            $order->materials()->delete();
            $order->operations()->delete();

            // Rebuild routing snapshot.
            $operationMap = [];

            foreach ($version->operations->sortBy('sequence') as $operation) {
                $orderOperation = $order->operations()->create([
                    'production_bom_operation_id' => $operation->id,
                    'production_operation_id' => $operation->production_operation_id,
                    'sequence' => $operation->sequence,
                    'planned_duration_minutes' => $operation->planned_duration_minutes,
                    'instructions' => $operation->instructions,
                ]);

                $operationMap[$operation->id] = $orderOperation->id;
            }

            // Rebuild material snapshots.
            foreach ($version->materials->sortBy('sequence') as $material) {
                $scrapPercent = (float) $material->scrap_percent;

                $this->validateScrapPercentage($scrapPercent);

                $requiredQuantity = $this->calculateRequiredQuantity(
                    (float) $material->qty,
                    (float) $version->output_qty,
                    $plannedQuantity,
                    $scrapPercent
                );

                $orderOperationId = null;

                if ($material->production_bom_operation_id !== null) {
                    $orderOperationId = $operationMap[
                        $material->production_bom_operation_id
                    ] ?? null;

                    if (!$orderOperationId) {
                        throw new RuntimeException(
                            'BOM material references an invalid production operation.'
                        );
                    }
                }

                $order->materials()->create([
                    'production_bom_material_id' => $material->id,
                    'production_order_operation_id' => $orderOperationId,
                    'component_variant_id' => $material->component_variant_id,
                    'unit_id' => $material->unit_id,
                    'bom_qty' => $material->qty,
                    'scrap_percent' => $scrapPercent,
                    'required_quantity' => $requiredQuantity,
                    'issued_quantity' => 0,
                    'returned_quantity' => 0,
                    'sequence' => $material->sequence,
                    'notes' => $material->notes,
                ]);
            }

            $this->activityService->record(
                $order,
                'UPDATED',
                'Draft',
                'Draft',
                'Draft Production Order updated.',
                [
                    'production_bom_id' => $bom->id,
                    'production_bom_code' => $bom->code,
                    'production_bom_version_id' => $version->id,
                    'planned_quantity' => $plannedQuantity,
                ]
            );

            return $this->getOrderDetail($order->id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        ProductionOrderHeader $order
    ): ProductionOrderHeader {
        return DB::transaction(function () use ($order) {
            $order = $this->lockOrder($order->id);

            $this->assertStatus($order, ['Draft']);

            $oldStatus = $order->status;

            $order->update([
                'status' => 'Submitted',
                'submitted_at' => now(),
                'submitted_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->activityService->record(
                $order,
                'SUBMITTED',
                $oldStatus,
                'Submitted',
                'Production Order submitted for approval.'
            );

            return $this->getOrderDetail($order->id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        ProductionOrderHeader $order
    ): ProductionOrderHeader {
        return DB::transaction(function () use ($order) {
            $order = $this->lockOrder($order->id);

            $this->assertStatus($order, ['Submitted']);

            $oldStatus = $order->status;

            $order->update([
                'status' => 'Approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->activityService->record(
                $order,
                'APPROVED',
                $oldStatus,
                'Approved',
                'Production Order approved.'
            );

            return $this->getOrderDetail($order->id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        ProductionOrderHeader $order,
        string $reason
    ): ProductionOrderHeader {
        return DB::transaction(function () use ($order, $reason) {
            $order = $this->lockOrder($order->id);

            $this->assertStatus($order, ['Submitted']);

            $reason = trim($reason);

            if ($reason === '') {
                throw new RuntimeException(
                    'Rejection reason is required.'
                );
            }

            $oldStatus = $order->status;

            $order->update([
                'status' => 'Rejected',
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
                'rejected_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);

            $this->activityService->record(
                $order,
                'REJECTED',
                $oldStatus,
                'Rejected',
                'Production Order rejected.',
                [
                    'reason' => $reason,
                ]
            );

            return $this->getOrderDetail($order->id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Start Production
    |--------------------------------------------------------------------------
    */

    public function startProduction(
        ProductionOrderHeader $order
    ): ProductionOrderHeader {
        return DB::transaction(function () use ($order) {
            $order = $this->lockOrder($order->id);

            $this->assertStatus($order, ['Approved']);

            $oldStatus = $order->status;

            $order->update([
                'status' => 'In Progress',
                'actual_start_date' => now()->toDateString(),
                'updated_by' => auth()->id(),
            ]);

            $this->activityService->record(
                $order,
                'STARTED',
                $oldStatus,
                'In Progress',
                'Production process started.'
            );

            return $this->getOrderDetail($order->id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        ProductionOrderHeader $order,
        string $reason
    ): ProductionOrderHeader {
        return DB::transaction(function () use ($order, $reason) {
            $order = $this->lockOrder($order->id);

            $this->assertStatus(
                $order,
                ['Draft', 'Submitted', 'Approved']
            );

            $reason = trim($reason);

            if ($reason === '') {
                throw new RuntimeException(
                    'Cancellation reason is required.'
                );
            }

            $oldStatus = $order->status;

            $order->update([
                'status' => 'Cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancelled_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);

            $this->activityService->record(
                $order,
                'CANCELLED',
                $oldStatus,
                'Cancelled',
                'Production Order cancelled.',
                [
                    'reason' => $reason,
                ]
            );

            return $this->getOrderDetail($order->id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow Helpers
    |--------------------------------------------------------------------------
    */

    protected function lockOrder(int $orderId): ProductionOrderHeader
    {
        return ProductionOrderHeader::query()
            ->where('id', $orderId)
            ->where('company_id', auth()->user()->company_id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    protected function assertStatus(
        ProductionOrderHeader $order,
        array $allowedStatuses
    ): void {
        if (!in_array($order->status, $allowedStatuses, true)) {
            throw new RuntimeException(
                "This action is not allowed when Production Order status is {$order->status}."
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Branch
    |--------------------------------------------------------------------------
    */

    protected function validateBranch(int $branchId, int $companyId)
    {
        $branch = DB::table('branches')
            ->where('id', $branchId)
            ->where('company_id', $companyId)
            ->first();

        if (!$branch) {
            throw new RuntimeException(
                'Selected branch does not belong to this company.'
            );
        }

        return $branch;
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Warehouse
    |--------------------------------------------------------------------------
    */

    protected function validateWarehouse(
        int $warehouseId,
        int $branchId
    ): void {
        $exists = DB::table('warehouses')
            ->where('id', $warehouseId)
            ->where('branch_id', $branchId)
            ->where('status', true)
            ->whereNull('deleted_at')
            ->exists();

        if (!$exists) {
            throw new RuntimeException(
                'Selected warehouse is invalid, inactive, or does not belong to the selected branch.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate BOM Version
    |--------------------------------------------------------------------------
    */

    protected function validateBomVersion(
        ProductionBomVersion $version,
        string $orderDate
    ): void {
        if (
            $version->effective_from &&
            $orderDate < $version->effective_from->format('Y-m-d')
        ) {
            throw new RuntimeException(
                'Selected BOM version is not yet effective on the Production Order date.'
            );
        }

        if (
            $version->effective_until &&
            $orderDate > $version->effective_until->format('Y-m-d')
        ) {
            throw new RuntimeException(
                'Selected BOM version has expired for the Production Order date.'
            );
        }

        if ($version->materials->isEmpty()) {
            throw new RuntimeException(
                'Selected BOM version has no materials.'
            );
        }

        if ($version->operations->isEmpty()) {
            throw new RuntimeException(
                'Selected BOM version has no production operations.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate BOM Components
    |--------------------------------------------------------------------------
    */

    protected function validateBomComponents(
        ProductionBom $bom,
        ProductionBomVersion $version
    ): void {
        $this->validateProductVariant(
            (int) $bom->product_variant_id
        );

        $this->validateVariantUnit(
            (int) $bom->product_variant_id,
            (int) $version->output_unit_id
        );

        foreach ($version->materials as $material) {
            $this->validateProductVariant(
                (int) $material->component_variant_id
            );

            $this->validateVariantUnit(
                (int) $material->component_variant_id,
                (int) $material->unit_id
            );
        }

        foreach ($version->operations as $operation) {
            $this->validateProductionOperation(
                (int) $operation->production_operation_id,
                (int) $bom->company_id
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Product Variant
    |--------------------------------------------------------------------------
    */

    protected function validateProductVariant(int $variantId): void
    {
        $query = DB::table('product_variants')
            ->where('id', $variantId)
            ->where('is_active', true);

        if (Schema::hasColumn('product_variants', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (!$query->exists()) {
            throw new RuntimeException(
                'A Product Variant used in the BOM is inactive or no longer available.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Variant Unit
    |--------------------------------------------------------------------------
    */

    protected function validateVariantUnit(
        int $variantId,
        int $unitId
    ): void {
        $query = DB::table('product_variant_units')
            ->where('product_variant_id', $variantId)
            ->where('unit_id', $unitId)
            ->where('is_active', true);

        if (Schema::hasColumn('product_variant_units', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (!$query->exists()) {
            throw new RuntimeException(
                'A unit used in the BOM is not active or is not assigned to its Product Variant.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Production Operation
    |--------------------------------------------------------------------------
    */

    protected function validateProductionOperation(
        int $operationId,
        int $companyId
    ): void {
        $exists = DB::table('production_operations')
            ->where('id', $operationId)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->exists();

        if (!$exists) {
            throw new RuntimeException(
                'A Production Operation used in the BOM is inactive or unavailable.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Scrap Percentage
    |--------------------------------------------------------------------------
    */

    protected function validateScrapPercentage(float $scrapPercent): void
    {
        if ($scrapPercent < 0 || $scrapPercent >= 100) {
            throw new RuntimeException(
                'Material scrap percentage must be between 0 and less than 100.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Required Material Quantity
    |--------------------------------------------------------------------------
    */

    protected function calculateRequiredQuantity(
        float $materialQty,
        float $bomOutputQty,
        float $plannedQuantity,
        float $scrapPercent
    ): float {
        if ($bomOutputQty <= 0) {
            throw new RuntimeException(
                'BOM output quantity must be greater than zero.'
            );
        }

        $this->validateScrapPercentage($scrapPercent);

        $netQuantity = (
            $materialQty / $bomOutputQty
        ) * $plannedQuantity;

        $grossQuantity = $netQuantity / (
            1 - ($scrapPercent / 100)
        );

        return round($grossQuantity, 6);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Production Order Detail
    |--------------------------------------------------------------------------
    */

    public function getOrderDetail(int $orderId): ProductionOrderHeader
    {
        return ProductionOrderHeader::query()
            ->with([
                'company',
                'branch',
                'productionBom',
                'productionBomVersion.outputUnit',
                'sourceWarehouse',
                'destinationWarehouse',

                'details.productVariant.product',
                'details.unit',

                'operations.productionOperation',

                'materials.componentVariant.product',
                'materials.unit',
                'materials.productionOrderOperation.productionOperation',

                'results.detail.productVariant.product',
                'results.detail.unit',

                'activities.performer',

                'creator',
                'updater',
                'submitter',
                'approver',
                'rejector',
                'canceller',
                'deleter',
            ])
            ->where('company_id', auth()->user()->company_id)
            ->findOrFail($orderId);
    }
}