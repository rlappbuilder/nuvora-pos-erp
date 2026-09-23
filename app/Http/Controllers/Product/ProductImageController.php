<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductImage;
use App\Services\Product\ProductImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductImageController extends Controller
{
    protected ProductImageService $productImageService;

    public function __construct(
        ProductImageService $productImageService
    ) {
        $this->productImageService = $productImageService;
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        Product $product
    ) {
        $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        try {

            $image = $this->productImageService->upload(
                $product,
                $request->file('image')
            );

            return response()->json([
                'success' => true,
                'message' => 'Product image berhasil ditambahkan.',
                'image' => $image,
            ]);

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal mengupload product image.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Set Primary
    |--------------------------------------------------------------------------
    */

    public function setPrimary(
    Product $product,
    ProductImage $productImage
) {
    if ($productImage->product_id !== $product->id) {
        abort(404);
    }

    try {
        $this->productImageService->setPrimary(
            $productImage
        );

        return response()->json([
            'success' => true,
            'message' => 'Primary product image berhasil diperbarui.',
        ]);

    } catch (\Throwable $e) {

        report($e);

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengubah primary product image.',
        ], 500);
    }
}

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

   public function destroy(
    Product $product,
    ProductImage $productImage
) {
    if ($productImage->product_id !== $product->id) {
        abort(404);
    }

    try {
        $this->productImageService->delete(
            $productImage
        );

        return response()->json([
            'success' => true,
            'message' => 'Product image berhasil dihapus.',
        ]);

    } catch (\Throwable $e) {

        report($e);

        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus product image.',
        ], 500);
    }
}

    /*
    |--------------------------------------------------------------------------
    | Reorder
    |--------------------------------------------------------------------------
    */

    public function reorder(
    Request $request,
    Product $product
) {
    $data = $request->validate([
        'image_ids' => [
            'required',
            'array',
        ],

        'image_ids.*' => [
            'integer',
            Rule::exists('product_images', 'id')
                ->where(
                    fn ($query) =>
                        $query->where(
                            'product_id',
                            $product->id
                        )
                ),
        ],
    ]);

    try {

        $this->productImageService->reorder(
            $product,
            $data['image_ids']
        );

        return response()->json([
            'success' => true,
            'message' => 'Urutan product image berhasil diperbarui.',
        ]);

    } catch (\Throwable $e) {

        report($e);

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengubah urutan product image.',
        ], 500);
    }
}
}