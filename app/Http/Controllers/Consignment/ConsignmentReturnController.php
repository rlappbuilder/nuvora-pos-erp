<?php

namespace App\Http\Controllers\Consignment;

use App\Http\Controllers\Controller;

use App\Http\Requests\Reseller\ConsignmentReturn\StoreConsignmentReturnRequest;
use App\Http\Requests\Reseller\ConsignmentReturn\UpdateConsignmentReturnRequest;
use App\Http\Requests\Reseller\ConsignmentReturn\RejectConsignmentReturnRequest;

use App\Models\Reseller\ConsignmentReturn\ConsignmentReturnHeader;
use App\Models\Reseller\ConsignmentReturn\ConsignmentReturnDetail;

use App\Models\Reseller\Reseller;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Unit;

use App\Models\Product\ProductVariant;

use App\Models\Inventory\ProductStock;

use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader;

use App\Services\Consignment\ConsignmentReturnService;
use App\Services\Core\CodeGeneratorService;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ConsignmentReturnController extends Controller
{
    public function __construct(
        protected ConsignmentReturnService $consignmentReturnService,
        protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {

        $query =
            ConsignmentReturnHeader::query()
                ->with([
                    'branch',
                    'warehouse',
                    'reseller',
                    'settlement',
                    'details.variant.product',
                    'details.unit',
                    'inventoryMovements',
                ])

                /*
                |--------------------------------------------------------------------------
                | Search
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('search'),
                    function ($query) use ($request) {

                        $search =
                            $request->search;

                        $query->where(
                            function ($query) use ($search) {

                                $query
                                    ->where(
                                        'return_number',
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
                )

                /*
                |--------------------------------------------------------------------------
                | Branch Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('branch_id'),
                    function ($query) use ($request) {

                        $query->where(
                            'branch_id',
                            $request->branch_id
                        );

                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Warehouse Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('warehouse_id'),
                    function ($query) use ($request) {

                        $query->where(
                            'warehouse_id',
                            $request->warehouse_id
                        );

                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Reseller Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('reseller_id'),
                    function ($query) use ($request) {

                        $query->where(
                            'reseller_id',
                            $request->reseller_id
                        );

                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Status Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('status'),
                    function ($query) use ($request) {

                        $query->where(
                            'status',
                            $request->status
                        );

                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Return Date Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('date_from'),
                    function ($query) use ($request) {

                        $query->whereDate(
                            'return_date',
                            '>=',
                            $request->date_from
                        );

                    }
                )

                ->when(
                    $request->filled('date_to'),
                    function ($query) use ($request) {

                        $query->whereDate(
                            'return_date',
                            '<=',
                            $request->date_to
                        );

                    }
                );


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $consignmentReturns =
            $query
                ->latest()
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

        $consignmentReturns
            ->getCollection()
            ->transform(
                function ($consignmentReturn) {

                    $consignmentReturn->total_items =
                        $consignmentReturn
                            ->details
                            ->count();

                    $consignmentReturn->total_returned =
                        $consignmentReturn
                            ->details
                            ->sum(
                                fn ($detail) =>
                                    (float)
                                    $detail->returned_qty
                            );

                    $consignmentReturn->total_cost =
                        $consignmentReturn
                            ->details
                            ->sum(
                                fn ($detail) =>
                                    (float)
                                    $detail->total_cost
                            );

                    return $consignmentReturn;

                }
            );


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

            'draft' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Draft'
                    )
                    ->count(),

            'submitted' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Submitted'
                    )
                    ->count(),

            'approved' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Approved'
                    )
                    ->count(),

            'rejected' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Rejected'
                    )
                    ->count(),

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
            'Resellers/ConsignmentReturn/Index',

            [

                'title' =>
                    'Consignment Return',

                'consignmentReturns' =>
                    $consignmentReturns,

                'statistics' =>
                    $statistics,

                'previewNumber' =>
                    $this
                        ->codeGeneratorService
                        ->preview(
                            'consignment_return'
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

                ...$this->formData(),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    private function formData(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Branches
            |--------------------------------------------------------------------------
            */

            'branches' =>
                Branch::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'company_id',
                        'name',
                    ])
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
                    ->orderBy('name')
                    ->get([
                        'id',
                        'company_id',
                        'reseller_code',
                        'name',
                        'contact_person',
                    ])
                    ->map(
                        fn ($reseller) => [

                            'id' =>
                                $reseller->id,

                            'company_id' =>
                                $reseller->company_id,

                            'code' =>
                                $reseller->reseller_code,

                            'name' =>
                                $reseller->name,

                            'contact_person' =>
                                $reseller->contact_person,

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
            | Settlements
            |--------------------------------------------------------------------------
            |
            | Optional reference only.
            |
            */

            'settlements' =>
                ConsignmentSettlementHeader::query()
                    ->where(
                        'status',
                        'Posted'
                    )
                    ->with([
                        'reseller',
                        'branch',
                        'warehouse',
                        'details.variant.product',
                        'details.unit',
                    ])
                    ->orderByDesc(
                        'settlement_date'
                    )
                    ->orderByDesc(
                        'id'
                    )
                    ->get()
                    ->map(
                        function ($settlement) {

                            return [

                                'id' =>
                                    $settlement->id,

                                'number' =>
                                    $settlement->settlement_number,

                                'label' =>
                                    $settlement->settlement_number,

                                'settlement_date' =>
                                    $settlement->settlement_date,

                                'company_id' =>
                                    $settlement->company_id,

                                'branch_id' =>
                                    $settlement->branch_id,

                                'warehouse_id' =>
                                    $settlement->warehouse_id,

                                'reseller_id' =>
                                    $settlement->reseller_id,

                                'grand_total' =>
                                    $settlement->grand_total,

                                'reseller' => [

                                    'id' =>
                                        $settlement
                                            ->reseller
                                            ?->id,

                                    'code' =>
                                        $settlement
                                            ->reseller
                                            ?->reseller_code,

                                    'name' =>
                                        $settlement
                                            ->reseller
                                            ?->name,

                                ],

                                'branch' => [

                                    'id' =>
                                        $settlement
                                            ->branch
                                            ?->id,

                                    'name' =>
                                        $settlement
                                            ->branch
                                            ?->name,

                                ],

                                'warehouse' => [

                                    'id' =>
                                        $settlement
                                            ->warehouse
                                            ?->id,

                                    'name' =>
                                        $settlement
                                            ->warehouse
                                            ?->name,

                                ],

                                'details' =>
                                    $settlement
                                        ->details
                                        ->map(
                                            function ($detail) {

                                                return [

                                                    'id' =>
                                                        $detail->id,

                                                    'product_variant_id' =>
                                                        $detail
                                                            ->product_variant_id,

                                                    'unit_id' =>
                                                        $detail
                                                            ->unit_id,

                                                    'qty_sold' =>
                                                        $detail
                                                            ->qty_sold,

                                                    'unit_price' =>
                                                        $detail
                                                            ->unit_price,

                                                    'total_amount' =>
                                                        $detail
                                                            ->total_amount,

                                                    'product' => [

    'id' =>
        $detail
            ->variant
            ?->product_id,

    'name' =>
        $detail
            ->variant
            ?->product
            ?->name,

],

'variant' => [

    'id' =>
        $detail
            ->variant
            ?->id,

    'sku' =>
        $detail
            ->variant
            ?->sku,

    'name' =>
        $detail
            ->variant
            ?->name,

],

                                                    'unit' => [

                                                        'id' =>
                                                            $detail
                                                                ->unit
                                                                ?->id,

                                                        'name' =>
                                                            $detail
                                                                ->unit
                                                                ?->name,

                                                    ],

                                                ];

                                            }
                                        )
                                        ->values(),

                            ];

                        }
                    )
                    ->values(),


            /*
            |--------------------------------------------------------------------------
            | Consignment Stocks
            |--------------------------------------------------------------------------
            |
            | Source utama Consignment Return.
            |
            */

            'consignmentStocks' =>
                ProductStock::query()
                    ->whereNotNull(
                        'reseller_id'
                    )
                    ->where(
                        'available_qty',
                        '>',
                        0
                    )
                    ->with([
                        'company',
                        'branch',
                        'warehouse',
                        'reseller',
                        'variant.product',
                        'unit',
                    ])
                    ->orderBy(
                        'reseller_id'
                    )
                    ->orderBy(
                        'warehouse_id'
                    )
                    ->orderBy(
                        'product_variant_id'
                    )
                    ->get()
                    ->map(
                        function ($stock) {

                            return [

                                'id' =>
                                    $stock->id,

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

                                    'label' =>
                                        implode(
                                            ' - ',
                                            array_filter([
                                                $stock->variant?->product?->name,
                                                $stock->variant?->name,
                                                $stock->variant?->sku,
                                            ])
                                        ),

                                'on_hand_qty' =>
                                    $stock->on_hand_qty,

                                'reserved_qty' =>
                                    $stock->reserved_qty,

                                'available_qty' =>
                                    $stock->available_qty,

                                'average_cost' =>
                                    $stock->average_cost,

                                'product' => [

                                    'id' =>
                                        $stock
                                            ->variant
                                            ?->product_id,

                                    'name' =>
                                        $stock
                                            ->variant
                                            ?->product
                                            ?->name,

                                ],

                                'variant' => [

                                    'id' =>
                                        $stock
                                            ->variant
                                            ?->id,

                                    'sku' =>
                                        $stock
                                            ->variant
                                            ?->sku,

                                    'name' =>
                                        $stock
                                            ->variant
                                            ?->name,

                                ],

                                'unit' => [

                                    'id' =>
                                        $stock
                                            ->unit
                                            ?->id,

                                    'name' =>
                                        $stock
                                            ->unit
                                            ?->name,

                                ],

                                'branch' => [

                                    'id' =>
                                        $stock
                                            ->branch
                                            ?->id,

                                    'name' =>
                                        $stock
                                            ->branch
                                            ?->name,

                                ],

                                'warehouse' => [

                                    'id' =>
                                        $stock
                                            ->warehouse
                                            ?->id,

                                    'name' =>
                                        $stock
                                            ->warehouse
                                            ?->name,

                                ],

                                'reseller' => [

                                    'id' =>
                                        $stock
                                            ->reseller
                                            ?->id,

                                    'code' =>
                                        $stock
                                            ->reseller
                                            ?->reseller_code,

                                    'name' =>
                                        $stock
                                            ->reseller
                                            ?->name,

                                ],

                            ];

                        }
                    )
                    ->values(),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render(
            'Resellers/ConsignmentReturn/Create',

            [

                'title' =>
                    'Create Consignment Return',

                'previewNumber' =>
                    $this
                        ->codeGeneratorService
                        ->preview(
                            'consignment_return'
                        ),

                ...$this->formData(),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        ConsignmentReturnHeader $consignmentReturn
    ) {

        abort_if(
            ! in_array(
                $consignmentReturn->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected consignment return can be edited.'
        );


        $consignmentReturn->load([

            'branch',

            'warehouse',

            'reseller',

            'settlement',

            'details.variant.product',

            'details.unit',

        ]);


        return Inertia::render(
            'Resellers/ConsignmentReturn/Edit',

            [

                'title' =>
                    'Edit Consignment Return',

                'consignmentReturn' =>
                    $consignmentReturn,

                ...$this->formData(),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreConsignmentReturnRequest $request
    ) {

        $data =
            $request->validated();


        $this
            ->consignmentReturnService
            ->createConsignmentReturn(
                $data
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment return created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        ConsignmentReturnHeader $consignmentReturn
    ) {

        $consignmentReturn->load([

            'company',

            'branch',

            'warehouse',

            'reseller',

            'settlement',

            'creator',

            'updater',

            'poster',

            'canceller',

            'details.variant.product',

            'details.unit',

            'activities.performer',

            'inventoryMovements',

            'inventoryMovements.variant.product',

            'inventoryMovements.unit',

        ]);


        return Inertia::render(
            'Resellers/ConsignmentReturn/Show',

            [

                'consignmentReturn' =>
                    $consignmentReturn,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Data
    |--------------------------------------------------------------------------
    */

    public function showData(
        ConsignmentReturnHeader $consignmentReturn
    ) {

        $consignmentReturn->load([

            'company',

            'branch',

            'warehouse',

            'reseller',

            'settlement',

            'creator',

            'updater',

            'poster',

            'canceller',

            'details.variant.product',

            'details.unit',

            'activities.performer',

            'inventoryMovements',

            'inventoryMovements.variant.product',

            'inventoryMovements.unit',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Prepare Consignment Return Details
        |--------------------------------------------------------------------------
        */

        $consignmentReturn
            ->details
            ->transform(
                function ($detail) {

                    $detail->returned_qty =
                        (float) (
                            $detail->returned_qty
                            ?? 0
                        );

                    $detail->unit_cost =
                        (float) (
                            $detail->unit_cost
                            ?? 0
                        );

                    $detail->total_cost =
                        (float) (
                            $detail->total_cost
                            ?? 0
                        );

                    return $detail;

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'data' =>
                $consignmentReturn,

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateConsignmentReturnRequest $request,
        ConsignmentReturnHeader $consignmentReturn
    ) {

        abort_if(
            ! in_array(
                $consignmentReturn->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected consignment return can be updated.'
        );


        $data =
            $request->validated();


        $this
            ->consignmentReturnService
            ->updateConsignmentReturn(
                $consignmentReturn,
                $data
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment return updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        ConsignmentReturnHeader $consignmentReturn
    ) {

        $this
            ->consignmentReturnService
            ->submitConsignmentReturn(
                $consignmentReturn
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment return submitted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        ConsignmentReturnHeader $consignmentReturn
    ) {

        $this
            ->consignmentReturnService
            ->approveConsignmentReturn(
                $consignmentReturn
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment return approved successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        RejectConsignmentReturnRequest $request,
        ConsignmentReturnHeader $consignmentReturn
    ) {

        $this
            ->consignmentReturnService
            ->rejectConsignmentReturn(
                $consignmentReturn,
                $request
                    ->validated()['reason']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment return rejected successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        ConsignmentReturnHeader $consignmentReturn
    ) {

        $this
            ->consignmentReturnService
            ->postConsignmentReturn(
                $consignmentReturn
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment return posted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        ConsignmentReturnHeader $consignmentReturn
    ) {

        $validated =
            $request->validate([

                'reason' => [

                    'required',

                    'string',

                    'max:1000',

                ],

            ]);


        $this
            ->consignmentReturnService
            ->cancelConsignmentReturn(
                $consignmentReturn,
                $validated['reason']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment return cancelled successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */

    public function bulkDelete(
        Request $request
    ) {

        $validated =
            $request->validate([

                'ids' => [

                    'required',

                    'array',

                    'min:1',

                ],

                'ids.*' => [

                    'required',

                    'integer',

                    'exists:consignment_return_headers,id',

                ],

            ]);


        $this
            ->consignmentReturnService
            ->deleteConsignmentReturns(
                $validated['ids']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment returns deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ConsignmentReturnHeader $consignmentReturn
    ) {

        $this
            ->consignmentReturnService
            ->deleteConsignmentReturns([
                $consignmentReturn->id,
            ]);


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment return deleted successfully.'
            );
    }

    /*
|--------------------------------------------------------------------------
| Print
|--------------------------------------------------------------------------
*/

public function print(
    ConsignmentReturnHeader $consignmentReturn
) {
    $consignmentReturn->load([
        'company',
        'branch',
        'warehouse',
        'reseller',
        'settlement',
        'creator',
        'poster',
        'details.variant.product',
        'details.unit',
    ]);

    return view(
        'print.Reseller.Reports.consignment-return',
        [
            'consignmentReturn' =>
                $consignmentReturn,
        ]
    );
}
}