<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

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
    ];
}