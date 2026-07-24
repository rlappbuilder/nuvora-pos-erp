<?php

namespace App\Http\Controllers\MasterData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Tax;
use App\Http\Requests\MasterData\TaxRequest;
use Inertia\Inertia;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MasterData\BulkActionRequest;
class TaxController extends Controller

{
    /**
     * Display tax List
     */
   public function index()
{
    $query = Tax::query();
               $allowedSorts = [
                'code',
                'name',
                'type',
                'rate',
                'is_default',
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
        $taxes = (clone $query)
            
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

            'deleted' => Tax::onlyTrashed()->count(),

        ];
    return Inertia::render(
    'MasterData/Taxes/Index',
    [

        'taxes' => $taxes,

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
        'MasterData/Taxes/Create',
        [
            'previewCode' => $codeGenerator->preview('Tax'),
        ]
    );
}
    /**
     * Store Tax
     */
 public function store(TaxRequest $request)
{
    $data = $request->validated();

    $data['created_by'] = auth()->id();

    // Tax::create($data);

    $data['code'] = app(CodeGeneratorService::class)
    ->next('tax');

    Tax::create($data);


    if ($request->boolean('create_another')) {

        return redirect()
            ->route('taxes.create')
            ->with(
                'success',
                'Tax created successfully.'
            );
    }

    return redirect()
        ->route('taxes.index')
        ->with(
            'success',
            'Tax created successfully.'
        );
}

public function show(Tax $tax)
{
    return Inertia::render(
        'MasterData/Taxes/Show',
        [
            'tax' => $tax,
        ]
    );
}
    public function edit(Tax $tax)
    {
        return Inertia::render(
            'MasterData/Taxes/Edit',
            [
                'tax' => $tax,
            ]
        );
    }
        /**
         * Update Tax
         */
        public function update(
        TaxRequest $request,
        Tax $tax
    )
    {
        $data = $request->validated();

        $data['updated_by'] = auth()->id();

        $tax->update($data);

    return redirect()
        ->route('taxes.index')
        ->with(
            'success',
            'Tax updated successfully.'
        );
    }
    /**
     * Delete Tax
     */
   public function destroy(
    Tax $tax
)
{
    $tax->delete();

    return redirect()
        ->back()
        ->with(
            'success',
            'Tax deleted successfully.'
        );
}
public function duplicate(Tax $tax)
{
    return Inertia::render(
        'MasterData/Taxes/Create',
        [
            'duplicate' => [
                'name' => $tax->name,
                'description' => $tax->description,
                'is_active' => $tax->is_active,
            ],
        ]
    );
}
public function bulkDelete(BulkActionRequest $request)
{


    Tax::whereIn('id', $request->ids)->delete();

    return redirect()
        ->route('taxes.index')
        ->with(
            'success',
            'Selected taxes deleted successfully.'
        );
}
public function bulkActivate(BulkActionRequest $request)
{
   

    Tax::whereIn('id', $request->ids)
        ->update([
            'is_active' => true,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('taxes.index')
        ->with(
            'success',
            'Selected taxes activated successfully.'
        );
}
public function bulkDeactivate(BulkActionRequest $request)
{


    Tax::whereIn('id', $request->ids)
        ->update([
            'is_active' => false,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('taxes.index')
        ->with(
            'success',
            'Selected tax deactivated successfully.'
        );
}
public function previewCode(
    CodeGeneratorService $codeGenerator
): JsonResponse
{
    return response()->json([
        'code' => $codeGenerator->preview('Tax'),
    ]);
}
public function syncCode(CodeGeneratorService $codeGenerator)
{
    $maxNumber = Tax::query()
        ->selectRaw('MAX(CAST(RIGHT(code, 4) AS UNSIGNED)) as max_number')
        ->value('max_number') ?? 0;

    $codeGenerator->sync(
        'tax',
        (int) $maxNumber
    );

    return redirect()->back()->with(
        'success',
        'Code generator berhasil disinkronkan.'
    );
}

}

