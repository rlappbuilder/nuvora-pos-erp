<?php

namespace App\Models\Production\ProductionOrder;

use App\Models\Core\DocumentActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'production_order_header_id',
        'number',
        'result_date',
        'status',
        'posted_at',
        'posted_by',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_reason',
    ];

    protected function casts(): array
    {
        return [
            'result_date' => 'date',
            'posted_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\Company::class,
            'company_id'
        );
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\Branch::class,
            'branch_id'
        );
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(
            ProductionOrderHeader::class,
            'production_order_header_id'
        );
    }

    public function detail(): HasOne
    {
        return $this->hasOne(
            ProductionResultDetail::class,
            'production_result_id'
        );
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(
            DocumentActivity::class,
            'document_id'
        )
            ->where('document_type', class_basename($this))
            ->orderBy('performed_at');
    }
}