<?php

namespace App\Http\Controllers\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product\ProductVariant;
use App\Http\Requests\Product\ProductVariant\StoreProductVariantRequest;
use App\Http\Requests\Product\ProductVariant\UpdateProductVariantRequest;
use Illuminate\Support\Facades\DB;
use App\Data\Product\VariantGeneratorData;
use App\Services\Product\VariantGeneratorService;
use App\Models\Product\Product;
use App\Models\MasterData\ProductVariantPrice;
class ProductVariantController extends Controller
{
    protected VariantGeneratorService $variantGeneratorService;
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
  
    $query = ProductVariant::query()
        ->with([
            'product',
            'values.attribute',
            'values.attributeValue',
        ])
        ->search($request->search);
        $allowedSorts = [

            'id',

            'sku',

            'name',

            'is_active',

            'created_at',

        ];
         // Status Filter
    
        if ($request->filled('is_active')) {
        $query->where('is_active', $request->boolean('is_active'));
    }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

    $sortBy = $request->get('sort_by', 'id');

    if (! in_array($sortBy, $allowedSorts)) {
        $sortBy = 'id';
    }

    $sortDirection = strtolower($request->get('sort_direction', 'desc'));

    $sortDirection = $sortDirection === 'asc'
        ? 'asc'
        : 'desc';

    $query->orderBy($sortBy, $sortDirection);

    $perPage = $request->integer('per_page', 10);

    $perPage = in_array($perPage, [10, 20, 30, 40, 50])
        ? $perPage
        : 10;

    $variants = $query
    ->paginate($perPage)
    ->through(function ($product) {

        $product->created_at_human = $product->created_at?->diffForHumans();

        return $product;

    })
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

  'products' => Product::where('is_active', true)->count(),
];      
    return Inertia::render('MasterData/ProductVariant/Index', [

    'title' => 'Product Variant',

    'statistics' => $statistics,

    'variants' => $variants,

    'products' => Product::query()
        ->select(
            'id',
            'code',
            'name'
        )
        ->withCount('variants')
        ->where('is_active', true)
        ->orderBy('name')
        ->get()
       // dd($product);
        ->map(function ($product) {

            return [

                'id' => $product->id,

                'code' => $product->code,

                'name' => $product->name,

                'label' => "{$product->code} • {$product->name}",

                'variants_count' => $product->variants_count,

                'has_variants' => $product->variants_count > 0,

            ];

        }),

    'filters' => $request->only([
        'search',
        'product_id',
        'is_active',
        'sort_by',
        'sort_direction',
        'per_page',
    ]),

]);
}

    /**
     * Show the form for creating a new resource.
     */
        public function create()
        {
          return Inertia::render('MasterData/ProductVariant/Create', [
            'title' => 'Create Product Variant',
            ...$this->formData(),
        ]);
        }

    /**
     * Store a newly created resource.
     */
  public function store(
    StoreProductVariantRequest $request
)
{
    $data = $request->validated();

    $product = Product::findOrFail(
        $data['product_id']
    );
 
    $attributes = $product->attributes()
        ->with([
            'values' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('display_value');
            },
        ])
        ->where('is_variant', true)
        ->where('is_active', true)
        ->get();

    $dto = new VariantGeneratorData(

        product: $product,

        attributes: $attributes,

        userId: auth()->id(),

    );
 
    try {

        if ($product->variants()->exists()) {

            $this->variantGeneratorService
                ->regenerate($dto);

        } else {

            $this->variantGeneratorService
                ->generate($dto);

        }

        return redirect()
            ->route('product-variants.index')
            ->with(
                'success',
                'Product Variant berhasil dibuat.'
            );

    } catch (\Throwable $e) {

        report($e);

        return back()
            ->withInput()
            ->with(
                'error',
                'Gagal membuat Product Variant.'
            );

    }
}
    /**
     * Display the specified resource.
     * lanjut disini 
     */ 
public function show(ProductVariant $product_variant)
{
    $product_variant->load([
        'product:id,code,name',

        'values.attribute:id,name,display_name',

        'values.attributeValue:id,value,display_value',

        'creator:id,name',
        'updater:id,name',
        'deleter:id,name',

        'activities.performer',
    ]);

    return Inertia::render(
        'MasterData/ProductVariant/Show',
        [
            'title' => 'Product Variant Detail',
            'variant' => $product_variant,
        ]
    );
}
    /**
     * Show the form for editing the specified resource.
     */
