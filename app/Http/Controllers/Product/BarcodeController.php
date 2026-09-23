<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Services\Product\BarcodeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarcodeController extends Controller
{
    protected BarcodeService $barcodeService;

    public function __construct(
        BarcodeService $barcodeService
    ) {
        $this->barcodeService = $barcodeService;
    }

    /**
     * Display Barcode Management.
     */
    public function index(Request $request)
    {
        $query = ProductVariant::query()
            ->with([
                'product:id,code,name',
                'values.attribute:id,name,display_name',
                'values.attributeValue:id,value,display_value',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        /*
        |--------------------------------------------------------------------------
        | Product Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('product_id')) {
            $query->where(
                'product_id',
                $request->product_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Barcode Filter
        |--------------------------------------------------------------------------
        */

        if ($request->get('barcode_status') === 'with_barcode') {
            $query->whereNotNull('barcode')
                ->where('barcode', '!=', '');
        }

        if ($request->get('barcode_status') === 'without_barcode') {
            $query->where(function ($query) {
                $query->whereNull('barcode')
                    ->orWhere('barcode', '');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('is_active')) {
            $query->where(
                'is_active',
                $request->boolean('is_active')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'id',
            'sku',
            'name',
            'barcode',
            'is_active',
            'created_at',
        ];

        $sortBy = $request->get(
            'sort_by',
            'id'
        );

        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }

        $sortDirection = strtolower(
            $request->get(
                'sort_direction',
                'desc'
            )
        );

        $sortDirection = $sortDirection === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy(
            $sortBy,
            $sortDirection
        );

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = $request->integer(
            'per_page',
            10
        );

        $perPage = in_array(
            $perPage,
            [10, 20, 30, 40, 50]
        )
            ? $perPage
            : 10;

        $variants = $query
            ->paginate($perPage)
            ->through(function ($variant) {

                $variant->created_at_human =
                    $variant->created_at?->diffForHumans();

                return $variant;
            })
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statisticsQuery = clone $query;

        $statistics = [
            'total' => (clone $statisticsQuery)->count(),

            'with_barcode' => (clone $statisticsQuery)
                ->whereNotNull('barcode')
                ->where('barcode', '!=', '')
                ->count(),

            'without_barcode' => (clone $statisticsQuery)
                ->where(function ($query) {
                    $query->whereNull('barcode')
                        ->orWhere('barcode', '');
                })
                ->count(),

            'active' => (clone $statisticsQuery)
                ->where('is_active', true)
                ->count(),
        ];

        return Inertia::render(
            'MasterData/Barcode/Index',
            [
                'title' => 'Barcode Management',

                'statistics' => $statistics,

                'variants' => $variants,

                'products' => Product::query()
                    ->select(
                        'id',
                        'code',
                        'name'
                    )
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

                'filters' => $request->only([
                    'search',
                    'product_id',
                    'barcode_status',
                    'is_active',
                    'sort_by',
                    'sort_direction',
                    'per_page',
                ]),
            ]
        );
    }

    /**
     * Generate internal barcode.
     */
    public function generate(
        ProductVariant $productVariant
    ) {
        try {

            $barcode = $this->barcodeService
                ->generate($productVariant);

            return back()->with(
                'success',
                "Barcode {$barcode} berhasil dibuat."
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Gagal membuat barcode.'
            );
        }
    }

    /**
     * Update barcode manually.
     */
    public function update(
        Request $request,
        ProductVariant $productVariant
    ) {
        $validated = $request->validate([
            'barcode' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        try {

            $barcode = $this->barcodeService
                ->update(
                    $productVariant,
                    $validated['barcode']
                );

            return back()->with(
                'success',
                "Barcode {$barcode} berhasil diperbarui."
            );

        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    /**
     * Preview barcode.
     */
    public function preview(
        ProductVariant $productVariant
    ) {
        if (blank($productVariant->barcode)) {
            return response()->json([
                'message' => 'Product Variant belum memiliki barcode.',
            ], 422);
        }

        $svg = $this->barcodeService
            ->renderSvg(
                $productVariant->barcode
            );

        return response()->json([
            'id' => $productVariant->id,
            'product' => $productVariant->product?->name,
            'variant' => $productVariant->name,
            'sku' => $productVariant->sku,
            'barcode' => $productVariant->barcode,
            'svg' => $svg,
        ]);
    }
}