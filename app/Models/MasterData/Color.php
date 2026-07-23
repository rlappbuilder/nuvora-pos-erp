<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Color extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [

        'code',

        'name',

        'hex_color',

        'description',

        'is_cctive',

        'created_by',

        'updated_by',

    ];

    protected $casts = [

        'is_active' => 'boolean',

    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()

            ->logFillable()

            ->logOnlyDirty()

            ->dontSubmitEmptyLogs();
    }
}