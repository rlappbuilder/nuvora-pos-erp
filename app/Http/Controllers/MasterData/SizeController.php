<?php

namespace App\Http\Controllers\MasterData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Size;
use App\Http\Requests\SizeRequest;
use Inertia\Inertia;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MasterData\BulkActionRequest;
class SizeController extends Controller

{
    /**
     * Display Size List
     */
   public function index()
{
    $query = Size::query();
                $allowedSorts = [

                    'code',

                    'name',

                    'sort_order',

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
               )
                    ->orWhere(
                    'sort_order',
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
        $sizes = (clone $query)
            
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

            'deleted' => Size::onlyTrashed()->count(),

        ];
    return Inertia::render(
    'MasterData/Sizes/Index',
    [

        'sizes' => $sizes,

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
        'MasterData/Sizes/Create',
        [
            'previewCode' => $codeGenerator->preview('Size'),
        ]
    );
}
    /**
     * Store size
     */
 public function store(SizeRequest $request)
{
      
    $data = $request->validated();

    $data['created_by'] = auth()->id();

    // size::create($data);

    $data['code'] = app(CodeGeneratorService::class)
    ->next('Size');

    Size::create($data);


    if ($request->boolean('create_another')) {

        return redirect()
            ->route('sizes.create')
            ->with(
                'success',
                'Size created successfully.'
            );
    }

    return redirect()
        ->route('sizes.index')
        ->with(
            'success',
            'Size created successfully.'
        );
}

public function show(Size $size)
{
    return Inertia::render(
        'MasterData/Sizes/Show',
        [
            'size' => $sizes,
        ]
    );
}
    public function edit(Size $size)
    {
        return Inertia::render(
            'MasterData/Sizes/Edit',
            [
                'size' => $size,
            ]
        );
    }
        /**
         * Update size
         */
        public function update(
        SizeRequest $request,
        Size $size
    )
    {
        $data = $request->validated();

        $data['updated_by'] = auth()->id();

        $size->update($data);

    return redirect()
        ->route('sizes.index')
        ->with(
            'success',
            'Size updated successfully.'
        );
    }
    /**
     * Delete size
     */
   public function destroy(
    Size $size
)
{
    $size->delete();

    return redirect()
        ->back()
        ->with(
            'success',
            'Size deleted successfully.'
        );
}
public function duplicate(Size $size)
{
    return Inertia::render(
        'MasterData/Sizes/Create',
        [
            'duplicate' => [
                'name' => $size->name,
                'sort_order' => $size->sort_order,
                'description' => $size->description,
                'is_active' => $size->is_active,
            ],
        ]
    );
}
public function bulkDelete(BulkActionRequest $request)
{


    Size::whereIn('id', $request->ids)->delete();

    return redirect()
        ->route('sizes.index')
        ->with(
            'success',
            'Selected Size deleted successfully.'
        );
}
public function bulkActivate(BulkActionRequest $request)
{
   

    Size::whereIn('id', $request->ids)
        ->update([
            'is_active' => true,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('sizes.index')
        ->with(
            'success',
            'Selected Size activated successfully.'
        );
}
public function bulkDeactivate(BulkActionRequest $request)
{


    Size::whereIn('id', $request->ids)
        ->update([
            'is_active' => false,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('sizes.index')
        ->with(
            'success',
            'Selected size deactivated successfully.'
        );
}
public function previewCode(
    CodeGeneratorService $codeGenerator
): JsonResponse
{
    return response()->json([
        'code' => $codeGenerator->preview('Size'),
    ]);
}
public function syncCode(CodeGeneratorService $codeGenerator)
{
    $maxNumber = Size::query()
        ->selectRaw('MAX(CAST(RIGHT(code, 4) AS UNSIGNED)) as max_number')
        ->value('max_number') ?? 0;

    $codeGenerator->sync(
        'Size',
        (int) $maxNumber
    );

    return redirect()->back()->with(
        'success',
        'Code generator berhasil disinkronkan.'
    );
}

}

