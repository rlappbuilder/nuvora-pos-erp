<?php

namespace App\Http\Controllers\Inventory;

use Inertia\Inertia;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\InventoryMovement;
use App\Models\ProductStock;

use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryAdjustmentController extends Controller
{
    public function index()
{
    $adjustments =

        InventoryAdjustment::with(

            'warehouse',
            'creator'

        )

        ->when(

            request('search'),

            function (

                $query

            ) {

                $query->where(

                    'adjustment_number',

                    'like',

                    '%' .
                    request('search') .
                    '%'

                );

            }

        )

        ->when(

            request('status'),

            function (

                $query

            ) {

                $query->where(

                    'status',

                    request('status')

                );

            }

        )

        ->when(

            request('date_from'),

            function (

                $query

            ) {

                $query->whereDate(

                    'adjustment_date',

                    '>=',

                    request('date_from')

                );

            }

        )

        ->when(

            request('date_to'),

            function (

                $query

            ) {

                $query->whereDate(

                    'adjustment_date',

                    '<=',

                    request('date_to')

                );

            }

        )

        ->latest()

        ->paginate(10)

        ->withQueryString();

    return Inertia::render(

        'Inventory/Adjustment/Index',

        [

            'adjustments' =>

                $adjustments,

            'summary' => [

                'draft' =>

                    InventoryAdjustment::where(

                        'status',

                        'Draft'

                    )->count(),

                'posted' =>

                    InventoryAdjustment::where(

                        'status',

                        'Posted'

                    )->count(),

                'cancelled' =>

                    InventoryAdjustment::where(

                        'status',

                        'Cancelled'

                    )->count(),

                'total' =>

                    InventoryAdjustment::count(),

            ],

            'filters' => [

                'search' =>

                    request('search'),

                'status' =>

                    request('status'),

                'date_from' =>

                    request('date_from'),

                'date_to' =>

                    request('date_to'),

            ],

        ]

    );
}

public function create()
{
    return Inertia::render(

        'Inventory/Adjustment/Create',

        [

            'warehouses' =>

                Warehouse::where(

                    'status',
                    true

                )

                ->orderBy('name')

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

        'warehouse_id' =>

            'required|exists:warehouses,id',

        'adjustment_date' =>

            'required|date',

        'details' =>

            'required|array|min:1',

    ]);

    $adjustment =

        InventoryAdjustment::create([

            'adjustment_number' =>

                'ADJ' .
                str_pad(

                    InventoryAdjustment::count() + 1,

                    5,

                    '0',

                    STR_PAD_LEFT

                ),

            'warehouse_id' =>

                $request->warehouse_id,

            'adjustment_date' =>

                $request->adjustment_date,

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

        InventoryAdjustmentDetail::create([

            'inventory_adjustment_id' =>

                $adjustment->id,

            'product_id' =>

                $row['product_id'],

            'system_qty' =>

                $row['system_qty'],

            'physical_qty' =>

                $row['physical_qty'],

            'difference_qty' =>

                $row['physical_qty']
                -
                $row['system_qty'],

                'unit_cost' =>

                $row['unit_cost'],

            'remarks' =>

                $row['remarks']
                ?? null,

        ]);

    }

    return redirect()

        ->route(

            'inventory-adjustments.show',

            $adjustment

        )

        ->with(

            'success',

            'Inventory Adjustment created.'

        );
}
public function show(
    InventoryAdjustment
    $inventoryAdjustment
)
{
    $inventoryAdjustment->load(

        'warehouse',

        'details.product',

        'creator',

        'poster',

        'canceller'

    );

    return Inertia::render(

        'Inventory/Adjustment/Show',

        [

            'adjustment' =>

                $inventoryAdjustment,

        ]

    );
}
public function getWarehouseStocks(
    Warehouse $warehouse
)
{
    return ProductStock::with(

        'product'

    )

    ->where(

        'warehouse_id',

        $warehouse->id

    )

    ->get();
}
public function post(
    InventoryAdjustment
    $inventoryAdjustment
)
{
    if (

        $inventoryAdjustment->status
        !== 'Draft'

    ) {

        return back();

    }

    foreach (

        $inventoryAdjustment->details

        as $detail

    ) {

        $stock =

            ProductStock::where(

                'product_id',

                $detail->product_id

            )

            ->where(

                'warehouse_id',

                $inventoryAdjustment->warehouse_id

            )

            ->first();

        if (

            $stock

        ) {

            $stock->qty =

                $detail->physical_qty;

            $stock->save();

        }

        $lastMovement =

            InventoryMovement::where(

                'product_id',

                $detail->product_id

            )

            ->where(

                'warehouse_id',

                $inventoryAdjustment->warehouse_id

            )

            ->latest('id')

            ->first();

        $currentBalance =

            $lastMovement
            ? $lastMovement->balance_qty
            : 0;

        InventoryMovement::create([

            'product_id' =>

                $detail->product_id,

            'warehouse_id' =>

                $inventoryAdjustment->warehouse_id,

            'reference_type' =>

                'ADJUSTMENT',

            'reference_id' =>

                $inventoryAdjustment->id,

            'reference_number' =>

                $inventoryAdjustment
                ->adjustment_number,

            'qty_in' =>

                $detail->difference_qty > 0
                ? $detail->difference_qty
                : 0,

            'qty_out' =>

                $detail->difference_qty < 0
                ? abs(
                    $detail->difference_qty
                )
                : 0,

            'balance_qty' =>

                $detail->physical_qty,

            'unit_cost' =>

                $detail->unit_cost,

            'total_cost' =>

                abs(
                    $detail->difference_qty
                )
                *
                $detail->unit_cost,

            'transaction_date' =>

                now(),

            'created_by' =>

                auth()->id(),

        ]);

    }

    $inventoryAdjustment->update([

        'status' =>

            'Posted',

        'posted_at' =>

            now(),

        'posted_by' =>

            auth()->id(),

    ]);

    return back()->with(

        'success',

        'Inventory Adjustment posted.'

    );
}
public function cancel(
    Request $request,
    InventoryAdjustment $inventoryAdjustment
)
{
    if (

        $inventoryAdjustment->status
        !== 'Draft'

    ) {

        return back();

    }

    $inventoryAdjustment->update([

        'status' =>

            'Cancelled',

        'cancelled_at' =>

            now(),

        'cancelled_by' =>

            auth()->id(),

        'cancel_reason' =>

            $request->cancel_reason,

    ]);

    return back()->with(

        'success',

        'Inventory Adjustment cancelled.'

    );
}
}
