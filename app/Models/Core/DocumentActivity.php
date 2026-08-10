<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company\Company;
use App\Models\User;

class DocumentActivity extends Model
{
    use HasFactory;

    protected $table = 'document_activities';

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        'company_id',

        /*
        |--------------------------------------------------------------------------
        | Document
        |--------------------------------------------------------------------------
        */

        'document_type',

        'document_id',

        /*
        |--------------------------------------------------------------------------
        | Activity
        |--------------------------------------------------------------------------
        */

        'action',

        'old_status',

        'new_status',

        'description',

        'metadata',

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        'performed_by',

        'performed_at',

    ];

    protected $casts = [

        'metadata' =>
            'array',

        'performed_at' =>
            'datetime',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(
            Company::class
        );
    }

    public function performer()
    {
        return $this->belongsTo(
            User::class,
            'performed_by'
        );
    }
}