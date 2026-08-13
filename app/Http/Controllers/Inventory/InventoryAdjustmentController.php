<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Inventory\InventoryAdjustmentHeader;
use App\Models\Inventory\ProductStock;
use App\Services\Core\CodeGeneratorService;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Unit;
use App\Models\Product\ProductVariant;
use App\Services\Inventory\InventoryService;
use App\Http\Requests\Inventory\InventoryAdjustment\CancelInventoryAdjustmentRequest;
use App\Http\Requests\Inventory\InventoryAdjustment\StoreInventoryAdjustmentRequest;
use App\Http\Requests\Inventory\InventoryAdjustment\UpdateInventoryAdjustmentRequest;
class InventoryAdjustmentController extends Controller
{
    public function __construct(
    protected InventoryService $inventoryService,
    protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

public function index(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | Query
    |--------------------------------------------------------------------------
    */

    $query = InventoryAdjustmentHeader::query()
            ->with([
                 'branch',
                'warehouse',
                'creator',
                'details.variant.product',
                'details.unit',
            ])
            ->withSum(
                'details',
                'total_cost'
            )

        ->when(
            $request->filled('search'),
            function ($query) use ($request) {

                $search = $request->search;

                $query->where(function ($query) use ($search) {

                    $query->where(
                        'number',
                        'like',
                        "%{$search}%"
                    )

                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    )

                    ->orWhereHas(
                        'branch',
                        function ($branch) use ($search) {

                            $branch->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );

                        }
                    )

                    ->orWhereHas(
                        'warehouse',
                        function ($warehouse) use ($search) {

                            $warehouse->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );

                        }
                    );

                });

            }
        )

        ->when(
            $request->filled('branch_id'),
            function ($query) use ($request) {

                $query->where(
                    'branch_id',
                    $request->branch_id
                );

            }
        )

        ->when(
            $request->filled('warehouse_id'),
            function ($query) use ($request) {

                $query->where(
                    'warehouse_id',
                    $request->warehouse_id
                );

            }
        )

        ->when(
            $request->filled('status'),
            function ($query) use ($request) {

                $query->where(
                    'status',
                    $request->status
                );

            }
        );

                /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */

            $sortBy = $request->input(
                'sort_by',
                'id'
            );

            $sortDirection = $request->input(
                'sort_direction',
                'desc'
            );

            $allowedSorts = [

                'id',
                'number',
                'transaction_date',
                'total_cost',
                'status',

            ];

            if (! in_array(
                $sortBy,
                $allowedSorts,
                true
            )) {

                $sortBy = 'id';

            }

            if (! in_array(
                $sortDirection,
                [
                    'asc',
                    'desc',
                ],
                true
            )) {

                $sortDirection = 'desc';

            }

            if ($sortBy === 'total_cost') {

                $query->orderBy(
                    'details_sum_total_cost',
                    $sortDirection
                );

            } else {

                $query->orderBy(
                    $sortBy,
                    $sortDirection
                );

            }
    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $adjustments = $query
    ->paginate(
        $request->integer(
            'per_page',
            10
        )
    )
    ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | Transform
    |--------------------------------------------------------------------------
    */

    $adjustments
    ->getCollection()
    ->transform(
        function ($adjustment) {

            $adjustment->details_count =
                $adjustment->details->count();

            $adjustment->total_cost =
                $adjustment->details_sum_total_cost ?? 0;

            return $adjustment;

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

            $statisticsQuery = clone $query;

        $statistics = [

            'total' =>
                (clone $statisticsQuery)->count(),

            'draft' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Draft'
                    )
                    ->count(),

            'rejected' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Rejected'
                    )
                    ->count(),

            'posted' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Posted'
                    )
                    ->count(),

        ];

    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    return Inertia::render(
        'Inventory/Adjustment/Index',
        array_merge(
            [
                'title' =>
                    'Inventory Adjustment',

                'adjustments' =>
                    $adjustments,

                'statistics' =>
                    $statistics,

                'previewNumber' =>
                    $this->codeGeneratorService
                        ->preview(
                            'stock_adjustment'
                        ),

                'filters' =>
                    $request->only([
                        'search',
                        'branch_id',
                        'warehouse_id',
                        'status',
                        'per_page',
                        'sort_by',
                        'sort_direction',
                    ]),
            ],

            $this->formData()
        )
    );
}
    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    protected function formData(): array
{
    return [

        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */
        'branches' => Branch::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ])
            ->map(fn ($branch) => [
                'id' => $branch->id,
                'label' => $branch->name,
            ])
            ->values(),


        /*
        |--------------------------------------------------------------------------
        | Warehouses
        |--------------------------------------------------------------------------
        */

        'warehouses' => Warehouse::query()
            ->orderBy('name')
            ->get([
                'id',
                'branch_id',
                'name',
            ])
            ->map(fn ($warehouse) => [
                'id' => $warehouse->id,
                'branch_id' => $warehouse->branch_id,
                'label' => $warehouse->name,
            ])
            ->values(),

        /*
        |--------------------------------------------------------------------------
        | Product Variants
        |--------------------------------------------------------------------------
        */

        'variants' => ProductVariant::query()
            ->active()
            ->whereHas(
                'units',
                function ($query) {
                    $query->active();
                }
            )
            ->with([
                'product',

                'units' => function ($query) {
                    $query
                        ->active()
                        ->with('unit')
                        ->orderBy('sort_order');
                },
            ])
            ->orderBy('sku')
            ->get([
                'id',
                'product_id',
                'sku',
                'name',
            ])
            ->map(fn ($variant) => [

                'id' => $variant->id,

                'label' => implode(' - ', array_filter([
                    $variant->sku,
                    $variant->product?->name,
                    $variant->name,
                ])),

                'units' => $variant->units
                    ->map(fn ($variantUnit) => [

                        'id' => $variantUnit->unit_id,

                        'label' => $variantUnit->unit?->name,

                        'conversion_factor' =>
                            $variantUnit->conversion_factor,

                        'is_base' =>
                            $variantUnit->is_base,

                        'is_default' =>
                            $variantUnit->is_default,

                    ])
                    ->values(),

            ])
            ->values(),


        /*
        |--------------------------------------------------------------------------
        | Units
        |--------------------------------------------------------------------------
        */

        'units' => Unit::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ])
            ->map(fn ($unit) => [
                'id' => $unit->id,
                'label' => $unit->name,
            ])
            ->values(),


        /*
        |--------------------------------------------------------------------------
        | Status Options
        |--------------------------------------------------------------------------
        */

        'statusOptions' => [

            [
                'label' => 'Draft',
                'value' => 'Draft',
            ],

            [
                'label' => 'Rejected',
                'value' => 'Rejected',
            ],

            [
                'label' => 'Posted',
                'value' => 'Posted',
            ],

        ],

    ];
}

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render(
            'Inventory/Adjustment/Form',
            $this->formData()
        );
    }
    public function stock(Request $request)
        {
            $validated = $request->validate([
                'company_id' => ['required', 'integer'],
                'branch_id' => ['required', 'integer'],
                'warehouse_id' => ['required', 'integer'],
                'product_variant_id' => ['required', 'integer'],
                'unit_id' => ['required', 'integer'],
            ]);

            $stock = ProductStock::query()
                ->where('company_id', $validated['company_id'])
                ->where('branch_id', $validated['branch_id'])
                ->where('warehouse_id', $validated['warehouse_id'])
                ->where('product_variant_id', $validated['product_variant_id'])
                ->where('unit_id', $validated['unit_id'])
                ->first();

            return response()->json([
                'system_qty' => $stock?->on_hand_qty ?? 0,
                'unit_cost' => $stock?->average_cost ?? 0,
            ]);
        }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

