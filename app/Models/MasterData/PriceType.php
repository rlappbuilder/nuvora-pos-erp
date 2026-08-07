<?php

namespace App\Models\MasterData;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceType extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'code',

        'name',

        'sort_order',

        'is_default',

        'is_active',

        'description',

        'created_by',

        'updated_by',

        'deleted_by',

    ];

    protected $casts = [

        'is_default' => 'boolean',

        'is_active' => 'boolean',

    ];

    public function prices(): HasMany
    {
        return $this->hasMany(
            ProductVariantPrice::class
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater()
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function deleter()
    {
        return $this->belongsTo(
            User::class,
            'deleted_by'
        );
    }
    public function scopeActive($query)
{
    return $query->where('is_active', true);
}
}