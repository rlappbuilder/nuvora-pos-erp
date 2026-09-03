<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingJournalAccount;
use App\Models\MasterData\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Accounting\ChartOfAccount;
class AccountingJournalService
{
    /*
    |--------------------------------------------------------------------------
    | Create Accounting Journal
    |--------------------------------------------------------------------------
    */

    public function createAccountingJournal(
        array $data
    ): AccountingJournal {

        return DB::transaction(function () use ($data) {

            $companyId =
                (int) $data['company_id'];


            /*
            |--------------------------------------------------------------------------
            | Validate Company
            |--------------------------------------------------------------------------
            */

            Company::query()
                ->where('id', $companyId)
                ->where('status', true)
                ->firstOrFail();


            /*
            |--------------------------------------------------------------------------
            | Duplicate Code
            |--------------------------------------------------------------------------
            */

            $exists =
                AccountingJournal::withTrashed()
                    ->where(
                        'company_id',
                        $companyId
                    )
                    ->where(
                        'code',
                        $data['code']
                    )
                    ->exists();


            if ($exists) {

                throw ValidationException::withMessages([

                    'code' =>
                        'Journal code already exists for this company.',

                ]);

            }


            /*
            |--------------------------------------------------------------------------
            | Create Journal
            |--------------------------------------------------------------------------
            */

            $journal =
                AccountingJournal::create([

                    'company_id' =>
                        $companyId,

                    'code' =>
                        $data['code'],

                    'name' =>
                        $data['name'],

                    'type' =>
                        $data['type']
                        ?? 'General',

                    'is_active' =>
                        $data['is_active']
                        ?? true,

                    'description' =>
                        $data['description']
                        ?? null,

                    'created_by' =>
                        $data['created_by']
                        ?? auth()->id(),

                    'updated_by' =>
                        $data['updated_by']
                        ?? auth()->id(),

                ]);


            /*
            |--------------------------------------------------------------------------
            | Journal Accounts
            |--------------------------------------------------------------------------
            */

            $this->syncAccounts(
                $journal,
                $data['accounts'] ?? [],
                $data['created_by'] ?? auth()->id(),
                $data['updated_by'] ?? auth()->id()
            );


            return $journal->fresh([
                'company',
                'accounts.account',
            ]);

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Update Accounting Journal
    |--------------------------------------------------------------------------
    */

    public function updateAccountingJournal(
        AccountingJournal $journal,
        array $data
    ): AccountingJournal {

        return DB::transaction(function () use (
            $journal,
            $data
        ) {

            /*
            |--------------------------------------------------------------------------
            | Duplicate Code
            |--------------------------------------------------------------------------
            */

            $exists =
                AccountingJournal::withTrashed()
                    ->where(
                        'company_id',
                        $journal->company_id
                    )
                    ->where(
                        'code',
                        $data['code']
                    )
                    ->where(
                        'id',
                        '!=',
                        $journal->id
                    )
                    ->exists();


            if ($exists) {

                throw ValidationException::withMessages([

                    'code' =>
                        'Journal code already exists for this company.',

                ]);

            }


            /*
            |--------------------------------------------------------------------------
            | Update Journal
            |--------------------------------------------------------------------------
            */

            $journal->update([

                'code' =>
                    $data['code'],

                'name' =>
                    $data['name'],

                'type' =>
                    $data['type']
                    ?? 'General',

                'is_active' =>
                    $data['is_active']
                    ?? true,

                'description' =>
                    $data['description']
                    ?? null,

                'updated_by' =>
                    $data['updated_by']
                    ?? auth()->id(),

            ]);

            /*
            |--------------------------------------------------------------------------
            | Journal Accounts
            |--------------------------------------------------------------------------
            */

            if (
                array_key_exists(
                    'accounts',
                    $data
                )
            ) {

                $journal
                    ->accounts()
                    ->withTrashed()
                    ->forceDelete();


                $this->syncAccounts(
                    $journal,
                    $data['accounts'],
                    $data['updated_by'] ?? auth()->id(),
                    $data['updated_by'] ?? auth()->id()
                );

            }


            return $journal->fresh([

                'company',

                'accounts.account',

            ]);

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Sync Journal Accounts
    |--------------------------------------------------------------------------
    */

    protected function syncAccounts(
        AccountingJournal $journal,
        array $accounts,
        ?int $createdBy = null,
        ?int $updatedBy = null
    ): void {

        foreach (
            $accounts as $account
        ) {

            AccountingJournalAccount::create([

                'accounting_journal_id' =>
                    $journal->id,

                'account_id' =>
                    $account['account_id'],

                'role' =>
                    $account['role']
                    ?? 'default',

                'is_default' =>
                    $account['is_default']
                    ?? false,

                'created_by' =>
                    $createdBy
                    ?? auth()->id(),

                'updated_by' =>
                    $updatedBy
                    ?? auth()->id(),

            ]);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Delete Accounting Journal
    |--------------------------------------------------------------------------
    */

    public function deleteAccountingJournal(
        AccountingJournal $journal,
        ?int $userId = null
    ): void {

        $userId =
            $userId
            ?? auth()->id();


        $journal->update([

            'deleted_by' =>
                $userId,

            'updated_by' =>
                $userId,

        ]);


        $journal->delete();

    }


    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */
/*
|--------------------------------------------------------------------------
| Form Data
|--------------------------------------------------------------------------
*/

public function formData(
    ?int $companyId = null
): array {

    $companies =
        Company::query()
            ->where(
                'status',
                true
            )
            ->orderBy(
                'company_name'
            )
            ->get([
                'id',
                'company_name',
            ]);


    /*
    |--------------------------------------------------------------------------
    | Chart Of Accounts
    |--------------------------------------------------------------------------
    */

    $coaAccounts =
        ChartOfAccount::query()
            ->active()
            ->posting()
            ->when(
                $companyId,
                function ($query) use ($companyId) {

                    $query->byCompany(
                        $companyId
                    );

                }
            )
            ->orderBy(
                'code'
            )
            ->get([
                'id',
                'company_id',
                'code',
                'name',
            ]);


    return [

        'companies' =>
            $companies,

        'coaAccounts' =>
            $coaAccounts,

    ];
}
}