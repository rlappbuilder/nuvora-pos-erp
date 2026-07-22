<?php

namespace App\Http\Controllers\MasterData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Category;
use App\Http\Requests\CategoryRequest;
use Inertia\Inertia;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MasterData\BulkActionRequest;
class CategoryController extends Controller

{
    /**
     * Display Category List
     */
   public function index()
{
    $query = Category::query();
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
        $categories = (clone $query)
            
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

            'deleted' => Category::onlyTrashed()->count(),

        ];
    return Inertia::render(
    'MasterData/Categories/Index',
    [

        'categories' => $categories,

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
public function create()
{
    $lastCategory = Category::withTrashed()
        ->latest('id')
        ->first();

    $nextNumber = $lastCategory
        ? ((int) substr($lastCategory->code, 3)) + 1
        : 1;

    $previewCode = 'CAT' . str_pad(
        $nextNumber,
        4,
        '0',
        STR_PAD_LEFT
    );

    return Inertia::render(
        'MasterData/Categories/Create',
        [
            'previewCode' => $previewCode,
        ]
    );
}
    /**
     * Store Category
     */
 public function store(CategoryRequest $request)
{
    $data = $request->validated();

    $data['created_by'] = auth()->id();

    // Category::create($data);

    $data['code'] = app(CodeGeneratorService::class)
    ->next('category');

    Category::create($data);


    if ($request->boolean('create_another')) {

        return redirect()
            ->route('categories.create')
            ->with(
                'success',
                'Category created successfully.'
            );
    }

    return redirect()
        ->route('categories.index')
        ->with(
            'success',
            'Category created successfully.'
        );
}

public function show(Category $category)
{
    return Inertia::render(
        'MasterData/Categories/Show',
        [
            'category' => $category,
        ]
    );
}
    public function edit(Category $category)
    {
        return Inertia::render(
            'MasterData/Categories/Edit',
            [
                'category' => $category,
            ]
        );
    }
        /**
         * Update Category
         */
        public function update(
        CategoryRequest $request,
        Category $category
    )
    {
        $data = $request->validated();

        $data['updated_by'] = auth()->id();

        $category->update($data);

    return redirect()
        ->route('categories.index')
        ->with(
            'success',
            'Category updated successfully.'
        );
    }
    /**
     * Delete Category
     */
   public function destroy(
    Category $category
)
{
    $category->delete();

    return redirect()
        ->back()
        ->with(
            'success',
            'Category deleted successfully.'
        );
}
public function duplicate(Category $category)
{
    return Inertia::render(
        'MasterData/Categories/Create',
        [
            'duplicate' => [
                'name' => $category->name,
                'description' => $category->description,
                'is_active' => $category->is_active,
            ],
        ]
    );
}
public function bulkDelete(BulkActionRequest $request)
{


    Category::whereIn('id', $request->ids)->delete();

    return redirect()
        ->route('categories.index')
        ->with(
            'success',
            'Selected categories deleted successfully.'
        );
}
public function bulkActivate(BulkActionRequest $request)
{
   

    Category::whereIn('id', $request->ids)
        ->update([
            'is_active' => true,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('categories.index')
        ->with(
            'success',
            'Selected categories activated successfully.'
        );
}
public function bulkDeactivate(BulkActionRequest $request)
{


    Category::whereIn('id', $request->ids)
        ->update([
            'is_active' => false,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('categories.index')
        ->with(
            'success',
            'Selected categories deactivated successfully.'
        );
}
public function previewCode(
    CodeGeneratorService $codeGenerator
): JsonResponse
{
    return response()->json([
        'code' => $codeGenerator->preview('category'),
    ]);
}
public function syncCode(CodeGeneratorService $codeGenerator)
{
    $maxNumber = Category::query()
        ->selectRaw('MAX(CAST(RIGHT(code, 4) AS UNSIGNED)) as max_number')
        ->value('max_number') ?? 0;

    $codeGenerator->sync(
        'category',
        (int) $maxNumber
    );

    return redirect()->back()->with(
        'success',
        'Code generator berhasil disinkronkan.'
    );
}

}

