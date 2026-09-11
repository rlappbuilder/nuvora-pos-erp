<?php

namespace App\Http\Controllers\Reseller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Reseller\Reseller;
use App\Http\Requests\Reseller\StoreResellerRequest;
use App\Http\Requests\Reseller\UpdateResellerRequest;
use Inertia\Inertia;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MasterData\BulkActionRequest;
use App\Models\Reseller\ResellerPrice;
use App\Models\Product\Product;
use Illuminate\Support\Facades\DB;
use App\Models\Reseller\ResellerPriceHistory;
class ResellerController extends Controller
{
    /**
     * Display reseller list
     */
   public function index()
{
    $companyId = auth()->user()->company_id;

    $query = Reseller::query()
        ->where(
            'company_id',
            $companyId
        );

    $allowedSorts = [
        'reseller_code',
        'name',
        'contact_person',
        'phone',
        'email',
        'city',
        'tax_number',
        'status',
        'created_at',
    ];

    $sort = request(
        'sort',
        'reseller_code'
    );

    $direction = request(
        'direction',
        'asc'
    );

    if (! in_array($sort, $allowedSorts)) {
        $sort = 'reseller_code';
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
                        'reseller_code',
                        'like',
                        '%' . request('search') . '%'
                    )
                    ->orWhere(
                        'name',
                        'like',
                        '%' . request('search') . '%'
                    )
                    ->orWhere(
                        'contact_person',
                        'like',
                        '%' . request('search') . '%'
                    )
                    ->orWhere(
                        'phone',
                        'like',
                        '%' . request('search') . '%'
                    );
                });
            }
        )

        ->when(
            request()->filled('status'),
            function ($query) {
                $query->where(
                    'status',
                    request('status')
                );
            }
        );

    $query->orderBy(
        $sort,
        $direction
    );

    $resellers = (clone $query)
        ->paginate(
            request('per_page', 10)
        )
        ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    $summaryQuery = Reseller::query()
        ->where(
            'company_id',
            $companyId
        );

    $stats = [
        'total' => (clone $summaryQuery)
            ->count(),

        'active' => (clone $summaryQuery)
            ->where(
                'status',
                true
            )
            ->count(),

        'inactive' => (clone $summaryQuery)
            ->where(
                'status',
                false
            )
            ->count(),

        'deleted' => Reseller::onlyTrashed()
            ->where(
                'company_id',
                $companyId
            )
            ->count(),
    ];


    return Inertia::render(
        'Resellers/Resellers/Index',
        [
            'resellers' => $resellers,

            'stats' => $stats,

            'filters' => [
                'search' => request('search'),

                'status' => request('status'),

                'per_page' => request(
                    'per_page',
                    10
                ),

                'sort' => $sort,

                'direction' => $direction,
            ],
        ]
    );
}

    /**
     * Show create reseller form
     */
    public function create(
            CodeGeneratorService $codeGenerator
        ) {
            return Inertia::render(
                'Resellers/Resellers/Create',
                [
                    'previewCode' => $codeGenerator->preview(
                        'reseller'
                    ),

                    'products' => Product::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get([
                            'id',
                            'code',
                            'name',
                        ]),
                ]
            );
        }
        
    /**
     * Store reseller
     */
        public function store(
            StoreResellerRequest $request
        ) {
            return DB::transaction(function () use ($request) {

                $data = $request->validated();

                $prices = $data['prices'] ?? [];

                unset($data['prices']);

                $data['company_id'] =
                    auth()->user()->company_id;

                $data['created_by'] =
                    auth()->id();

                $data['reseller_code'] =
                    app(
                        CodeGeneratorService::class
                    )->next('reseller');

                $reseller = Reseller::create($data);

                foreach ($prices as $price) {

                    ResellerPrice::create([
                        'reseller_id' => $reseller->id,
                        'product_id' => $price['product_id'],
                        'price' => $price['price'],
                    ]);

                    ResellerPriceHistory::create([
                        'reseller_id' => $reseller->id,
                        'product_id' => $price['product_id'],
                        'price' => $price['price'],
                        'effective_from' => now(),
                        'effective_to' => null,
                        'created_by' => auth()->id(),
                    ]);
                }

                if ($request->boolean('create_another')) {

                    return redirect()
                        ->route('resellers.create')
                        ->with(
                            'success',
                            'Reseller created successfully.'
                        );
                }

                return redirect()
                    ->route('resellers.index')
                    ->with(
                        'success',
                        'Reseller created successfully.'
                    );
            });
        }
    
        /**
         * Show reseller
         */
        public function show(Reseller $reseller)
        {
            $reseller->load([
                'prices.product',
                'priceHistories.product',
            ]);

            return Inertia::render(
                'Resellers/Resellers/Show',
                [
                    'reseller' => $reseller,
                ]
            );
        }

    /**
     * Show edit reseller form
     */
   public function edit(Reseller $reseller)
    {
        $reseller->load([
            'prices.product',
        ]);

        return Inertia::render(
            'Resellers/Resellers/Edit',
            [
                'reseller' => $reseller,

                'products' => Product::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get([
                        'id',
                        'code',
                        'name',
                    ]),
            ]
        );
    }

    /**
     * Update reseller
     */
        public function update(
            UpdateResellerRequest $request,
            Reseller $reseller
        ) {
            return DB::transaction(function () use (
                $request,
                $reseller
            ) {

                $data = $request->validated();

                $prices = $data['prices'] ?? [];

                unset($data['prices']);

                $data['updated_by'] =
                    auth()->id();

                $reseller->update($data);

                $existingPrices = $reseller->prices()
                    ->get()
                    ->keyBy('product_id');

                $newProductIds = collect($prices)
                    ->pluck('product_id')
                    ->map(fn ($id) => (int) $id)
                    ->values();

                foreach ($prices as $price) {

                    $productId =
                        (int) $price['product_id'];

                    $newPrice =
                        (float) $price['price'];

                    $existingPrice =
                        $existingPrices->get(
                            $productId
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | New Product Price
                    |--------------------------------------------------------------------------
                    */

                    if (! $existingPrice) {

                        ResellerPrice::create([
                            'reseller_id' => $reseller->id,
                            'product_id' => $productId,
                            'price' => $newPrice,
                        ]);

                        ResellerPriceHistory::create([
                            'reseller_id' => $reseller->id,
                            'product_id' => $productId,
                            'price' => $newPrice,
                            'effective_from' => now(),
                            'effective_to' => null,
                            'created_by' => auth()->id(),
                        ]);

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Price Unchanged
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (float) $existingPrice->price
                        === $newPrice
                    ) {

                        continue;

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Close Current History
                    |--------------------------------------------------------------------------
                    */

                    $existingHistory =
                        $reseller->priceHistories()
                            ->where(
                                'product_id',
                                $productId
                            )
                            ->whereNull(
                                'effective_to'
                            )
                            ->latest(
                                'effective_from'
                            )
                            ->first();

                    if ($existingHistory) {

                        $existingHistory->update([
                            'effective_to' => now(),
                        ]);

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Update Current Price
                    |--------------------------------------------------------------------------
                    */

                    $existingPrice->update([
                        'price' => $newPrice,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Create New History
                    |--------------------------------------------------------------------------
                    */

                    ResellerPriceHistory::create([
                        'reseller_id' => $reseller->id,
                        'product_id' => $productId,
                        'price' => $newPrice,
                        'effective_from' => now(),
                        'effective_to' => null,
                        'created_by' => auth()->id(),
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Removed Products
                |--------------------------------------------------------------------------
                */

                $removedProductIds =
                    $existingPrices
                        ->keys()
                        ->diff(
                            $newProductIds
                        );

                foreach (
                    $removedProductIds
                    as $productId
                ) {

                    $existingHistory =
                        $reseller->priceHistories()
                            ->where(
                                'product_id',
                                $productId
                            )
                            ->whereNull(
                                'effective_to'
                            )
                            ->latest(
                                'effective_from'
                            )
                            ->first();

                    if ($existingHistory) {

                        $existingHistory->update([
                            'effective_to' => now(),
                        ]);

                    }

                    $existingPrices
                        ->get($productId)
                        ?->delete();
                }

                return redirect()
                    ->route('resellers.index')
                    ->with(
                        'success',
                        'Reseller updated successfully.'
                    );
            });
        }

    /**
     * Delete reseller
     */
    public function destroy(
        Reseller $reseller
    ) {
        $reseller->delete();

        return redirect()
            ->back()
            ->with(
                'success',
                'Reseller deleted successfully.'
            );
    }

    /**
     * Duplicate reseller
     */
    public function duplicate(
    Reseller $reseller
        ) {
            $reseller->load('prices.product');

            return Inertia::render(
                'Resellers/Resellers/Create',
                [
                    'duplicate' => [
                        'name' => $reseller->name,
                        'contact_person' => $reseller->contact_person,
                        'phone' => $reseller->phone,
                        'email' => $reseller->email,
                        'address' => $reseller->address,
                        'city' => $reseller->city,
                        'tax_number' => $reseller->tax_number,
                        'status' => $reseller->status,

                        'prices' => $reseller->prices
                            ->map(function ($price) {
                                return [
                                    'product_id' => $price->product_id,
                                    'price' => $price->price,
                                    'product' => $price->product,
                                ];
                            })
                            ->values()
                            ->toArray(),
                    ],
                ]
            );
        }

    /**
     * Bulk delete
     */
    public function bulkDelete(
        BulkActionRequest $request
    ) {
        Reseller::whereIn(
            'id',
            $request->ids
        )->delete();

        return redirect()
            ->route('resellers.index')
            ->with(
                'success',
                'Selected resellers deleted successfully.'
            );
    }

    /**
     * Bulk activate
     */
    public function bulkActivate(
        BulkActionRequest $request
    ) {
        Reseller::whereIn(
            'id',
            $request->ids
        )->update([
            'status' => true,
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('resellers.index')
            ->with(
                'success',
                'Selected resellers activated successfully.'
            );
    }

    /**
     * Bulk deactivate
     */
    public function bulkDeactivate(
        BulkActionRequest $request
    ) {
        Reseller::whereIn(
            'id',
            $request->ids
        )->update([
            'status' => false,
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('resellers.index')
            ->with(
                'success',
                'Selected resellers deactivated successfully.'
            );
    }

    /**
     * Preview reseller code
     */
    public function previewCode(
        CodeGeneratorService $codeGenerator
    ): JsonResponse {
        return response()->json([
            'code' => $codeGenerator->preview(
                'reseller'
            ),
        ]);
    }

    /**
     * Sync reseller code generator
     */
    public function syncCode(
        CodeGeneratorService $codeGenerator
    ) {
        $maxNumber = Reseller::query()
            ->withTrashed()
            ->where(
                'company_id',
                auth()->user()->company_id
            )
            ->selectRaw(
                'MAX(CAST(RIGHT(reseller_code, 4) AS UNSIGNED)) as max_number'
            )
            ->value('max_number') ?? 0;

        $codeGenerator->sync(
            'reseller',
            (int) $maxNumber
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Code generator berhasil disinkronkan.'
            );
    }
}