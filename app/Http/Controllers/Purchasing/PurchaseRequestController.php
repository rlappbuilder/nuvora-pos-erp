<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchasing\PurchaseRequest\StorePurchaseRequest;
use App\Http\Requests\Purchasing\PurchaseRequest\UpdatePurchaseRequest;
use App\Http\Requests\Purchasing\PurchaseRequest\RejectPurchaseRequest;
use App\Models\Purchasing\PurchaseRequestHeader;
use App\Services\Purchasing\PurchaseRequestService;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Unit;
use App\Models\Product\ProductVariant;

class PurchaseRequestController extends Controller
{
    public function __construct(
        protected PurchaseRequestService $purchaseRequestService,
        protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query =
            PurchaseRequestHeader::query()
                ->with([
                    'branch',
                    'warehouse',
                    'details.productVariant.product',
                    'details.unit',
                ])

                ->when(
                    $request->filled('search'),
                    function ($query) use ($request) {

                        $search =
                            $request->search;

                        $query->where(
                            function ($query) use ($search) {

                                $query
                                    ->where(
                                        'number',
                                        'like',
                                        "%{$search}%"
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
                                        'details.productVariant',
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

                ->when(
                    $request->filled('branch_id'),
                    function ($query) use ($request) {

                        $query->where(
                            'branch_id',
                            $request->branch_id
                        );

                    }
                )

                ->when(
                    $request->filled('warehouse_id'),
                    function ($query) use ($request) {

                        $query->where(
                            'warehouse_id',
                            $request->warehouse_id
                        );

                    }
                )

                ->when(
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
        | Pagination
        |--------------------------------------------------------------------------
        */

        $purchaseRequests =
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

        $purchaseRequests
            ->getCollection()
            ->transform(
                function ($purchaseRequest) {

                    $purchaseRequest->total_items =
                        $purchaseRequest
                            ->details
                            ->count();

                    $purchaseRequest->total_quantity =
                        $purchaseRequest
                            ->details
                            ->sum('qty');

                    return $purchaseRequest;
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
            'Purchasing/PurchaseRequest/Index',
            array_merge(
                [

                    'title' =>
                        'Purchase Request',

                    'purchaseRequests' =>
                        $purchaseRequests,

                    'statistics' =>
                        $statistics,

                    'previewNumber' =>
                        $this
                            ->codeGeneratorService
                            ->preview(
                                'purchase_request'
                            ),

                    'filters' =>
                        $request->only([
                            'search',
                            'branch_id',
                            'warehouse_id',
                            'status',
                            'per_page',
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

            /*
            |--------------------------------------------------------------------------
            | Priorities
            |--------------------------------------------------------------------------
            */

            'priorities' => [

                [
                    'value' =>
                        'Low',

                    'label' =>
                        'Low',
                ],

                [
                    'value' =>
                        'Normal',

                    'label' =>
                        'Normal',
                ],

                [
                    'value' =>
                        'High',

                    'label' =>
                        'High',
                ],

                [
                    'value' =>
                        'Urgent',

                    'label' =>
                        'Urgent',
                ],

            ],

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StorePurchaseRequest $request
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
            ->purchaseRequestService
            ->createPurchaseRequest(
                $data
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase request created successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        PurchaseRequestHeader $purchaseRequest
    ) {

        $purchaseRequest->load([
            'branch',
            'warehouse',
            'details.productVariant.product',
            'details.unit',
        ]);

        return Inertia::render(
           'Purchasing/PurchaseRequest/Show',
       
           [
               'purchaseRequest' =>
                    $purchaseRequest,
           ]
       );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdatePurchaseRequest $request,
        PurchaseRequestHeader $purchaseRequest
    ) {

        abort_if(
            ! in_array(
                $purchaseRequest->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected purchase request can be updated.'
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
            ->purchaseRequestService
            ->updatePurchaseRequest(
                $purchaseRequest,
                $data
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase request updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        PurchaseRequestHeader $purchaseRequest
    ) {

        $this
            ->purchaseRequestService
            ->submitPurchaseRequest(
                $purchaseRequest
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase request submitted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        PurchaseRequestHeader $purchaseRequest
    ) {

        $this
            ->purchaseRequestService
            ->approvePurchaseRequest(
                $purchaseRequest
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase request approved successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        RejectPurchaseRequest $request,
        PurchaseRequestHeader $purchaseRequest
    ) {

        $this
            ->purchaseRequestService
            ->rejectPurchaseRequest(
                $purchaseRequest,
                $request
                    ->validated()['reason']
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase request rejected successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        PurchaseRequestHeader $purchaseRequest
    ) {

        $this
            ->purchaseRequestService
            ->cancelPurchaseRequest(
                $purchaseRequest
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase request cancelled successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        PurchaseRequestHeader $purchaseRequest
    ) {

        $this
            ->purchaseRequestService
            ->deletePurchaseRequests([
                $purchaseRequest->id,
            ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase request deleted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Duplicate
    |--------------------------------------------------------------------------
    */

    public function duplicate(
        PurchaseRequestHeader $purchaseRequest
    ) {

        $this
            ->purchaseRequestService
            ->duplicatePurchaseRequest(
                $purchaseRequest
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase request duplicated successfully.'
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
                    'exists:purchase_requests,id',
                ],

            ]);

        $this
            ->purchaseRequestService
            ->deletePurchaseRequests(
                $validated['ids']
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase requests deleted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Data
    |--------------------------------------------------------------------------
    */

public function showData(
    PurchaseRequestHeader $purchaseRequest
) {

    $purchaseRequest->load([

        'branch',

        'warehouse',

        'details.productVariant.product',

        'details.unit',

        'activities.performer',

    ]);

    return response()->json([

        'data' =>
            $purchaseRequest,

    ]);

}
}