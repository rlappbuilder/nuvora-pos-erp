<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Unit;
use App\Models\MasterData\Warehouse;
use App\Models\Product\ProductVariant;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\StockOpnameHeader;

use App\Services\Inventory\InventoryService;
use App\Services\Core\CodeGeneratorService;
use App\Http\Requests\Inventory\StockOpname\UpdateStockOpnameRequest;
use App\Http\Requests\Inventory\StockOpname\StoreStockOpnameRequest;
use App\Http\Requests\Inventory\StockOpname\CancelStockOpnameRequest;

class StockOpnameController extends Controller
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
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query =
            StockOpnameHeader::query()
                ->with([
                    'branch',
                    'warehouse',
                    'creator',
                    'details.variant.product',
                    'details.unit',
                ])
                ->withCount(
                    'details'
                )
                ->withSum(
                    'details',
                    'difference_cost'
                );
        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('search'),
            function ($query) use ($request) {

                $search =
                    $request->search;

                $query->where(function ($query) use ($search) {

                    $query
                        ->where(
                            'number',
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
        );


        /*
        |--------------------------------------------------------------------------
        | Branch Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('branch_id'),
            function ($query) use ($request) {

                $query->where(
                    'branch_id',
                    $request->branch_id
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Warehouse Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('warehouse_id'),
            function ($query) use ($request) {

                $query->where(
                    'warehouse_id',
                    $request->warehouse_id
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $query->when(
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
        | Date Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('date_from'),
            function ($query) use ($request) {

                $query->whereDate(
                    'transaction_date',
                    '>=',
                    $request->date_from
                );

            }
        );


        $query->when(
            $request->filled('date_to'),
            function ($query) use ($request) {

                $query->whereDate(
                    'transaction_date',
                    '<=',
                    $request->date_to
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $sortBy =
            $request->input(
                'sort_by',
                'id'
            );

        $sortDirection =
            $request->input(
                'sort_direction',
                'desc'
            );


        $allowedSorts = [

            'id',

            'number',

            'transaction_date',

            'status',

        ];


        if (
            ! in_array(
                $sortBy,
                $allowedSorts,
                true
            )
        ) {

            $sortBy = 'id';

        }


        if (
            ! in_array(
                $sortDirection,
                [
                    'asc',
                    'desc',
                ],
                true
            )
        ) {

            $sortDirection = 'desc';

        }


        $query->orderBy(
            $sortBy,
            $sortDirection
        );


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statisticsQuery =
            clone $query;


        $statistics = [

            'total' =>
                (clone $statisticsQuery)
                    ->count(),

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
        | Pagination
        |--------------------------------------------------------------------------
        */

        $opnames =
            $query
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
        $opnames
            ->getCollection()
            ->transform(
                function ($opname) {

                    $opname->details_count =
                        $opname->details_count
                        ?? 0;

                    $opname->difference_cost =
                        $opname->details_sum_difference_cost
                        ?? 0;

                    return $opname;

                }
            );
        /*
        |--------------------------------------------------------------------------
        | Master Data
        |--------------------------------------------------------------------------
        */

        $branches =
            Branch::query()
                ->orderBy('name')
                ->get()
                ->map(function ($branch) {

                    return [

                        'id' =>
                            $branch->id,

                        'label' =>
                            $branch->name,

                    ];

                })
                ->values();


        $warehouses =
            Warehouse::query()
                ->orderBy('name')
                ->get()
                ->map(function ($warehouse) {

                    return [

                        'id' =>
                            $warehouse->id,

                        'label' =>
                            $warehouse->name,

                        'branch_id' =>
                            $warehouse->branch_id,

                    ];

                })
                ->values();


        $variants =
            ProductVariant::query()
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

                    'id' =>
                        $variant->id,

                    'label' =>
                        implode(
                            ' - ',
                            array_filter([
                                $variant->product?->name,
                                $variant->name,
                            ])
                        ),

                    'units' =>
                        $variant->units
                            ->map(fn ($variantUnit) => [

                                'id' =>
                                    $variantUnit->unit_id,

                                'label' =>
                                    $variantUnit->unit?->name,

                                'conversion_factor' =>
                                    $variantUnit->conversion_factor,

                                'is_base' =>
                                    $variantUnit->is_base,

                                'is_default' =>
                                    $variantUnit->is_default,

                            ])
                            ->values(),

                ])
                ->values();


        $units =
            Unit::query()
                ->orderBy('name')
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Preview Number
        |--------------------------------------------------------------------------
        */

        $previewNumber =
            $this->codeGeneratorService
                ->preview(
                    'stock_opname'
                );


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Inventory/StockOpname/Index',
            [

                'title' =>
                    'Stock Opname',

                'opnames' =>
                    $opnames,

                'statistics' =>
                    $statistics,

                'branches' =>
                    $branches,

                'warehouses' =>
                    $warehouses,

                'variants' =>
                    $variants,

                'units' =>
                    $units,

                'previewNumber' =>
                    $previewNumber,

                'filters' =>
                    $request->only([
                        'search',
                        'branch_id',
                        'warehouse_id',
                        'status',
                        'date_from',
                        'date_to',
                        'per_page',
                        'sort_by',
                        'sort_direction',
                    ]),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render(
            'Inventory/StockOpname/Create',
            [

                'title' =>
                    'Create Stock Opname',

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreStockOpnameRequest $request
    ) {

        $data =
            $request->validated();


        $branch =
            Branch::findOrFail(
                $data['branch_id']
            );


        $data['company_id'] =
            $branch->company_id;


        $this->inventoryService
            ->createStockOpname(
                $data
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Stock opname created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        StockOpnameHeader $stockOpname
    ) {

        $stockOpname->load([

            'company',

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
            'Inventory/StockOpname/Show',
            [

                'opname' =>
                    $stockOpname,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
    UpdateStockOpnameRequest $request,
    StockOpnameHeader $stockOpname
        ) {

            abort_if(
                $stockOpname->status === 'Posted',
                422,
                'Posted stock opname cannot be updated.'
            );


            $data =
                $request->validated();


            $branch =
                Branch::findOrFail(
                    $data['branch_id']
                );


            $data['company_id'] =
                $branch->company_id;


            $this->inventoryService
                ->updateStockOpname(
                    $stockOpname,
                    $data
                );


            return redirect()
                ->back()
                ->with(
                    'success',
                    'Stock opname updated successfully.'
                );
        }


    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        StockOpnameHeader $stockOpname
    ) {

        $this->inventoryService
            ->postStockOpname(
                $stockOpname
            );


        return redirect()
            ->route(
                'stock-opnames.index',
                $stockOpname
            )
            ->with(
                'success',
                'Stock opname posted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        CancelStockOpnameRequest $request,
        StockOpnameHeader $stockOpname
    ) {

        $data =
            $request->validated();


        $branch =
            Branch::findOrFail(
                $stockOpname->branch_id
            );


        $data['company_id'] =
            $branch->company_id;


        /*
        |--------------------------------------------------------------------------
        | Ensure Company ID
        |--------------------------------------------------------------------------
        */

        if (
            $stockOpname->company_id === null
        ) {

            $stockOpname->company_id =
                $data['company_id'];

            $stockOpname->save();

        }


        $this->inventoryService
            ->cancelStockOpname(
                $stockOpname,
                $data['reason']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Stock opname rejected successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Warehouse Stocks
    |--------------------------------------------------------------------------
    */

    public function warehouseStocks(
        Warehouse $warehouse
    ) {

        $stocks =
            ProductStock::query()
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
            'data' =>
                $stocks,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    public function data(
        StockOpnameHeader $stockOpname
    ) {

        $stockOpname->load([

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
            'data' =>
                $stockOpname,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate
    |--------------------------------------------------------------------------
    */

    public function duplicate(
        StockOpnameHeader $stockOpname
    ) {

        $this->inventoryService
            ->duplicateStockOpname(
                $stockOpname
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Stock opname duplicated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        StockOpnameHeader $stockOpname
    ) {

        $this->inventoryService
            ->deleteStockOpname(
                $stockOpname
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Stock opname deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */

    public function bulkDelete(
        Request $request
    ) {

        $validated =
            $request->validate([

                'ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'ids.*' => [
                    'required',
                    'integer',
                    'exists:stock_opname_headers,id',
                ],

            ]);


        $this->inventoryService
            ->bulkDeleteStockOpnames(
                $validated['ids']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Stock opnames deleted successfully.'
            );
    }

}