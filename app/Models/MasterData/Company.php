<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Company extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [

        'company_code',

        'company_name',

        'legal_name',

        'phone',

        'email',

        'website',

        'tax_number',

        'director_name',

        'logo',

        'address',

        'city',

        'province',

        'postal_code',

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

            ->logFillable()

            ->logOnlyDirty()

            ->dontSubmitEmptyLogs();
    }

    public function branches()
{
    return $this->hasMany(
        Branch::class
    );
}
}