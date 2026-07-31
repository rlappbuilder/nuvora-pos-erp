<?php

namespace App\Http\Controllers\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Http\Requests\Product\Product\StoreProductRequest;
use App\Http\Requests\Product\Product\UpdateProductRequest;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Support\Facades\DB;
use App\Models\MasterData\Category;
use App\Models\MasterData\Brand;
use App\Models\MasterData\Unit;
class ProductController extends Controller
{
    protected string $module = 'Product';
    protected CodeGeneratorService $codeGenerator;
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
    $query = Product::query()
        ->with([
            'category',
            'brand',
            'unit',
        ])
        ->search($request->search);
        $allowedSorts = [
            'id',
            'code',
            'sku',
            'name',
            'product_type',
            'is_active',
            'created_at',
        ];

  // Category Filter
         // Status Filter
     //    dd($request->all());
        if ($request->filled('is_active')) {
        $query->where('is_active', $request->boolean('is_active'));
    }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Brand Filter
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Product Type Filter
        if ($request->filled('product_type')) {
            $query->where('product_type', $request->product_type);
        }

    // Sorting
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

    $products = $query
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

    'product' => (clone $statisticsQuery)
        ->where('product_type', 'PRODUCT')
        ->count(),

    'service' => (clone $statisticsQuery)
        ->where('product_type', 'SERVICE')
        ->count(),
];

    return Inertia::render('Product/Index', [
    'title' => 'Product',
    'statistics' => $statistics,
    'products' => $products,

    'categories' => Category::query()
        ->select('id', 'name')
        ->where('is_active', true)
        ->orderBy('name')
        ->get(),

    'brands' => Brand::query()
        ->select('id', 'name')
        ->where('is_active', true)
        ->orderBy('name')
        ->get(),

    'filters' => $request->only([
        'search',
        'category_id',
        'brand_id',
        'product_type',
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
          return Inertia::render('Product/Create', [
            'title' => 'Create Product',

            'previewCode' => $this->codeGenerator->preview($this->module),

            ...$this->formData(),
        ]);
        }

    /**
     * Store a newly created resource.
     */
  public function store(StoreProductRequest $request)
{
   $data = $request->validated();

    $data['code'] = $this->codeGenerator->next($this->module);
    $data['slug'] = Product::generateSlug($data['name']);
    $data['created_by'] = auth()->id();

    try {
        DB::beginTransaction();

        $data['created_by'] = auth()->id();

        Product::create($data);

        DB::commit();

        if ($request->boolean('create_another')) {
            return redirect()
                ->route('products.create')
                ->with('success', 'Product berhasil ditambahkan.');
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Product berhasil ditambahkan.');

    } catch (\Throwable $e) {

        DB::rollBack();

        report($e);

        return back()
            ->withInput()
            ->with('error', 'Failed to Save Product.');
    }
}
    /**
     * Display the specified resource.
     */
  public function show(Product $product)
{
    $product->load([
    'category:id,name',
    'brand:id,name',
    'unit:id,name',
    'creator:id,name',
    'updater:id,name',
    'deleter:id,name',
]);

    return Inertia::render(
        'Product/Show',
        [
            'title' => 'Detail Product',
            'product' => $product,
        ]
    );
}

    /**
     * Show the form for editing the specified resource.
     */
   public function edit(Product $product)
    {
        return Inertia::render(
            'Product/Edit',
            [
                'title' => 'Edit Product',
                 'product' => $product->load([
                'category',
                'brand',
                'unit',
                 ]),
                ...$this->formData(),
            ]
        );
    }

    /**
     * Update the specified resource.
     */
      public function update(
    UpdateProductRequest $request,
    Product $product
) {
    $data = $request->validated();

    if ($product->name !== $data['name']) {
        $data['slug'] = Product::generateSlug(
            $data['name'],
            $product->id
        );
    }

    try {
        DB::beginTransaction();

        $data['updated_by'] = auth()->id();

        $product->update($data);

        DB::commit();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product berhasil diperbarui.');
    } catch (\Throwable $e) {
        DB::rollBack();

        report($e);

        throw $e; // sementara untuk melihat error asli
    }
}
    /**
     * Remove the specified resource.
     */
    public function destroy(Product $product)
{
    if (! $product->canDelete()) {
        return back()->with(
            'error',
            'Product tidak dapat dihapus karena masih digunakan.'
        );
    }

    try {
        DB::beginTransaction();

        $product->update([
            'deleted_by' => auth()->id(),
        ]);

        $product->delete();

        DB::commit();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product berhasil dihapus.');
    } catch (\Throwable $e) {
        DB::rollBack();

        report($e);

        return back()
            ->with('error', 'Gagal menghapus Product.');
    }
}
   public function duplicate(Product $product)
    {
       return Inertia::render('Product/Create', [
    'previewCode' => $this->codeGenerator->preview($this->module),

    'duplicate' => [
        'category_id'    => $product->category_id,
        'brand_id'       => $product->brand_id,
        'unit_id'        => $product->unit_id,

        'name'           => $product->name . ' Copy',
        'product_type'   => $product->product_type,

        'track_stock'    => $product->track_stock,
        'is_sellable'    => $product->is_sellable,
        'is_purchasable' => $product->is_purchasable,

        'minimum_stock'  => $product->minimum_stock,
        'description'    => $product->description,
        'is_active'      => $product->is_active,
        ],

                ...$this->formData(),
            ]
        );
    }

   public function bulkDelete(Request $request)
{
    $request->validate([
        'ids' => ['required', 'array'],
        'ids.*' => ['exists:products,id'],
    ]);

    try {

        DB::beginTransaction();

        $products = Product::whereIn('id', $request->ids)->get();

        $deleted = 0;
        $failed = 0;

        foreach ($products as $product) {

            if (! $product->canDelete()) {
                $failed++;
                continue;
            }

            $product->update([
                'deleted_by' => auth()->id(),
            ]);

            $product->delete();

            $deleted++;
        }

        DB::commit();

        if ($deleted === 0) {
            return back()->with(
                'error',
                'Tidak ada Product yang dapat dihapus karena masih digunakan.'
            );
        }

        if ($failed > 0) {
            return back()->with(
                'warning',
                "{$deleted} Product berhasil dihapus, {$failed} data tidak dapat dihapus karena masih digunakan."
            );
        }

        return back()->with(
            'success',
            'Product berhasil dihapus.'
        );

    } catch (\Throwable $e) {

        DB::rollBack();

        report($e);

        return back()->with(
            'error',
            'Gagal menghapus Product.'
        );
    }
}
    public function bulkActivate(Request $request)
        {
            $request->validate([
                'ids' => ['required', 'array'],
                'ids.*' => ['exists:products,id'],
            ]);

            try {

                DB::beginTransaction();

                Product::whereIn('id', $request->ids)
                    ->update([
                        'is_active' => true,
                        'updated_by' => auth()->id(),
                    ]);

                DB::commit();

                return back()->with(
                    'success',
                    'Product berhasil diaktifkan.'
                );

            } catch (\Throwable $e) {

                DB::rollBack();

                report($e);

                return back()->with(
                    'error',
                    'Gagal mengaktifkan Product.'
                );
            }
        }

    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:products,id'],
        ]);

        try {

            DB::beginTransaction();

            Product::whereIn('id', $request->ids)
                ->update([
                    'is_active' => false,
                    'updated_by' => auth()->id(),
                ]);

            DB::commit();

            return back()->with(
                'success',
                'Product berhasil dinonaktifkan.'
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                'Gagal menonaktifkan Product.'
            );
        }
    }
   
 protected function formData(): array
{
    return [
        'categories' => Category::orderBy('name')->get(),
        'brands' => Brand::orderBy('name')->get(),
        'units' => Unit::orderBy('name')->get(),
    ];
}
public function generateSku()
{
    return response()->json([
        'sku' => Product::generateSku(),
    ]);
}
public function __construct(
    CodeGeneratorService $codeGenerator
) {
    $this->codeGenerator = $codeGenerator;
}
}