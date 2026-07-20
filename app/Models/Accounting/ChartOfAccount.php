<?php

namespace App\Models\Accounting;

use App\Models\User;
use App\Models\MasterData\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Chart Of Account Model
 *
 * Represents a company's Chart of Accounts (COA).
 */
class ChartOfAccount extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'chart_of_accounts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'parent_id',
        'account_category_id',
        'code',
        'name',
        'normal_balance',
        'level',
        'is_header',
        'is_posting',
        'opening_balance',
        'status',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'opening_balance' => 'decimal:2',
        'level'           => 'integer',
        'is_header'       => 'boolean',
        'is_posting'      => 'boolean',
        'status'          => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Account Category.
     */
    public function accountCategory(): BelongsTo
    {
        return $this->belongsTo(AccountCategory::class);
    }

    /**
     * Parent Account.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Child Accounts.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Created By.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Updated By.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Deleted By.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Only active accounts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Only posting accounts.
     */
    public function scopePosting(Builder $query): Builder
    {
        return $query->where('is_posting', true);
    }

    /**
     * Only header accounts.
     */
    public function scopeHeader(Builder $query): Builder
    {
        return $query->where('is_header', true);
    }

    /**
     * Only root accounts.
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Filter by company.
     */
    public function scopeByCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}