<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Category extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [

        'code',

        'name',

        'description',

        'is_active',

        'created_by',

        'updated_by',

    ];

    protected $casts = [

        'is_active' => 'boolean',

    ];
    public function getRouteKeyName(): string
{
    return 'id';
}

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()

            ->useLogName('category')

            ->logFillable()

            ->logOnlyDirty()

            ->dontSubmitEmptyLogs();
    }

    public function scopeActive($query)
{
    return $query->where('is_active', true);
}
}