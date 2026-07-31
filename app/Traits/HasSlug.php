<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);

        $originalSlug = $slug;
        $counter = 1;

        while (
            static::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}