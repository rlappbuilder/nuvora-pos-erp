<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionBom;
use App\Models\Production\ProductionOrder\ProductionOrderHeader;
use App\Services\Core\CodeGeneratorService;
use App\Services\Production\ProductionOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductionOrderController extends Controller
{
    protected ProductionOrderService $productionOrderService;

    protected CodeGeneratorService $codeGeneratorService;

    public function __construct(
        ProductionOrderService $productionOrderService,
        CodeGeneratorService $codeGeneratorService
    ) {
        $this->productionOrderService = $productionOrderService;
        $this->codeGeneratorService = $codeGeneratorService;
    }

    /*
    |--------------------------------------------------------------------------
    | Preview Code
    |--------------------------------------------------------------------------
    */

    public function previewCode()
    {
        return response()->json([
            'code' => $this->codeGeneratorService->preview(
                'production_order'
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
        $companyId = auth()->user()->company_id;

        $filters = [
            'search' => $request->input('search'),
            'branch_id' => $request->input('branch_id'),
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'per_page' => $request->input('per_page', 10),
            'sort' => $request->input('sort', 'created_at'),
            'direction' => $request->input('direction', 'desc'),
        ];

        $query = ProductionOrderHeader::query()
            ->with([
                'branch',
                'productionBom',
                'productionBomVersion',
                'sourceWarehouse',
                'destinationWarehouse',
                'details.productVariant.product',
                'details.unit',
                'creator',
            ])
            ->where('company_id', $companyId);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $query->when($filters['search'], function ($q, $search) {

            $q->where(function ($sub) use ($search) {

                $sub->where('number', 'like', "%{$search}%")
                    ->orWhereHas('productionBom', function ($bom) use ($search) {
                        $bom->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas(
                        'details.productVariant',
                        function ($variant) use ($search) {
                            $variant->where('sku', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhereHas(
                                    'product',
                                    fn ($product) => $product->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                        }
                    );

            });

        });

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $query->when(
            $filters['branch_id'],
            fn ($q, $branchId) => $q->where(
                'branch_id',
                $branchId
            )
        );

        $query->when(
            $filters['status'],
            fn ($q, $status) => $q->where(
                'status',
                $status
            )
        );

        $query->when(
            $filters['date_from'],
            fn ($q, $date) => $q->whereDate(
                'order_date',
                '>=',
                $date
            )
        );

        $query->when(
            $filters['date_to'],
            fn ($q, $date) => $q->whereDate(
                'order_date',
                '<=',
                $date
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'number',
            'order_date',
            'planned_start_date',
            'planned_end_date',
            'status',
            'created_at',
        ];

        $sort = in_array($filters['sort'], $allowedSorts, true)
            ? $filters['sort']
            : 'created_at';

        $direction = strtolower($filters['direction']) === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sort, $direction);

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statisticsQuery = ProductionOrderHeader::query()
            ->where('company_id', $companyId);

        $statistics = [
            'total' => (clone $statisticsQuery)->count(),

            'draft' => (clone $statisticsQuery)
                ->where('status', 'Draft')
                ->count(),

            'submitted' => (clone $statisticsQuery)
                ->where('status', 'Submitted')
                ->count(),

            'approved' => (clone $statisticsQuery)
                ->where('status', 'Approved')
                ->count(),

            'in_progress' => (clone $statisticsQuery)
                ->where('status', 'In Progress')
                ->count(),

            'completed' => (clone $statisticsQuery)
                ->where('status', 'Completed')
                ->count(),

            'rejected' => (clone $statisticsQuery)
                ->where('status', 'Rejected')
                ->count(),

            'cancelled' => (clone $statisticsQuery)
                ->where('status', 'Cancelled')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = (int) $filters['per_page'];

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $orders = $query
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Production/Order/Index', [
            'title' => 'Production Orders',
            'orders' => $orders,
            'statistics' => $statistics,
            'branches' => $this->getBranches(),
            'filters' => $filters,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    protected function formData(
        ?ProductionOrderHeader $order = null
    ): array {

        $companyId = auth()->user()->company_id;

        return [
            'previewCode' => $this->codeGeneratorService->preview(
                'production_order'
            ),

            'branches' => $this->getBranches(),

            'warehouses' => $this->getWarehouses(),

            'productionBoms' => ProductionBom::query()
                ->with([
                    'branch',
                    'productVariant.product',
                    'versions' => function ($query) {
                        $query->where('status', 'active')
                            ->with([
                                'outputUnit',
                                'operations.productionOperation',
                                'materials.componentVariant.product',
                                'materials.unit',
                                'materials.productionBomOperation.productionOperation',
                            ])
                            ->orderByDesc('version');
                    },
                ])
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->whereHas('versions', function ($query) {
                    $query->where('status', 'active');
                })
                ->orderBy('code')
                ->get(),

            'order' => $order
                ? $this->productionOrderService->getOrderDetail($order->id)
                : null,
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
            'Production/Order/Create',
            array_merge(
                [
                    'title' => 'Create Production Order',
                    'mode' => 'create',
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            'production_bom_id' => [
                'required',
                'integer',
                'exists:production_boms,id',
            ],

            'production_bom_version_id' => [
                'required',
                'integer',
                'exists:production_bom_versions,id',
            ],

            'source_warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],

            'destination_warehouse_id' => [
                'required',
                'integer',
                'different:source_warehouse_id',
                'exists:warehouses,id',
            ],

            'order_date' => [
                'required',
                'date',
            ],

            'planned_start_date' => [
                'nullable',
                'date',
            ],

            'planned_end_date' => [
                'nullable',
                'date',
                'after_or_equal:planned_start_date',
            ],

            'planned_quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $order = $this->productionOrderService->create($validated);

        return redirect()
            ->route('production-orders.show', $order->id)
            ->with(
                'success',
                'Production Order created successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(ProductionOrderHeader $order)
    {
        abort_unless(
            (int) $order->company_id ===
            (int) auth()->user()->company_id,
            403
        );

        $order = $this->productionOrderService->getOrderDetail(
            $order->id
        );

        return Inertia::render('Production/Order/Show', [
            'title' => 'Production Order Detail',
            'order' => $order,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(ProductionOrderHeader $order)
    {
        abort_unless(
            (int) $order->company_id ===
            (int) auth()->user()->company_id,
            403
        );

        abort_unless($order->status === 'Draft', 403);

        $order = $this->productionOrderService->getOrderDetail(
            $order->id
        );

        return Inertia::render(
            'Production/Order/Edit',
            array_merge(
                [
                    'title' => 'Edit Production Order',
                    'mode' => 'edit',
                ],
                $this->formData($order)
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Draft
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        ProductionOrderHeader $order
    ) {

        abort_unless(
            (int) $order->company_id ===
            (int) auth()->user()->company_id,
            403
        );

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            'production_bom_id' => [
                'required',
                'integer',
                'exists:production_boms,id',
            ],

            'production_bom_version_id' => [
                'required',
                'integer',
                'exists:production_bom_versions,id',
            ],

            'source_warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],

            'destination_warehouse_id' => [
                'required',
                'integer',
                'different:source_warehouse_id',
                'exists:warehouses,id',
            ],

            'order_date' => [
                'required',
                'date',
            ],

            'planned_start_date' => [
                'nullable',
                'date',
            ],

            'planned_end_date' => [
                'nullable',
                'date',
                'after_or_equal:planned_start_date',
            ],

            'planned_quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $order = $this->productionOrderService->updateDraft(
            $order,
            $validated
        );

        return redirect()
            ->route('production-orders.show', $order->id)
            ->with(
                'success',
                'Production Order updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Master Data Helpers
    |--------------------------------------------------------------------------
    */

    protected function getBranches()
    {
        return DB::table('branches')
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ])
            ->map(fn ($branch) => [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'label' => $branch->code . ' - ' . $branch->name,
            ]);
    }

    protected function getWarehouses()
    {
        return DB::table('warehouses')
            ->where('status', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get([
                'id',
                'branch_id',
                'code',
                'name',
                'warehouse_type',
            ])
            ->map(fn ($warehouse) => [
                'id' => $warehouse->id,
                'branch_id' => $warehouse->branch_id,
                'code' => $warehouse->code,
                'name' => $warehouse->name,
                'warehouse_type' => $warehouse->warehouse_type,
                'label' => $warehouse->code . ' - ' . $warehouse->name,
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(ProductionOrderHeader $order)
    {
        $this->authorizeOrder($order);

        $order = $this->productionOrderService->submit($order);

        return redirect()
            ->route('production-orders.show', $order->id)
            ->with('success', 'Production Order submitted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(ProductionOrderHeader $order)
    {
        $this->authorizeOrder($order);

        $order = $this->productionOrderService->approve($order);

        return redirect()
            ->route('production-orders.show', $order->id)
            ->with('success', 'Production Order approved successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        Request $request,
        ProductionOrderHeader $order
    ) {
        $this->authorizeOrder($order);

        $validated = $request->validate([
            'rejected_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $order = $this->productionOrderService->reject(
            $order,
            $validated['rejected_reason']
        );

        return redirect()
            ->route('production-orders.show', $order->id)
            ->with('success', 'Production Order rejected successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Start Production
    |--------------------------------------------------------------------------
    */

    public function startProduction(ProductionOrderHeader $order)
    {
        $this->authorizeOrder($order);

        $order = $this->productionOrderService->startProduction($order);

        return redirect()
            ->route('production-orders.show', $order->id)
            ->with('success', 'Production process started successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        ProductionOrderHeader $order
    ) {
        $this->authorizeOrder($order);

        $validated = $request->validate([
            'cancelled_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $order = $this->productionOrderService->cancel(
            $order,
            $validated['cancelled_reason']
        );

        return redirect()
            ->route('production-orders.show', $order->id)
            ->with('success', 'Production Order cancelled successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization Helper
    |--------------------------------------------------------------------------
    */

    protected function authorizeOrder(
        ProductionOrderHeader $order
    ): void {
        abort_unless(
            (int) $order->company_id ===
            (int) auth()->user()->company_id,
            403
        );
    }
}