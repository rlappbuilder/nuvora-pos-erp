<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockIssue;
use App\Models\StockIssueDetail;
use App\Models\Warehouse;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use Carbon\Carbon;

class StockIssueController extends Controller
{
 public function index()
    {
        $startDate =

            Carbon::now()

            ->startOfMonth()

            ->format(

                'Y-m-d'

            );

        $endDate =

            Carbon::now()

            ->endOfMonth()

            ->format(

                'Y-m-d'

            );

        $query =

            StockIssue::query()

            ->with([

                'warehouse',

                'creator',

                'details',

            ])

            ->withCount(

                'details'

            )

            ->when(

                request(

                    'search'

                ),

                function (

                    $q

                ) {

                    $q->where(

                        'issue_number',

                        'like',

                        '%'

                        .

                        request(

                            'search'

                        )

                        .

                        '%'

                    );

                }

            )

            ->when(

                request(

                    'status'

                ),

                function (

                    $q

                ) {

                    $q->where(

                        'status',

                        request(

                            'status'

                        )

                    );

                }

            )

            ->when(

                request(

                    'date'

                ),

                function (

                    $query

                ) {

                    $dates = explode(

                        ' to ',

                        request(

                            'date'

                        )

                    );

                    $from =

                        $dates[0];

                    $to =

                        $dates[1]

                        ??

                        $dates[0];

                    $query->whereBetween(

                        'issue_date',

                        [

                            $from,

                            $to,

                        ]

                    );

                }

            )

            ->when(

                !request(

                    'date'

                ),

                function (

                    $query

                ) use (

                    $startDate,

                    $endDate

                ) {

                    $query->whereBetween(

                        'issue_date',

                        [

                            $startDate,

                            $endDate,

                        ]

                    );

                }

            )

            ->latest();

        $summaryQuery =

            clone $query;

        $issues =

            $query

            ->paginate(10)

            ->through(

                function (

                    $issue

                ) {

                    return [

                        'id' =>

                            $issue->id,

                        'issue_number' =>

                            $issue->issue_number,

                        'issue_date' =>

                            $issue->issue_date,

                        'warehouse' =>

                            $issue

                            ->warehouse

                            ?->name,

                        'issue_type' =>

                            $issue->issue_type,

                        'status' =>

                            $issue->status,

                        'items' =>

                            $issue

                            ->details_count,

                        'total_qty' =>

                            $issue

                            ->details

                            ->sum(

                                'qty'

                            ),

                        'total_cost' =>

                            $issue

                            ->details

                            ->sum(

                                'total_cost'

                            ),

                        'creator' =>

                            $issue

                            ->creator

                            ?->name,

                    ];

                }

            )

            ->withQueryString();

        return Inertia::render(

            'Inventory/Issue/Index',

            [

                'issues' =>

                    $issues,

                'filters' => [

                    'search' =>

                        request(

                            'search'

                        ),

                    'status' =>

                        request(

                            'status'

                        ),

                    'date' =>

                        request(

                            'date'

                        ),

                ],

                'summary' => [

                    'draft' =>

                        (

                            clone $summaryQuery

                        )

                        ->where(

                            'status',

                            'Draft'

                        )

                        ->count(),

                    'posted' =>

                        (

                            clone $summaryQuery

                        )

                        ->where(

                            'status',

                            'Posted'

                        )

                        ->count(),

                    'completed' =>

                        (

                            clone $summaryQuery

                        )

                        ->where(

                            'status',

                            'Completed'

                        )

                        ->count(),

                    'cancelled' =>

                        (

                            clone $summaryQuery

                        )

                        ->where(

                            'status',

                            'Cancelled'

                        )

                        ->count(),

                ],

            ]

        );
    }

