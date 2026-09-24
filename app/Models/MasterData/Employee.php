<?php

namespace App\Models\MasterData;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Employee extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'employee_code',
        'name',
        'phone',
        'email',
        'position',
        'join_date',
        'user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }
}