public function edit(ProductVariant $product_variant)
{
    $product_variant->load([
        'product',
        'values.attribute',
        'values.attributeValue',
    ]);

    return Inertia::render(
        'MasterData/ProductVariant/Edit',
        [
            'title' => 'Edit Product Variant',
            'variant' => $product_variant,
            ...$this->formData(),
        ]
    );
}

    /**
     * Update the specified resource.
     */
     public function update(
    UpdateProductVariantRequest $request,
    ProductVariant $product_variant
) {
    $data = $request->validated();

    try {

        DB::beginTransaction();

        $data['updated_by'] = auth()->id();

        $product_variant->update($data);

        DB::commit();

        return redirect()
            ->route('product-variants.index')
            ->with(
                'success',
                'Product Variant berhasil diperbarui.'
            );

    } catch (\Throwable $e) {

        DB::rollBack();

        report($e);

        throw $e;
    }
}
public function destroy(ProductVariant $product_variant)
{
    if (! $product_variant->canDelete()) {

        return back()->with(
            'error',
            'Product variant tidak dapat dihapus karena masih digunakan.'
        );

    }

    try {

        DB::beginTransaction();

        $product_variant->update([
            'deleted_by' => auth()->id(),
        ]);

        $product_variant->delete();

        DB::commit();

        return redirect()
            ->route('product-variants.index')
            ->with(
                'success',
                'Product Variant berhasil dihapus.'
            );

    } catch (\Throwable $e) {

        DB::rollBack();

        report($e);

        return back()->with(
            'error',
            'Gagal menghapus Product Variant.'
        );

    }
}
   
/* lanjut bulk delete */
   public function bulkDelete(Request $request)
{
    $request->validate([
        'ids' => ['required', 'array'],
        'ids.*' => ['exists:product_variants,id'],
    ]);

    try {

        DB::beginTransaction();

        $variants = ProductVariant::whereIn('id', $request->ids)->get();

        $deleted = 0;
        $failed = 0;

        foreach ($variants as $variant) {

            if (! $variant->canDelete()) {
                $failed++;
                continue;
            }

            $variant->update([
                'deleted_by' => auth()->id(),
            ]);

            $variant->delete();

            $deleted++;
        }

        DB::commit();

        if ($deleted === 0) {
            return back()->with(
                'error',
                'Tidak ada Product Varian yang dapat dihapus karena masih digunakan.'
            );
        }

        if ($failed > 0) {
            return back()->with(
                'warning',
                "{$deleted} Product Variant berhasil dihapus, {$failed} data tidak dapat dihapus karena masih digunakan."
            );
        }

        return back()->with(
            'success',
            'Product  Variant berhasil dihapus.'
        );

    } catch (\Throwable $e) {

        DB::rollBack();

        report($e);

        return back()->with(
            'error',
            'Gagal menghapus Product Variant.'
        );
    }
}
    public function bulkActivate(Request $request)
        {
            $request->validate([
                'ids' => ['required', 'array'],
                'ids.*' => ['exists:product_variants,id'],
            ]);

            try {

                DB::beginTransaction();

                ProductVariant::whereIn('id', $request->ids)
                    ->update([
                        'is_active' => true,
                        'updated_by' => auth()->id(),
                    ]);

                DB::commit();

                return back()->with(
                    'success',
                    'Product Variant berhasil diaktifkan.'
                );

            } catch (\Throwable $e) {

                DB::rollBack();

                report($e);

                return back()->with(
                    'error',
                    'Gagal mengaktifkan ProductVariant.'
                );
            }
        }

    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:product_variants,id'],
        ]);

        try {

            DB::beginTransaction();

            ProductVariant::whereIn('id', $request->ids)
                ->update([
                    'is_active' => false,
                    'updated_by' => auth()->id(),
                ]);

            DB::commit();

            return back()->with(
                'success',
                'Product Variant berhasil dinonaktifkan.'
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                'Gagal menonaktifkan Product Variant.'
            );
        }
    }
   
public function __construct(
    VariantGeneratorService $variantGeneratorService
)
{
    $this->variantGeneratorService = $variantGeneratorService;
}
protected function formData(): array
{
    return [

        'products' => Product::query()
            ->select(
                'id',
                'code',
                'name'
            )
            ->where('is_active', true)
            ->orderBy('name')
            ->get(),

    ];
}
public function preview(
    Product $product
)
{
    return response()->json(

        $this->variantGeneratorService
            ->preview($product)

    );
}
public function priceHistory(ProductVariant $productVariant)
{
    $history = \App\Models\MasterData\ProductVariantPrice::query()
        ->with([
            'branch:id,name',
            'variant.product:id,code,name',
            'unit:id,name',
            'priceType:id,name',
        ])
        ->where('product_variant_id', $productVariant->id)
        ->orderByDesc('effective_from')
        ->get();

    return response()->json($history);
}
}