    public function create()
{
    $products =

        ProductStock::with(

            'product'

        )

        ->where(

            'qty',

            '>',

            0

        )

        ->get()

        ->map(

            function (

                $stock

            ) {

                $unitCost =

                    InventoryMovement::where(

                        'product_id',

                        $stock->product_id

                    )

                    ->where(

                        'warehouse_id',

                        $stock->warehouse_id

                    )

                    ->latest(

                        'id'

                    )

                    ->value(

                        'unit_cost'

                    )

                    ?? 0;

                return [

                    'id' =>

                        $stock->product_id,

                    'sku' =>

                        $stock->product?->sku,

                    'name' =>

                        $stock->product?->name,

                    'warehouse_id' =>

                        $stock->warehouse_id,

                    'qty' =>

                        $stock->qty,

                    'unit_cost' =>

                        $unitCost,

                ];

            }

        )

        ->values();

    return Inertia::render(

        'Inventory/Issue/Create',

        [

            'issueNumber' =>

                StockIssue::generateNumber(),

            'warehouses' =>

                Warehouse::orderBy(

                    'name'

                )

                ->get(

                    [

                        'id',

                        'name',

                    ]

                ),

            'products' =>

                $products,

            'issueTypes' =>

                collect(

                    config(

                        'inventory.issue_types'

                    )

                )

                ->map(

                    fn (

                        $type

                    ) => [

                        'id' =>

                            $type,

                        'name' =>

                            $type,

                    ]

                )

                ->values(),

        ]

    );
}
public function store(
    Request $request
)
{
    $request->validate([

        'issue_date' =>

            'required|date',

        'warehouse_id' =>

            'required|exists:warehouses,id',

        'issue_type' =>

            'required|string',

        'reference_number' =>

            'nullable|string|max:100',

        'remarks' =>

            'nullable|string',

        'items' =>

            'required|array|min:1',

        'items.*.product_id' =>

            'required|exists:products,id',

        'items.*.qty' =>

            'required|numeric|min:0.01',

        'items.*.unit_cost' =>

            'required|numeric|min:0',

        'items.*.total_cost' =>

            'required|numeric|min:0',

    ]);

    $issue =

        StockIssue::create([

            'issue_number' =>

                'ISS'

                .

                str_pad(

                    StockIssue::count()

                    + 1,

                    5,

                    '0',

                    STR_PAD_LEFT

                ),

            'issue_date' =>

                $request->issue_date,

            'warehouse_id' =>

                $request->warehouse_id,

            'issue_type' =>

                $request->issue_type,

            'reference_number' =>

                $request->reference_number,

            'remarks' =>

                $request->remarks,

            'total_qty' =>

                collect(

                    $request->items

                )->sum(

                    'qty'

                ),

            'total_cost' =>

                collect(

                    $request->items

                )->sum(

                    'total_cost'

                ),

            'status' =>

                'Draft',

            'created_by' =>

                auth()->id(),

        ]);

    foreach (

        $request->items

        as $item

    ) {

        StockIssueDetail::create([

            'stock_issue_id' =>

                $issue->id,

            'product_id' =>

                $item['product_id'],

            'qty' =>

                $item['qty'],

            'unit_cost' =>

                $item['unit_cost'],

            'total_cost' =>

                $item['total_cost'],

            'remarks' =>

                $item['remarks']

                ??

                null,

        ]);

    }

    return redirect()

        ->route(

            'stock-issues.show',

            $issue

        )

        ->with(

            'success',

            'Stock Issue created successfully.'

        );
}
public function show(
    StockIssue $stockIssue
)
{
    $stockIssue->load(

        'warehouse',

        'details.product',

        'creator',

        'poster',

        'completer',

        'canceller'

    );

    return Inertia::render(

        'Inventory/Issue/Show',

        [

            'issue' =>

                $stockIssue,

        ]

    );
}
public function post(
    StockIssue $stockIssue
)
{
    if (

    $stockIssue->status

    !==

    'Draft'

) {

    return back();

}

DB::transaction(

    function ()

    use (

        $stockIssue

    ) {

        foreach (

            $stockIssue->details

            as

            $detail

        ) {

            $stock =

                ProductStock::where(

                    'product_id',

                    $detail->product_id

                )

                ->where(

                    'warehouse_id',

                    $stockIssue->warehouse_id

                )

                ->lockForUpdate()

                ->first();

            if (

                !$stock ||

                $stock->qty

                <

                $detail->qty

            ) {

                throw new \Exception(

                    'Insufficient stock.'

                );

            }

            $stock->decrement(

                'qty',

                $detail->qty

            );

            InventoryMovement::create([

                'product_id' =>

                    $detail->product_id,

                'warehouse_id' =>

                    $stockIssue->warehouse_id,

                'reference_type' =>

                    'ISSUE',

                'reference_id' =>

                    $stockIssue->id,

                'reference_number' =>

                    $stockIssue->issue_number,

                'qty_in' =>

                    0,

                'qty_out' =>

                    $detail->qty,

                'balance_qty' =>

                    $stock

                    ->fresh()

                    ->qty,

                'unit_cost' =>

                    $detail->unit_cost,

                'total_cost' =>

                    $detail->total_cost,

                'transaction_date' =>

                    now(),

                'created_by' =>

                    auth()->id(),

            ]);

        }

        $stockIssue->update([

            'status' =>

                'Posted',

            'posted_by' =>

                auth()->id(),

            'posted_at' =>

                now(),

        ]);

    }

);

return redirect()

->route(

    'stock-issues.show',

    $stockIssue

)

->with(

    'success',

    'Stock Issue posted successfully.'

);



}
public function complete(
    StockIssue $stockIssue
)
{
    if (

        $stockIssue->status

        !==

        'Posted'

    ) {

        return back()

            ->with(

                'error',

                'Only posted documents can be completed.'

            );

    }

    $stockIssue->update([

        'status' =>

            'Completed',

        'completed_by' =>

            auth()->id(),

        'completed_at' =>

            now(),

    ]);

    return back()

        ->with(

            'success',

            'Stock Issue completed successfully.'

        );

}
public function cancel(
    Request $request,
    StockIssue $stockIssue
)
{
    if (

        $stockIssue->status

        !==

        'Draft'

    ) {

        return back()

            ->with(

                'error',

                'Only draft documents can be cancelled.'

            );

    }

    $request->validate([

        'cancel_reason' =>

            'required|string|max:1000',

    ]);

    $stockIssue->update([

        'status' =>

            'Cancelled',

        'cancel_reason' =>

            $request->cancel_reason,

        'cancelled_by' =>

            auth()->id(),

        'cancelled_at' =>

            now(),

    ]);

   return redirect()

->route(

    'stock-issues.show',

    $stockIssue

)

->with(

    'success',

    'Stock Issue cancelled successfully.'

);

}
public function getWarehouseStocks(
    Warehouse $warehouse
)
{
    $products =

        ProductStock::with(

            'product'

        )

        ->where(

            'warehouse_id',

            $warehouse->id

        )

        ->where(

            'qty',

            '>',

            0

        )

        ->get()

        ->map(

            function (

                $stock

            ) {

                return [

                    'id' =>

                        $stock

                        ->product_id,

                    'sku' =>

                        $stock

                        ->product

                        ?->sku,

                    'name' =>

                        $stock

                        ->product

                        ?->name,

                    'qty' =>

                        $stock->qty,

                    'unit_cost' =>

                        InventoryMovement::where(

                            'product_id',

                            $stock->product_id

                        )

                        ->where(

                            'warehouse_id',

                            $stock->warehouse_id

                        )

                        ->latest(

                            'id'

                        )

                        ->value(

                            'unit_cost'

                        )

                        ??

                        0,

                ];

            }

        )

        ->values();

    return response()->json(

        $products

    );

}
}

   
