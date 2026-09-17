<?php

namespace App\Models\Reseller\ConsignmentSettlement;

use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\Reseller\Reseller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MasterData\Warehouse;
use App\Models\Core\DocumentActivity;
use App\Models\Inventory\InventoryMovement;

class ConsignmentSettlementHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'settlement_headers';

    protected $fillable = [
        'company_id',
        'branch_id',
        'warehouse_id',
        'reseller_id',
        'settlement_number',
        'settlement_date',
        'period_from',
        'period_to',
        'status',
        'subtotal',
        'adjustment_amount',
        'grand_total',
        'payment_amount',
        'receivable_amount',
        'payment_status',
        'remarks',
        'created_by',
        'updated_by',
        'posted_by',
        'posted_at',
        'cancelled_by',
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'settlement_date' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        
        'payment_amount' => 'decimal:2',
        'receivable_amount' => 'decimal:2',

        'subtotal' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',

        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(
            ConsignmentSettlementDetail::class,
            'settlement_header_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Audit Relationships
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'posted_by'
        );
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelled_by'
        );
    }
    public function warehouse(): BelongsTo
{
    return $this->belongsTo(
        Warehouse::class
    );
}

/*
|--------------------------------------------------------------------------
| Document Activities
|--------------------------------------------------------------------------
*/

public function activities(): HasMany
{
    return $this->hasMany(
        DocumentActivity::class,
        'document_id'
    )
    ->where(
        'document_type',
        class_basename($this)
    )
    ->orderBy(
        'performed_at',
        'asc'
    );
}
public function movements(): HasMany
{
    return $this->hasMany(
        InventoryMovement::class,
        'reference_id'
    )
    ->where(
        'reference_type',
        'SETTLEMENT'
    );
}
}