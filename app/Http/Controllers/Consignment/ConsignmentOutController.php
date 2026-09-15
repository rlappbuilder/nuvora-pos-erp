<?php

namespace App\Http\Controllers\Consignment;

use App\Http\Controllers\Controller;

use App\Http\Requests\Reseller\ConsignmentOut\StoreConsignmentOutRequest;
use App\Http\Requests\Reseller\ConsignmentOut\UpdateConsignmentOutRequest;
use App\Http\Requests\Reseller\ConsignmentOut\RejectConsignmentOutRequest;

use App\Models\Reseller\ConsignmentOut\ConsignmentOut;
use App\Models\Reseller\ConsignmentOut\ConsignmentOutDetail;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Unit;
use App\Models\Reseller\Reseller;
use App\Models\Product\ProductVariant;
use App\Services\Consignment\ConsignmentOutService;
use App\Services\Core\CodeGeneratorService;
use App\Models\Reseller\ResellerPrice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Resellers\Reports\ConsignmentOutExport;
class ConsignmentOutController extends Controller
{
    public function __construct(
        protected ConsignmentOutService $consignmentOutService,
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
                        'consignment_out'
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
            ConsignmentOut::query()
                ->with([
                    'reseller',
                    'branch',
                    'warehouse',
                    'details.variant.product',
                    'details.unit',
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
                                        'consignment_out_number',
                                        'like',
                                        "%{$search}%"
                                    )

                                    ->orWhere(
                                        'reference_number',
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
                | Transaction Date Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('date_from'),
                    function ($query) use ($request) {

                        $query->whereDate(
                            'transaction_date',
                            '>=',
                            $request->date_from
                        );

                    }
                )

                ->when(
                    $request->filled('date_to'),
                    function ($query) use ($request) {

                        $query->whereDate(
                            'transaction_date',
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
        | Pagination
        |--------------------------------------------------------------------------
        */

        $consignmentOuts =
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

        $consignmentOuts
            ->getCollection()
            ->transform(
                function ($consignmentOut) {

                    $consignmentOut->total_items =
                        $consignmentOut
                            ->details
                            ->sum('qty');

                    $consignmentOut->total_transaction =
                        $consignmentOut
                            ->details
                            ->sum('total_price');

                    return $consignmentOut;

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Filtered Transaction Summary
        |--------------------------------------------------------------------------
        */

        $totalTransaction =
            ConsignmentOutDetail::query()
                ->whereIn(
                    'consignment_out_id',
                    $filteredIds
                )
                ->sum('total_price');


        $totalItems =
            ConsignmentOutDetail::query()
                ->whereIn(
                    'consignment_out_id',
                    $filteredIds
                )
                ->sum('qty');


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
            'Resellers/ConsignmentOut/Index',

            array_merge(

                [

                    'title' =>
                        'Consignment Out',

                    'consignmentOuts' =>
                        $consignmentOuts,

                    'statistics' =>
                        $statistics,

                    'previewNumber' =>
                        $this
                            ->codeGeneratorService
                            ->preview(
                                'consignment_out'
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
                    'reseller_code',
                    'name',
                ])
                ->map(
                    fn ($reseller) => [

                        'id' =>
                            $reseller->id,

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
                            $price->price,

                    ]
                )
                ->values(),


        /*
        |--------------------------------------------------------------------------
        | Product Variant
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
                                            $variantUnit
                                                ->conversion_factor,

                                        'is_base' =>
                                            $variantUnit
                                                ->is_base,

                                        'is_default' =>
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
            'Resellers/ConsignmentOut/Create',

            array_merge(

                [

                    'title' =>
                        'Create Consignment Out',

                    'previewNumber' =>
                        $this
                            ->codeGeneratorService
                            ->preview(
                                'consignment_out'
                            ),

                ],

                $this->formData()

            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        ConsignmentOut $consignmentOut
    ) {

        abort_if(
            ! in_array(
                $consignmentOut->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected consignment out can be edited.'
        );


        $consignmentOut->load([

            'company',

            'branch',

            'warehouse',

            'reseller',

            'details.variant.product',

            'details.unit',

        ]);

dd(
    $consignmentOut->consignment_out_number
);
        return Inertia::render(
            'Resellers/ConsignmentOut/Edit',

            array_merge(

                [

                    'title' =>
                        'Edit Consignment Out',

                    'consignmentOut' =>
                        $consignmentOut,

                ],

                $this->formData()

            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreConsignmentOutRequest $request
    ) {

        $data =
            $request->validated();


        $branch =
            Branch::findOrFail(
                $data['branch_id']
            );


        $data['company_id'] =
            $branch->company_id;


        $this
            ->consignmentOutService
            ->createConsignmentOut(
                $data
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment out created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        ConsignmentOut $consignmentOut
    ) {

        $consignmentOut->load([

            'company',

            'branch',

            'warehouse',

            'reseller',

            'creator',

            'updater',

            'submitter',

            'approver',

            'rejector',

            'poster',

            'canceller',

            'details.variant.product',

            'details.unit',

            'activities.performer',

            'movements',

        ]);


        return Inertia::render(
            'Resellers/ConsignmentOut/Show',

            [

                'consignmentOut' =>
                    $consignmentOut,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateConsignmentOutRequest $request,
        ConsignmentOut $consignmentOut
    ) {

        abort_if(
            ! in_array(
                $consignmentOut->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected consignment out can be updated.'
        );


        $data =
            $request->validated();


        $branch =
            Branch::findOrFail(
                $data['branch_id']
            );


        $data['company_id'] =
            $branch->company_id;


        $this
            ->consignmentOutService
            ->updateConsignmentOut(
                $consignmentOut,
                $data
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment out updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        ConsignmentOut $consignmentOut
    ) {

        $this
            ->consignmentOutService
            ->submitConsignmentOut(
                $consignmentOut
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment out submitted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        ConsignmentOut $consignmentOut
    ) {

        $this
            ->consignmentOutService
            ->approveConsignmentOut(
                $consignmentOut
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment out approved successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        RejectConsignmentOutRequest $request,
        ConsignmentOut $consignmentOut
    ) {

        $this
            ->consignmentOutService
            ->rejectConsignmentOut(
                $consignmentOut,
                $request
                    ->validated()['reason']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment out rejected successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        ConsignmentOut $consignmentOut
    ) {

        $this
            ->consignmentOutService
            ->postConsignmentOut(
                $consignmentOut
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment out posted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        ConsignmentOut $consignmentOut
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
            ->consignmentOutService
            ->cancelConsignmentOut(
                $consignmentOut,
                $validated['reason']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment out cancelled successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ConsignmentOut $consignmentOut
    ) {

        $this
            ->consignmentOutService
            ->deleteConsignmentOuts([

                $consignmentOut->id,

            ]);


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment out deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate
    |--------------------------------------------------------------------------
    */

    public function duplicate(
        ConsignmentOut $consignmentOut
    ) {

        $this
            ->consignmentOutService
            ->duplicateConsignmentOut(
                $consignmentOut
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment out duplicated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Data
    |--------------------------------------------------------------------------
    */

    public function showData(
        ConsignmentOut $consignmentOut
    ) {

        $consignmentOut->load([

            'company',

            'branch',

            'warehouse',

            'reseller',

            'creator',

            'updater',

            'submitter',

            'approver',

            'rejector',

            'poster',

            'canceller',

            'details.variant.product',

            'details.unit',

            'activities.performer',

            'movements',

        ]);


        return response()->json([

            'data' =>
                $consignmentOut,

        ]);

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
                    'exists:consignment_outs,id',

                ],

            ]);


        $this
            ->consignmentOutService
            ->deleteConsignmentOuts(
                $validated['ids']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment outs deleted successfully.'
            );
    }
private function loadConsignmentOutForExport(
    ConsignmentOut $consignmentOut
): ConsignmentOut {

    return $consignmentOut->load([
        'company',
        'branch',
        'warehouse',
        'reseller',
        'details.variant.product',
        'details.unit',
    ]);
}

public function print(
    ConsignmentOut $consignmentOut
) {

    $consignmentOut =
        $this->loadConsignmentOutForExport(
            $consignmentOut
        );

    return view(
        'print.Reseller.Reports.consigment-out',
        [
            'consignmentOut' =>
                $consignmentOut,
        ]
    );
}
public function pdf(
    ConsignmentOut $consignmentOut
) {

    $consignmentOut =
        $this->loadConsignmentOutForExport(
            $consignmentOut
        );

    return Pdf::loadView(
        'pdf.Reseller.Reports.consigment-out',
        [
            'consignmentOut' =>
                $consignmentOut,
        ]
    )
        ->setPaper(
            'a4',
            'portrait'
        )
        ->download(
            'consignment-out-' .
            $consignmentOut->consignment_out_number .
            '.pdf'
        );
}
public function excel(
    ConsignmentOut $consignmentOut
) {

    $consignmentOut =
        $this->loadConsignmentOutForExport(
            $consignmentOut
        );

    return Excel::download(
        new ConsignmentOutExport(
            $consignmentOut
        ),
        'consignment-out-' .
        $consignmentOut->consignment_out_number .
        '.xlsx'
    );
}
}