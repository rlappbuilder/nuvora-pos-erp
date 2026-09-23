<?php

namespace App\Services\Product;

use App\Models\Product\Product;
use App\Models\Product\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductImageService
{
    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    public function upload(
        Product $product,
        UploadedFile $file
    ): ProductImage {
        return DB::transaction(function () use ($product, $file) {

            $isFirstImage = ! $product->images()->exists();

            $path = $file->store(
                "products/{$product->id}",
                'public'
            );

            if (! $path) {
                throw new RuntimeException(
                    'Gagal menyimpan gambar produk.'
                );
            }

            $image = $product->images()->create([
                'image' => $path,
                'is_primary' => $isFirstImage,
                'sort_order' => (
                    $product->images()->max('sort_order') ?? -1
                ) + 1,
            ]);

            return $image;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Set Primary
    |--------------------------------------------------------------------------
    */

    public function setPrimary(
        ProductImage $image
    ): ProductImage {

        return DB::transaction(function () use ($image) {

            ProductImage::query()
                ->where('product_id', $image->product_id)
                ->update([
                    'is_primary' => false,
                ]);

            $image->update([
                'is_primary' => true,
            ]);

            return $image->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        ProductImage $image
    ): void {

        DB::transaction(function () use ($image) {

            $productId = $image->product_id;
            $wasPrimary = $image->is_primary;

            $path = $image->image;

            $image->delete();

            if ($path) {
                Storage::disk('public')->delete($path);
            }

            /*
            |--------------------------------------------------------------------------
            | If primary image was deleted,
            | promote the first remaining image.
            |--------------------------------------------------------------------------
            */

            if ($wasPrimary) {

                $nextImage = ProductImage::query()
                    ->where('product_id', $productId)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->first();

                if ($nextImage) {
                    $nextImage->update([
                        'is_primary' => true,
                    ]);
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reorder
    |--------------------------------------------------------------------------
    */

    public function reorder(
        Product $product,
        array $imageIds
    ): void {

        DB::transaction(function () use (
            $product,
            $imageIds
        ) {

            foreach ($imageIds as $index => $imageId) {

                ProductImage::query()
                    ->where('product_id', $product->id)
                    ->whereKey($imageId)
                    ->update([
                        'sort_order' => $index,
                    ]);
            }
        });
    }
}