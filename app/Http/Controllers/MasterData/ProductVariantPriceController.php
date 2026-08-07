<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Unit;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Product\ProductVariantUnit;
use App\Models\MasterData\PriceType;
use App\Models\MasterData\ProductVariantPrice;

use App\Services\MasterData\ProductVariantPriceService;

use App\Http\Requests\MasterData\StoreProductVariantPriceRequest;
use App\Http\Requests\MasterData\UpdateProductVariantPriceRequest;
use App\Http\Requests\MasterData\BulkDeleteProductVariantPriceRequest;
use App\Http\Requests\MasterData\BulkActivateProductVariantPriceRequest;
use App\Http\Requests\MasterData\BulkDeactivateProductVariantPriceRequest;

class ProductVariantPriceController extends Controller
{
    public function __construct(
        protected ProductVariantPriceService $service
    ) {}

    public function index(
    Request $request
)
{
    $query = ProductVariantPrice::query()

        ->with([

            'branch',

            'variant.product',

            'unit',

            'priceType',

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

            $request->filled('branch_id'),

            function ($query) use ($request) {

                $query->where(

                    'branch_id',

                    $request->branch_id

                );

            }

        )

        ->when(

            $request->filled('price_type_id'),

            function ($query) use ($request) {

                $query->where(

                    'price_type_id',

                    $request->price_type_id

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

    $prices = $query

        ->latest()

        ->paginate(

            $request->integer(

                'per_page',

                20

            )

        )

        ->withQueryString();

    $statisticsQuery = clone $query;

    $statistics = [

        'total' => (clone $statisticsQuery)

            ->count(),

        'active' => (clone $statisticsQuery)

            ->where(

                'is_active',

                true

            )

            ->count(),

        'inactive' => (clone $statisticsQuery)

            ->where(

                'is_active',

                false

            )

            ->count(),

    ];

    return Inertia::render(

        'MasterData/ProductVariantPrice/Index',

        array_merge(

            [

                'title' => 'Product Variant Price',

                'prices' => $prices,

                'statistics' => $statistics,

                'filters' => $request->only([

                    'search',

                    'product_id',

                    'branch_id',

                    'price_type_id',

                    'is_active',

                    'per_page',

                ]),

            ],

            $this->formData()

        )

    );
}

    protected function formData(): array
{
  
    return [

        'branches' => Branch::query()

            ->active()

            ->orderBy('name')

            ->get()

            ->map(function ($branch) {

                return [

                    'id' => $branch->id,

                    'name' => $branch->name,

                    'label' => $branch->code
                        . ' • '
                        . $branch->name,

                ];

            }),

         'products' => Product::query()

            ->active()

            ->orderBy('name')

            ->get()

            ->map(function ($product) {

                return [

                    'id' => $product->id,

                    'name' => $product->name,

                    'label' => $product->code
                        . ' • '
                        . $product->name,

                ];

            }),

        'variants' => ProductVariant::query()

            ->with([
                'product:id,code,name',
                'units.unit',
            ])

            ->active()

            ->orderBy('name')

            ->get()

            ->map(function ($variant) {

                return [

                'id' => $variant->id,

                'product_id' => $variant->product_id,

                'name' => $variant->name,

                'label' =>

                    "{$variant->product->code} • "

                    ."{$variant->product->name} • "

                    ."{$variant->name}",

                'units' => $variant->units

                    ->where('is_active', true)

                    ->map(function ($unit) {

                        return [

                            'id' => $unit->unit_id,

                            'name' => $unit->unit->name,

                            'label' => $unit->unit->name,

                        ];

                    })

                    ->values(),

            ];

            }),

            'unitOptions' => Unit::query()
            ->select('id', 'name')
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

        'priceTypes' => PriceType::query()

            ->active()

            ->orderBy('sort_order')

            ->orderBy('name')

            ->get()

            ->map(function ($type) {

                return [

                    'id' => $type->id,

                    'name' => $type->name,

                    'label' => $type->name,

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
public function latestPrice(Request $request)
{
    $price = ProductVariantPrice::query()

        ->where(
            'branch_id',
            $request->branch_id
        )

        ->where(
            'product_variant_id',
            $request->product_variant_id
        )

        ->where(
            'unit_id',
            $request->unit_id
        )

        ->latest('effective_from')

        ->first();

    return response()->json([

        'last_purchase_price' => $price?->last_purchase_price ?? 0,

        'selling_price' => $price?->selling_price ?? 0,

    ]);
}
public function checkPrice(
    Request $request
)
{
   
    $price = ProductVariantPrice::query()

        ->where(
            'branch_id',
            $request->branch_id
        )

        ->where(
            'product_variant_id',
            $request->product_variant_id
        )

        ->where(
            'unit_id',
            $request->unit_id
        )

        ->where(
            'price_type_id',
            $request->price_type_id
        )

        ->latest('effective_from')

        ->first();

    return response()->json([

    'exists' => (bool) $price,

    'data' => $price,

]);
}
    public function create()
    {
        //
    }

    public function store(
    StoreProductVariantPriceRequest $request
)
{
    try {

        $this->service->store([

            ...$request->validated(),

            'created_by' => auth()->id(),

        ]);

        return redirect()

            ->route(
                'product-variant-prices.index'
            )

            ->with(

                'success',

                'Product Variant Price created successfully.'

            );

    } catch (\Throwable $e) {

        return back()

            ->withInput()

            ->with(

                'error',

                $e->getMessage()

            );

    }
}

    public function show(
        ProductVariantPrice $productVariantPrice
    ) {
        //
    }

    public function edit(
    ProductVariantPrice $productVariantPrice
)
{
    $productVariantPrice->load([

        'variant.product:id,code,name',

        'unit:id,name',

        'priceType:id,name',

        'branch:id,name',

    ]);

    return response()->json([

        'id' => $productVariantPrice->id,

        'branch_id' => $productVariantPrice->branch_id,

        'product_variant_id' => $productVariantPrice->product_variant_id,

        'unit_id' => $productVariantPrice->unit_id,

        'price_type_id' => $productVariantPrice->price_type_id,

        'last_purchase_price' => $productVariantPrice->last_purchase_price,

        'selling_price' => $productVariantPrice->selling_price,

        'effective_from' => optional(
            $productVariantPrice->effective_from
        )->format('Y-m-d'),

        'effective_until' => optional(
            $productVariantPrice->effective_until
        )->format('Y-m-d'),

        'is_active' => $productVariantPrice->is_active,

        'description' => $productVariantPrice->description,

        'variant' => [

            'id' => $productVariantPrice->variant->id,

            'product_id' => $productVariantPrice->variant->product_id,

        ],

    ]);
}

   public function update(
    UpdateProductVariantPriceRequest $request,
    ProductVariantPrice $productVariantPrice
)
{
    try {

        $this->service->update(

            $productVariantPrice,

            [

                ...$request->validated(),

                'updated_by' => auth()->id(),

            ]

        );

        return redirect()

            ->route(
                'product-variant-prices.index'
            )

            ->with(

                'success',

                'Product Variant Price updated successfully.'

            );

    } catch (\Throwable $e) {

        return back()

            ->withInput()

            ->with(

                'error',

                $e->getMessage()

            );

    }
}

    public function destroy(
    ProductVariantPrice $productVariantPrice
)
{
    try {

       $this->service->delete(
            $productVariantPrice,
            auth()->id()
        );

        return redirect()

            ->route(
                'product-variant-prices.index'
            )

            ->with(

                'success',

                'Product Variant Price deleted successfully.'

            );

    } catch (\Throwable $e) {

        return back()

            ->with(

                'error',

                $e->getMessage()

            );

    }
}

    public function bulkDelete(
    BulkDeleteProductVariantPriceRequest $request
)
{
    try {

        $this->service->bulkDelete(
            $request->validated()['ids'],
            auth()->id()
        );

        return back()->with(

            'success',

            'Selected Product Variant Prices deleted successfully.'

        );

    } catch (\Throwable $e) {

        return back()->with(

            'error',

            $e->getMessage()

        );

    }
}

    public function bulkActivate(
    BulkActivateProductVariantPriceRequest $request
)
{
    try {

        $this->service->bulkActivate(

            $request->validated()['ids']

        );

        return back()->with(

            'success',

            'Selected Product Variant Prices activated successfully.'

        );

    } catch (\Throwable $e) {

        return back()->with(

            'error',

            $e->getMessage()

        );

    }
}

    public function bulkDeactivate(
    BulkDeactivateProductVariantPriceRequest $request
)
{
    try {

        $this->service->bulkDeactivate(

            $request->validated()['ids']

        );

        return back()->with(

            'success',

            'Selected Product Variant Prices deactivated successfully.'

        );

    } catch (\Throwable $e) {

        return back()->with(

            'error',

            $e->getMessage()

        );

    }
}
public function history(
    ProductVariantPrice $productVariantPrice
)
{
    return response()->json(

        $this->service->history(
            $productVariantPrice
        )

    );
}
}