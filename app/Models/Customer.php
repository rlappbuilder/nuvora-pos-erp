<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'customer_code',

        'name',

        'contact_person',

        'phone',

        'email',

        'address',

        'city',

        'tax_number',

        'payment_term',

        'credit_limit',

        'status',

        'created_by',

        'updated_by',

    ];

    protected $casts = [

        'credit_limit' => 'decimal:2',

        'status' => 'boolean',

    ];
}