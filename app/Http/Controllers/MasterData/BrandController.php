<?php

namespace App\Http\Controllers\MasterData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Brand;
use App\Http\Requests\BrandRequest;
use Inertia\Inertia;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MasterData\BulkActionRequest;
class BrandController extends Controller

{
    /**
     * Display brand List
     */
   public function index()
{
    $query = Brand::query();
                $allowedSorts = [
                'code',
                'name',
                'description',
                'is_active',
                'created_at',
            ];

            $sort = request('sort', 'code');
            $direction = request('direction', 'asc');

            if (! in_array($sort, $allowedSorts)) {
                $sort = 'code';
            }

            if (! in_array($direction, ['asc', 'desc'])) {
                $direction = 'asc';
            }
$query
    ->when(
        request('search'),
        function ($query) {

            $query->where(function ($query) {

                $query->where(
                    'code',
                    'like',
                    '%' . request('search') . '%'
                )

                ->orWhere(
                    'name',
                    'like',
                    '%' . request('search') . '%'
                );

            });

        }
    )

    ->when(
        request()->filled('is_active'),
        function ($query) {

            $query->where(
                'is_active',
                request('is_active')
            );

        }
    );

$query->orderBy($sort, $direction);
        $brands = (clone $query)
            
            ->paginate(
                request('per_page', 10)
            )

        ->withQueryString();
          $stats = [

            'total' => (clone $query)->count(),

            'active' => (clone $query)
                ->where('is_active', true)
                ->count(),

            'inactive' => (clone $query)
                ->where('is_active', false)
                ->count(),

            'deleted' => Brand::onlyTrashed()->count(),

        ];
    return Inertia::render(
    'MasterData/Brands/Index',
    [

        'brands' => $brands,

        'stats' => $stats,

        'filters' => [

            'search' => request('search'),

            'is_active' => request('is_active'),

            'per_page' => request('per_page', 10),

            'sort'  => $sort,

            'direction' => $direction,

        ],

    ]
);
}
public function create(CodeGeneratorService $codeGenerator)
{
    return Inertia::render(
        'MasterData/Brands/Create',
        [
            'previewCode' => $codeGenerator->preview('Brand'),
        ]
    );
}
    /**
     * Store Brand
     */
 public function store(BrandRequest $request)
{
    $data = $request->validated();

    $data['created_by'] = auth()->id();

    // Brand::create($data);

    $data['code'] = app(CodeGeneratorService::class)
    ->next('brand');

    Brand::create($data);


    if ($request->boolean('create_another')) {

        return redirect()
            ->route('brands.create')
            ->with(
                'success',
                'Brand created successfully.'
            );
    }

    return redirect()
        ->route('brands.index')
        ->with(
            'success',
            'Brand created successfully.'
        );
}

public function show(Brand $brand)
{
    return Inertia::render(
        'MasterData/Brands/Show',
        [
            'brand' => $brand,
        ]
    );
}
    public function edit(Brand $brand)
    {
        return Inertia::render(
            'MasterData/Brands/Edit',
            [
                'brand' => $brand,
            ]
        );
    }
        /**
         * Update Brand
         */
        public function update(
        BrandRequest $request,
        Brand $brand
    )
    {
        $data = $request->validated();

        $data['updated_by'] = auth()->id();

        $brand->update($data);

    return redirect()
        ->route('brands.index')
        ->with(
            'success',
            'Brand updated successfully.'
        );
    }
    /**
     * Delete Brand
     */
   public function destroy(
    Brand $brand
)
{
    $brand->delete();

    return redirect()
        ->back()
        ->with(
            'success',
            'Brand deleted successfully.'
        );
}
public function duplicate(Brand $brand)
{
    return Inertia::render(
        'MasterData/Brands/Create',
        [
            'duplicate' => [
                'name' => $brand->name,
                'description' => $brand->description,
                'is_active' => $brand->is_active,
            ],
        ]
    );
}
public function bulkDelete(BulkActionRequest $request)
{


    Brand::whereIn('id', $request->ids)->delete();

    return redirect()
        ->route('brands.index')
        ->with(
            'success',
            'Selected brands deleted successfully.'
        );
}
public function bulkActivate(BulkActionRequest $request)
{
   

    Brand::whereIn('id', $request->ids)
        ->update([
            'is_active' => true,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('brands.index')
        ->with(
            'success',
            'Selected brands activated successfully.'
        );
}
public function bulkDeactivate(BulkActionRequest $request)
{


    Brand::whereIn('id', $request->ids)
        ->update([
            'is_active' => false,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('brands.index')
        ->with(
            'success',
            'Selected brands deactivated successfully.'
        );
}
public function previewCode(
    CodeGeneratorService $codeGenerator
): JsonResponse
{
    return response()->json([
        'code' => $codeGenerator->preview('brand'),
    ]);
}
public function syncCode(CodeGeneratorService $codeGenerator)
{
    $maxNumber = Brand::query()
        ->selectRaw('MAX(CAST(RIGHT(code, 4) AS UNSIGNED)) as max_number')
        ->value('max_number') ?? 0;

    $codeGenerator->sync(
        'brand',
        (int) $maxNumber
    );

    return redirect()->back()->with(
        'success',
        'Code generator berhasil disinkronkan.'
    );
}

}

