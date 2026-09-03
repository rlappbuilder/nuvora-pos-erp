<?php

namespace App\Services\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\GeneralLedger;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GeneralLedgerService
{
    /**
     * Create general ledger entries from a journal entry.
     */
 
public function createFromJournalEntry(
    JournalEntry $journalEntry
): void {

    $journalEntry->loadMissing([
        'lines',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Validate Journal Entry
    |--------------------------------------------------------------------------
    */

    if ($journalEntry->status !== 'Draft') {

        throw new RuntimeException(
            'Only Draft journal entry can be posted.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Lines
    |--------------------------------------------------------------------------
    */

    if ($journalEntry->lines->isEmpty()) {

        throw new RuntimeException(
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

        throw new RuntimeException(
            'Journal entry is not balanced. Total debit must equal total credit.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Ledger
    |--------------------------------------------------------------------------
    */

    if (
        GeneralLedger::query()
            ->where(
                'journal_entry_id',
                $journalEntry->id
            )
            ->exists()
    ) {

        throw new RuntimeException(
            'General ledger entries already exist for this journal entry.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Track Affected Accounts
    |--------------------------------------------------------------------------
    */

    $accountIds = [];


    /*
    |--------------------------------------------------------------------------
    | Create Ledger Entries
    |--------------------------------------------------------------------------
    */

    foreach (
        $journalEntry->lines as $line
    ) {

        $account =
            ChartOfAccount::query()
                ->whereKey(
                    $line->account_id
                )
                ->where(
                    'company_id',
                    $journalEntry->company_id
                )
                ->active()
                ->posting()
                ->first();


        if (!$account) {

            throw new RuntimeException(
                "Account ID {$line->account_id} is invalid or not a posting account."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Create GL Row
        |--------------------------------------------------------------------------
        */

        GeneralLedger::create([

            'company_id' =>
                $journalEntry->company_id,

            'branch_id' =>
                $journalEntry->branch_id,

            'account_id' =>
                $account->id,


            /*
            | Source Document
            */

            'journal_entry_id' =>
                $journalEntry->id,

            'journal_entry_line_id' =>
                $line->id,


            /*
            | Accounting Structure
            */

            'accounting_journal_id' =>
                $journalEntry->accounting_journal_id,

            'fiscal_year_id' =>
                $journalEntry->fiscal_year_id,

            'accounting_period_id' =>
                $journalEntry->accounting_period_id,


            /*
            | Transaction
            */

            'entry_date' =>
                $journalEntry->entry_date,

            'reference' =>
                $journalEntry->reference,

            'description' =>
                $line->description
                ?: $journalEntry->description,


            /*
            | Amount
            */

            'debit' =>
                $line->debit,

            'credit' =>
                $line->credit,


            /*
            | Balance
            |
            | Recalculated after all GL rows
            | have been created.
            */

            'running_balance' =>
                0,


            /*
            | Audit
            */

            'created_by' =>
                $journalEntry->updated_by
                ?: $journalEntry->created_by,

        ]);


        $accountIds[] =
            $account->id;
    }


    /*
    |--------------------------------------------------------------------------
    | Recalculate Running Balance
    |--------------------------------------------------------------------------
    */

    foreach (
        array_unique($accountIds) as $accountId
    ) {

        $this->recalculateAccount(
            $journalEntry->company_id,
            $accountId
        );

    }
}

    /**
     * Recalculate running balance for an account.
     */
    public function recalculateAccount(
        int $companyId,
        int $accountId
    ): void {
        $account = ChartOfAccount::query()
            ->whereKey($accountId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $balance = (float) $account->opening_balance;

        $ledgers = GeneralLedger::query()
            ->where('company_id', $companyId)
            ->where('account_id', $accountId)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($ledgers as $ledger) {

            if ($account->normal_balance === 'Debit') {

                $balance =
                    $balance
                    + (float) $ledger->debit
                    - (float) $ledger->credit;

            } else {

                $balance =
                    $balance
                    + (float) $ledger->credit
                    - (float) $ledger->debit;
            }

            $ledger->update([
                'running_balance' => $balance,
            ]);
        }
    }
}