<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Product\ProductVariantUnit;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;
class Unit extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [

    'code',

    'name',

    'symbol',

    'description',

    'status',

    'created_by',

    'updated_by',

];
    protected $casts = [

        'status' => 'boolean',

    ];
public function getRouteKeyName(): string
{
    return 'id';
}
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()

            ->useLogName('Unit')

            ->logFillable()

            ->logOnlyDirty()

            ->dontSubmitEmptyLogs();
    }
public function productVariantUnits(): HasMany
{
    return $this->hasMany(
        ProductVariantUnit::class
    );
}
public function scopeActive(
    Builder $query
): Builder
{
    return $query->where(
        'is_active',
        true
    );
}
}