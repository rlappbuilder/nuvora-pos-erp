<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Unit;
use App\Models\MasterData\Warehouse;
use App\Models\Product\ProductVariant;
use App\Services\Inventory\InventoryService;
use App\Services\Core\CodeGeneratorService;
use App\Models\Inventory\StockIssueHeader;
use App\Http\Requests\Inventory\StockIssue\StoreStockIssueRequest;
use App\Http\Requests\Inventory\StockIssue\UpdateStockIssueRequest;
use App\Http\Requests\Inventory\StockIssue\CancelStockIssueRequest;

use App\Models\Inventory\ProductStock;


class StockIssueController extends Controller
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
            StockIssueHeader::query()
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
                    'total_cost'
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

                    ->orWhere(
                        'issue_type',
                        'like',
                        "%{$search}%"
                    )

                    ->orWhereHas(
                        'branch',
                        function ($branch) use (
                            $search
                        ) {

                            $branch->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );

                        }
                    )

                    ->orWhereHas(
                        'warehouse',
                        function ($warehouse) use (
                            $search
                        ) {

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
    | Issue Type
    |--------------------------------------------------------------------------
    */

    $query->when(
        $request->filled('issue_type'),
        function ($query) use ($request) {

            $query->where(
                'issue_type',
                $request->issue_type
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

        'issue_type',

        'status',

        'total_cost',

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


    if (
        $sortBy === 'total_cost'
    ) {

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
    | Statistics
    |--------------------------------------------------------------------------
    |
    | Clone BEFORE pagination.
    |
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

    $issues =
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
    $issues
        ->getCollection()
        ->transform(
            function ($issue) {

                $issue->details_count =
                    $issue->details_count
                    ?? 0;

                $issue->total_cost =
                    $issue->details_sum_total_cost
                    ?? 0;

                return $issue;

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
            ->with('product')
            ->orderBy('id')
            ->get();


    $units =
        Unit::query()
            ->orderBy('name')
            ->get();
//dd('Cdddd');

    /*
    |--------------------------------------------------------------------------
    | Preview Number
    |--------------------------------------------------------------------------
    */

    $previewNumber =
        $this->codeGeneratorService
            ->preview(
                'stock_issue'
            );


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    return Inertia::render(
    'Inventory/Issue/Index',
    [

        'title' =>
            'Stock Issue',

        'issues' =>
            $issues,

        'statistics' =>
            $statistics,

                'branches' =>
            \App\Models\MasterData\Branch::query()
                ->orderBy('name')
                ->get()
                ->map(fn ($branch) => [
                    'id' => $branch->id,
                    'label' => $branch->name,
                ])
                ->values(),

        'warehouses' =>
            Warehouse::query()
                ->orderBy('name')
                ->get()
                ->map(fn ($warehouse) => [
                    'id' => $warehouse->id,
                    'label' => $warehouse->name,
                    'branch_id' => $warehouse->branch_id,
                ])
                ->values(),

       'variants' =>
    \App\Models\Product\ProductVariant::query()
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
        ->values(),

        'issueTypeOptions' =>
         \App\Enums\Inventory\StockIssueType::options(),

        'previewNumber' =>
            $this->codeGeneratorService
                ->preview('stock_issue'),

       'filters' =>
            $request->only([
                'search',
                'branch_id',
                'warehouse_id',
                'issue_type',
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
                'Inventory/Issue/Create',
                [

                    'title' =>
                        'Create Stock Issue',

                ]
            );
        }
    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

       public function store(
            StoreStockIssueRequest $request
        ) {

            $data = $request->validated();

            $branch = Branch::findOrFail(
                $data['branch_id']
            );

            $data['company_id'] =
                $branch->company_id;

            $this->inventoryService
                ->createStockIssue(
                    $data
                );

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Stock issue created successfully.'
                );
        }
    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

   public function show(
    StockIssueHeader $stockIssue
) {

    $stockIssue->load([

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
        'Inventory/Issue/Show',
        [

            'issue' =>
                $stockIssue,

        ]
    );
}
    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
    UpdateStockIssueRequest $request,
    StockIssueHeader $stockIssue
        ) {

            abort_if(
                $stockIssue->status === 'Posted',
                422,
                'Posted stock issue cannot be updated.'
            );

            $data = $request->validated();

            $branch =
                \App\Models\MasterData\Branch::findOrFail(
                    $data['branch_id']
                );

            $data['company_id'] =
                $branch->company_id;

            $this->inventoryService
                ->updateStockIssue(
                    $stockIssue,
                    $data
                );

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Stock issue updated successfully.'
                );
        }
    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        StockIssueHeader $stockIssue
    ) {

        $this->inventoryService
            ->postStockIssue(
                $stockIssue
            );


        return redirect()
            ->route(
                'stock-issues.index',
                $stockIssue
            )
            ->with(
                'success',
                'Stock issue posted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
    CancelStockIssueRequest $request,
    StockIssueHeader $stockIssue
        ) {

            $data = $request->validated();

            $branch =
                \App\Models\MasterData\Branch::findOrFail(
                    $stockIssue->branch_id
                );

            $data['company_id'] =
                $branch->company_id;


            /*
            |--------------------------------------------------------------------------
            | Ensure Company ID
            |--------------------------------------------------------------------------
            */

            if (
                $stockIssue->company_id === null
            ) {

                $stockIssue->company_id =
                    $data['company_id'];

                $stockIssue->save();

            }


            $this->inventoryService
                ->cancelStockIssue(
                    $stockIssue,
                    $data['reason']
                );


            return redirect()
                ->back()
                ->with(
                    'success',
                    'Stock issue rejected successfully.'
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
    /*
|--------------------------------------------------------------------------
| Duplicate
|--------------------------------------------------------------------------
*/

public function data(
    StockIssueHeader $stockIssue
) {

   $stockIssue->load([

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
        'data' => $stockIssue,
    ]);
}
public function duplicate(
    StockIssueHeader $stockIssue
) {

    $this->inventoryService
        ->duplicateStockIssue(
            $stockIssue
        );

    return redirect()
        ->back()
        ->with(
            'success',
            'Stock issue duplicated successfully.'
        );
}
/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

public function destroy(
    StockIssueHeader $stockIssue
) {

    $this->inventoryService
        ->deleteStockIssues([
            $stockIssue->id,
        ]);

    return redirect()
        ->back()
        ->with(
            'success',
            'Stock issue deleted successfully.'
        );
}
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
                'exists:stock_issue_headers,id',
            ],

        ]);


    $this->inventoryService
        ->bulkDeleteStockIssues(
            $validated['ids']
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Stock issues deleted successfully.'
        );
}
}