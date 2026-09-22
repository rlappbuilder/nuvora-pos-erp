<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\ProductStock;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function __construct(
        protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    /**
     * Display a listing of warehouses.
     */
   public function index(Request $request)
{
    $warehouses = Warehouse::with([
        'branch.company',
    ])
        ->when(
            $request->filled('search'),
            function ($query) use ($request) {

                $search = $request->search;

                $query->where(function ($query) use ($search) {

                    $query->where(
                        'code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'name',
                        'like',
                        "%{$search}%"
                    );

                });

            }
        )
        ->latest()
        ->paginate(10)
        ->withQueryString();

    $warehouses->getCollection()->transform(
        function ($warehouse) {

            /*
            |--------------------------------------------------------------------------
            | Total Products
            |--------------------------------------------------------------------------
            */

            $warehouse->total_products =
                ProductStock::query()
                    ->where(
                        'product_stocks.warehouse_id',
                        $warehouse->id
                    )
                    ->whereNull(
                        'product_stocks.reseller_id'
                    )
                    ->join(
                        'product_variants',
                        'product_variants.id',
                        '=',
                        'product_stocks.product_variant_id'
                    )
                    ->distinct()
                    ->count(
                        'product_variants.product_id'
                    );


            /*
            |--------------------------------------------------------------------------
            | Current Stock
            |--------------------------------------------------------------------------
            |
            | Hanya stock milik warehouse Nuvora.
            | Stock reseller/consignment tidak ikut.
            |
            */

            $warehouse->current_stock =
                ProductStock::query()
                    ->where(
                        'warehouse_id',
                        $warehouse->id
                    )
                    ->whereNull(
                        'reseller_id'
                    )
                    ->sum('on_hand_qty');


            /*
            |--------------------------------------------------------------------------
            | Last Movement
            |--------------------------------------------------------------------------
            */

            $warehouse->last_movement =
                InventoryMovement::query()
                    ->where(
                        'warehouse_id',
                        $warehouse->id
                    )
                    ->max('transaction_date');


            return $warehouse;
        }
    );

    return Inertia::render(
        'MasterData/Warehouses/Index',
        [
            'warehouses' => $warehouses,

            'filters' => [
                'search' => $request->search,
            ],
        ]
    );
}

    /**
     * Show the form for creating a new warehouse.
     */
    public function create()
    {
        return Inertia::render(
            'MasterData/Warehouses/Create',
            [
                'branches' => Branch::query()
                    ->where('status', true)
                    ->get(),
            ]
        );
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(WarehouseRequest $request)
    {
        $code = $this->codeGeneratorService->next('warehouse');

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
            ->route('warehouses.index')
            ->with(
                'success',
                'Warehouse created successfully.'
            );
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse)
    {
        $warehouse->load([
            'branch.company',
        ]);

        return Inertia::render(
            'MasterData/Warehouses/Show',
            [
                'warehouse' => $warehouse,
            ]
        );
    }

    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit(Warehouse $warehouse)
    {
        return Inertia::render(
            'MasterData/Warehouses/Edit',
            [
                'warehouse' => $warehouse,

                'branches' => Branch::query()
                    ->where('status', true)
                    ->get(),
            ]
        );
    }

    /**
     * Update the specified warehouse.
     */
    public function update(
        WarehouseRequest $request,
        Warehouse $warehouse
    ) {
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
     * Remove the specified warehouse.
     */
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return redirect()
            ->route('warehouses.index')
            ->with(
                'success',
                'Warehouse deleted successfully.'
            );
    }
}