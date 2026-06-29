<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'supplier_code',

        'name',

        'contact_person',

        'phone',

        'email',

        'address',

        'city',

        'tax_number',

        'payment_term',

        'status',

        'created_by',

        'updated_by',

    ];

    protected $casts = [

        'status' => 'boolean',

    ];
}