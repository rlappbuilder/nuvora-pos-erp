<?php

namespace App\Services\Product;

use App\Models\Product\ProductVariant;
use Picqer\Barcode\Renderers\SvgRenderer;
use Picqer\Barcode\Types\TypeCode128;
use RuntimeException;

class BarcodeService
{
    /**
     * Generate internal barcode for a product variant.
     */
    public function generate(ProductVariant $variant): string
    {
        if (filled($variant->barcode)) {
            return $variant->barcode;
        }

        /*
        |--------------------------------------------------------------------------
        | Internal Barcode
        |--------------------------------------------------------------------------
        |
        | Format:
        | NV + Product Variant ID (10 digits)
        |
        | Example:
        | Variant ID 22
        | NV0000000022
        |
        | Code 128 supports alphanumeric values.
        |
        */

        $barcode = 'NV' . str_pad(
            (string) $variant->id,
            10,
            '0',
            STR_PAD_LEFT
        );

        /*
        |--------------------------------------------------------------------------
        | Safety Check
        |--------------------------------------------------------------------------
        */

        $exists = ProductVariant::query()
            ->where('barcode', $barcode)
            ->whereKeyNot($variant->id)
            ->exists();

        if ($exists) {
            throw new RuntimeException(
                "Barcode {$barcode} sudah digunakan oleh Product Variant lain."
            );
        }

        $variant->update([
            'barcode' => $barcode,
        ]);

        return $barcode;
    }

    /**
     * Update barcode manually.
     */
    public function update(ProductVariant $variant, string $barcode): string
    {
        $barcode = trim($barcode);

        if ($barcode === '') {
            throw new RuntimeException('Barcode tidak boleh kosong.');
        }

        $exists = ProductVariant::query()
            ->where('barcode', $barcode)
            ->whereKeyNot($variant->id)
            ->exists();

        if ($exists) {
            throw new RuntimeException(
                "Barcode {$barcode} sudah digunakan oleh Product Variant lain."
            );
        }

        $variant->update([
            'barcode' => $barcode,
        ]);

        return $barcode;
    }

    /**
     * Generate SVG barcode.
     */
    public function renderSvg(
        string $barcode,
        float $width = 450,
        float $height = 100
    ): string {
        $barcodeObject = (new TypeCode128())
            ->getBarcode($barcode);

        $renderer = new SvgRenderer();

        return $renderer->render(
            $barcodeObject,
            $width,
            $height
        );
    }
}