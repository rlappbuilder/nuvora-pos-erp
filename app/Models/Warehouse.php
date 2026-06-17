<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory;
    use SoftDeletes;
protected $casts = [

        'status' => 'boolean',

    ];
    protected $fillable = [

        'branch_id',

        'code',

        'name',

        'warehouse_type',

        'pic_name',

        'phone',

        'email',

        'address',

        'status',

        'created_by',

        'updated_by',

    ];

    public function branch()
    {
        return $this->belongsTo(
            Branch::class
        );
    }
    public function stocks()
{
    return $this->hasMany(
        ProductStock::class
    );
}
public function stockMovements()
{
    return $this->hasMany(
        StockMovement::class
    );
}
}