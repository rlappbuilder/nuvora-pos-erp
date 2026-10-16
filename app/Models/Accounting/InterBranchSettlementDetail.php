<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterBranchSettlementDetail extends Model
{
    use HasFactory;

    protected $table = 'inter_branch_settlement_details';

    protected $fillable = [
        'inter_branch_settlement_header_id',
        'fund_transfer_header_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Settlement Header
    |--------------------------------------------------------------------------
    */

    public function settlementHeader(): BelongsTo
    {
        return $this->belongsTo(
            InterBranchSettlementHeader::class,
            'inter_branch_settlement_header_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Source Fund Transfer
    |--------------------------------------------------------------------------
    */

    public function fundTransfer(): BelongsTo
    {
        return $this->belongsTo(
            FundTransferHeader::class,
            'fund_transfer_header_id'
        );
    }
}