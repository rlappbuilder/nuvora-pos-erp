<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [

        'company_id',

        'code',

        'name',

        'manager_name',

        'phone',

        'email',

        'address',

        'city',

        'province',

        'is_head_office',

        'is_active',

        'created_by',

        'updated_by',

    ];

    public function company()
    {
        return $this->belongsTo(
            Company::class
        );
    }

    public function warehouses()
    {
        return $this->hasMany(
            Warehouse::class
        );
    }
    public function scopeActive($query)
{
    return $query->where('is_active', true);
}
}