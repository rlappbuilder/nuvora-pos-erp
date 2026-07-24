<?php

namespace App\Models\MasterData;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Currency extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimal_places',
        'exchange_rate',
        'is_base_currency',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'decimal_places'   => 'integer',
        'exchange_rate'    => 'decimal:6',
        'is_base_currency' => 'boolean',
        'is_active'        => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function createdBy()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updatedBy()
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}