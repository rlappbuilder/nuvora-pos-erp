<?php

namespace App\Http\Controllers\Consignment;

use App\Http\Controllers\Controller;

use App\Http\Requests\Reseller\ConsignmentSettlement\StoreConsignmentSettlementRequest;

use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader;
use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementDetail;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Unit;
use App\Models\Reseller\Reseller;
use App\Models\Reseller\ResellerPrice;
use App\Models\Product\ProductVariant;
use App\Models\Inventory\ProductStock;
use App\Services\Consignment\ConsignmentSettlementService;
use App\Services\Core\CodeGeneratorService;

use Illuminate\Http\Request;
use Inertia\Inertia;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\Resellers\Reports\ConsignmentSettlementExport;

class ConsignmentSettlementController extends Controller
{
    public function __construct(
        protected ConsignmentSettlementService $consignmentSettlementService,
        protected CodeGeneratorService $codeGeneratorService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Preview Code
    |--------------------------------------------------------------------------
    */

    public function previewCode()
    {
        return response()->json([

            'code' =>
                $this
                    ->codeGeneratorService
                    ->preview(
                        'consignment_settlement'
                    ),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query =
            ConsignmentSettlementHeader::query()
                ->with([
                    'company',
                    'branch',
                    'warehouse',
                    'reseller',
                    'details.variant.product',
                    'details.unit',
                ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('search'),
            function ($query) use ($request) {

                $search =
                    $request->search;

                $query->where(
                    function ($query) use ($search) {

                        $query
                            ->where(
                                'settlement_number',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhereHas(
                                'reseller',
                                function ($reseller) use ($search) {

                                    $reseller
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )

                                        ->orWhere(
                                            'reseller_code',
                                            'like',
                                            "%{$search}%"
                                        );

                                }
                            )

                            ->orWhereHas(
                                'branch',
                                function ($branch) use ($search) {

                                    $branch->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    );

                                }
                            )

                            ->orWhereHas(
                                'warehouse',
                                function ($warehouse) use ($search) {

                                    $warehouse->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    );

                                }
                            )

                            ->orWhereHas(
                                'details.variant',
                                function ($variant) use ($search) {

                                    $variant
                                        ->where(
                                            'sku',
                                            'like',
                                            "%{$search}%"
                                        )

                                        ->orWhere(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )

                                        ->orWhereHas(
                                            'product',
                                            function ($product) use ($search) {

                                                $product->where(
                                                    'name',
                                                    'like',
                                                    "%{$search}%"
                                                );

                                            }
                                        );

                                }
                            );

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Branch Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('branch_id'),
            function ($query) use ($request) {

                $query->where(
                    'branch_id',
                    $request->branch_id
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Warehouse Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('warehouse_id'),
            function ($query) use ($request) {

                $query->where(
                    'warehouse_id',
                    $request->warehouse_id
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Reseller Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('reseller_id'),
            function ($query) use ($request) {

                $query->where(
                    'reseller_id',
                    $request->reseller_id
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('status'),
            function ($query) use ($request) {

                $query->where(
                    'status',
                    $request->status
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Settlement Date Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('date_from'),
            function ($query) use ($request) {

                $query->whereDate(
                    'settlement_date',
                    '>=',
                    $request->date_from
                );

            }
        );

        $query->when(
            $request->filled('date_to'),
            function ($query) use ($request) {

                $query->whereDate(
                    'settlement_date',
                    '<=',
                    $request->date_to
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Filtered Document IDs
        |--------------------------------------------------------------------------
        */

        $filteredIds =
            (clone $query)
                ->select('id')
                ->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $sort =
            $request->get(
                'sort',
                'created_at'
            );

        $direction =
            $request->get(
                'direction',
                'desc'
            );

        $allowedSorts = [
            'settlement_number',
            'settlement_date',
            'status',
            'grand_total',
            'payment_amount',
            'receivable_amount',
            'created_at',
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $query->orderBy(
            $sort,
            $direction
        );
        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
       
           $settlements =
             $query
                 ->paginate(
                    $request->integer(
                        'per_page',
                        10
                    )
                )
                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Row Statistics
        |--------------------------------------------------------------------------
        */

        $settlements
            ->getCollection()
            ->transform(
                function ($settlement) {

                    $settlement->total_items =
                        $settlement
                            ->details
                            ->sum('qty_sold');

                    $settlement->total_transaction =
                        $settlement
                            ->details
                            ->sum('total_amount');

                    return $settlement;

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Filtered Transaction Summary
        |--------------------------------------------------------------------------
        */

        $totalTransaction =
            ConsignmentSettlementDetail::query()
                ->whereIn(
                    'settlement_header_id',
                    $filteredIds
                )
                ->sum('total_amount');


        $totalItems =
            ConsignmentSettlementDetail::query()
                ->whereIn(
                    'settlement_header_id',
                    $filteredIds
                )
                ->sum('qty_sold');


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statisticsQuery =
            clone $query;

        $statistics = [

            'total' =>
                (clone $statisticsQuery)
                    ->count(),

            'total_transaction' =>
                $totalTransaction,

            'total_items' =>
                $totalItems,

            'posted' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Posted'
                    )
                    ->count(),

            'cancelled' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Cancelled'
                    )
                    ->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Resellers/ConsignmentSettlement/Index',

            array_merge(

                [

                    'title' =>
                        'Consignment Settlement',

                    'settlements' =>
                        $settlements,

                    'statistics' =>
                        $statistics,

                    'previewNumber' =>
                        $this
                            ->codeGeneratorService
                            ->preview(
                                'consignment_settlement'
                            ),

                    'filters' =>
                        $request->only([
                            'search',
                            'branch_id',
                            'warehouse_id',
                            'reseller_id',
                            'status',
                            'per_page',
                            'date_from',
                            'date_to',
                        ]),

                ],

                $this->formData()

            )
        );
    }


 /*
|--------------------------------------------------------------------------
| Form Data
|--------------------------------------------------------------------------
*/

private function formData(): array
{
    /*
    |--------------------------------------------------------------------------
    | Branches
    |--------------------------------------------------------------------------
    */

    $branches =
        Branch::query()
            ->orderBy('name')
            ->get([
                'id',
                'company_id',
                'name',
            ]);


    /*
    |--------------------------------------------------------------------------
    | Consignment Stocks
    |--------------------------------------------------------------------------
    |
    | Stock yang dikirim ke frontend hanya stock consignment.
    | Kombinasi stock:
    |
    | company
    | + branch
    | + warehouse
    | + reseller
    | + product variant
    | + unit
    |
    */

    $consignmentStocks =
        ProductStock::query()
            ->whereNotNull('reseller_id')
            ->whereIn(
                'company_id',
                $branches
                    ->pluck('company_id')
                    ->unique()
                    ->values()
            )
            ->get([
                'company_id',
                'branch_id',
                'warehouse_id',
                'reseller_id',
                'product_variant_id',
                'unit_id',
                'available_qty',
            ])
            ->map(
                fn ($stock) => [

                    'company_id' =>
                        $stock->company_id,

                    'branch_id' =>
                        $stock->branch_id,

                    'warehouse_id' =>
                        $stock->warehouse_id,

                    'reseller_id' =>
                        $stock->reseller_id,

                    'product_variant_id' =>
                        $stock->product_variant_id,

                    'unit_id' =>
                        $stock->unit_id,

                    'available_qty' =>
                        (float) $stock->available_qty,

                ]
            )
            ->values();


             $availableResellerIds =
            $consignmentStocks
                ->where(
                    'available_qty',
                    '>',
                    0
                )
                ->pluck('reseller_id')
                ->unique()
                ->values();

    return [

        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        'branches' =>
            $branches
                ->map(
                    fn ($branch) => [

                        'id' =>
                            $branch->id,

                        'company_id' =>
                            $branch->company_id,

                        'label' =>
                            $branch->name,

                    ]
                )
                ->values(),


        /*
        |--------------------------------------------------------------------------
        | Warehouses
        |--------------------------------------------------------------------------
        */

        'warehouses' =>
            Warehouse::query()
                ->whereIn(
                    'branch_id',
                    $branches->pluck('id')
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'branch_id',
                    'name',
                ])
                ->map(
                    fn ($warehouse) => [

                        'id' =>
                            $warehouse->id,

                        'branch_id' =>
                            $warehouse->branch_id,

                        'label' =>
                            $warehouse->name,

                    ]
                )
                ->values(),

    

        /*
        |--------------------------------------------------------------------------
        | Resellers
        |--------------------------------------------------------------------------
        */
            
   

        'resellers' =>
            Reseller::query()
                ->where(
                    'status',
                    true
                )
                ->whereIn(
                    'company_id',
                    $branches
                        ->pluck('company_id')
                        ->unique()
                        ->values()
                )
                ->whereIn(
                    'id',
                    $availableResellerIds
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'company_id',
                    'reseller_code',
                    'name',
                ])
                ->map(
                    fn ($reseller) => [

                        'id' =>
                            $reseller->id,

                        'company_id' =>
                            $reseller->company_id,

                        'code' =>
                            $reseller->reseller_code,

                        'label' =>
                            implode(
                                ' - ',
                                array_filter([
                                    $reseller->reseller_code,
                                    $reseller->name,
                                ])
                            ),

                    ]
                )
                ->values(),
        /*
        |--------------------------------------------------------------------------
        | Reseller Prices
        |--------------------------------------------------------------------------
        */

        'resellerPrices' =>
            ResellerPrice::query()
                ->orderBy('reseller_id')
                ->orderBy('product_id')
                ->get([
                    'id',
                    'reseller_id',
                    'product_id',
                    'price',
                ])
                ->map(
                    fn ($price) => [

                        'id' =>
                            $price->id,

                        'reseller_id' =>
                            $price->reseller_id,

                        'product_id' =>
                            $price->product_id,

                        'price' =>
                            (float) $price->price,

                    ]
                )
                ->values(),


        /*
        |--------------------------------------------------------------------------
        | Product Variants
        |--------------------------------------------------------------------------
        */

        'variants' =>
            ProductVariant::query()
                ->active()
                ->whereHas(
                    'units',
                    function ($query) {

                        $query->active();

                    }
                )
                ->with([

                    'product',

                    'units' => function ($query) {

                        $query
                            ->active()
                            ->with('unit')
                            ->orderBy('sort_order');

                    },

                ])
                ->orderBy('sku')
                ->get([
                    'id',
                    'product_id',
                    'sku',
                    'name',
                ])
                ->map(
                    fn ($variant) => [

                        'id' =>
                            $variant->id,

                        'product_id' =>
                            $variant->product_id,

                        'sku' =>
                            $variant->sku,

                        'name' =>
                            $variant->name,

                        'product' => [

                            'id' =>
                                $variant->product?->id,

                            'name' =>
                                $variant->product?->name,

                        ],

                        'label' =>
                            implode(
                                ' - ',
                                array_filter([
                                    $variant->sku,
                                    $variant->product?->name,
                                    $variant->name,
                                ])
                            ),

                        'units' =>
                            $variant
                                ->units
                                ->map(
                                    fn ($variantUnit) => [

                                        'id' =>
                                            $variantUnit->unit_id,

                                        'label' =>
                                            $variantUnit
                                                ->unit
                                                ?->name,

                                        'conversion_factor' =>
                                            (float)
                                            $variantUnit
                                                ->conversion_factor,

                                        'is_base' =>
                                            (bool)
                                            $variantUnit
                                                ->is_base,

                                        'is_default' =>
                                            (bool)
                                            $variantUnit
                                                ->is_default,

                                    ]
                                )
                                ->values(),

                    ]
                )
                ->values(),


        /*
        |--------------------------------------------------------------------------
        | Units
        |--------------------------------------------------------------------------
        */

        'units' =>
            Unit::query()
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ])
                ->map(
                    fn ($unit) => [

                        'id' =>
                            $unit->id,

                        'label' =>
                            $unit->name,

                    ]
                )
                ->values(),


        /*
        |--------------------------------------------------------------------------
        | Consignment Stocks
        |--------------------------------------------------------------------------
        */

        'consignmentStocks' =>
            $consignmentStocks,

    ];
}


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */
public function create()
{
    $formData = $this->formData();

    return Inertia::render(
        'Resellers/ConsignmentSettlement/Create',

        array_merge(

            [

                'title' =>
                    'Create Consignment Settlement',

                'previewNumber' =>
                    $this
                        ->codeGeneratorService
                        ->preview(
                            'consignment_settlement'
                        ),

            ],

            $formData

        )
    );
}


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreConsignmentSettlementRequest $request
    ) {

        $data =
            $request->validated();


        /*
        |--------------------------------------------------------------------------
        | Resolve Company From Branch
        |--------------------------------------------------------------------------
        */

        $branch =
            Branch::findOrFail(
                $data['branch_id']
            );


        $data['company_id'] =
            $branch->company_id;


        /*
        |--------------------------------------------------------------------------
        | Create Settlement
        |--------------------------------------------------------------------------
        */

      $settlement =
        $this
            ->consignmentSettlementService
            ->createSettlement(
                $data
            );
        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment settlement posted successfully.'
            )
            ->with(
                'settlement_success',
                [
                    'id' => $settlement->id,

                    'number' =>
                        $settlement->settlement_number,

                    'grand_total' =>
                        (float) $settlement->grand_total,

                    'print_url' =>
                        route(
                            'consignment-settlements.print',
                            $settlement
                        ),
                ]
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */
    public function show(
        ConsignmentSettlementHeader $consignmentSettlement
    ) {
        $consignmentSettlement->load([
            'company',
            'branch',
            'warehouse',
            'reseller',
            'creator',
            'updater',
            'poster',
            'canceller',
            'details.variant.product',
            'details.unit',
            'activities.performer',
            'movements',
        ]);

        return Inertia::render(
            'Resellers/ConsignmentSettlement/Show',
            [
                'settlement' => $consignmentSettlement,
            ]
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        ConsignmentSettlementHeader $settlement
    ) {

        $validated =
            $request->validate([

                'reason' => [

                    'required',
                    'string',
                    'min:3',
                    'max:1000',

                ],

            ]);


        $this
            ->consignmentSettlementService
            ->cancelSettlement(
                $settlement,
                $validated['reason']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment settlement cancelled successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Data
    |--------------------------------------------------------------------------
    */

    public function showData(
        ConsignmentSettlementHeader $settlement
    ) {

        $settlement->load([

            'company',

            'branch',

            'warehouse',

            'reseller',

            'creator',

            'updater',

            'poster',

            'canceller',

            'details.variant.product',

            'details.unit',

            'activities.performer',

            'movements',

        ]);


        return response()->json([

            'data' =>
                $settlement,

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Export Data
    |--------------------------------------------------------------------------
    */

    private function loadSettlementForExport(
        ConsignmentSettlementHeader $settlement
    ): ConsignmentSettlementHeader {

        return $settlement->load([

            'company',

            'branch',

            'warehouse',

            'reseller',

            'details.variant.product',

            'details.unit',

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    public function print(
        ConsignmentSettlementHeader $settlement
    ) {

        $settlement =
            $this->loadSettlementForExport(
                $settlement
            );


        return view(
            'print.Reseller.Reports.consignment-settlement',
            [
                'settlement' =>
                    $settlement,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function pdf(
        ConsignmentSettlementHeader $settlement
    ) {

        $settlement =
            $this->loadSettlementForExport(
                $settlement
            );


        return Pdf::loadView(
            'pdf.Reseller.Reports.consignment-settlement',
            [
                'settlement' =>
                    $settlement,
            ]
        )
            ->setPaper(
                'a4',
                'portrait'
            )
            ->download(
                'consignment-settlement-' .
                $settlement->settlement_number .
                '.pdf'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Excel
    |--------------------------------------------------------------------------
    */

    public function excel(
        ConsignmentSettlementHeader $settlement
    ) {

        $settlement =
            $this->loadSettlementForExport(
                $settlement
            );


        return Excel::download(
            new ConsignmentSettlementExport(
                $settlement
            ),
            'consignment-settlement-' .
            $settlement->settlement_number .
            '.xlsx'
        );
    }
}