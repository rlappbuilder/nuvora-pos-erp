<?php

namespace App\Http\Controllers\MasterData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Unit;
use App\Http\Requests\UnitRequest;
use Inertia\Inertia;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MasterData\BulkActionRequest;
class UnitController extends Controller

{
    /**
     * Display unit List
     */
   public function index()
{
    $query = Unit::query();
                $allowedSorts = [

                    'code',

                    'name',

                    'symbol',

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
                    'symbol',
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
        $units = (clone $query)
            
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

            'deleted' => Unit::onlyTrashed()->count(),

        ];
    return Inertia::render(
    'MasterData/Units/Index',
    [

        'units' => $units,

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
        'MasterData/Units/Create',
        [
            'previewCode' => $codeGenerator->preview('Unit'),
        ]
    );
}
    /**
     * Store unit
     */
 public function store(UnitRequest $request)
{
      
    $data = $request->validated();

    $data['created_by'] = auth()->id();

    // unit::create($data);

    $data['code'] = app(CodeGeneratorService::class)
    ->next('Unit');

    Unit::create($data);


    if ($request->boolean('create_another')) {

        return redirect()
            ->route('units.create')
            ->with(
                'success',
                'Unit created successfully.'
            );
    }

    return redirect()
        ->route('units.index')
        ->with(
            'success',
            'Unit created successfully.'
        );
}

public function show(Unit $unit)
{
    return Inertia::render(
        'MasterData/Units/Show',
        [
            'unit' => $unit,
        ]
    );
}
    public function edit(Unit $unit)
    {
        return Inertia::render(
            'MasterData/Units/Edit',
            [
                'unit' => $unit,
            ]
        );
    }
        /**
         * Update unit
         */
        public function update(
        UnitRequest $request,
        Unit $unit
    )
    {
        $data = $request->validated();

        $data['updated_by'] = auth()->id();

        $unit->update($data);

    return redirect()
        ->route('units.index')
        ->with(
            'success',
            'Unit updated successfully.'
        );
    }
    /**
     * Delete unit
     */
   public function destroy(
    Unit $unit
)
{
    $unit->delete();

    return redirect()
        ->back()
        ->with(
            'success',
            'Unit deleted successfully.'
        );
}
public function duplicate(Unit $unit)
{
    return Inertia::render(
        'MasterData/Units/Create',
        [
            'duplicate' => [
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'description' => $unit->description,
                'is_active' => $unit->is_active,
            ],
        ]
    );
}
public function bulkDelete(BulkActionRequest $request)
{


    Unit::whereIn('id', $request->ids)->delete();

    return redirect()
        ->route('units.index')
        ->with(
            'success',
            'Selected Unit deleted successfully.'
        );
}
public function bulkActivate(BulkActionRequest $request)
{
   

    Unit::whereIn('id', $request->ids)
        ->update([
            'is_active' => true,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('units.index')
        ->with(
            'success',
            'Selected unit activated successfully.'
        );
}
public function bulkDeactivate(BulkActionRequest $request)
{


    Unit::whereIn('id', $request->ids)
        ->update([
            'is_active' => false,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('units.index')
        ->with(
            'success',
            'Selected unit deactivated successfully.'
        );
}
public function previewCode(
    CodeGeneratorService $codeGenerator
): JsonResponse
{
    return response()->json([
        'code' => $codeGenerator->preview('Unit'),
    ]);
}
public function syncCode(CodeGeneratorService $codeGenerator)
{
    $maxNumber = Unit::query()
        ->selectRaw('MAX(CAST(RIGHT(code, 4) AS UNSIGNED)) as max_number')
        ->value('max_number') ?? 0;

    $codeGenerator->sync(
        'Unit',
        (int) $maxNumber
    );

    return redirect()->back()->with(
        'success',
        'Code generator berhasil disinkronkan.'
    );
}

}

