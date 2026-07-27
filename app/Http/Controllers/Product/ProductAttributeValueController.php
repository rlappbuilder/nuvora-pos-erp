<?php

namespace App\Http\Controllers\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product\ProductAttributeValue;
use App\Http\Requests\Product\ProductAttributeValue\ProductAttributeValueStoreRequest;
use App\Http\Requests\Product\ProductAttributeValue\ProductAttributeValueUpdateRequest;
use App\Services\CodeGeneratorService;
use Illuminate\Support\Facades\DB;
class ProductAttributeValueController extends Controller
{
    protected string $module = 'Product Attribute value';
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
   $query = ProductAttributeValue::query()
    ->search($request->search);
    $allowedSorts = [
                    'id',
                    'code',
                    'value',
                    'display_value',
                    'sort_order',
                    'is_active',
                    'created_at',
                ];
    // Search
    

    // Status Filter
    if ($request->filled('is_active')) {
        $query->where('is_active', $request->boolean('is_active'));
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

    $productAttributeValue = $query
        ->paginate($perPage)
        ->withQueryString();

    $statistics = [
    'total' => ProductAttributeValue::count(),
    'active' => ProductAttributeValue::active()->count(),
    'inactive' => ProductAttributeValue::where('is_active', false)->count(),
    'total' => ProductAttributeValue::count(),
    'active' => ProductAttributeValue::active()->count(),
    'inactive' => ProductAttributeValue::where('is_active', false)->count(),
    ];

    return Inertia::render(
        'MasterData/ProductAttributeValue/Index',
        [
            'title' => 'Product Attributes Value',
            'statistics' => $statistics,
            'productAttributeValues' => $productAttributeValue,
            'filters' => $request->only([
            'search',
            'is_active',
            'sort_by',
            'sort_direction',
            'per_page',
        ]),
        ]
    );
}

    /**
     * Show the form for creating a new resource.
     */
        public function create()
        {
            return Inertia::render(
                'MasterData/ProductAttributeValue/Create',
                [
                    'title' => 'Create Product Attribute',

                    ...$this->formData(),
                ]
            );
        }

    /**
     * Store a newly created resource.
     */
  public function store(ProductAttributeValueStoreRequest $request)
{
    $data = $request->validated();

    try {
        \DB::beginTransaction();

        $data['created_by'] = auth()->id();

        ProductAttributeValue::create($data);

        \DB::commit();

        if ($request->boolean('create_another')) {
            return redirect()
                ->route('product-attribute-values.create')
                ->with('success', 'Product Attribute berhasil ditambahkan.');
        }

        return redirect()
            ->route('product-attribute-values.index')
            ->with('success', 'Product Attribute berhasil ditambahkan.');
    } catch (\Throwable $e) {

        \DB::rollBack();

        report($e);

        return back()
            ->withInput()
            ->with('error', 'Failed to Save Product Attribute.');
    }
}
    /**
     * Display the specified resource.
     */
  public function show(ProductAttributeValue $productAttributeValue)
{
   $productAttributeValue->load([
    'company:id,name',
    'productAttribute:id,name',
    'creator:id,name',
    'updater:id,name',
    'deleter:id,name',
]);

    return Inertia::render(
        'MasterData/ProductAttributeValue/Show',
        [
            'title' => 'Detail Product Attribute',
            'productAttributeValue' => $productAttributeValue,
        ]
    );
}

    /**
     * Show the form for editing the specified resource.
     */
   public function edit(ProductAttributeValue $productAttributeValue)
    {
        return Inertia::render(
            'MasterData/ProductAttributeValue/Edit',
            [
                'title' => 'Edit Product Attribute',
                'productAttributeValue' => $productAttributeValue,

                ...$this->formData(),
            ]
        );
    }

    /**
     * Update the specified resource.
     */
        public function update(
            ProductAttributeValueUpdateRequest $request,
            productAttributeValue $productAttributeValue
        ) {
            $data = $request->validated();

            try {
                DB::beginTransaction();

                $data['updated_by'] = auth()->id();

                $productAttributeValue->update($data);

                DB::commit();

                return redirect()
                    ->route('product-attribute-values.index')
                    ->with('success', 'Product Attribute Value berhasil diperbarui.');
            } catch (\Throwable $e) {
                DB::rollBack();

                report($e);

                return back()
                    ->withInput()
                    ->with('error', 'Gagal memperbarui Product Attribute Value.');
            }
        }

