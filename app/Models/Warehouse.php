<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'warehouse_type',
        'address',
        'status',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}