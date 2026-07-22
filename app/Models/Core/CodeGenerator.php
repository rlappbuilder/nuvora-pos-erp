<?php

namespace App\Models\Core;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeGenerator extends Model
{
    protected $fillable = [

        'company_id',

        'module',

        'prefix',

        'format',

        'separator',

        'digit',

        'next_number',

        'reset_type',

        'last_reset_at',

        'is_active',

    ];

    protected $casts = [

        'last_reset_at' => 'datetime',

        'is_active' => 'boolean',

    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}