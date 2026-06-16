<?php

namespace App\Http\Controllers\MasterData;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $products = Product::with(

        'category',
        'brand'
        
    )

    ->when(

        request('search'),

        function ($query) {

            $query->where(

                'sku',

                'like',

                '%' . request('search') . '%'

            )

            ->orWhere(

                'name',

                'like',

                '%' . request('search') . '%'

            );

        }

    )

    ->latest()

    ->paginate(10)

    ->withQueryString();

    return Inertia::render(

        'MasterData/Products/Index',

        [

            'products' => $products,

            'filters' => [

                'search' => request('search')

            ]

        ]

    );
}

    /**
     * Show the form for creating a new resource.
     */
   public function create()
{
    return Inertia::render(

        'MasterData/Products/Create',

        [

            'categories' => Category::where(

                'status',

                true

            )->get(),

            'brands' => Brand::where(

                'status',

                true

            )->get()

        ]

    );
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(
    ProductRequest $request
)
{
    $lastProduct = Product::latest()

        ->first();

    $code = 'PRD0001';

    if ($lastProduct) {

        $number = (int)

            substr(

                $lastProduct->sku,

                3

            );

        $code = 'PRD'

            . str_pad(

                $number + 1,

                4,

                '0',

                STR_PAD_LEFT

            );
    }

    Product::create([

        'category_id' => $request->category_id,

        'brand_id' => $request->brand_id,

        'sku' => $code,

        'barcode' => $request->barcode,

        'name' => $request->name,

        'description' => $request->description,

        'unit' => $request->unit,

        'cost_price' => $request->cost_price,

        'selling_price' => $request->selling_price,

        'minimum_stock' => $request->minimum_stock,

        'status' => $request->status,

        'created_by' => auth()->id(),

    ]);

    return redirect()

        ->route(

            'products.index'

        )

        ->with(

            'success',

            'Product created successfully.'

        );
}

    /**
     * Display the specified resource.
     */
    public function show(
    Product $product
)
{
    $product->load(

        'category',

        'brand'

    );

    return Inertia::render(

        'MasterData/Products/Show',

        [

            'product' => $product

        ]

    );
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(
    Product $product
)
{
    return Inertia::render(

        'MasterData/Products/Edit',

        [

            'product' => $product,

            'categories' => Category::where(

                'status',

                true

            )->get(),

            'brands' => Brand::where(

                'status',

                true

            )->get()

        ]

    );
}

    /**
     * Update the specified resource in storage.
     */
    public function update(
    ProductRequest $request,
    Product $product
)
{
    $product->update([

        'category_id' => $request->category_id,

        'brand_id' => $request->brand_id,

        'barcode' => $request->barcode,

        'name' => $request->name,

        'description' => $request->description,

        'unit' => $request->unit,

        'cost_price' => $request->cost_price,

        'selling_price' => $request->selling_price,

        'minimum_stock' => $request->minimum_stock,

        'status' => $request->status,

        'updated_by' => auth()->id(),

    ]);

    return redirect()

        ->route(

            'products.show',

            $product->id

        )

        ->with(

            'success',

            'Product updated successfully.'

        );
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
    Product $product
)
{
    $product->delete();

    return redirect()

        ->route(

            'products.index'

        )

        ->with(

            'success',

            'Product deleted successfully.'

        );
}
}
