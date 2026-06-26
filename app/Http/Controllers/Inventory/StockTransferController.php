<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Product;
use App\Models\Warehouse;

use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;


use App\Models\StockTransfer;
use App\Models\StockTransferDetail;
use App\Models\InventoryMovement;
class StockTransferController extends Controller
{
    public function index()
{
    $transfers =

        StockTransfer::with(

            'fromWarehouse',

            'toWarehouse',

            'creator'

        )

        ->latest()

        ->paginate(10)

        ->withQueryString();

    return Inertia::render(

        'Inventory/Transfer/Index',

        [

            'transfers' =>

                $transfers,

            'summary' => [

                'draft' =>

                    StockTransfer::where(

                        'status',

                        'Draft'

                    )->count(),

                'posted' =>

                    StockTransfer::where(

                        'status',

                        'Posted'

                    )->count(),

                'completed' =>

                    StockTransfer::where(

                        'status',

                        'Completed'

                    )->count(),

                'total' =>

                    StockTransfer::count(),

            ],

        ]

    );
}
public function create()
{
    return Inertia::render(

        'Inventory/Transfer/Create',

        [

            'warehouses' =>

                Warehouse::orderBy('name')

                ->get(),

            'products' =>

                Product::where(

                    'status',

                    true

                )

                ->orderBy('name')

                ->get(),

        ]

    );
}
public function store(
    Request $request
)
{
    $request->validate([

        'from_warehouse_id' =>

            'required',

        'to_warehouse_id' =>

            'required',

        'transfer_date' =>

            'required|date',

        'details' =>

            'required|array|min:1',

    ]);

    $transfer =

        StockTransfer::create([

            'transfer_number' =>

                'TRF' .

                str_pad(

                    StockTransfer::count() + 1,

                    5,

                    '0',

                    STR_PAD_LEFT

                ),

            'from_warehouse_id' =>

                $request->from_warehouse_id,

            'to_warehouse_id' =>

                $request->to_warehouse_id,

            'transfer_date' =>

                $request->transfer_date,

            'status' =>

                'Draft',

            'remarks' =>

                $request->remarks,

            'created_by' =>

                auth()->id(),

        ]);

    foreach (

        $request->details

        as $row

    ) {

        StockTransferDetail::create([

            'stock_transfer_id' =>

                $transfer->id,

            'product_id' =>

                $row['product_id'],

            'qty' =>

                $row['qty'],

            'unit_cost' =>

                $row['unit_cost'],

            'total_cost' =>

                $row['qty']
                *
                $row['unit_cost'],

            'remarks' =>

                $row['remarks']
                ?? null,

        ]);

    }

    return redirect()

        ->route(

            'stock-transfers.show',

            $transfer

        )

        ->with(

            'success',

            'Stock Transfer created.'

        );
}
public function show(
    StockTransfer
    $stockTransfer
)
{
    $stockTransfer->load(

        'fromWarehouse',

        'toWarehouse',

        'details.product',

        'creator',

        'poster',

        'completer',

        'canceller'

    );

    return Inertia::render(

        'Inventory/Transfer/Show',

        [

            'transfer' =>

                $stockTransfer,

        ]

    );
}
public function post(
    StockTransfer $stockTransfer
)
{
    if (

        $stockTransfer->status
        !==
        'Draft'

    ) {

        return back();

    }

    DB::transaction(

        function ()

        use (

            $stockTransfer

        ) {

            foreach (

                $stockTransfer->details

                as $detail

            ) {

                $sourceStock =

                    ProductStock::where(

                        'product_id',

                        $detail->product_id

                    )

                    ->where(

                        'warehouse_id',

                        $stockTransfer
                        ->from_warehouse_id

                    )

                    ->first();

                if (

                    !$sourceStock ||

                    $sourceStock->qty
                    <
                    $detail->qty

                ) {

                    throw new \Exception(

                        'Insufficient stock.'

                    );

                }

                $sourceStock->decrement(

                    'qty',

                    $detail->qty

                );

                InventoryMovement::create([

                    'product_id' =>

                        $detail->product_id,

                    'warehouse_id' =>

                        $stockTransfer
                        ->from_warehouse_id,

                    'reference_type' =>

                        'TRANSFER_OUT',

                    'reference_id' =>

                        $stockTransfer->id,

                    'reference_number' =>

                        $stockTransfer
                        ->transfer_number,

                    'qty_in' => 0,

                    'qty_out' =>

                        $detail->qty,

                    'balance_qty' =>

                        $sourceStock
                        ->fresh()
                        ->qty,

                    'unit_cost' =>

                        $detail->unit_cost,

                    'total_cost' =>

                        $detail->total_cost,

                    'transaction_date' =>

                        now(),

                    'created_by' =>

                        auth()->id(),

                ]);

                $destinationStock =

                    ProductStock::firstOrCreate(

                        [

                            'product_id' =>

                                $detail->product_id,

                            'warehouse_id' =>

                                $stockTransfer
                                ->to_warehouse_id,

                        ],

                        [

                            'qty' => 0,

                        ]

                    );

                $destinationStock->increment(

                    'qty',

                    $detail->qty

                );

                InventoryMovement::create([

                    'product_id' =>

                        $detail->product_id,

                    'warehouse_id' =>

                        $stockTransfer
                        ->to_warehouse_id,

                    'reference_type' =>

                        'TRANSFER_IN',

                    'reference_id' =>

                        $stockTransfer->id,

                    'reference_number' =>

                        $stockTransfer
                        ->transfer_number,

                    'qty_in' =>

                        $detail->qty,

                    'qty_out' => 0,

                    'balance_qty' =>

                        $destinationStock
                        ->fresh()
                        ->qty,

                    'unit_cost' =>

                        $detail->unit_cost,

                    'total_cost' =>

                        $detail->total_cost,

                    'transaction_date' =>

                        now(),

                    'created_by' =>

                        auth()->id(),

                ]);

            }

            $stockTransfer->update([

                'status' =>

                    'Posted',

                'posted_by' =>

                    auth()->id(),

                'posted_at' =>

                    now(),

            ]);

        }

    );

    return redirect()

    ->route(

        'stock-transfers.show',

        $stockTransfer

    )

    ->with(

        'success',

        'Transfer posted successfully.'

    );
}
public function complete(
    StockTransfer $stockTransfer
)
{
    if (

        $stockTransfer->status
        !== 'Posted'

    ) {

        return back();

    }

    $stockTransfer->update([

        'status' => 'Completed',

        'completed_by' => auth()->id(),

        'completed_at' => now(),

    ]);

    return redirect()

        ->route(

            'stock-transfers.show',

            $stockTransfer

        )

        ->with(

            'success',

            'Transfer completed successfully.'

        );

}
public function cancel(
    Request $request,
    StockTransfer $stockTransfer
)
{
    if (

        $stockTransfer->status
        !==
        'Draft'

    ) {

        return back()

            ->with(

                'error',

                'Only draft transfers can be cancelled.'

            );

    }

    $request->validate([

        'cancel_reason' =>

            'required|string|max:500',

    ]);

    $stockTransfer->update([

        'status' =>

            'Cancelled',

        'cancelled_by' =>

            auth()->id(),

        'cancelled_at' =>

            now(),

        'cancel_reason' =>

            $request->cancel_reason,

    ]);

  return redirect()

    ->route(

        'stock-transfers.show',

        $stockTransfer

    )

    ->with(

        'success',

        'Transfer cancelled successfully.'

    );

}
public function getWarehouseStocks(
    Warehouse $warehouse
)
{
    $stocks =

        ProductStock::with(

            'product'

        )

        ->where(

            'warehouse_id',

            $warehouse->id

        )

        ->where(

            'qty',

            '>',

            0

        )

        ->get()

        ->map(

            function (

                $stock

            ) {

                return [

                    'product_id' =>

                        $stock->product_id,

                    'product_name' =>

                        $stock->product?->name,

                    'available_qty' =>

                        $stock->qty,

                    'unit_cost' =>

                        InventoryMovement::where(

                            'product_id',

                            $stock->product_id

                        )

                        ->where(

                            'warehouse_id',

                            $stock->warehouse_id

                        )

                        ->latest('id')

                        ->value(

                            'unit_cost'

                        )

                        ?? 0,

                ];

            }

        );

    return response()->json(

        $stocks

    );
}


}
