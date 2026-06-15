<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Brand extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [

        'code',

        'name',

        'description',

        'status',

        'created_by',

        'updated_by',

    ];

    protected $casts = [

        'status' => 'boolean',

    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()

            ->useLogName('Brand')

            ->logFillable()

            ->logOnlyDirty()

            ->dontSubmitEmptyLogs();
    }
}