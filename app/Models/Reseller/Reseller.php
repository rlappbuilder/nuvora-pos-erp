<?php

namespace App\Models\Reseller;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\MasterData\Company;
use App\Models\User;
use App\Models\Reseller\ResellerPrice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reseller extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'resellers';

    protected $fillable = [
        'company_id',
        'reseller_code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'tax_number',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }

    public function prices(): HasMany
    {
        return $this->hasMany(
            ResellerPrice::class
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(
            ResellerPriceHistory::class
        );
    }
}