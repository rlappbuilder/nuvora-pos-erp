<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\PosSaleRequest;
use App\Models\MasterData\Customer;
use App\Models\Inventory\ProductStock;
use App\Models\MasterData\PriceType;
use App\Models\MasterData\ProductVariantPrice;
use App\Models\Product\ProductVariant;
use App\Services\POS\PosTransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosTransactionController extends Controller
{
    protected PosTransactionService $posTransactionService;

    public function __construct(
        PosTransactionService $posTransactionService
    ) {
        $this->posTransactionService = $posTransactionService;
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Active Cashier Session
        |--------------------------------------------------------------------------
        */

        $activeSession = $this
            ->posTransactionService
            ->getActiveSession($user);


        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        $customers = Customer::query()
            ->where('status', true)
            ->orderBy('name')
            ->get([
                'id',
                'customer_code',
                'name',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Price Types
        |--------------------------------------------------------------------------
        */

        $priceTypes = PriceType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);


        /*
        |--------------------------------------------------------------------------
        | POS Products
        |--------------------------------------------------------------------------
        */

        /*
|--------------------------------------------------------------------------
| POS Products
|--------------------------------------------------------------------------
*/

$products = collect();

if ($activeSession) {

    $products = ProductVariantPrice::query()

        ->with([
            'variant.product.primaryImage',
            'variant.values.attribute',
            'variant.values.attributeValue',
            'unit',
            'priceType',
        ])

        /*
        |--------------------------------------------------------------------------
        | Branch
        |--------------------------------------------------------------------------
        */

        ->where(
            'branch_id',
            $activeSession->branch_id
        )

        /*
        |--------------------------------------------------------------------------
        | Active Price
        |--------------------------------------------------------------------------
        */

        ->where(
            'is_active',
            true
        )

        ->whereDate(
            'effective_from',
            '<=',
            now()->toDateString()
        )

        ->where(function ($query) {

            $query
                ->whereNull('effective_until')
                ->orWhereDate(
                    'effective_until',
                    '>=',
                    now()->toDateString()
                );

        })

        /*
        |--------------------------------------------------------------------------
        | Active Sellable Variant
        |--------------------------------------------------------------------------
        */

        ->whereHas(
            'variant',
            function ($query) {

                $query
                    ->where(
                        'is_active',
                        true
                    )

                    ->whereHas(
                        'product',
                        function ($query) {

                            $query
                                ->where(
                                    'is_active',
                                    true
                                )
                                ->where(
                                    'is_sellable',
                                    true
                                );

                        }
                    );

            }
        )

        /*
        |--------------------------------------------------------------------------
        | Lowest Price First
        |--------------------------------------------------------------------------
        */

        ->orderBy(
            'selling_price'
        )

        ->get()

        /*
        |--------------------------------------------------------------------------
        | Build Variant Data
        |--------------------------------------------------------------------------
        */

        ->map(
            function ($price) use ($activeSession) {

                $variant = $price->variant;
                $product = $variant?->product;
                $image = $product?->primaryImage;

                $variantName = $variant?->values
                    ?->map(function ($value) {
                        return $value->attributeValue?->name;
                    })
                    ->filter()
                    ->implode(' / ');


                /*
                |--------------------------------------------------------------------------
                | Stock
                |--------------------------------------------------------------------------
                */

                $stock = ProductStock::query()

                    ->where(
                        'company_id',
                        $activeSession->company_id
                    )

                    ->where(
                        'branch_id',
                        $activeSession->branch_id
                    )

                    ->where(
                        'warehouse_id',
                        $activeSession->warehouse_id
                    )

                    ->where(
                        'product_variant_id',
                        $variant->id
                    )

                    ->where(
                        'unit_id',
                        $price->unit_id
                    )

                    ->whereNull(
                        'reseller_id'
                    )

                    ->first();


                /*
                |--------------------------------------------------------------------------
                | Variant
                |--------------------------------------------------------------------------
                */

                return [

                    'product_id' =>
                        $product?->id,

                    'product_name' =>
                        $product?->name,

                    'variant_id' =>
                        $variant->id,

                      'variant_name' => $variantName ?: (
                        $variant->name !== 'Default'
                            ? $variant->name
                            : null
                      ),

                    'sku' =>
                        $variant->sku,

                    'barcode' =>
                        $variant->barcode,

                    'unit_id' =>
                        $price->unit_id,

                    'unit_name' =>
                        $price->unit?->name,

                    'price_type_id' =>
                        $price->price_type_id,

                    'price_type_name' =>
                        $price->priceType?->name,

                    'selling_price' =>
                        (float) $price->selling_price,

                    'image' =>
                        $image?->image
                            ? asset(
                                'storage/' . $image->image
                            )
                            : null,

                    'available_stock' =>
                        (float) (
                            $stock?->available_qty ?? 0
                        ),

                ];

            }
        )

        ->filter(
            fn ($item) =>
                $item['product_id'] !== null
        )

        /*
        |--------------------------------------------------------------------------
        | Group By Product
        |--------------------------------------------------------------------------
        */

        ->groupBy(
            'product_id'
        )

        /*
        |--------------------------------------------------------------------------
        | Build Product Cards
        |--------------------------------------------------------------------------
        */

        ->map(
            function ($variants) {

                /*
                |--------------------------------------------------------------------------
                | Variants already sorted by lowest price
                |--------------------------------------------------------------------------
                */

                $lowestVariant =
                    $variants->first();


                return [

                    'id' =>
                        $lowestVariant['product_id'],

                    'product_id' =>
                        $lowestVariant['product_id'],

                    'product_name' =>
                        $lowestVariant['product_name'],

                    'name' =>
                        $lowestVariant['product_name'],

                    'sku' => $lowestVariant['sku'],

                    'image' =>
                        $lowestVariant['image'],

                    'selling_price' =>
                        $lowestVariant['selling_price'],

                    'available_stock' =>
                        $lowestVariant['available_stock'],

                    'variants' =>
                        $variants
                            ->values()
                            ->toArray(),

                ];

            }
        )

        ->values();

}


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'POS/Transaction/Index',
            [

                'activeSession' =>
                    $activeSession,

                'customers' =>
                    $customers,

                'priceTypes' =>
                    $priceTypes,

                'products' =>
                    $products,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        PosSaleRequest $request
    ) {
        $sale = $this
            ->posTransactionService
            ->createSale(
                $request->validated()
            );

        return redirect()
            ->route(
                'pos.transactions.index'
            )
            ->with(
                'success',
                "Transaction {$sale->sale_number} completed successfully."
            );
    }
}