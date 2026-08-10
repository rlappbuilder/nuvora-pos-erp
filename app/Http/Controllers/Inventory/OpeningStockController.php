<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\OpeningStock\StoreOpeningStockRequest;
use App\Http\Requests\Inventory\OpeningStock\UpdateOpeningStockRequest;
use App\Models\Inventory\OpeningStockHeader;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Unit;
use App\Models\Product\ProductVariant;
use App\Services\Core\CodeGeneratorService;
use App\Http\Requests\Inventory\OpeningStock\RejectOpeningStockRequest;
class OpeningStockController extends Controller
{
    public function __construct(
    protected InventoryService $inventoryService,
    protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    public function index(Request $request)
{
    $query = OpeningStockHeader::query()
        ->with([
                'branch',
                'warehouse',
                'details.variant.product',
                'details.unit',
            ])

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

    $openingStocks = $query
        ->latest()
        ->paginate(
            $request->integer(
                'per_page',
                10
            )
        )
        ->withQueryString();
        $openingStocks->getCollection()->transform(
            function ($openingStock) {

                $openingStock->total_items =
                    $openingStock->details->count();

                $openingStock->total_quantity =
                    $openingStock->details->sum('qty');

                $openingStock->total_cost =
                    $openingStock->details->sum('total_cost');

                return $openingStock;
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
                ->where('status', 'Draft')
                ->count(),

        'rejected' =>
            (clone $statisticsQuery)
                ->where('status', 'Rejected')
                ->count(),

        'posted' =>
            (clone $statisticsQuery)
                ->where('status', 'Posted')
                ->count(),

    ];

    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    return Inertia::render(
        'Inventory/OpeningStock/Index',
        array_merge(
            [
                'title' =>
                    'Opening Stock',

                'openingStocks' =>
                    $openingStocks,

                'statistics' =>
                    $statistics,

                'previewNumber' =>
                    $this->codeGeneratorService
                        ->preview('opening_stock'),

                'filters' =>
                    $request->only([
                        'search',
                        'branch_id',
                        'warehouse_id',
                        'status',
                        'per_page',
                    ]),
            ],

            $this->formData()
        )
    );
}
private function formData(): array
{
    return [

        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        'branches' => \App\Models\MasterData\Branch::query()
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

        'warehouses' => \App\Models\MasterData\Warehouse::query()
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

        'variants' => \App\Models\Product\ProductVariant::query()
           //'variants' => ProductVariant::query()
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

        'units' => \App\Models\MasterData\Unit::query()
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

    ];
}
    public function store(StoreOpeningStockRequest $request)
{
    $data = $request->validated();

    $branch = \App\Models\MasterData\Branch::findOrFail(
        $data['branch_id']
    );

    $data['company_id'] = $branch->company_id;

    $this->inventoryService->openingStock(
        $data
    );

    return redirect()
        ->back()
        ->with(
            'success',
            'Opening stock created successfully.'
        );
}
public function post(OpeningStockHeader $openingStock)
{
    $this->inventoryService->postOpeningStock(
        $openingStock
    );

    return redirect()
        ->back()
        ->with(
            'success',
            'Opening stock posted successfully.'
        );
}
    public function show( OpeningStockHeader $openingStock ) {
        $openingStock->load([
            'details',
        ]);

        return Inertia::render(
            'Inventory/OpeningStock/Show',
            [
                'openingStock' => $openingStock,
            ]
        );
    }

    public function update(UpdateOpeningStockRequest $request,OpeningStockHeader $openingStock) {
            abort_if(
                $openingStock->status === 'Posted',
                422,
                'Posted opening stock cannot be updated.'
            );

            $data = $request->validated();

            $branch = \App\Models\MasterData\Branch::findOrFail(
                $data['branch_id']
            );

            $data['company_id'] = $branch->company_id;

            $this->inventoryService->updateOpeningStock(
                $openingStock,
                $data
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Opening stock updated successfully.'
                );
        }

    public function destroy(OpeningStockHeader $openingStock) {
            $this->inventoryService->deleteOpeningStocks([
                $openingStock->id,
            ]);

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Opening stock deleted successfully.'
                );
        }

    public function duplicate(
    OpeningStockHeader $openingStock
) {
    $this->inventoryService
        ->duplicateOpeningStock($openingStock);

    return redirect()
        ->back()
        ->with(
            'success',
            'Opening stock duplicated successfully.'
        );
}

    public function bulkDelete(Request $request) {
            $validated = $request->validate([
                'ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'ids.*' => [
                    'required',
                    'integer',
                    'exists:opening_stock_headers,id',
                ],
            ]);

            $this->inventoryService->deleteOpeningStocks(
                $validated['ids']
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Opening stock deleted successfully.'
                );
        }
public function reject(
    RejectOpeningStockRequest $request,
    OpeningStockHeader $openingStock
) {
    $this->inventoryService->rejectOpeningStock(
        $openingStock,
        $request->validated()['reason']
    );

    return redirect()
        ->back()
        ->with(
            'success',
            'Opening stock rejected successfully.'
        );
}
public function showData(
    OpeningStockHeader $openingStock
) {
    $openingStock->load([

        /*
        |--------------------------------------------------------------------------
        | Master Data
        |--------------------------------------------------------------------------
        */

        'branch',

        'warehouse',


        /*
        |--------------------------------------------------------------------------
        | Audit Users
        |--------------------------------------------------------------------------
        */

        'creator',

        'updater',

        'poster',

        'rejector',

        'deleter',


        /*
        |--------------------------------------------------------------------------
        | Stock Details
        |--------------------------------------------------------------------------
        */

        'details.variant.product',

        'details.unit',


        /*
        |--------------------------------------------------------------------------
        | Workflow
        |--------------------------------------------------------------------------
        */

        'activities.performer',


        /*
        |--------------------------------------------------------------------------
        | Inventory Impact
        |--------------------------------------------------------------------------
        */

        'movements.productVariant.product',
        'movements.unit',

    ]);
  
    return response()->json([

        'data' => $openingStock,

    ]);
}
}