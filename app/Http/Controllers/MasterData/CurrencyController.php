<?php

namespace App\Http\Controllers\MasterData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Currency;
use App\Http\Requests\MasterData\CurrencyRequest;
use Inertia\Inertia;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MasterData\BulkActionRequest;

class CurrencyController extends Controller

{
    /**
     * Display Currency List
     */
   public function index()
{
    $query = Currency::query();
              $allowedSorts = [
                'code',
                'name',
                'symbol',
                'decimal_places',
                'exchange_rate',
                'is_base_currency',
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
        $currencies = (clone $query)
            
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

            'deleted' => Currency::onlyTrashed()->count(),

        ];
    return Inertia::render(
    'MasterData/Currencies/Index',
    [

        'currencies' => $currencies,

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
    return Inertia::render('MasterData/Currencies/Create',      
    );
}
    /**
     * Store Currency
     */
 public function store(CurrencyRequest $request)
{
    $data = $request->validated();

    $data['created_by'] = auth()->id();

    // Currency::create($data);

$data['created_by'] = auth()->id();


    Currency::create($data);


    if ($request->boolean('create_another')) {

        return redirect()
            ->route('currencies.create')
            ->with(
                'success',
                'Currency created successfully.'
            );
    }

    return redirect()
        ->route('currencies.index')
        ->with(
            'success',
            'Currency created successfully.'
        );
}

public function show(Currency $currency)
{
    return Inertia::render(
        'MasterData/Currencies/Show',
        [
            'currency' => $currency,
        ]
    );
}
    public function edit(Currency $currency)
    {
        return Inertia::render(
            'MasterData/Currencies/Edit',
            [
                'currency' => $currency,
            ]
        );
    }
        /**
         * Update Currency
         */
        public function update(
        CurrencyRequest $request,
        Currency $currency
    )
    {
        $data = $request->validated();

        $data['updated_by'] = auth()->id();

        $currency->update($data);

    return redirect()
        ->route('currencies.index')
        ->with(
            'success',
            'Currency updated successfully.'
        );
    }
    /**
     * Delete Currrency
     */
   public function destroy(
    Currency $currency
)
{
    $currency->delete();

    return redirect()
        ->back()
        ->with(
            'success',
            'Currency deleted successfully.'
        );
}
public function duplicate(Currency $currency)
{
    return Inertia::render(
        'MasterData/Currencies/Create',
        [
            'duplicate' => [
            'code'              => $currency->code,
            'name'             => $currency->name,
            'symbol'           => $currency->symbol,
            'decimal_places'   => $currency->decimal_places,
            'exchange_rate'    => $currency->exchange_rate,
            'is_base_currency' => false,
            'description'      => $currency->description,
            'is_active'        => $currency->is_active,
        ],
        ]
    );
}
public function bulkDelete(BulkActionRequest $request)
{


    Currency::whereIn('id', $request->ids)->delete();

    return redirect()
        ->route('currencies.index')
        ->with(
            'success',
            'Selected currencies deleted successfully.'
        );
}
public function bulkActivate(BulkActionRequest $request)
{
   

    Currency::whereIn('id', $request->ids)
        ->update([
            'is_active' => true,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('currencies.index')
        ->with(
            'success',
            'Selected currencies activated successfully.'
        );
}
public function bulkDeactivate(BulkActionRequest $request)
{


    Currency::whereIn('id', $request->ids)
        ->update([
            'is_active' => false,
            'updated_by' => auth()->id(),
        ]);

    return redirect()
        ->route('currencies.index')
        ->with(
            'success',
            'Selected currencies deactivated successfully.'
        );
}

}

