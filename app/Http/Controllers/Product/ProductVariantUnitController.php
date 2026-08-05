<?php

namespace App\Http\Controllers\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\MasterData\Unit;
use App\Models\Product\ProductVariant;
use App\Models\Product\ProductVariantUnit;
use App\Models\Product\Product;
use App\Services\Product\ProductVariantUnitService;

use App\Http\Requests\Product\ProductVariantUnit\StoreProductVariantUnitRequest;
use App\Http\Requests\Product\ProductVariantUnit\UpdateProductVariantUnitRequest;
use App\Http\Requests\Product\ProductVariantUnit\bulkActivateProductVariantUnitRequest;
use App\Http\Requests\Product\ProductVariantUnit\bulkDeactivateProductVariantUnitRequest;
use App\Http\Requests\Product\ProductVariantUnit\bulkDeleteProductVariantUnitRequest;
class ProductVariantUnitController extends Controller
{
    public function __construct(
        protected ProductVariantUnitService $service
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
    Request $request
        )
        {
            $query = ProductVariantUnit::query()

                ->with([
                    'variant.product',
                    'unit',
                ])

                ->when(
                    $request->filled('search'),
                    function ($query) use ($request) {

                        $query->whereHas(
                            'variant',
                            function ($variant) use ($request) {

                                $variant->where(
                                    'name',
                                    'like',
                                    "%{$request->search}%"
                                );

                            }
                        );

                    }
                )

                ->when(
                    $request->filled('product_id'),
                    function ($query) use ($request) {

                        $query->whereHas(
                            'variant',
                            function ($variant) use ($request) {

                                $variant->where(
                                    'product_id',
                                    $request->product_id
                                );

                            }
                        );

                    }
                )

                ->when(
                    $request->filled('is_active'),
                    function ($query) use ($request) {

                        $query->where(
                            'is_active',
                            filter_var(
                                $request->is_active,
                                FILTER_VALIDATE_BOOLEAN
                            )
                        );

                    }
                );

            $units = $query

                ->latest()

                ->paginate(10)

                ->withQueryString();

            $statisticsQuery = clone $query;

            $statistics = [

                'total' => (clone $statisticsQuery)->count(),

                'active' => (clone $statisticsQuery)
                    ->where('is_active', true)
                    ->count(),

                'inactive' => (clone $statisticsQuery)
                    ->where('is_active', false)
                    ->count(),

            ];
           // dd($units->first());
            return Inertia::render(

            'MasterData/ProductVariantUnit/Index',

            array_merge(

                [

                    'title' => 'Product Variant Unit',

                    'statistics' => $statistics,

                    'units' => $units,

                    'filters' => $request->only([

                        'search',

                        'product_id',

                        'is_active',

                    ]),

                ],

                $this->formData()

            )

        );
        }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        //
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
    StoreProductVariantUnitRequest $request
)
{
    $this->service->store(
        $request->validated()
    );

    return back()->with(
        'success',
        'Product Variant Unit created successfully.'
    );
}
    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        ProductVariantUnit $productVariantUnit
    )
    {
        //
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        ProductVariantUnit $productVariantUnit
    )
    {
        //
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
 
    UpdateProductVariantUnitRequest $request,
    ProductVariantUnit $productVariantUnit
)
{
    try {

        $this->service->update(
            $productVariantUnit,
            $request->validated()
        );

        return back()->with(

            'success',

            'Product Variant Unit updated successfully.'

        );

    } catch (\Throwable $e) {

        return back()->with(

            'error',

            $e->getMessage()

        );

    }
}

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ProductVariantUnit $productVariantUnit
    )
    {
        try {

            $this->service->destroy(
                $productVariantUnit
            );

            return redirect()

                ->route(
                    'product-variant-units.index'
                )

                ->with(

                    'success',

                    'Product Variant Unit berhasil dihapus.'

                );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(

                'error',

                $e->getMessage()

            );

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */

    public function bulkDelete(
    BulkDeleteProductVariantUnitRequest $request
)
{
    $data = $request->validated();

    try {

        $this->service->bulkDelete(
            $data['ids']
        );

        return redirect()
            ->route(
                'product-variant-units.index'
            )
            ->with(
                'success',
                'Selected Product Variant Units deleted successfully.'
            );

    } catch (\Throwable $e) {

        report($e);

        return back()
            ->with(
                'error',
                $e->getMessage()
            );

    }
}

    /*
    |--------------------------------------------------------------------------
    | Bulk Activate
    |--------------------------------------------------------------------------
    */

    public function bulkActivate(
    BulkActivateProductVariantUnitRequest $request
)
{
    $data = $request->validated();

    try {

        $this->service->bulkActivate(
            $data['ids']
        );

        return redirect()
            ->route(
                'product-variant-units.index'
            )
            ->with(
                'success',
                'Selected Product Variant Units activated successfully.'
            );

    } catch (\Throwable $e) {

        report($e);

        return back()
            ->with(
                'error',
                $e->getMessage()
            );

    }
}

    /*
    |--------------------------------------------------------------------------
    | Bulk Deactivate
    |--------------------------------------------------------------------------
    */

    public function bulkDeactivate(
    BulkDeactivateProductVariantUnitRequest $request
)
{
    $data = $request->validated();

    try {

        $this->service->bulkDeactivate(
            $data['ids']
        );

        return redirect()
            ->route(
                'product-variant-units.index'
            )
            ->with(
                'success',
                'Selected Product Variant Units deactivated successfully.'
            );

    } catch (\Throwable $e) {

        report($e);

        return back()
            ->with(
                'error',
                $e->getMessage()
            );

    }
}
/*
|--------------------------------------------------------------------------
| Form Data
|--------------------------------------------------------------------------
*/
protected function formData(): array
{
    return [

        'variants' => ProductVariant::query()

            ->with('product:id,code,name')

            ->whereHas('product')

            ->orderBy('name')

            ->get()

            ->map(function ($variant) {

                return [

                    'id' => $variant->id,

                    'name' => $variant->name,

                    'label' => "{$variant->product->code} • {$variant->product->name} • {$variant->name}",

                ];

            }),

        'products' => Product::query()

            ->select(
                'id',
                'code',
                'name'
            )

            ->orderBy('name')

            ->get()

            ->map(function ($product) {

                return [

                    'id' => $product->id,

                    'name' => $product->name,

                    'label' => "{$product->code} • {$product->name}",

                ];

            }),

        'unitOptions' => Unit::query()

            ->select(
                'id',
                'name'
            )

            ->active()

            ->orderBy('name')

            ->get()

            ->map(function ($unit) {

                return [

                    'id' => $unit->id,

                    'name' => $unit->name,

                    'label' => $unit->name,

                ];

            }),

        'statusOptions' => [

            [

                'label' => 'Active',

                'value' => true,

            ],

            [

                'label' => 'Inactive',

                'value' => false,

            ],

        ],

    ];
}
public function availableUnits(
    Request $request,
    ProductVariant $productVariant
)
{
    return response()->json(

        $this->service->getAvailableUnits(

            $productVariant->id,

            $request->integer('current_unit')

        )

    );
}

}