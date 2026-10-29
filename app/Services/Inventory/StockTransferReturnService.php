<?php

namespace App\Services\Inventory;

use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\BranchAccountMapping;
use App\Models\Accounting\FiscalYear;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\StockTransferHeader;
use App\Models\Inventory\StockTransferReturnHeader;
use App\Models\Inventory\StockTransferReturnDetail;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Services\Accounting\JournalEntryService;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class StockTransferReturnService
{
    public function __construct(
        protected CodeGeneratorService $codeGeneratorService,
        protected DocumentActivityService $documentActivityService,
        protected JournalEntryService $journalEntryService,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Create Return
    |--------------------------------------------------------------------------
    */

    public function createReturn(array $data): StockTransferReturnHeader
    {
        return DB::transaction(function () use ($data) {
            $activeBranchId = (int) session('current_branch_id');
            $companyId = (int) auth()->user()->company_id;

            if ($activeBranchId <= 0) {
                throw new RuntimeException(
                    'Active branch has not been selected.'
                );
            }

            $transfer = StockTransferHeader::query()
                ->with('details')
                ->lockForUpdate()
                ->findOrFail($data['stock_transfer_header_id']);

            $this->validateOriginalTransfer(
                $transfer,
                $activeBranchId,
                $companyId
            );

            if (empty($data['details']) || ! is_array($data['details'])) {
                throw new RuntimeException(
                    'Return must contain at least one detail.'
                );
            }

            /*
             * Prevent duplicate original detail IDs in one return.
             */
            $detailIds = [];

            foreach ($data['details'] as $index => $item) {
                $detailId = (int) (
                    $item['stock_transfer_detail_id'] ?? 0
                );

                if ($detailId <= 0) {
                    throw ValidationException::withMessages([
                        "details.{$index}.stock_transfer_detail_id" =>
                            'A valid original transfer detail is required.',
                    ]);
                }

                if (in_array($detailId, $detailIds, true)) {
                    throw ValidationException::withMessages([
                        "details.{$index}.stock_transfer_detail_id" =>
                            'The same stock transfer detail cannot be added more than once.',
                    ]);
                }

                $detailIds[] = $detailId;
            }

            /*
             * Lock existing stock records in a stable order.
             * The original transfer header lock serializes creation
             * requests for the same transfer, provided all return
             * creation paths use this service.
             */
            $stockKeys = $transfer->details
                ->whereIn('id', $detailIds)
                ->map(fn ($detail) => [
                    'variant_id' => (int) $detail->product_variant_id,
                    'unit_id' => (int) $detail->unit_id,
                ])
                ->unique(fn ($item) =>
                    $item['variant_id'] . ':' . $item['unit_id']
                )
                ->sortBy(fn ($item) =>
                    $item['variant_id'] . ':' . $item['unit_id']
                );

            foreach ($stockKeys as $key) {
                $this->getAvailableStock(
                    (int) $transfer->company_id,
                    (int) $transfer->to_branch_id,
                    (int) $transfer->to_warehouse_id,
                    $key['variant_id'],
                    $key['unit_id'],
                    true
                );
            }

            /*
             * Validate all requested lines before creating the header.
             */
            foreach ($data['details'] as $index => $item) {
                $originalDetail = $transfer->details->firstWhere(
                    'id',
                    (int) $item['stock_transfer_detail_id']
                );

                if (! $originalDetail) {
                    throw ValidationException::withMessages([
                        "details.{$index}.stock_transfer_detail_id" =>
                            'The selected detail does not belong to the original stock transfer.',
                    ]);
                }

                $qty = (float) ($item['qty'] ?? 0);

                if (! is_finite($qty) || $qty <= 0) {
                    throw ValidationException::withMessages([
                        "details.{$index}.qty" =>
                            'Return quantity must be greater than zero.',
                    ]);
                }

                $remainingQty = $this->getRemainingReturnQty(
                    $transfer,
                    $originalDetail
                );

                $availableStock = $this->getAvailableStock(
                    (int) $transfer->company_id,
                    (int) $transfer->to_branch_id,
                    (int) $transfer->to_warehouse_id,
                    (int) $originalDetail->product_variant_id,
                    (int) $originalDetail->unit_id,
                    true
                );

                $maxQty = min($remainingQty, $availableStock);

                if ($qty > $maxQty + 0.000001) {
                    throw ValidationException::withMessages([
                        "details.{$index}.qty" =>
                            "Maximum return quantity is {$maxQty}.",
                    ]);
                }

                if ((float) $originalDetail->unit_cost < 0) {
                    throw ValidationException::withMessages([
                        "details.{$index}.qty" =>
                            'Original transfer cost is invalid.',
                    ]);
                }
            }

            $return = StockTransferReturnHeader::create([
                'company_id' => $transfer->company_id,
                'stock_transfer_header_id' => $transfer->id,

                // Return originates from the original recipient.
                'from_branch_id' => $transfer->to_branch_id,
                'from_warehouse_id' => $transfer->to_warehouse_id,

                // Return goes back to the original sender.
                'to_branch_id' => $transfer->from_branch_id,
                'to_warehouse_id' => $transfer->from_warehouse_id,

                'number' => $this->codeGeneratorService
                    ->next('stock_transfer_return'),

                'transaction_date' => $data['transaction_date'],
                'status' => 'Draft',
                'description' => $data['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['details'] as $item) {
                $originalDetail = $transfer->details->firstWhere(
                    'id',
                    (int) $item['stock_transfer_detail_id']
                );

                $qty = (float) $item['qty'];
                $unitCost = (float) $originalDetail->unit_cost;

                StockTransferReturnDetail::create([
                    'stock_transfer_return_header_id' => $return->id,
                    'stock_transfer_detail_id' => $originalDetail->id,
                    'product_variant_id' => $originalDetail->product_variant_id,
                    'unit_id' => $originalDetail->unit_id,
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'total_cost' => round($qty * $unitCost, 2),
                    'description' => $item['description'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            }

            $this->documentActivityService->record(
                $return,
                'CREATED',
                null,
                'Draft',
                'Stock transfer return created.'
            );

            return $return->fresh('details');
        }, 3);
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submitReturn(
        StockTransferReturnHeader $return
    ): StockTransferReturnHeader {
        return DB::transaction(function () use ($return) {
            $return = StockTransferReturnHeader::query()
                ->with('details')
                ->lockForUpdate()
                ->findOrFail($return->id);

            if ($return->status !== 'Draft') {
                throw new RuntimeException(
                    'Only Draft stock transfer return can be submitted.'
                );
            }

            if ($return->details->isEmpty()) {
                throw new RuntimeException(
                    'Stock transfer return must contain at least one detail.'
                );
            }

            $return->update([
                'status' => 'Submitted',
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $return,
                'SUBMITTED',
                'Draft',
                'Submitted',
                'Stock transfer return submitted.'
            );

            return $return->fresh('details');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approveReturn(
        StockTransferReturnHeader $return
    ): StockTransferReturnHeader {
        return DB::transaction(function () use ($return) {
            $return = StockTransferReturnHeader::query()
                ->lockForUpdate()
                ->findOrFail($return->id);

            if ($return->status !== 'Submitted') {
                throw new RuntimeException(
                    'Only Submitted stock transfer return can be approved.'
                );
            }

            $return->update([
                'status' => 'Approved',
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $return,
                'APPROVED',
                'Submitted',
                'Approved',
                'Stock transfer return approved.'
            );

            return $return->fresh('details');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function postReturn(
        StockTransferReturnHeader $return
    ): void {
        DB::transaction(function () use ($return) {
            /*
             * Lock the return and its original transfer in a stable order.
             */
            $return = StockTransferReturnHeader::query()
                ->lockForUpdate()
                ->findOrFail($return->id);

            if ($return->status !== 'Approved') {
                throw new RuntimeException(
                    'Only Approved stock transfer return can be posted.'
                );
            }

            $transfer = StockTransferHeader::query()
                ->with('details')
                ->lockForUpdate()
                ->findOrFail($return->stock_transfer_header_id);

            $return->load('details');

            $this->validateOriginalTransfer(
                $transfer,
                (int) $return->from_branch_id,
                (int) $return->company_id
            );

            if (
                (int) $return->from_branch_id !==
                (int) session('current_branch_id')
            ) {
                throw new RuntimeException(
                    'The active branch must match the return origin branch.'
                );
            }

            if ($return->details->isEmpty()) {
                throw new RuntimeException(
                    'Stock transfer return must contain at least one detail.'
                );
            }

            /*
             * Ensure no duplicate original detail exists in this return.
             */
            $detailIds = $return->details
                ->pluck('stock_transfer_detail_id')
                ->map(fn ($id) => (int) $id);

            if ($detailIds->unique()->count() !== $detailIds->count()) {
                throw new RuntimeException(
                    'The same original transfer detail appears more than once in this return.'
                );
            }

            $fiscalYear = FiscalYear::query()
                ->where('company_id', $return->company_id)
                ->whereDate('start_date', '<=', $return->transaction_date)
                ->whereDate('end_date', '>=', $return->transaction_date)
                ->first();

            if (! $fiscalYear) {
                throw new RuntimeException(
                    'Fiscal year is not configured for the return date.'
                );
            }

            $accountingPeriod = AccountingPeriod::query()
                ->where('fiscal_year_id', $fiscalYear->id)
                ->whereDate('start_date', '<=', $return->transaction_date)
                ->whereDate('end_date', '>=', $return->transaction_date)
                ->where('status', 'Open')
                ->first();

            if (! $accountingPeriod) {
                throw new RuntimeException(
                    'Accounting period is not open for the return date.'
                );
            }

            $accountingJournal = AccountingJournal::query()
                ->where('company_id', $return->company_id)
                ->where('code', 'GEN')
                ->where('type', 'General')
                ->where('is_active', true)
                ->first();

            if (! $accountingJournal) {
                throw new RuntimeException(
                    'General accounting journal is not configured.'
                );
            }

            $fromInventory = $this->getBranchAccount(
                (int) $return->company_id,
                (int) $return->from_branch_id,
                'inventory_merchandise'
            );

            $toInventory = $this->getBranchAccount(
                (int) $return->company_id,
                (int) $return->to_branch_id,
                'inventory_merchandise'
            );

            $fromPayable = $this->getBranchAccount(
                (int) $return->company_id,
                (int) $return->from_branch_id,
                'inventory_transfer_payable'
            );

            $toReceivable = $this->getBranchAccount(
                (int) $return->company_id,
                (int) $return->to_branch_id,
                'inventory_transfer_receivable'
            );

            /*
             * Lock all existing source stock records in stable order.
             * Missing stock records are rejected for the source branch.
             */
            $stockKeys = $return->details
                ->map(fn ($detail) => [
                    'variant_id' => (int) $detail->product_variant_id,
                    'unit_id' => (int) $detail->unit_id,
                ])
                ->unique(fn ($item) =>
                    $item['variant_id'] . ':' . $item['unit_id']
                )
                ->sortBy(fn ($item) =>
                    $item['variant_id'] . ':' . $item['unit_id']
                );

            foreach ($stockKeys as $key) {
                $stock = ProductStock::query()
                    ->where('company_id', $return->company_id)
                    ->where('branch_id', $return->from_branch_id)
                    ->where('warehouse_id', $return->from_warehouse_id)
                    ->where('product_variant_id', $key['variant_id'])
                    ->where('unit_id', $key['unit_id'])
                    ->whereNull('reseller_id')
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    throw new RuntimeException(
                        'Return origin stock was not found.'
                    );
                }
            }

            $totalReturnValue = 0.0;

            foreach ($return->details as $detail) {
                $originalDetail = $transfer->details->firstWhere(
                    'id',
                    $detail->stock_transfer_detail_id
                );

                if (! $originalDetail) {
                    throw new RuntimeException(
                        'Original stock transfer detail was not found.'
                    );
                }

                /*
                 * Verify variant and unit still match the original detail.
                 */
                if (
                    (int) $detail->product_variant_id !==
                        (int) $originalDetail->product_variant_id ||
                    (int) $detail->unit_id !==
                        (int) $originalDetail->unit_id
                ) {
                    throw new RuntimeException(
                        'Return detail does not match the original transfer product and unit.'
                    );
                }

                $remainingQty = $this->getRemainingReturnQty(
                    $transfer,
                    $originalDetail,
                    $return->id
                );

                $stock = ProductStock::query()
                    ->where('company_id', $return->company_id)
                    ->where('branch_id', $return->from_branch_id)
                    ->where('warehouse_id', $return->from_warehouse_id)
                    ->where('product_variant_id', $detail->product_variant_id)
                    ->where('unit_id', $detail->unit_id)
                    ->whereNull('reseller_id')
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    throw new RuntimeException(
                        'Return origin stock was not found.'
                    );
                }

                $availableQty = (float) $stock->available_qty;
                $qty = (float) $detail->qty;

                if (
                    ! is_finite($qty) ||
                    $qty <= 0 ||
                    $qty > $remainingQty + 0.000001 ||
                    $qty > $availableQty + 0.000001
                ) {
                    throw new RuntimeException(
                        'Return quantity exceeds the remaining transfer quantity or available stock.'
                    );
                }

                $actualUnitCost = (float) $stock->average_cost;
                $returnUnitCost = (float) $originalDetail->unit_cost;

                /*
                 * Temporary costing policy:
                 * do not post when actual average cost differs from
                 * the original transfer cost.
                 */
                if (abs($actualUnitCost - $returnUnitCost) > 0.005) {
                    throw new RuntimeException(
                        'Return posting blocked: the current inventory average cost differs from the original transfer cost. Resolve the cost variance policy before posting.'
                    );
                }

                $unitCost = $returnUnitCost;
                $totalCost = round($qty * $unitCost, 2);

                $detail->update([
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'updated_by' => auth()->id(),
                ]);

                $totalReturnValue += $totalCost;

                /*
                 * Inventory out from the return-origin branch.
                 */
                $sourceStock = $this->updateCurrentStock([
                    'company_id' => $return->company_id,
                    'branch_id' => $return->from_branch_id,
                    'warehouse_id' => $return->from_warehouse_id,
                    'product_variant_id' => $detail->product_variant_id,
                    'unit_id' => $detail->unit_id,
                    'qty' => -$qty,
                    'average_cost' => $unitCost,
                    'transaction_date' => $return->transaction_date,
                    'update_average_cost' => false,
                    'lock' => true,
                ]);

                $this->createMovement($sourceStock, [
                    'reference_type' => 'STOCK_TRANSFER_RETURN',
                    'reference_id' => $return->id,
                    'reference_number' => $return->number,
                    'qty_in' => 0,
                    'qty_out' => $qty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'transaction_date' => $return->transaction_date,
                    'description' => $detail->description
                        ?? $return->description,
                ]);

                /*
                 * Inventory in to the original sender branch.
                 */
                $destinationStock = $this->updateCurrentStock([
                    'company_id' => $return->company_id,
                    'branch_id' => $return->to_branch_id,
                    'warehouse_id' => $return->to_warehouse_id,
                    'product_variant_id' => $detail->product_variant_id,
                    'unit_id' => $detail->unit_id,
                    'qty' => $qty,
                    'average_cost' => $unitCost,
                    'transaction_date' => $return->transaction_date,
                    'update_average_cost' => true,
                    'lock' => true,
                ]);

                $this->createMovement($destinationStock, [
                    'reference_type' => 'STOCK_TRANSFER_RETURN',
                    'reference_id' => $return->id,
                    'reference_number' => $return->number,
                    'qty_in' => $qty,
                    'qty_out' => 0,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'transaction_date' => $return->transaction_date,
                    'description' => $detail->description
                        ?? $return->description,
                ]);
            }

            if ($totalReturnValue <= 0) {
                throw new RuntimeException(
                    'Stock transfer return value must be greater than zero.'
                );
            }

            /*
             * Returning branch:
             * Debit Inter-Branch Payable
             * Credit Merchandise Inventory
             */
            $sourceJournalEntry = $this->journalEntryService->create([
                'company_id' => $return->company_id,
                'branch_id' => $return->from_branch_id,
                'accounting_journal_id' => $accountingJournal->id,
                'fiscal_year_id' => $fiscalYear->id,
                'accounting_period_id' => $accountingPeriod->id,
                'entry_date' => $return->transaction_date,
                'reference' => $return->number,
                'description' => 'Stock transfer return out ' . $return->number,
                'lines' => [
                    [
                        'account_id' => $fromPayable,
                        'branch_id' => $return->from_branch_id,
                        'description' => 'Inter-Branch Payable - ' . $return->number,
                        'debit' => $totalReturnValue,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $fromInventory,
                        'branch_id' => $return->from_branch_id,
                        'description' => 'Inventory return out - ' . $return->number,
                        'debit' => 0,
                        'credit' => $totalReturnValue,
                    ],
                ],
            ]);

            $this->journalEntryService->post($sourceJournalEntry);

            /*
             * Receiving branch:
             * Debit Merchandise Inventory
             * Credit Inter-Branch Receivable
             */
            $destinationJournalEntry = $this->journalEntryService->create([
                'company_id' => $return->company_id,
                'branch_id' => $return->to_branch_id,
                'accounting_journal_id' => $accountingJournal->id,
                'fiscal_year_id' => $fiscalYear->id,
                'accounting_period_id' => $accountingPeriod->id,
                'entry_date' => $return->transaction_date,
                'reference' => $return->number,
                'description' => 'Stock transfer return in ' . $return->number,
                'lines' => [
                    [
                        'account_id' => $toInventory,
                        'branch_id' => $return->to_branch_id,
                        'description' => 'Inventory return in - ' . $return->number,
                        'debit' => $totalReturnValue,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $toReceivable,
                        'branch_id' => $return->to_branch_id,
                        'description' => 'Inter-Branch Receivable - ' . $return->number,
                        'debit' => 0,
                        'credit' => $totalReturnValue,
                    ],
                ],
            ]);

            $this->journalEntryService->post($destinationJournalEntry);

            $return->update([
                'source_journal_entry_id' => $sourceJournalEntry->id,
                'destination_journal_entry_id' => $destinationJournalEntry->id,
                'status' => 'Posted',
                'posted_at' => now(),
                'posted_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $return,
                'POSTED',
                'Approved',
                'Posted',
                'Stock transfer return posted.'
            );
        }, 3);
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function rejectReturn(
        StockTransferReturnHeader $return,
        string $reason
    ): void {
        DB::transaction(function () use ($return, $reason) {
            $return = StockTransferReturnHeader::query()
                ->lockForUpdate()
                ->findOrFail($return->id);

            if ($return->status !== 'Submitted') {
                throw new RuntimeException(
                    'Only Submitted stock transfer return can be rejected.'
                );
            }

            if (trim($reason) === '') {
                throw new RuntimeException(
                    'Rejection reason is required.'
                );
            }

            $return->update([
                'status' => 'Rejected',
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
                'rejected_reason' => trim($reason),
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $return,
                'REJECTED',
                'Submitted',
                'Rejected',
                'Stock transfer return rejected.',
                ['reason' => trim($reason)]
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancelReturn(
        StockTransferReturnHeader $return,
        string $reason
    ): void {
        DB::transaction(function () use ($return, $reason) {
            $return = StockTransferReturnHeader::query()
                ->lockForUpdate()
                ->findOrFail($return->id);

            if (! in_array($return->status, [
                'Draft',
                'Submitted',
                'Approved',
            ], true)) {
                throw new RuntimeException(
                    'Only Draft, Submitted, or Approved return can be cancelled.'
                );
            }

            if (trim($reason) === '') {
                throw new RuntimeException(
                    'Cancellation reason is required.'
                );
            }

            $oldStatus = $return->status;

            $return->update([
                'status' => 'Cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancelled_reason' => trim($reason),
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $return,
                'CANCELLED',
                $oldStatus,
                'Cancelled',
                'Stock transfer return cancelled.',
                ['reason' => trim($reason)]
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    private function validateOriginalTransfer(
        StockTransferHeader $transfer,
        int $activeBranchId,
        int $companyId
    ): void {
        if ($transfer->status !== 'Posted') {
            throw new RuntimeException(
                'Only Posted stock transfer can be returned.'
            );
        }

        if ((int) $transfer->company_id !== $companyId) {
            throw new RuntimeException(
                'Stock transfer does not belong to the current company.'
            );
        }

        if ((int) $transfer->to_branch_id !== $activeBranchId) {
            throw new RuntimeException(
                'The original transfer must have been received by the active branch.'
            );
        }
    }

    private function getRemainingReturnQty(
        StockTransferHeader $transfer,
        $originalDetail,
        ?int $excludeReturnId = null
    ): float {
        $returnedQty = StockTransferReturnDetail::query()
            ->where(
                'stock_transfer_detail_id',
                $originalDetail->id
            )
            ->whereHas(
                'stockTransferReturnHeader',
                function ($query) use (
                    $transfer,
                    $excludeReturnId
                ) {
                    $query
                        ->where(
                            'stock_transfer_header_id',
                            $transfer->id
                        )
                        ->whereIn('status', [
                            'Draft',
                            'Submitted',
                            'Approved',
                            'Posted',
                        ]);

                    if ($excludeReturnId !== null) {
                        $query->where(
                            'id',
                            '!=',
                            $excludeReturnId
                        );
                    }
                }
            )
            ->sum('qty');

        return max(
            0,
            (float) $originalDetail->qty - (float) $returnedQty
        );
    }

    private function getAvailableStock(
        int $companyId,
        int $branchId,
        int $warehouseId,
        int $variantId,
        int $unitId,
        bool $lock = false
    ): float {
        $query = ProductStock::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('warehouse_id', $warehouseId)
            ->where('product_variant_id', $variantId)
            ->where('unit_id', $unitId)
            ->whereNull('reseller_id');

        if ($lock) {
            $query->lockForUpdate();
        }

        return (float) ($query->first()?->available_qty ?? 0);
    }

    private function getBranchAccount(
        int $companyId,
        int $branchId,
        string $key
    ): int {
        $mapping = BranchAccountMapping::query()
            ->with('account')
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        if (! $mapping || ! $mapping->account) {
            throw new RuntimeException(
                "Account mapping {$key} is not configured for branch {$branchId}."
            );
        }

        return (int) $mapping->account->id;
    }

    /*
     * Keep these helpers aligned with InventoryService.
     * They remain private so the existing service is untouched.
     */

    private function updateCurrentStock(array $data): ProductStock
    {
        $query = ProductStock::query()
            ->where('company_id', $data['company_id'] ?? null)
            ->where('branch_id', $data['branch_id'])
            ->where('warehouse_id', $data['warehouse_id'])
            ->where('product_variant_id', $data['product_variant_id'])
            ->where('unit_id', $data['unit_id'])
            ->where('reseller_id', $data['reseller_id'] ?? null);

        if (($data['lock'] ?? false) === true) {
            $query->lockForUpdate();
        }

        $stock = $query->first();

        if (! $stock) {
            /*
             * Do not silently create a missing source stock record
             * for an inventory-out operation.
             */
            if ((float) $data['qty'] < 0) {
                throw new RuntimeException(
                    'Cannot issue stock because the source stock record does not exist.'
                );
            }

            $stock = new ProductStock();

            $stock->company_id = $data['company_id'] ?? null;
            $stock->branch_id = $data['branch_id'];
            $stock->warehouse_id = $data['warehouse_id'];
            $stock->product_variant_id = $data['product_variant_id'];
            $stock->reseller_id = $data['reseller_id'] ?? null;
            $stock->unit_id = $data['unit_id'];
            $stock->on_hand_qty = 0;
            $stock->reserved_qty = 0;
            $stock->available_qty = 0;
            $stock->average_cost = 0;
        }

        $oldQty = (float) $stock->on_hand_qty;
        $oldAverageCost = (float) $stock->average_cost;
        $movementQty = (float) $data['qty'];
        $newQty = $oldQty + $movementQty;

        if ($newQty < -0.000001) {
            throw new RuntimeException(
                'Stock quantity cannot be negative.'
            );
        }

        /*
         * Clamp insignificant floating-point residue.
         */
        if (abs($newQty) < 0.000001) {
            $newQty = 0;
        }

        if (
            ($data['update_average_cost'] ?? false) === true &&
            $movementQty > 0
        ) {
            $incomingCost = (float) ($data['average_cost'] ?? 0);

            if ($oldQty <= 0) {
                $stock->average_cost = $incomingCost;
            } else {
                $stock->average_cost = (
                    ($oldQty * $oldAverageCost) +
                    ($movementQty * $incomingCost)
                ) / $newQty;
            }
        }

        $stock->on_hand_qty = $newQty;
        $stock->available_qty =
            $stock->on_hand_qty - (float) $stock->reserved_qty;

        if ($stock->available_qty < -0.000001) {
            throw new RuntimeException(
                'Stock availability cannot become negative.'
            );
        }

        $stock->last_transaction_at = $data['transaction_date'];
        $stock->updated_by = auth()->id();
        $stock->created_by ??= auth()->id();

        $stock->save();

        return $stock;
    }

    private function createMovement(
        ProductStock $stock,
        array $data
    ): InventoryMovement {
        return InventoryMovement::create([
            'company_id' => $stock->company_id,
            'branch_id' => $stock->branch_id,
            'warehouse_id' => $stock->warehouse_id,
            'reseller_id' => $data['reseller_id'] ?? null,
            'product_variant_id' => $stock->product_variant_id,
            'unit_id' => $stock->unit_id,
            'reference_type' => $data['reference_type'],
            'reference_id' => $data['reference_id'],
            'reference_number' => $data['reference_number'],
            'qty_in' => $data['qty_in'] ?? 0,
            'qty_out' => $data['qty_out'] ?? 0,
            'balance_qty' => $stock->on_hand_qty,
            'unit_cost' => $data['unit_cost'] ?? 0,
            'total_cost' => $data['total_cost'] ?? 0,
            'transaction_date' => $data['transaction_date'],
            'description' => $data['description'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }
}