public function store(
    StoreInventoryAdjustmentRequest $request
) {

    $data = $request->validated();

    $branch = Branch::findOrFail(
        $data['branch_id']
    );

    $data['company_id'] =
        $branch->company_id;

    $this->inventoryService
        ->inventoryAdjustment(
            $data
        );

    return redirect()
        ->back()
        ->with(
            'success',
            'Inventory adjustment created successfully.'
        );
}

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

        public function show(
            InventoryAdjustmentHeader $inventoryAdjustment
        ) {

            $inventoryAdjustment->load([
                'branch',
                'warehouse',
                'creator',
                'updater',
                'poster',
                'rejector',
                'deleter',
                'details.variant.product',
                'details.unit',
                'movements',
                'activities',
            ]);

            return Inertia::render(
                'Inventory/Adjustment/Show',
                [
                    'adjustment' =>
                        $inventoryAdjustment,
                ]
            );
        }
    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        InventoryAdjustmentHeader $inventoryAdjustment
    ) {

        $this->inventoryService
            ->postInventoryAdjustment(
                $inventoryAdjustment
            );


        return redirect()
            ->route(
                'inventory-adjustments.index'
            )
            ->with(
                'success',
                'Inventory adjustment posted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
    CancelInventoryAdjustmentRequest $request,
    InventoryAdjustmentHeader $inventoryAdjustment
        ) {

            $this->inventoryService
                ->cancelInventoryAdjustment(
                    $inventoryAdjustment,
                    $request->validated()['reason']
                );


            return redirect()
                ->route(
                    'inventory-adjustments.index'
                )
                ->with(
                    'success',
                    'Inventory adjustment rejected successfully.'
                );
        }

    /*
    |--------------------------------------------------------------------------
    | Warehouse Stocks
    |--------------------------------------------------------------------------
    */

    public function getWarehouseStocks(
        Warehouse $warehouse
    ) {

        $stocks = ProductStock::query()
            ->with([
                'variant.product',
                'unit',
            ])
            ->where(
                'warehouse_id',
                $warehouse->id
            )
            ->orderBy(
                'product_variant_id'
            )
            ->get();


        return response()->json([
            'data' => $stocks,
        ]);
    }
    public function resubmit(
    InventoryAdjustmentHeader $inventoryAdjustment
        ) {

            $this->inventoryService
                ->resubmitInventoryAdjustment(
                    $inventoryAdjustment
                );
            return redirect()
                ->route(
                    'inventory-adjustments.index'
                )
                ->with(
                    'success',
                    'Inventory adjustment resubmitted successfully.'
                );
        }
 public function data(
    InventoryAdjustmentHeader $inventoryAdjustment
) {

    $inventoryAdjustment->load([

        'branch',

        'warehouse',

        'creator',

        'updater',

        'poster',

        'rejector',

        'deleter',

        'details.variant.product',

        'details.unit',

        'movements.productVariant.product',

        'movements.unit',

        'activities.performer',

    ]);

    return response()->json([
        'data' => $inventoryAdjustment,
    ]);
}   
public function update(
    UpdateInventoryAdjustmentRequest $request,
    InventoryAdjustmentHeader $inventoryAdjustment
) {

    $data = $request->validated();

    $this->inventoryService->updateInventoryAdjustment(
        $inventoryAdjustment,
        $data
    );

    return redirect()
        ->back()
        ->with(
            'success',
            'Inventory adjustment updated successfully.'
        );
}
public function duplicate(
    InventoryAdjustmentHeader $inventoryAdjustment
) {
    $this->inventoryService
        ->duplicateInventoryAdjustment(
            $inventoryAdjustment
        );

    return redirect()
        ->back()
        ->with(
            'success',
            'Inventory adjustment duplicated successfully.'
        );
}
public function destroy(
    InventoryAdjustmentHeader $inventoryAdjustment
) {
    $this->inventoryService
        ->deleteInventoryAdjustments([
            $inventoryAdjustment->id,
        ]);

    return redirect()
        ->back()
        ->with(
            'success',
            'Inventory adjustment deleted successfully.'
        );
}
}