<?php

namespace App\Http\Controllers\MasterData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Color;
use App\Http\Requests\ColorRequest;
use Inertia\Inertia;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MasterData\BulkActionRequest;
class ColorController extends Controller

{
    /**
     * Display color List
     */
   public function index()
{
    $query = Color::query();
                $allowedSorts = [

                    'code',

                    'name',

                    'hex_color',

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
                    'hex_color',
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
        $colors = (clone $query)
            
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

            'deleted' => Color::onlyTrashed()->count(),

        ];
    return Inertia::render(
    'MasterData/Colors/Index',
    [

        'colors' => $colors,

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
        'MasterData/Colors/Create',
        [
            'previewCode' => $codeGenerator->preview('Color'),
        ]
    );
}
    /**
     * Store color
     */
 public function store(ColorRequest $request)
{
      
    $data = $request->validated();

    $data['created_by'] = auth()->id();

    // color::create($data);

    $data['code'] = app(CodeGeneratorService::class)
    ->next('Color');

    Color::create($data);


    if ($request->boolean('create_another')) {

        return redirect()
            ->route('colors.create')
            ->with(
                'success',
                'Color created successfully.'
            );
    }

    return redirect()
        ->route('colors.index')
        ->with(
            'success',
            'Color created successfully.'
        );
}

public function show(Color $color)
{
    return Inertia::render(
        'MasterData/Colors/Show',
        [
            'color' => $color,
        ]
    );
}
    public function edit(Color $color)
    {
        return Inertia::render(
            'MasterData/Colors/Edit',
            [
                'color' => $color,
            ]
        );
    }
        /**
         * Update color
         */
        public function update(
        ColorRequest $request,
        Color $color
    )
    {
        $data = $request->validated();

        $data['updated_by'] = auth()->id();

        $color->update($data);

    return redirect()
        ->route('colors.index')
        ->with(
            'success',
            'Color updated successfully.'
        );
    }
    /**
     * Delete color
     */
   public function destroy(
    Color $color
)
{
    $color->delete();

    return redirect()
        ->back()
        ->with(
            'success',
            'Color deleted successfully.'
        );
}
public function duplicate(Color $color)
{
    return Inertia::render(
        'MasterData/Colors/Create',
        [
            'duplicate' => [
                'name' => $color->name,
                'hex_color' => $color->hex_color,
                'description' => $color->description,
                'is_active' => $color->is_active,
            ],
        ]
    );
}
public function bulkDelete(BulkActionRequest $request)
{


    Color::whereIn('id', $request->ids)->delete();

    return redirect()
        ->route('colors.index')
        ->with(
            'success',
            'Selected Color deleted successfully.'
        );
}
public function bulkActivate(BulkActionRequest $request)
{
   

    Color::whereIn('id', $request->ids)
        ->update([
            'is_active' => true,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('colors.index')
        ->with(
            'success',
            'Selected Color activated successfully.'
        );
}
public function bulkDeactivate(BulkActionRequest $request)
{


    Color::whereIn('id', $request->ids)
        ->update([
            'is_active' => false,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('colors.index')
        ->with(
            'success',
            'Selected color deactivated successfully.'
        );
}
public function previewCode(
    CodeGeneratorService $codeGenerator
): JsonResponse
{
    return response()->json([
        'code' => $codeGenerator->preview('Color'),
    ]);
}
public function syncCode(CodeGeneratorService $codeGenerator)
{
    $maxNumber = Color::query()
        ->selectRaw('MAX(CAST(RIGHT(code, 4) AS UNSIGNED)) as max_number')
        ->value('max_number') ?? 0;

    $codeGenerator->sync(
        'Color',
        (int) $maxNumber
    );

    return redirect()->back()->with(
        'success',
        'Code generator berhasil disinkronkan.'
    );
}

}

