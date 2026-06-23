<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Http\Requests\WarehouseRequest;
use Inertia\Inertia;
use App\Models\ProductStock;
use App\Models\InventoryMovement;
class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
   $warehouses = Warehouse::with(
    'branch.company'

    )

    ->when(

        request('search'),

        function ($query) {

            $query->where(

                'code',

                'like',

                '%' . request('search') . '%'

            )

            ->orWhere(

                'name',

                'like',

                '%' . request('search') . '%'

            );

        }

    )

    ->latest()

    ->paginate(10)

    ->withQueryString();

    $warehouses->getCollection()

    ->transform(

        function ($warehouse) {

            $warehouse->total_products =

                ProductStock::where(

                    'warehouse_id',

                    $warehouse->id

                )

                ->distinct()

                ->count(
                    'product_id'
                );

            $warehouse->current_stock =

                ProductStock::where(

                    'warehouse_id',

                    $warehouse->id

                )

                ->sum('qty');

            $warehouse->last_movement =

                InventoryMovement::where(

                    'warehouse_id',

                    $warehouse->id

                )

                ->max(
                    'transaction_date'
                );

            return $warehouse;

        }

    );

    return Inertia::render(

        'MasterData/Warehouses/Index',

        [

            'warehouses' => $warehouses,

            'filters' => [

                'search' => request('search')

            ]

        ]

    );
}

    /**
     * Show the form for creating a new resource.
     */
   public function create()
{
    return Inertia::render(

        'MasterData/Warehouses/Create',

        [

            'branches' => Branch::where(

                'status',

                true

            )->get()

        ]

    );
}
    /**
     * Store a newly created resource in storage.
     */
    public function store(
    WarehouseRequest $request
)
{
    $lastWarehouse = Warehouse::withTrashed()

        ->latest('id')

        ->first();

    if (!$lastWarehouse) {

        $code = 'WH0001';

    } else {

        $lastNumber = (int) substr(

            $lastWarehouse->code,

            2

        );

        $code = 'WH' .

            str_pad(

                $lastNumber + 1,

                4,

                '0',

                STR_PAD_LEFT

            );

    }

    Warehouse::create([

        'branch_id' => $request->branch_id,

        'code' => $code,

        'name' => $request->name,

        'warehouse_type' => $request->warehouse_type,

        'pic_name' => $request->pic_name,

        'phone' => $request->phone,

        'email' => $request->email,

        'address' => $request->address,

        'status' => $request->status,

        'created_by' => auth()->id(),

    ]);

    return redirect()

        ->route(

            'warehouses.index'

        )

        ->with(

            'success',

            'Warehouse created successfully.'

        );
}

    /**
     * Display the specified resource.
     */
    public function show(
    Warehouse $warehouse
)
{
    $warehouse->load(
    'branch.company'
);

    return Inertia::render(

        'MasterData/Warehouses/Show',

        [

            'warehouse' => $warehouse

        ]

    );
}
    /**
     * Show the form for editing the specified resource.
     */
   public function edit(
    Warehouse $warehouse
)
{
    return Inertia::render(

        'MasterData/Warehouses/Edit',

        [

            'warehouse' => $warehouse,

            'branches' => Branch::where(

                'status',

                true

            )->get()

        ]

    );
}
    /**
     * Update the specified resource in storage.
     */
    public function update(
    WarehouseRequest $request,
    Warehouse $warehouse
)
{
    $warehouse->update([

        'branch_id' => $request->branch_id,

        'name' => $request->name,

        'warehouse_type' => $request->warehouse_type,

        'pic_name' => $request->pic_name,

        'phone' => $request->phone,

        'email' => $request->email,

        'address' => $request->address,

        'status' => $request->status,

        'updated_by' => auth()->id(),

    ]);

    return redirect()

        ->route(

            'warehouses.show',

            $warehouse->id

        )

        ->with(

            'success',

            'Warehouse updated successfully.'

        );
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
    Warehouse $warehouse
)
{
    $warehouse->delete();

    return redirect()

        ->route(

            'warehouses.index'

        )

        ->with(

            'success',

            'Warehouse deleted successfully.'

        );
}
}
