<?php

namespace App\Http\Controllers\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product\ProductAttribute;
use App\Http\Requests\Product\ProductAttribute\ProductAttributeStoreRequest;
use App\Http\Requests\Product\ProductAttribute\ProductAttributeUpdateRequest;
use App\Services\CodeGeneratorService;
use Illuminate\Support\Facades\DB;
class ProductAttributeController extends Controller
{
    protected string $module = 'ProductAttribute';
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
   $query = ProductAttribute::query()
    ->search($request->search);
    $allowedSorts = [
                    'id',
                    'code',
                    'name',
                    'display_name',
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

    $productAttributes = $query
        ->paginate($perPage)
        ->withQueryString();

    $statistics = [
    'total' => ProductAttribute::count(),
    'active' => ProductAttribute::active()->count(),
    'inactive' => ProductAttribute::where('is_active', false)->count(),
    'variant' => ProductAttribute::variant()->count(),
    'required' => ProductAttribute::where('is_required', true)->count(),
    ];

    return Inertia::render(
        'MasterData/ProductAttribute/Index',
        [
            'title' => 'Product Attributes',
            'statistics' => $statistics,
            'productAttributes' => $productAttributes,
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
                'MasterData/ProductAttribute/Create',
                [
                    'title' => 'Create Product Attribute',

                    ...$this->formData(),
                ]
            );
        }

    /**
     * Store a newly created resource.
     */
  public function store(ProductAttributeStoreRequest $request)
    {
        $data = $request->validated();

        try {
            \DB::beginTransaction();

            $data['created_by'] = auth()->id();

            ProductAttribute::create($data);

            \DB::commit();

            return redirect()
                ->route('product-attributes.index')
                ->with('success', 'Product Attribute Succes Added.');
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
  public function show(ProductAttribute $productAttribute)
{
    $productAttribute->load([
        'company:id,name',
        'creator:id,name',
        'updater:id,name',
        'deleter:id,name',
    ]);

    return Inertia::render(
        'MasterData/ProductAttribute/Show',
        [
            'title' => 'Detail Product Attribute',
            'productAttribute' => $productAttribute,
        ]
    );
}

    /**
     * Show the form for editing the specified resource.
     */
   public function edit(ProductAttribute $productAttribute)
    {
        return Inertia::render(
            'MasterData/ProductAttribute/Edit',
            [
                'title' => 'Edit Product Attribute',
                'productAttribute' => $productAttribute,

                ...$this->formData(),
            ]
        );
    }

    /**
     * Update the specified resource.
     */
        public function update(
            ProductAttributeUpdateRequest $request,
            ProductAttribute $productAttribute
        ) {
            $data = $request->validated();

            try {
                DB::beginTransaction();

                $data['updated_by'] = auth()->id();

                $productAttribute->update($data);

                DB::commit();

                return redirect()
                    ->route('product-attributes.index')
                    ->with('success', 'Product Attribute berhasil diperbarui.');
            } catch (\Throwable $e) {
                DB::rollBack();

                report($e);

                return back()
                    ->withInput()
                    ->with('error', 'Gagal memperbarui Product Attribute.');
            }
        }

    /**
     * Remove the specified resource.
     */
    public function destroy(ProductAttribute $productAttribute)
        {
            try {
                DB::beginTransaction();

                $productAttribute->update([
                    'deleted_by' => auth()->id(),
                ]);

                $productAttribute->delete();

                DB::commit();

                return redirect()
                    ->route('product-attributes.index')
                    ->with('success', 'Product Attribute berhasil dihapus.');
            } catch (\Throwable $e) {
                DB::rollBack();

                report($e);

                return back()
                    ->with('error', 'Gagal menghapus Product Attribute.');
            }
        }

   public function duplicate(ProductAttribute $productAttribute)
    {
        return Inertia::render(
            'MasterData/ProductAttribute/Create',
            [
                'title' => 'Duplicate Product Attribute',

             //   'previewCode' => CodeGeneratorService::preview($this->module),

                'isDuplicate' => true,

                'productAttribute' => [
                    
                    'company_id'   => $productAttribute->company_id,
                    'name'         => $productAttribute->name . ' Copy',
                    'display_name' => $productAttribute->display_name,
                    'input_type'   => $productAttribute->input_type,
                    'is_required'  => $productAttribute->is_required,
                    'is_variant'   => $productAttribute->is_variant,
                    'sort_order'   => $productAttribute->sort_order,
                    'description'  => $productAttribute->description,
                    'is_active'    => $productAttribute->is_active,
                ],

                ...$this->formData(),
            ]
        );
    }

    public function bulkDelete(Request $request)
{
    $request->validate([
        'ids' => ['required', 'array'],
        'ids.*' => ['exists:product_attributes,id'],
    ]);

    try {

        DB::beginTransaction();

        $productAttributes = ProductAttribute::whereIn('id', $request->ids)->get();

        foreach ($productAttributes as $productAttribute) {

            if (! $productAttribute->canDelete()) {
                continue;
            }

            $productAttribute->update([
                'deleted_by' => auth()->id(),
            ]);

            $productAttribute->delete();
        }

        DB::commit();

                return back()->with('success', 'Product Attribute Deleted.');

            } catch (\Throwable $e) {

                DB::rollBack();

                throw $e;
            }
        }

    public function bulkActivate(Request $request)
        {
            $request->validate([
                'ids' => ['required', 'array'],
                'ids.*' => ['exists:product_attributes,id'],
            ]);

            try {

                DB::beginTransaction();

                ProductAttribute::whereIn('id', $request->ids)
                    ->update([
                        'is_active' => true,
                        'updated_by' => auth()->id(),
                    ]);

                DB::commit();

                return back()->with(
                    'success',
                    'Product Attribute berhasil diaktifkan.'
                );

            } catch (\Throwable $e) {

                DB::rollBack();

                report($e);

                return back()->with(
                    'error',
                    'Gagal mengaktifkan Product Attribute.'
                );
            }
        }

    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:product_attributes,id'],
        ]);

        try {

            DB::beginTransaction();

            ProductAttribute::whereIn('id', $request->ids)
                ->update([
                    'is_active' => false,
                    'updated_by' => auth()->id(),
                ]);

            DB::commit();

            return back()->with(
                'success',
                'Product Attribute berhasil dinonaktifkan.'
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                'Gagal menonaktifkan Product Attribute.'
            );
        }
    }

  private function formData(): array
{
    return [
        'inputTypes' => [
            [
                'label' => 'Select',
                'value' => 'Select',
            ],
            [
                'label' => 'Radio',
                'value' => 'Radio',
            ],
            [
                'label' => 'Button',
                'value' => 'Button',
            ],
            [
                'label' => 'Color',
                'value' => 'Color',
            ],
            [
                'label' => 'Text',
                'value' => 'Text',
            ],
        ],
    ];
}

}