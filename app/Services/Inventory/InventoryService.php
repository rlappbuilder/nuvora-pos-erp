<?php

namespace App\Services\Inventory;
use App\Models\Inventory\InventoryAdjustmentHeader;
use App\Models\Inventory\InventoryAdjustmentDetail;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\InventoryMovement;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\OpeningStockHeader;
use App\Models\Inventory\OpeningStockDetail;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use App\Models\Core\DocumentActivity;
class InventoryService
{
    public function __construct(
    CodeGeneratorService $codeGeneratorService,
    DocumentActivityService $documentActivityService
) {
    $this->codeGeneratorService =
        $codeGeneratorService;

    $this->documentActivityService =
        $documentActivityService;
}
    /*
    |--------------------------------------------------------------------------
    | Public Methods
    |--------------------------------------------------------------------------
    */

   public function openingStock(array $data): OpeningStockHeader
{
    return DB::transaction(function () use ($data) {

        /*
        |--------------------------------------------------------------------------
        | Create Header - DRAFT
        |--------------------------------------------------------------------------
        */

        $header = OpeningStockHeader::create([

            'company_id' =>
                $data['company_id'],

            'branch_id' =>
                $data['branch_id'],

            'warehouse_id' =>
                $data['warehouse_id'],

            'number' =>
                $this->codeGeneratorService
                    ->next('opening_stock'),

            'transaction_date' =>
                $data['transaction_date'],

            'status' =>
                'Draft',

            'description' =>
                $data['description'] ?? null,

            'created_by' =>
                auth()->id(),

        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Details
        |--------------------------------------------------------------------------
        */

        foreach ($data['details'] as $detail) {

            OpeningStockDetail::create([

                'opening_stock_header_id' =>
                    $header->id,

                'product_variant_id' =>
                    $detail['product_variant_id'],

                'unit_id' =>
                    $detail['unit_id'],

                'qty' =>
                    $detail['qty'],

                'unit_cost' =>
                    $detail['unit_cost'],

                'total_cost' =>
                    $detail['qty']
                    *
                    $detail['unit_cost'],

                'description' =>
                    $detail['description'] ?? null,

                'created_by' =>
                    auth()->id(),

            ]);

        }

        /*
        |--------------------------------------------------------------------------
        | Document Activity - CREATED
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService->record(

            $header,

            'CREATED',

            null,

            'Draft',

            'Opening stock created.'

        );

        return $header;
    });
}
public function postOpeningStock(
    OpeningStockHeader $openingStock
): void {
    DB::transaction(function () use ($openingStock) {

        /*
        |--------------------------------------------------------------------------
        | Lock Header
        |--------------------------------------------------------------------------
        */

        $openingStock = OpeningStockHeader::query()
            ->with('details')
            ->lockForUpdate()
            ->findOrFail($openingStock->id);


        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        if ($openingStock->status !== 'Draft') {

            throw new \RuntimeException(
                'Only Draft opening stock can be posted.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Post Details
        |--------------------------------------------------------------------------
        */

        foreach ($openingStock->details as $detail) {

            $stock = $this->updateCurrentStock([

                'company_id' =>
                    $openingStock->company_id,

                'branch_id' =>
                    $openingStock->branch_id,

                'warehouse_id' =>
                    $openingStock->warehouse_id,

                'product_variant_id' =>
                    $detail->product_variant_id,

                'unit_id' =>
                    $detail->unit_id,

                'qty' =>
                    $detail->qty,

                'average_cost' =>
                    $detail->unit_cost,

                'transaction_date' =>
                    $openingStock->transaction_date,

            ]);


            /*
            |--------------------------------------------------------------------------
            | Inventory Movement
            |--------------------------------------------------------------------------
            */

            $this->createMovement(

                $stock,

                [

                    'reference_type' =>
                        'OPENING_STOCK',

                    'reference_id' =>
                        $openingStock->id,

                    'reference_number' =>
                        $openingStock->number,

                    'qty_in' =>
                        $detail->qty,

                    'qty_out' =>
                        0,

                    'unit_cost' =>
                        $detail->unit_cost,

                    'total_cost' =>
                        $detail->total_cost,

                    'transaction_date' =>
                        $openingStock->transaction_date,

                    'description' =>
                        $detail->description
                        ??
                        $openingStock->description
                        ??
                        null,

                ]

            );

        }


        /*
        |--------------------------------------------------------------------------
        | Mark Posted
        |--------------------------------------------------------------------------
        */

        $openingStock->update([

            'status' =>
                'Posted',

            'posted_at' =>
                now(),

            'posted_by' =>
                auth()->id(),

            'updated_by' =>
                auth()->id(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Document Activity - POSTED
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService->record(

            $openingStock,

            'POSTED',

            'Draft',

            'Posted',

            'Opening stock posted.'

        );

    });
}
public function rejectOpeningStock(
    OpeningStockHeader $openingStock,
    string $reason
): void {
    DB::transaction(function () use (
        $openingStock,
        $reason
    ) {

        /*
        |--------------------------------------------------------------------------
        | Lock Header
        |--------------------------------------------------------------------------
        */

        $openingStock = OpeningStockHeader::query()
            ->lockForUpdate()
            ->findOrFail(
                $openingStock->id
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        if ($openingStock->status !== 'Draft') {

            throw new \RuntimeException(
                'Only Draft opening stock can be rejected.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Reject
        |--------------------------------------------------------------------------
        */

        $openingStock->update([

            'status' =>
                'Rejected',

            'rejected_at' =>
                now(),

            'rejected_by' =>
                auth()->id(),

            'rejected_reason' =>
                $reason,

            'updated_by' =>
                auth()->id(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Document Activity
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService->record(

            $openingStock,

            'REJECTED',

            'Draft',

            'Rejected',

            'Opening stock rejected.',

            [
                'reason' =>
                    $reason,
            ]

        );

    });
}

public function duplicateOpeningStock(OpeningStockHeader $openingStock): OpeningStockHeader {
    return DB::transaction(function () use ($openingStock) {

        $openingStock->load('details');

        $duplicate = OpeningStockHeader::create([

            'company_id' =>
                $openingStock->company_id,

            'branch_id' =>
                $openingStock->branch_id,

            'warehouse_id' =>
                $openingStock->warehouse_id,

            'number' =>
                $this->codeGeneratorService
                    ->next('opening_stock'),

            'transaction_date' =>
                $openingStock->transaction_date,

            'status' => 'Draft',

            'description' =>
                $openingStock->description
                ? 'Copy - ' . $openingStock->description
                : 'Copy Opening Stock',

            'created_by' =>
                auth()->id(),

        ]);

        foreach ($openingStock->details as $detail) {

            OpeningStockDetail::create([

                'opening_stock_header_id' =>
                    $duplicate->id,

                'product_variant_id' =>
                    $detail->product_variant_id,

                'unit_id' =>
                    $detail->unit_id,

                'qty' =>
                    $detail->qty,

                'unit_cost' =>
                    $detail->unit_cost,

                'total_cost' =>
                    $detail->total_cost,

                'description' =>
                    $detail->description,

                'created_by' =>
                    auth()->id(),

            ]);

        }

        return $duplicate;
    });
}
public function duplicateInventoryAdjustment(
    InventoryAdjustmentHeader $inventoryAdjustment
): InventoryAdjustmentHeader {
    return DB::transaction(function () use (
        $inventoryAdjustment
    ) {

        $inventoryAdjustment->load('details');

        $duplicate = InventoryAdjustmentHeader::create([

            'company_id' =>
                $inventoryAdjustment->company_id,

            'branch_id' =>
                $inventoryAdjustment->branch_id,

            'warehouse_id' =>
                $inventoryAdjustment->warehouse_id,

            'number' =>
                $this->codeGeneratorService
                    ->next('stock_adjustment'),

            'transaction_date' =>
                $inventoryAdjustment->transaction_date,

            'status' => 'Draft',

            'description' =>
                $inventoryAdjustment->description
                ? 'Copy - ' .
                    $inventoryAdjustment->description
                : 'Copy Inventory Adjustment',

            'created_by' =>
                auth()->id(),

        ]);


        foreach (
            $inventoryAdjustment->details
            as $detail
        ) {

            InventoryAdjustmentDetail::create([

                'inventory_adjustment_header_id' =>
                    $duplicate->id,

                'product_variant_id' =>
                    $detail->product_variant_id,

                'unit_id' =>
                    $detail->unit_id,

                'system_qty' =>
                    $detail->system_qty,

                'actual_qty' =>
                    $detail->actual_qty,

                'difference_qty' =>
                    $detail->difference_qty,

                'unit_cost' =>
                    $detail->unit_cost,

                'total_cost' =>
                    $detail->total_cost,

                'description' =>
                    $detail->description,

                'created_by' =>
                    auth()->id(),

            ]);

        }


        return $duplicate;
    });
}
public function updateOpeningStock(
    OpeningStockHeader $openingStock,
    array $data
): void {
    DB::transaction(function () use (
        $openingStock,
        $data
    ) {

        /*
        |--------------------------------------------------------------------------
        | Lock Header
        |--------------------------------------------------------------------------
        */

        $openingStock = OpeningStockHeader::query()
            ->lockForUpdate()
            ->findOrFail($openingStock->id);


        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        if (! in_array(
            $openingStock->status,
            [
                'Draft',
                'Rejected',
            ]
        )) {

            throw new \RuntimeException(
                'Only Draft or Rejected opening stock can be edited.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Capture Old Status
        |--------------------------------------------------------------------------
        */

        $oldStatus =
            $openingStock->status;


        /*
        |--------------------------------------------------------------------------
        | Update Header
        |--------------------------------------------------------------------------
        */

        $openingStock->update([

            'company_id' =>
                $data['company_id'],

            'branch_id' =>
                $data['branch_id'],

            'warehouse_id' =>
                $data['warehouse_id'],

            'transaction_date' =>
                $data['transaction_date'],

            'description' =>
                $data['description'] ?? null,

            'updated_by' =>
                auth()->id(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Replace Details
        |--------------------------------------------------------------------------
        */

        $openingStock->details()->delete();


        foreach ($data['details'] as $detail) {

            OpeningStockDetail::create([

                'opening_stock_header_id' =>
                    $openingStock->id,

                'product_variant_id' =>
                    $detail['product_variant_id'],

                'unit_id' =>
                    $detail['unit_id'],

                'qty' =>
                    $detail['qty'],

                'unit_cost' =>
                    $detail['unit_cost'],

                'total_cost' =>
                    $detail['qty']
                    *
                    $detail['unit_cost'],

                'description' =>
                    $detail['description'] ?? null,

                'created_by' =>
                    auth()->id(),

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Activity
        |--------------------------------------------------------------------------
        */

        if ($oldStatus === 'Rejected') {

            /*
            | Rejected → Draft
            */

            $openingStock->update([

                'status' =>
                    'Draft',

                'rejected_at' =>
                    null,

                'rejected_by' =>
                    null,

                'rejected_reason' =>
                    null,

                'updated_by' =>
                    auth()->id(),

            ]);

            $this->documentActivityService->record(

                $openingStock,

                'RESUBMITTED',

                'Rejected',

                'Draft',

                'Rejected opening stock was corrected and resubmitted.'

            );

        } else {

            /*
            | Draft → Draft
            */

            $this->documentActivityService->record(

                $openingStock,

                'UPDATED',

                'Draft',

                'Draft',

                'Opening stock updated.'

            );

        }

    });
}

public function deleteOpeningStocks(array $ids): void
{
    DB::transaction(function () use ($ids) {

        $openingStocks = OpeningStockHeader::whereIn(
            'id',
            $ids
        )->get();

        foreach ($openingStocks as $openingStock) {

            if ($openingStock->status === 'Posted') {

                throw new \RuntimeException(
                    'Posted opening stock cannot be deleted.'
                );
            }

            $openingStock->details()->delete();

            $openingStock->delete();
        }

    });
}
public function deleteInventoryAdjustments(
    array $ids
): void {
    DB::transaction(function () use ($ids) {

        $inventoryAdjustments =
            InventoryAdjustmentHeader::whereIn(
                'id',
                $ids
            )->get();

        foreach (
            $inventoryAdjustments
            as $inventoryAdjustment
        ) {

            if (
                $inventoryAdjustment->status === 'Posted'
            ) {

                throw new \RuntimeException(
                    'Posted inventory adjustment cannot be deleted.'
                );

            }

            $inventoryAdjustment
                ->details()
                ->delete();

            $inventoryAdjustment->delete();
        }

    });
}
    public function stockIn(array $data): void
{
    DB::transaction(function () use ($data) {

        $stock = $this->updateCurrentStock([

            'company_id' =>
                $data['company_id'],

            'branch_id' =>
                $data['branch_id'],

            'warehouse_id' =>
                $data['warehouse_id'],

            'product_variant_id' =>
                $data['product_variant_id'],

            'unit_id' =>
                $data['unit_id'],

            'qty' =>
                $data['qty'],

            'average_cost' =>
                $data['unit_cost'] ?? null,

            'transaction_date' =>
                $data['transaction_date'],

        ]);

        $this->createMovement(

            $stock,

            [

                'reference_type' =>
                    $data['reference_type'],

                'reference_id' =>
                    $data['reference_id'],

                'reference_number' =>
                    $data['reference_number'],

                'qty_in' =>
                    $data['qty'],

                'qty_out' =>
                    0,

                'unit_cost' =>
                    $data['unit_cost'] ?? 0,

                'total_cost' =>
                    $data['total_cost']
                    ??
                    (
                        $data['qty']
                        *
                        ($data['unit_cost'] ?? 0)
                    ),

                'transaction_date' =>
                    $data['transaction_date'],

                'description' =>
                    $data['description'] ?? null,

            ]

        );

    });
}

    public function stockOut(array $data): void
{
    DB::transaction(function () use ($data) {

        $stock = ProductStock::where([
            'company_id' => $data['company_id'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['warehouse_id'],
            'product_variant_id' => $data['product_variant_id'],
            'unit_id' => $data['unit_id'],
        ])
        ->lockForUpdate()
        ->firstOrFail();

        if ($stock->available_qty < $data['qty']) {
            throw new \RuntimeException(
                'Insufficient available stock.'
            );
        }

        $stock->on_hand_qty -= $data['qty'];

        $stock->available_qty =
            $stock->on_hand_qty -
            $stock->reserved_qty;

        $stock->last_transaction_at =
            $data['transaction_date'];

        $stock->updated_by =
            auth()->id();

        $stock->save();

        $this->createMovement(
            $stock,
            [
                'reference_type' =>
                    $data['reference_type'],

                'reference_id' =>
                    $data['reference_id'],

                'reference_number' =>
                    $data['reference_number'],

                'qty_in' => 0,

                'qty_out' =>
                    $data['qty'],

                'unit_cost' =>
                    $data['unit_cost']
                    ?? $stock->average_cost,

                'total_cost' =>
                    $data['total_cost']
                    ??
                    (
                        $data['qty']
                        *
                        ($data['unit_cost']
                            ?? $stock->average_cost)
                    ),

                'transaction_date' =>
                    $data['transaction_date'],

                'description' =>
                    $data['description'] ?? null,
            ]
        );

    });
}

    public function adjustment(array $data): void
{
    DB::transaction(function () use ($data) {

        $stock = ProductStock::where([
            'company_id' => $data['company_id'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['warehouse_id'],
            'product_variant_id' => $data['product_variant_id'],
            'unit_id' => $data['unit_id'],
        ])
        ->lockForUpdate()
        ->first();

        /*
        |--------------------------------------------------------------------------
        | Stock belum ada
        |--------------------------------------------------------------------------
        */

        if (! $stock) {

            if ($data['qty'] < 0) {
                throw new \RuntimeException(
                    'Cannot reduce stock that does not exist.'
                );
            }

            $stock = $this->updateCurrentStock([

                'company_id' =>
                    $data['company_id'],

                'branch_id' =>
                    $data['branch_id'],

                'warehouse_id' =>
                    $data['warehouse_id'],

                'product_variant_id' =>
                    $data['product_variant_id'],

                'unit_id' =>
                    $data['unit_id'],

                'qty' =>
                    $data['qty'],

                'average_cost' =>
                    $data['unit_cost'] ?? 0,

                'transaction_date' =>
                    $data['transaction_date'],

            ]);

        } else {

            /*
            |--------------------------------------------------------------------------
            | Adjustment
            |--------------------------------------------------------------------------
            */

            $newQty =
                $stock->on_hand_qty
                +
                $data['qty'];

            if ($newQty < 0) {

                throw new \RuntimeException(
                    'Adjustment would result in negative stock.'
                );

            }

            $stock->on_hand_qty =
                $newQty;

            $stock->available_qty =
                $stock->on_hand_qty
                -
                $stock->reserved_qty;

            if (isset($data['unit_cost'])) {

                $stock->average_cost =
                    $data['unit_cost'];

            }

            $stock->last_transaction_at =
                $data['transaction_date'];

            $stock->updated_by =
                auth()->id();

            $stock->save();

        }

        /*
        |--------------------------------------------------------------------------
        | Movement
        |--------------------------------------------------------------------------
        */

        $this->createMovement(

            $stock,

            [

                'reference_type' =>
                    $data['reference_type'],

                'reference_id' =>
                    $data['reference_id'],

                'reference_number' =>
                    $data['reference_number'],

                'qty_in' =>
                    $data['qty'] > 0
                        ? $data['qty']
                        : 0,

                'qty_out' =>
                    $data['qty'] < 0
                        ? abs($data['qty'])
                        : 0,

                'unit_cost' =>
                    $data['unit_cost']
                    ?? $stock->average_cost,

                'total_cost' =>
                    abs($data['qty'])
                    *
                    (
                        $data['unit_cost']
                        ?? $stock->average_cost
                    ),

                'transaction_date' =>
                    $data['transaction_date'],

                'description' =>
                    $data['description'] ?? null,

            ]

        );

    });
}

public function transfer(array $data): void
{
    DB::transaction(function () use ($data) {

        /*
        |--------------------------------------------------------------------------
        | Source Stock
        |--------------------------------------------------------------------------
        */

        $sourceStock = ProductStock::where([
            'company_id' => $data['company_id'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['from_warehouse_id'],
            'product_variant_id' => $data['product_variant_id'],
            'unit_id' => $data['unit_id'],
        ])
        ->lockForUpdate()
        ->firstOrFail();

        if ($sourceStock->available_qty < $data['qty']) {

            throw new \RuntimeException(
                'Insufficient available stock for transfer.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Destination Stock
        |--------------------------------------------------------------------------
        */

        $destinationStock = ProductStock::where([
            'company_id' => $data['company_id'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['to_warehouse_id'],
            'product_variant_id' => $data['product_variant_id'],
            'unit_id' => $data['unit_id'],
        ])
        ->lockForUpdate()
        ->first();

        if (! $destinationStock) {

            $destinationStock = new ProductStock();

            $destinationStock->company_id =
                $data['company_id'];

            $destinationStock->branch_id =
                $data['branch_id'];

            $destinationStock->warehouse_id =
                $data['to_warehouse_id'];

            $destinationStock->product_variant_id =
                $data['product_variant_id'];

            $destinationStock->unit_id =
                $data['unit_id'];

            $destinationStock->on_hand_qty = 0;

            $destinationStock->reserved_qty = 0;

            $destinationStock->available_qty = 0;

            $destinationStock->average_cost =
                $sourceStock->average_cost;

            $destinationStock->created_by =
                auth()->id();
        }

        /*
        |--------------------------------------------------------------------------
        | Source Stock Update
        |--------------------------------------------------------------------------
        */

        $sourceStock->on_hand_qty -=
            $data['qty'];

        $sourceStock->available_qty =
            $sourceStock->on_hand_qty
            -
            $sourceStock->reserved_qty;

        $sourceStock->last_transaction_at =
            $data['transaction_date'];

        $sourceStock->updated_by =
            auth()->id();

        $sourceStock->save();

        /*
        |--------------------------------------------------------------------------
        | Destination Stock Update
        |--------------------------------------------------------------------------
        */

        $destinationStock->on_hand_qty +=
            $data['qty'];

        $destinationStock->available_qty =
            $destinationStock->on_hand_qty
            -
            $destinationStock->reserved_qty;

        $destinationStock->last_transaction_at =
            $data['transaction_date'];

        $destinationStock->updated_by =
            auth()->id();

        $destinationStock->save();

        /*
        |--------------------------------------------------------------------------
        | Out Movement
        |--------------------------------------------------------------------------
        */

        $this->createMovement(

            $sourceStock,

            [

                'reference_type' =>
                    $data['reference_type'],

                'reference_id' =>
                    $data['reference_id'],

                'reference_number' =>
                    $data['reference_number'],

                'qty_in' => 0,

                'qty_out' =>
                    $data['qty'],

                'unit_cost' =>
                    $sourceStock->average_cost,

                'total_cost' =>
                    $data['qty']
                    *
                    $sourceStock->average_cost,

                'transaction_date' =>
                    $data['transaction_date'],

                'description' =>
                    $data['description'] ?? null,

            ]

        );

        /*
        |--------------------------------------------------------------------------
        | In Movement
        |--------------------------------------------------------------------------
        */

        $this->createMovement(

            $destinationStock,

            [

                'reference_type' =>
                    $data['reference_type'],

                'reference_id' =>
                    $data['reference_id'],

                'reference_number' =>
                    $data['reference_number'],

                'qty_in' =>
                    $data['qty'],

                'qty_out' => 0,

                'unit_cost' =>
                    $sourceStock->average_cost,

                'total_cost' =>
                    $data['qty']
                    *
                    $sourceStock->average_cost,

                'transaction_date' =>
                    $data['transaction_date'],

                'description' =>
                    $data['description'] ?? null,

            ]

        );

    });
}

    public function reserve(array $data): void
{
    DB::transaction(function () use ($data) {

        $stock = ProductStock::where([
            'company_id' => $data['company_id'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['warehouse_id'],
            'product_variant_id' => $data['product_variant_id'],
            'unit_id' => $data['unit_id'],
        ])
        ->lockForUpdate()
        ->firstOrFail();

        if ($stock->available_qty < $data['qty']) {

            throw new \RuntimeException(
                'Insufficient available stock for reservation.'
            );
        }

        $stock->reserved_qty +=
            $data['qty'];

        $stock->available_qty =
            $stock->on_hand_qty
            -
            $stock->reserved_qty;

        $stock->updated_by =
            auth()->id();

        $stock->save();

    });
}

    public function releaseReservation(array $data): void
{
    DB::transaction(function () use ($data) {

        $stock = ProductStock::where([
            'company_id' => $data['company_id'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['warehouse_id'],
            'product_variant_id' => $data['product_variant_id'],
            'unit_id' => $data['unit_id'],
        ])
        ->lockForUpdate()
        ->firstOrFail();

        if ($stock->reserved_qty < $data['qty']) {

            throw new \RuntimeException(
                'Release quantity exceeds reserved stock.'
            );
        }

        $stock->reserved_qty -=
            $data['qty'];

        $stock->available_qty =
            $stock->on_hand_qty
            -
            $stock->reserved_qty;

        $stock->updated_by =
            auth()->id();

        $stock->save();

    });
}

    /*
    |--------------------------------------------------------------------------
    | Private Methods
    |--------------------------------------------------------------------------
    */

    private function updateCurrentStock(array $data): ProductStock
{
    $stock = ProductStock::firstOrNew([

        'company_id'         => $data['company_id'],

        'branch_id'          => $data['branch_id'],

        'warehouse_id'       => $data['warehouse_id'],

        'product_variant_id' => $data['product_variant_id'],

        'unit_id'            => $data['unit_id'],

    ]);

    if (! $stock->exists) {

        $stock->on_hand_qty = 0;

        $stock->reserved_qty = 0;

        $stock->available_qty = 0;

        $stock->average_cost = 0;
    }

    $stock->on_hand_qty += $data['qty'];

    $stock->available_qty =
        $stock->on_hand_qty -
        $stock->reserved_qty;

    if (isset($data['average_cost'])) {

        $stock->average_cost =
            $data['average_cost'];
    }

    $stock->last_transaction_at =
        $data['transaction_date'];

    $stock->updated_by =
        auth()->id();

    $stock->created_by ??=
        auth()->id();

    $stock->save();

    return $stock;
}

    private function createMovement(ProductStock $stock,array $data): InventoryMovement
{
    return InventoryMovement::create([

        /*
        |--------------------------------------------------------------------------
        | Identity
        |--------------------------------------------------------------------------
        */

        'company_id'         => $stock->company_id,

        'branch_id'          => $stock->branch_id,

        'warehouse_id'       => $stock->warehouse_id,

        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        'product_variant_id' => $stock->product_variant_id,

        'unit_id'            => $stock->unit_id,

        /*
        |--------------------------------------------------------------------------
        | Reference
        |--------------------------------------------------------------------------
        */

        'reference_type'     => $data['reference_type'],

        'reference_id'       => $data['reference_id'],

        'reference_number'   => $data['reference_number'],

        /*
        |--------------------------------------------------------------------------
        | Movement
        |--------------------------------------------------------------------------
        */

        'qty_in'             => $data['qty_in'] ?? 0,

        'qty_out'            => $data['qty_out'] ?? 0,

        'balance_qty'        => $stock->on_hand_qty,

        /*
        |--------------------------------------------------------------------------
        | Cost
        |--------------------------------------------------------------------------
        */

        'unit_cost'          => $data['unit_cost'] ?? 0,

        'total_cost'         => $data['total_cost'] ?? 0,

        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        'transaction_date'   => $data['transaction_date'],

        'description'        => $data['description'] ?? null,

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        'created_by'         => auth()->id(),

    ]);
}

    private function calculateAverageCost(
        ProductStock $stock,
        array $data
    ): float
    {
        return 0;
    }

public function inventoryAdjustment(
    array $data
): InventoryAdjustmentHeader {

    return DB::transaction(function () use ($data) {

        /*
        |--------------------------------------------------------------------------
        | Create Header - DRAFT
        |--------------------------------------------------------------------------
        */

            $header = InventoryAdjustmentHeader::create([

                'company_id' =>
                    $data['company_id'],

                'branch_id' =>
                    $data['branch_id'],

                'warehouse_id' =>
                    $data['warehouse_id'],

                'number' =>
                    $this->codeGeneratorService
                        ->next('stock_adjustment'),

                'transaction_date' =>
                    $data['transaction_date'],

                'status' =>
                    'Draft',

                'description' =>
                    $data['description'] ?? null,

                'created_by' =>
                    auth()->id(),

            ]);

//dd('AFTER HEADER CREATE', $header);
                /*
        |--------------------------------------------------------------------------
        | Create Details
        |--------------------------------------------------------------------------
        */

        foreach ($data['details'] as $detail) {

            /*
            |--------------------------------------------------------------------------
            | Get Current Product Stock
            |--------------------------------------------------------------------------
            */

            $stock = ProductStock::query()
                ->where(
                    'company_id',
                    $header->company_id
                )
                ->where(
                    'branch_id',
                    $header->branch_id
                )
                ->where(
                    'warehouse_id',
                    $header->warehouse_id
                )
                ->where(
                    'product_variant_id',
                    $detail['product_variant_id']
                )
                ->where(
                    'unit_id',
                    $detail['unit_id']
                )
                ->first();


            /*
            |--------------------------------------------------------------------------
            | System Quantity
            |--------------------------------------------------------------------------
            */

            $systemQty = $stock
                ? (float) $stock->on_hand_qty
                : 0;


            /*
            |--------------------------------------------------------------------------
            | Actual Quantity
            |--------------------------------------------------------------------------
            */

            $actualQty =
                (float) $detail['actual_qty'];


            /*
            |--------------------------------------------------------------------------
            | Difference Quantity
            |--------------------------------------------------------------------------
            */

            $differenceQty =
                $actualQty
                -
                $systemQty;


            /*
            |--------------------------------------------------------------------------
            | Unit Cost
            |--------------------------------------------------------------------------
            */

            $unitCost = $stock
                ? (float) $stock->average_cost
                : 0;


            /*
            |--------------------------------------------------------------------------
            | Total Cost
            |--------------------------------------------------------------------------
            */

            $totalCost =
                abs($differenceQty)
                *
                $unitCost;


            /*
            |--------------------------------------------------------------------------
            | Create Detail
            |--------------------------------------------------------------------------
            */
           
            InventoryAdjustmentDetail::create([

                'inventory_adjustment_header_id' =>
                    $header->id,

                'product_variant_id' =>
                    $detail['product_variant_id'],

                'unit_id' =>
                    $detail['unit_id'],

                'system_qty' =>
                    $systemQty,

                'actual_qty' =>
                    $actualQty,

                'difference_qty' =>
                    $differenceQty,

                'unit_cost' =>
                    $unitCost,

                'total_cost' =>
                    $totalCost,

                'description' =>
                    $detail['description'] ?? null,

                'created_by' =>
                    auth()->id(),

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Document Activity
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService->record(
            $header,
            'CREATED',
            null,
            'Draft',
            'Inventory adjustment created.'
        );


        return $header;

    });

}
public function updateInventoryAdjustment(
    InventoryAdjustmentHeader $header,
    array $data
): InventoryAdjustmentHeader {

    return DB::transaction(function () use ($header, $data) {

        /*
        |--------------------------------------------------------------------------
        | Lock Header
        |--------------------------------------------------------------------------
        */

        $header = InventoryAdjustmentHeader::query()
            ->lockForUpdate()
            ->findOrFail($header->id);


        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        if (! in_array(
            $header->status,
            ['Draft', 'Rejected']
        )) {

            throw new \RuntimeException(
                'Only Draft or Rejected inventory adjustment can be updated.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Update Header
        |--------------------------------------------------------------------------
        */

        $header->update([

            'company_id' =>
                $data['company_id'],

            'branch_id' =>
                $data['branch_id'],

            'warehouse_id' =>
                $data['warehouse_id'],

            'transaction_date' =>
                $data['transaction_date'],

            'description' =>
                $data['description'] ?? null,

            'updated_by' =>
                auth()->id(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Delete Existing Details
        |--------------------------------------------------------------------------
        */

        $header->details()->delete();


        /*
        |--------------------------------------------------------------------------
        | Create New Details
        |--------------------------------------------------------------------------
        */

        foreach ($data['details'] as $detail) {

            /*
            |--------------------------------------------------------------------------
            | Get Latest Product Stock
            |--------------------------------------------------------------------------
            */

            $stock = ProductStock::query()
                ->where(
                    'company_id',
                    $header->company_id
                )
                ->where(
                    'branch_id',
                    $header->branch_id
                )
                ->where(
                    'warehouse_id',
                    $header->warehouse_id
                )
                ->where(
                    'product_variant_id',
                    $detail['product_variant_id']
                )
                ->where(
                    'unit_id',
                    $detail['unit_id']
                )
                ->first();


            /*
            |--------------------------------------------------------------------------
            | System Quantity
            |--------------------------------------------------------------------------
            */

            $systemQty = $stock
                ? (float) $stock->on_hand_qty
                : 0;


            /*
            |--------------------------------------------------------------------------
            | Actual Quantity
            |--------------------------------------------------------------------------
            */

            $actualQty =
                (float) $detail['actual_qty'];


            /*
            |--------------------------------------------------------------------------
            | Difference
            |--------------------------------------------------------------------------
            */

            $differenceQty =
                $actualQty
                -
                $systemQty;


            /*
            |--------------------------------------------------------------------------
            | Unit Cost
            |--------------------------------------------------------------------------
            */

            $unitCost = $stock
                ? (float) $stock->average_cost
                : 0;


            /*
            |--------------------------------------------------------------------------
            | Total Cost
            |--------------------------------------------------------------------------
            */

            $totalCost =
                abs($differenceQty)
                *
                $unitCost;


            /*
            |--------------------------------------------------------------------------
            | Create Detail
            |--------------------------------------------------------------------------
            */

            InventoryAdjustmentDetail::create([

                'inventory_adjustment_header_id' =>
                    $header->id,

                'product_variant_id' =>
                    $detail['product_variant_id'],

                'unit_id' =>
                    $detail['unit_id'],

                'system_qty' =>
                    $systemQty,

                'actual_qty' =>
                    $actualQty,

                'difference_qty' =>
                    $differenceQty,

                'unit_cost' =>
                    $unitCost,

                'total_cost' =>
                    $totalCost,

                'description' =>
                    $detail['description'] ?? null,

                'created_by' =>
                    auth()->id(),

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Update Workflow State
        |--------------------------------------------------------------------------
        */

        if ($header->status === 'Rejected') {

            $header->update([

                'status' =>
                    'Draft',

                'rejected_at' =>
                    null,

                'rejected_by' =>
                    null,

                'rejected_reason' =>
                    null,

                'updated_by' =>
                    auth()->id(),

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Document Activity
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService->record(
            $header,
            'UPDATED'
        );


        return $header->fresh([
            'details',
        ]);

    });

}
public function postInventoryAdjustment(
    InventoryAdjustmentHeader $inventoryAdjustment
): void {

    DB::transaction(function () use ($inventoryAdjustment) {

        /*
        |--------------------------------------------------------------------------
        | Lock Header
        |--------------------------------------------------------------------------
        */

        $inventoryAdjustment =
            InventoryAdjustmentHeader::query()
                ->with('details')
                ->lockForUpdate()
                ->findOrFail(
                    $inventoryAdjustment->id
                );


        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        if (
            $inventoryAdjustment->status
            !== 'Draft'
        ) {

            throw new \RuntimeException(
                'Only Draft inventory adjustment can be posted.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Post Details
        |--------------------------------------------------------------------------
        */

        foreach (
            $inventoryAdjustment->details
            as $detail
        ) {

            /*
            |--------------------------------------------------------------------------
            | Validate Difference
            |--------------------------------------------------------------------------
            */

            $differenceQty =
                (float) $detail->difference_qty;


            /*
            |--------------------------------------------------------------------------
            | No Stock Impact
            |--------------------------------------------------------------------------
            */

            if (
                $differenceQty == 0
            ) {

                continue;

            }


            /*
            |--------------------------------------------------------------------------
            | Determine Stock Direction
            |--------------------------------------------------------------------------
            */

            $qtyIn =
                $differenceQty > 0
                    ? $differenceQty
                    : 0;


            $qtyOut =
                $differenceQty < 0
                    ? abs($differenceQty)
                    : 0;


            /*
            |--------------------------------------------------------------------------
            | Update Product Stock
            |--------------------------------------------------------------------------
            */

            $stock =
                $this->updateCurrentStock([

                    'company_id' =>
                        $inventoryAdjustment
                            ->company_id,

                    'branch_id' =>
                        $inventoryAdjustment
                            ->branch_id,

                    'warehouse_id' =>
                        $inventoryAdjustment
                            ->warehouse_id,

                    'product_variant_id' =>
                        $detail
                            ->product_variant_id,

                    'unit_id' =>
                        $detail
                            ->unit_id,

                    'qty' =>
                        $differenceQty,

                    'average_cost' =>
                        $detail
                            ->unit_cost,

                    'transaction_date' =>
                        $inventoryAdjustment
                            ->transaction_date,

                ]);


            /*
            |--------------------------------------------------------------------------
            | Inventory Movement
            |--------------------------------------------------------------------------
            */

            $this->createMovement(

                $stock,

                [

                    'reference_type' =>
                        'INVENTORY_ADJUSTMENT',

                    'reference_id' =>
                        $inventoryAdjustment
                            ->id,

                    'reference_number' =>
                        $inventoryAdjustment
                            ->number,

                    'qty_in' =>
                        $qtyIn,

                    'qty_out' =>
                        $qtyOut,

                    'unit_cost' =>
                        $detail
                            ->unit_cost,

                    'total_cost' =>
                        $detail
                            ->total_cost,

                    'transaction_date' =>
                        $inventoryAdjustment
                            ->transaction_date,

                    'description' =>
                        $detail
                            ->description
                        ??
                        $inventoryAdjustment
                            ->description
                        ??
                        null,

                ]

            );

        }


        /*
        |--------------------------------------------------------------------------
        | Mark Posted
        |--------------------------------------------------------------------------
        */

        $inventoryAdjustment->update([

            'status' =>
                'Posted',

            'posted_at' =>
                now(),

            'posted_by' =>
                auth()->id(),

            'updated_by' =>
                auth()->id(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Document Activity
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService
        ->record(
            $inventoryAdjustment,
            'POSTED',
            'Draft',
            'Posted',
            'Inventory adjustment posted.'
        );

    });

}
public function cancelInventoryAdjustment(
    InventoryAdjustmentHeader $inventoryAdjustment,
    string $reason
): void {

    DB::transaction(function () use (
        $inventoryAdjustment,
        $reason
    ) {

        /*
        |--------------------------------------------------------------------------
        | Lock Header
        |--------------------------------------------------------------------------
        */

        $inventoryAdjustment =
            InventoryAdjustmentHeader::query()
                ->lockForUpdate()
                ->findOrFail(
                    $inventoryAdjustment->id
                );


        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        if (
            $inventoryAdjustment->status
            !== 'Draft'
        ) {

            throw new \RuntimeException(
                'Only Draft inventory adjustment can be rejected.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Mark Rejected
        |--------------------------------------------------------------------------
        */

        $inventoryAdjustment->update([

            'status' =>
                'Rejected',

            'rejected_at' =>
                now(),

            'rejected_by' =>
                auth()->id(),

            'rejected_reason' =>
                $reason,

            'updated_by' =>
                auth()->id(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Document Activity
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService
            ->record(
                $inventoryAdjustment,
                'REJECTED'
            );

    });

}
public function resubmitInventoryAdjustment(
    InventoryAdjustmentHeader $inventoryAdjustment
): void {

    DB::transaction(function () use (
        $inventoryAdjustment
    ) {

        /*
        |--------------------------------------------------------------------------
        | Lock Header
        |--------------------------------------------------------------------------
        */

        $inventoryAdjustment =
            InventoryAdjustmentHeader::query()
                ->lockForUpdate()
                ->findOrFail(
                    $inventoryAdjustment->id
                );


        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        if (
            $inventoryAdjustment->status
            !== 'Rejected'
        ) {

            throw new \RuntimeException(
                'Only Rejected inventory adjustment can be resubmitted.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Back To Draft
        |--------------------------------------------------------------------------
        */

        $inventoryAdjustment->update([

            'status' =>
                'Draft',

            'rejected_at' =>
                null,

            'rejected_by' =>
                null,

            'rejected_reason' =>
                null,

            'updated_by' =>
                auth()->id(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Document Activity
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService
            ->record(
                $inventoryAdjustment,
                'RESUBMITTED'
            );

    });

}
}