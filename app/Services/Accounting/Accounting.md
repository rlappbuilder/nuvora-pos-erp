<?php

namespace App\Services\Accounting;
use App\Services\Accounting\GeneralLedgerService;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use App\Models\MasterData\Branch;
use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\ChartOfAccount;
use Carbon\Carbon;
class JournalEntryService
{
    protected CodeGeneratorService $codeGeneratorService;

    protected DocumentActivityService $documentActivityService;
    protected GeneralLedgerService $generalLedgerService;

    public function __construct(
        CodeGeneratorService $codeGeneratorService,
        DocumentActivityService $documentActivityService,
        GeneralLedgerService $generalLedgerService
    ) {
        $this->codeGeneratorService = $codeGeneratorService;
        $this->documentActivityService = $documentActivityService;
        $this->generalLedgerService = $generalLedgerService;
    }


    /*
    |--------------------------------------------------------------------------
    | Public Methods
    |--------------------------------------------------------------------------
    */


   /*
|--------------------------------------------------------------------------
| Create Journal Entry - DRAFT
|--------------------------------------------------------------------------
*/

public function create(array $data): JournalEntry
{
    return DB::transaction(function () use ($data) {

        /*
        |--------------------------------------------------------------------------
        | Validate Lines
        |--------------------------------------------------------------------------
        */

        if (
            empty($data['lines']) ||
            !is_array($data['lines'])
        ) {
            throw new \RuntimeException(
                'Journal entry must have at least one line.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Resolve Company From Branch
        |--------------------------------------------------------------------------
        */

        $branch = Branch::query()
            ->findOrFail(
                $data['branch_id']
            );

        $companyId =
            $branch->company_id;


        /*
        |--------------------------------------------------------------------------
        | Validate Accounting Journal
        |--------------------------------------------------------------------------
        */

        $accountingJournal = AccountingJournal::query()
            ->where(
                'id',
                $data['accounting_journal_id']
            )
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'is_active',
                true
            )
            ->first();

        if (!$accountingJournal) {

            throw new \RuntimeException(
                'Selected accounting journal is invalid for this company.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Fiscal Year
        |--------------------------------------------------------------------------
        */

        $fiscalYear = FiscalYear::query()
            ->where(
                'id',
                $data['fiscal_year_id']
            )
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'status',
                'Open'
            )
            ->first();

        if (!$fiscalYear) {

            throw new \RuntimeException(
                'Selected fiscal year is invalid or already closed.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Accounting Period
        |--------------------------------------------------------------------------
        */

        $accountingPeriod = AccountingPeriod::query()
            ->where(
                'id',
                $data['accounting_period_id']
            )
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'fiscal_year_id',
                $fiscalYear->id
            )
            ->where(
                'status',
                'Open'
            )
            ->first();

        if (!$accountingPeriod) {

            throw new \RuntimeException(
                'Selected accounting period is invalid or already closed.'
            );

        }

/*
|--------------------------------------------------------------------------
| Normalize Entry Date
|--------------------------------------------------------------------------
*/

$entryDate =
    Carbon::parse(
        $data['entry_date']
    )->startOfDay();

$fiscalYearStart =
    Carbon::parse(
        $fiscalYear->start_date
    )->startOfDay();

$fiscalYearEnd =
    Carbon::parse(
        $fiscalYear->end_date
    )->endOfDay();

$accountingPeriodStart =
    Carbon::parse(
        $accountingPeriod->start_date
    )->startOfDay();

$accountingPeriodEnd =
    Carbon::parse(
        $accountingPeriod->end_date
    )->endOfDay();


/*
|--------------------------------------------------------------------------
| Validate Entry Date - Fiscal Year
|--------------------------------------------------------------------------
*/

if (
    !$entryDate->betweenIncluded(
        $fiscalYearStart,
        $fiscalYearEnd
    )
) {

    throw new \RuntimeException(
        'Entry date is outside the selected fiscal year.'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Entry Date - Accounting Period
|--------------------------------------------------------------------------
*/

if (
    !$entryDate->betweenIncluded(
        $accountingPeriodStart,
        $accountingPeriodEnd
    )
) {

    throw new \RuntimeException(
        'Entry date is outside the selected accounting period.'
    );

}

        /*
        |--------------------------------------------------------------------------
        | Validate Accounts
        |--------------------------------------------------------------------------
        */

        $accountIds = collect(
            $data['lines']
        )
            ->pluck('account_id')
            ->unique()
            ->values();


        $validAccountCount = ChartOfAccount::query()
            ->whereIn(
                'id',
                $accountIds
            )
            ->where(
                'company_id',
                $companyId
            )
            ->active()
            ->posting()
            ->count();


        if (
            $validAccountCount !==
            $accountIds->count()
        ) {

            throw new \RuntimeException(
                'One or more selected accounts are invalid for this company.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Balance
        |--------------------------------------------------------------------------
        */

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($data['lines'] as $line) {

            $debit =
                (float) ($line['debit'] ?? 0);

            $credit =
                (float) ($line['credit'] ?? 0);


            if (
                $debit < 0 ||
                $credit < 0
            ) {

                throw new \RuntimeException(
                    'Debit and credit cannot be negative.'
                );

            }


            if (
                $debit > 0 &&
                $credit > 0
            ) {

                throw new \RuntimeException(
                    'A journal entry line cannot contain both debit and credit.'
                );

            }


            if (
                $debit == 0 &&
                $credit == 0
            ) {

                throw new \RuntimeException(
                    'A journal entry line must contain either debit or credit.'
                );

            }


            $totalDebit += $debit;

            $totalCredit += $credit;

        }


        if (
            round($totalDebit, 2) !==
            round($totalCredit, 2)
        ) {

            throw new \RuntimeException(
                'Journal entry is not balanced. Total debit must equal total credit.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Create Header - DRAFT
        |--------------------------------------------------------------------------
        */

        $header = JournalEntry::create([

            'company_id' =>
                $companyId,

            'branch_id' =>
                $data['branch_id'],

            'accounting_journal_id' =>
                $data['accounting_journal_id'],

            'fiscal_year_id' =>
                $data['fiscal_year_id'],

            'accounting_period_id' =>
                $data['accounting_period_id'],

            'code' =>
                $this->codeGeneratorService
                    ->next('journal'),

            'entry_date' =>
                $data['entry_date'],

            'reference' =>
                $data['reference'] ?? null,

            'description' =>
                $data['description'] ?? null,

            'status' =>
                'Draft',

            'created_by' =>
                auth()->id(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Create Lines
        |--------------------------------------------------------------------------
        */

        foreach (
            $data['lines']
            as $index => $line
        ) {

            JournalEntryLine::create([
                'journal_entry_id' =>
                    $header->id,

                'account_id' =>
                    $line['account_id'],

                'branch_id' =>
                    $line['branch_id'] ?? null,

                'line_number' =>
                    $index + 1,

                'description' =>
                    $line['description'] ?? null,

                'debit' =>
                    $line['debit'] ?? 0,

                'credit' =>
                    $line['credit'] ?? 0,

                'created_by' =>
                    auth()->id(),
            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Document Activity - CREATED
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService->record(

            $header,

            'CREATED',

            null,

            'Draft',

            'Journal entry created.'

        );


        return $header;

    });
}

/*
|--------------------------------------------------------------------------
| Update Journal Entry - DRAFT
|--------------------------------------------------------------------------
*/

public function update(
    JournalEntry $journalEntry,
    array $data
): JournalEntry {

    return DB::transaction(function () use (
        $journalEntry,
        $data
    ) {

        /*
        |--------------------------------------------------------------------------
        | Lock Header
        |--------------------------------------------------------------------------
        */

        $journalEntry = JournalEntry::query()
            ->with('lines')
            ->lockForUpdate()
            ->findOrFail(
                $journalEntry->id
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        if (
            $journalEntry->status !== 'Draft'
        ) {

            throw new \RuntimeException(
                'Only Draft journal entry can be updated.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Lines
        |--------------------------------------------------------------------------
        */

        if (
            empty($data['lines']) ||
            !is_array($data['lines'])
        ) {

            throw new \RuntimeException(
                'Journal entry must have at least one line.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Resolve Company From Branch
        |--------------------------------------------------------------------------
        */

        $branch = Branch::query()
            ->findOrFail(
                $data['branch_id']
            );

        $companyId =
            $branch->company_id;


        /*
        |--------------------------------------------------------------------------
        | Validate Accounting Journal
        |--------------------------------------------------------------------------
        */

        $accountingJournal =
            AccountingJournal::query()
                ->where(
                    'id',
                    $data['accounting_journal_id']
                )
                ->where(
                    'company_id',
                    $companyId
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();

        if (!$accountingJournal) {

            throw new \RuntimeException(
                'Selected accounting journal is invalid for this company.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Fiscal Year
        |--------------------------------------------------------------------------
        */

        $fiscalYear = FiscalYear::query()
            ->where(
                'id',
                $data['fiscal_year_id']
            )
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'status',
                'Open'
            )
            ->first();

        if (!$fiscalYear) {

            throw new \RuntimeException(
                'Selected fiscal year is invalid or already closed.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Accounting Period
        |--------------------------------------------------------------------------
        */

        $accountingPeriod =
            AccountingPeriod::query()
                ->where(
                    'id',
                    $data['accounting_period_id']
                )
                ->where(
                    'company_id',
                    $companyId
                )
                ->where(
                    'fiscal_year_id',
                    $fiscalYear->id
                )
                ->where(
                    'status',
                    'Open'
                )
                ->first();

        if (!$accountingPeriod) {

            throw new \RuntimeException(
                'Selected accounting period is invalid or already closed.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Entry Date - Fiscal Year
        |--------------------------------------------------------------------------
        */

        if (
            $data['entry_date'] <
                $fiscalYear->start_date ||
            $data['entry_date'] >
                $fiscalYear->end_date
        ) {

            throw new \RuntimeException(
                'Entry date is outside the selected fiscal year.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Entry Date - Accounting Period
        |--------------------------------------------------------------------------
        */

        if (
            $data['entry_date'] <
                $accountingPeriod->start_date ||
            $data['entry_date'] >
                $accountingPeriod->end_date
        ) {

            throw new \RuntimeException(
                'Entry date is outside the selected accounting period.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Accounts
        |--------------------------------------------------------------------------
        */

        $accountIds = collect(
            $data['lines']
        )
            ->pluck('account_id')
            ->unique()
            ->values();


        $validAccountCount =
            ChartOfAccount::query()
                ->whereIn(
                    'id',
                    $accountIds
                )
                ->where(
                    'company_id',
                    $companyId
                )
                ->active()
                ->posting()
                ->count();


        if (
            $validAccountCount !==
            $accountIds->count()
        ) {

            throw new \RuntimeException(
                'One or more selected accounts are invalid for this company.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Balance
        |--------------------------------------------------------------------------
        */

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($data['lines'] as $line) {

            $debit =
                (float) ($line['debit'] ?? 0);

            $credit =
                (float) ($line['credit'] ?? 0);


            if (
                $debit < 0 ||
                $credit < 0
            ) {

                throw new \RuntimeException(
                    'Debit and credit cannot be negative.'
                );

            }


            if (
                $debit > 0 &&
                $credit > 0
            ) {

                throw new \RuntimeException(
                    'A journal entry line cannot contain both debit and credit.'
                );

            }


            if (
                $debit == 0 &&
                $credit == 0
            ) {

                throw new \RuntimeException(
                    'A journal entry line must contain either debit or credit.'
                );

            }


            $totalDebit += $debit;

            $totalCredit += $credit;

        }


        if (
            round($totalDebit, 2) !==
            round($totalCredit, 2)
        ) {

            throw new \RuntimeException(
                'Journal entry is not balanced. Total debit must equal total credit.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Update Header
        |--------------------------------------------------------------------------
        */

        $journalEntry->update([

            'company_id' =>
                $companyId,

            'branch_id' =>
                $data['branch_id'],

            'accounting_journal_id' =>
                $data['accounting_journal_id'],

            'fiscal_year_id' =>
                $data['fiscal_year_id'],

            'accounting_period_id' =>
                $data['accounting_period_id'],

            'entry_date' =>
                $data['entry_date'],

            'reference' =>
                $data['reference'] ?? null,

            'description' =>
                $data['description'] ?? null,

            'updated_by' =>
                auth()->id(),

        ]);


       /*
|--------------------------------------------------------------------------
| Replace Lines
|--------------------------------------------------------------------------
*/

$journalEntry->lines()->delete();

foreach (
    $data['lines']
    as $index => $line
) {

    JournalEntryLine::create([
        'journal_entry_id' =>
            $journalEntry->id,

        'account_id' =>
            $line['account_id'],

        'branch_id' =>
            $line['branch_id'] ?? null,

        'line_number' =>
            $index + 1,

        'description' =>
            $line['description'] ?? null,

        'debit' =>
            $line['debit'] ?? 0,

        'credit' =>
            $line['credit'] ?? 0,

        'created_by' =>
            auth()->id(),
    ]);

} // ← foreach


/*
|--------------------------------------------------------------------------
| Document Activity - UPDATED
|--------------------------------------------------------------------------
*/

$this->documentActivityService->record(

    $journalEntry,

    'UPDATED',

    'Draft',

    'Draft',

    'Journal entry updated.'

);


return $journalEntry->fresh('lines');

}); // ← DB::transaction

} // ← update()
   /*
|--------------------------------------------------------------------------
| Post Journal Entry
|--------------------------------------------------------------------------
*/

public function post(
    JournalEntry $journalEntry
): void {

    DB::transaction(function () use (
        $journalEntry
    ) {

        /*
        |--------------------------------------------------------------------------
        | Lock Header
        |--------------------------------------------------------------------------
        */

        $journalEntry =
            JournalEntry::query()
                ->with('lines')
                ->lockForUpdate()
                ->findOrFail(
                    $journalEntry->id
                );


        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        if (
            $journalEntry->status !== 'Draft'
        ) {

            throw new \RuntimeException(
                'Only Draft journal entry can be posted.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Lines
        |--------------------------------------------------------------------------
        */

        if (
            $journalEntry->lines->isEmpty()
        ) {

            throw new \RuntimeException(
                'Journal entry must have at least one line.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Balance
        |--------------------------------------------------------------------------
        */

        $totalDebit =
            $journalEntry->lines
                ->sum('debit');

        $totalCredit =
            $journalEntry->lines
                ->sum('credit');


        if (
            round(
                (float) $totalDebit,
                2
            ) !==
            round(
                (float) $totalCredit,
                2
            )
        ) {

            throw new \RuntimeException(
                'Journal entry is not balanced. Total debit must equal total credit.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Create General Ledger
        |--------------------------------------------------------------------------
        */

        $this->generalLedgerService
            ->createFromJournalEntry(
                $journalEntry
            );


        /*
        |--------------------------------------------------------------------------
        | Mark Posted
        |--------------------------------------------------------------------------
        */

        $journalEntry->update([

            'status' =>
                'Posted',

            'posted_at' =>
                now(),

            'posted_by' =>
                auth()->id(),

            'updated_by' =>
                auth()->id(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Document Activity - POSTED
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService->record(

            $journalEntry,

            'POSTED',

            'Draft',

            'Posted',

            'Journal entry posted.'

        );

    });
}

<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Core\DocumentActivity;
use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\User;
use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\AccountingPeriod;

class JournalEntry extends Model
{
    use SoftDeletes;

    protected $table = 'journal_entries';

    protected $fillable = [
        'company_id',
        'branch_id',
        'accounting_journal_id',
        'fiscal_year_id',
        'accounting_period_id',
        'code',
        'entry_date',
        'reference',
        'description',
        'status',
        'posted_by',
        'posted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Organization
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

    /*
    |--------------------------------------------------------------------------
    | Accounting Structure
    |--------------------------------------------------------------------------
    */

    public function accountingJournal(): BelongsTo
    {
        return $this->belongsTo(AccountingJournal::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Lines
    |--------------------------------------------------------------------------
    */

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class)
            ->orderBy('line_number');
    }

    /*
    |--------------------------------------------------------------------------
    | Posting
    |--------------------------------------------------------------------------
    */

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    */

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /*
|--------------------------------------------------------------------------
| General Ledger
|--------------------------------------------------------------------------
*/

public function ledgerEntries(): HasMany
{
    return $this->hasMany(
        GeneralLedger::class,
        'journal_entry_id'
    );
}
/*
|--------------------------------------------------------------------------
| Workflow Activities
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
}

<?php

namespace App\Models\Accounting;

use App\Models\MasterData\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Accounting Period Model
 *
 * Represents an accounting period within a fiscal year.
 */
class AccountingPeriod extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table = 'accounting_periods';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        'company_id',


        /*
        |--------------------------------------------------------------------------
        | Fiscal Year
        |--------------------------------------------------------------------------
        */

        'fiscal_year_id',


        /*
        |--------------------------------------------------------------------------
        | Period
        |--------------------------------------------------------------------------
        */

        'period_number',

        'name',

        'start_date',

        'end_date',


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        'status',


        /*
        |--------------------------------------------------------------------------
        | Closing
        |--------------------------------------------------------------------------
        */

        'closed_at',

        'closed_by',


        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        'description',


        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        'created_by',

        'updated_by',

        'deleted_by',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'period_number' =>
            'integer',

        'start_date' =>
            'date',

        'end_date' =>
            'date',

        'closed_at' =>
            'datetime',

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
        return $this->belongsTo(
            Company::class
        );
    }


    /**
     * Fiscal Year.
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(
            FiscalYear::class,
            'fiscal_year_id'
        );
    }


    /**
     * Closed By.
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'closed_by'
        );
    }


    /**
     * Created By.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    /**
     * Updated By.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }


    /**
     * Deleted By.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'deleted_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Only open periods.
     */
    public function scopeOpen(
        Builder $query
    ): Builder {

        return $query->where(
            'status',
            'Open'
        );

    }


    /**
     * Only closed periods.
     */
    public function scopeClosed(
        Builder $query
    ): Builder {

        return $query->where(
            'status',
            'Closed'
        );

    }


    /**
     * Filter by company.
     */
    public function scopeByCompany(
        Builder $query,
        int $companyId
    ): Builder {

        return $query->where(
            'company_id',
            $companyId
        );

    }


    /**
     * Find the period containing a date.
     */
    public function scopeForDate(
        Builder $query,
        $date
    ): Builder {

        return $query
            ->whereDate(
                'start_date',
                '<=',
                $date
            )
            ->whereDate(
                'end_date',
                '>=',
                $date
            );

    }

}

}