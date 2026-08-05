<?php

namespace App\Data\Product;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Collection;

class VariantGeneratorData
{
    public function __construct(

        public Product $product,

        public Collection $attributes,

        public ?int $userId = null,

    ) {
    }
}