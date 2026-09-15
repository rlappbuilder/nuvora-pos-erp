<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;
use App\Models\Reseller\Reseller;

use App\Services\Inventory\StockBalanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Resellers\Reports\ConsignmentStockExport;
class ConsignmentStockController extends Controller
{
    public function __construct(
        protected StockBalanceService $stockBalanceService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $filters = $request->only([
            'search',
            'branch_id',
            'warehouse_id',
            'reseller_id',
            'product_variant_id',
            'unit_id',
            'per_page',
            'page',
            'sort_by',
            'sort_direction',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Consignment Stock
        |--------------------------------------------------------------------------
        */

        $consignmentStock =
            $this->stockBalanceService
                ->getConsignmentStock($filters);


        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        $branches =
            Branch::query()
                ->orderBy('name')
                ->get()
                ->map(
                    fn ($branch) => [
                        'id' =>
                            $branch->id,

                        'label' =>
                            $branch->name,
                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Warehouses
        |--------------------------------------------------------------------------
        */

        $warehouses =
            Warehouse::query()
                ->orderBy('name')
                ->get()
                ->map(
                    fn ($warehouse) => [
                        'id' =>
                            $warehouse->id,

                        'label' =>
                            $warehouse->name,

                        'branch_id' =>
                            $warehouse->branch_id,
                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Resellers
        |--------------------------------------------------------------------------
        */

        $resellers =
            Reseller::query()
                ->where('status', true)
                ->orderBy('name')
                ->get()
                ->map(
                    fn ($reseller) => [
                        'id' =>
                            $reseller->id,

                        'label' =>
                            $reseller->name,

                        'code' =>
                            $reseller->reseller_code,
                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Product Variants
        |--------------------------------------------------------------------------
        */

        $variants =
            ProductVariant::query()
                ->active()
                ->with('product')
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
                                    $variant
                                        ->product
                                        ?->name,

                                    $variant->name,

                                    $variant->sku,
                                ])
                            ),
                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Units
        |--------------------------------------------------------------------------
        */

        $units =
            Unit::query()
                ->orderBy('name')
                ->get()
                ->map(
                    fn ($unit) => [
                        'id' =>
                            $unit->id,

                        'label' =>
                            $unit->name,
                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [

            'total_products' =>
                $consignmentStock
                    ->total(),

            'total_on_hand' =>
                $consignmentStock
                    ->getCollection()
                    ->sum('on_hand_qty'),

            'total_available' =>
                $consignmentStock
                    ->getCollection()
                    ->sum('available_qty'),

            'total_stock_value' =>
                $consignmentStock
                    ->getCollection()
                    ->sum('stock_value'),

            'total_consignment_value' =>
                $consignmentStock
                    ->getCollection()
                    ->sum('consignment_value'),

        ];


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Resellers/ConsignmentStock/Index',
            [

                'title' =>
                    'Consignment Stock',

                'consignmentStock' =>
                    $consignmentStock,

                'statistics' =>
                    $statistics,

                'branches' =>
                    $branches,

                'warehouses' =>
                    $warehouses,

                'resellers' =>
                    $resellers,

                'variants' =>
                    $variants,

                'units' =>
                    $units,

                'filters' =>
                    $request->only([
                        'search',
                        'branch_id',
                        'warehouse_id',
                        'reseller_id',
                        'product_variant_id',
                        'unit_id',
                        'per_page',
                        'sort_by',
                        'sort_direction',
                    ]),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Movements
    |--------------------------------------------------------------------------
    */

    public function movements(Request $request)
    {
        $validated =
            $request->validate([
                'product_variant_id' =>
                    ['required', 'integer'],

                'branch_id' =>
                    ['required', 'integer'],

                'warehouse_id' =>
                    ['required', 'integer'],

                'reseller_id' =>
                    ['required', 'integer'],

                'unit_id' =>
                    ['nullable', 'integer'],
            ]);


        $movements =
            $this->stockBalanceService
                ->getConsignmentMovements(
                    (int) $validated['product_variant_id'],
                    (int) $validated['branch_id'],
                    (int) $validated['warehouse_id'],
                    (int) $validated['reseller_id'],
                    isset($validated['unit_id'])
                        ? (int) $validated['unit_id']
                        : null
                );


        return response()->json([
            'data' =>
                $movements,
        ]);
    }
    private function exportFilters(Request $request): array
{
    return $request->only([
        'date_from',
        'date_to',
        'reseller_id',
        'branch_id',
    ]);
}
private function getExportData(Request $request)
{
    return $this->stockBalanceService->getConsignmentStock(
        $this->exportFilters($request)
    );
}
public function print(Request $request)
{
    $consignmentStock = $this->getExportData($request);

    return view(
        'print.Reseller.Reports.consignment-stock',
        [
            'consignmentStock' => $consignmentStock,
            'filters' => $this->exportFilters($request),
        ]
    );
}
public function excel(Request $request)
{
    return Excel::download(
        new ConsignmentStockExport(
            $this->exportFilters($request)
        ),
        'consignment-stock.xlsx'
    );
}
public function pdf(Request $request)
{
    $consignmentStock = $this->getExportData($request);

    return Pdf::loadView(
        'pdf.Reseller.Reports.consignment-stock',
        [
            'consignmentStock' => $consignmentStock,
            'filters' => $this->exportFilters($request),
        ]
    )
        ->setPaper('a4', 'landscape')
        ->download(
            'consignment-stock.pdf'
        );
}
}