    /**
     * Remove the specified resource.
     */
    public function destroy(ProductAttributeValue $productAttributeValue)
        {
            try {
                DB::beginTransaction();

                $productAttributeValue->update([
                    'deleted_by' => auth()->id(),
                ]);

                $productAttributeValue->delete();

                DB::commit();

                return redirect()
                    ->route('product-attribute-values.index')
                    ->with('success', 'Product Attribute Value berhasil dihapus.');
            } catch (\Throwable $e) {
                DB::rollBack();

                report($e);

                return back()
                    ->with('error', 'Gagal menghapus Product Attribute Value.');
            }
        }

   public function duplicate(ProductAttributeValue $productAttributeValue)
    {
        return Inertia::render(
            'MasterData/ProductAttributeValue/Create',
            [
                'title' => 'Duplicate Product Attribute Value',

             //   'previewCode' => CodeGeneratorService::preview($this->module),

                'isDuplicate' => true,

               'productAttributeValue' => [

                    'company_id' => $productAttributeValue->company_id,
                    'product_attribute_id' => $productAttributeValue->product_attribute_id,
                    'code' => null,
                    'value' => $productAttributeValue->value . ' Copy',
                    'display_value' => $productAttributeValue->display_value . ' Copy',
                    'color_code' => $productAttributeValue->color_code,
                    'sort_order' => $productAttributeValue->sort_order,
                    'description' => $productAttributeValue->description,
                    'is_active' => $productAttributeValue->is_active,

                ],

                ...$this->formData(),
            ]
        );
    }

   public function bulkDelete(Request $request)
{
    $request->validate([
        'ids' => ['required', 'array'],
        'ids.*' => ['exists:product_attribute_values,id'],
    ]);

    try {
        DB::beginTransaction();

        $productAttributeValues = ProductAttributeValue::whereIn(
            'id',
            $request->ids
        )->get();

        foreach ($productAttributeValues as $productAttributeValue) {

            if (! $productAttributeValue->canDelete()) {

                DB::rollBack();

                return back()->with(
                    'error',
                    'Product Attribute Value tidak dapat dihapus karena masih digunakan.'
                );
            }

            $productAttributeValue->delete();
        }

        DB::commit();

        return back()->with(
            'success',
            'Product Attribute Value berhasil dihapus.'
        );

    } catch (\Throwable $e) {

        DB::rollBack();

        report($e);

        return back()->with(
            'error',
            'Gagal menghapus Product Attribute Value.'
        );
    }
}
    public function bulkActivate(Request $request)
        {
            $request->validate([
                'ids' => ['required', 'array'],
                'ids.*' => ['exists:product_attribute_values,id'],
            ]);

            try {

                DB::beginTransaction();

            $productAttributeValues = ProductAttributeValue::whereIn('id', $request->ids)->get();

            foreach ($productAttributeValues as $productAttributeValue) {

                if (! $productAttributeValue->canActivate()) {
                    continue;
                }

                $productAttributeValue->activate();
            }

                DB::commit();

                return back()->with(
                    'success',
                    'Product Attribute Value berhasil diaktifkan.'
                );

            } catch (\Throwable $e) {

                DB::rollBack();

                report($e);

                return back()->with(
                    'error',
                    'Gagal mengaktifkan Product Attribute Value.'
                );
            }
        }

    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:product_attribute_values,id'],
        ]);

        try {

            DB::beginTransaction();

            $productAttributeValues = ProductAttributeValue::whereIn('id', $request->ids)->get();

                foreach ($productAttributeValues as $productAttributeValue) {

                    if (! $productAttributeValue->canDeactivate()) {
                        continue;
                    }

                    $productAttributeValue->deactivate();
                }
            DB::commit();

            return back()->with(
                'success',
                'Product Attribute Value berhasil dinonaktifkan.'
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                'Gagal menonaktifkan Product Attribute value.'
            );
        }
    }

 private function formData(): array
{
    return [
        'productAttributes' => \App\Models\Product\ProductAttribute::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]),
    ];
}

}