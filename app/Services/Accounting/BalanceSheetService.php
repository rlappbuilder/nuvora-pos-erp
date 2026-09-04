<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalYear;
use App\Models\MasterData\Branch;

class BalanceSheetService
{
    /*
    |--------------------------------------------------------------------------
    | Generate Balance Sheet
    |--------------------------------------------------------------------------
    */

    public function generate(
        ?int $branchId = null,
        ?int $fiscalYearId = null,
        ?int $accountingPeriodId = null,
        ?string $asOfDate = null
    ): array {

        $asOfDate =
            $asOfDate
            ?: now()->toDateString();


        /*
        |--------------------------------------------------------------------------
        | Resolve Company
        |--------------------------------------------------------------------------
        */

        $companyId = null;

        if ($branchId) {

            $companyId =
                Branch::query()
                    ->whereKey($branchId)
                    ->value('company_id');

        } elseif ($fiscalYearId) {

            $companyId =
                FiscalYear::query()
                    ->whereKey($fiscalYearId)
                    ->value('company_id');

        } elseif ($accountingPeriodId) {

            $companyId =
                AccountingPeriod::query()
                    ->whereKey($accountingPeriodId)
                    ->value('company_id');

        }


        /*
        |--------------------------------------------------------------------------
        | Load Balance Sheet Accounts
        |--------------------------------------------------------------------------
        */

        $accounts =
            ChartOfAccount::query()

                ->with([
                    'accountCategory.accountType.accountGroup',
                ])

                ->active()

                ->posting()

                ->when(
                    $companyId,
                    fn ($query) =>
                        $query->where(
                            'company_id',
                            $companyId
                        )
                )

                ->withSum(
                    [
                        'ledgerEntries as period_debit' =>
                            function ($query) use (
                                $branchId,
                                $fiscalYearId,
                                $accountingPeriodId,
                                $asOfDate
                            ) {

                                $query
                                    ->whereDate(
                                        'entry_date',
                                        '<=',
                                        $asOfDate
                                    )

                                    ->when(
                                        $branchId,
                                        fn ($query) =>
                                            $query->where(
                                                'branch_id',
                                                $branchId
                                            )
                                    )

                                    ->when(
                                        $fiscalYearId,
                                        fn ($query) =>
                                            $query->where(
                                                'fiscal_year_id',
                                                $fiscalYearId
                                            )
                                    )

                                    ->when(
                                        $accountingPeriodId,
                                        fn ($query) =>
                                            $query->where(
                                                'accounting_period_id',
                                                $accountingPeriodId
                                            )
                                    );
                            },
                    ],
                    'debit'
                )

                ->withSum(
                    [
                        'ledgerEntries as period_credit' =>
                            function ($query) use (
                                $branchId,
                                $fiscalYearId,
                                $accountingPeriodId,
                                $asOfDate
                            ) {

                                $query
                                    ->whereDate(
                                        'entry_date',
                                        '<=',
                                        $asOfDate
                                    )

                                    ->when(
                                        $branchId,
                                        fn ($query) =>
                                            $query->where(
                                                'branch_id',
                                                $branchId
                                            )
                                    )

                                    ->when(
                                        $fiscalYearId,
                                        fn ($query) =>
                                            $query->where(
                                                'fiscal_year_id',
                                                $fiscalYearId
                                            )
                                    )

                                    ->when(
                                        $accountingPeriodId,
                                        fn ($query) =>
                                            $query->where(
                                                'accounting_period_id',
                                                $accountingPeriodId
                                            )
                                    );
                            },
                    ],
                    'credit'
                )

                ->orderBy('code')

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Normalize Accounts
        |--------------------------------------------------------------------------
        */

        $rows =
            $accounts
                ->map(function ($account) {

                    $opening =
                        (float) (
                            $account->opening_balance
                            ?? 0
                        );

                    $debit =
                        (float) (
                            $account->period_debit
                            ?? 0
                        );

                    $credit =
                        (float) (
                            $account->period_credit
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Ending Balance
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $account->normal_balance === 'Debit'
                    ) {

                        $balance =
                            $opening
                            + $debit
                            - $credit;

                    } else {

                        $balance =
                            $opening
                            + $credit
                            - $debit;

                    }


                    return [

                        'id' =>
                            $account->id,

                        'code' =>
                            $account->code,

                        'name' =>
                            $account->name,

                        'normal_balance' =>
                            $account->normal_balance,

                        'balance' =>
                            $balance,

                        'account_category' =>
                            $account
                                ->accountCategory
                                ?->name,

                        'account_type' =>
                            $account
                                ->accountCategory
                                ?->accountType
                                ?->name,

                        'account_group' =>
                            $account
                                ->accountCategory
                                ?->accountType
                                ?->accountGroup
                                ?->name,

                        'account_group_code' =>
                            $account
                                ->accountCategory
                                ?->accountType
                                ?->accountGroup
                                ?->code,

                    ];

                })
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Balance Sheet Groups
        |--------------------------------------------------------------------------
        */

        $assets =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '100000'
                )
                ->values();

        $liabilities =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '200000'
                )
                ->values();

        $equity =
    $rows
        ->filter(
            fn ($row) =>
                $row['account_group_code']
                === '300000'
                && $row['code'] !== '310301'
        )
        ->values();


        /*
        |--------------------------------------------------------------------------
        | Profit & Loss Accounts
        |--------------------------------------------------------------------------
        */

        $revenue =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '400000'
                )
                ->sum(
                    fn ($row) =>
                        $row['balance']
                );

        $costOfGoodsSold =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '500000'
                )
                ->sum(
                    fn ($row) =>
                        $row['balance']
                );

        $expense =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '600000'
                )
                ->sum(
                    fn ($row) =>
                        $row['balance']
                );

        $otherIncome =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '700000'
                )
                ->sum(
                    fn ($row) =>
                        $row['balance']
                );

        $otherExpense =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '800000'
                )
                ->sum(
                    fn ($row) =>
                        $row['balance']
                );


        /*
        |--------------------------------------------------------------------------
        | Current Year Earnings
        |--------------------------------------------------------------------------
        */

        $currentYearEarnings =
            $revenue
            - $costOfGoodsSold
            - $expense
            + $otherIncome
            - $otherExpense;


        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */

        $totalAssets =
            $assets->sum('balance');

        $totalLiabilities =
            $liabilities->sum('balance');

        $totalEquity =
            $equity->sum('balance')
            + $currentYearEarnings;

        $totalLiabilitiesAndEquity =
            $totalLiabilities
            + $totalEquity;


        /*
        |--------------------------------------------------------------------------
        | Balance Check
        |--------------------------------------------------------------------------
        */

        $difference =
            $totalAssets
            - $totalLiabilitiesAndEquity;

        $isBalanced =
            abs($difference) < 0.01;


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [

            'total_assets' =>
                $totalAssets,

            'total_liabilities' =>
                $totalLiabilities,

            'total_equity' =>
                $totalEquity,

            'current_year_earnings' =>
                $currentYearEarnings,

            'total_liabilities_equity' =>
                $totalLiabilitiesAndEquity,

            'difference' =>
                $difference,

            'is_balanced' =>
                $isBalanced,

        ];


        /*
        |--------------------------------------------------------------------------
        | Report
        |--------------------------------------------------------------------------
        */

        return [

            'report' => [

                'assets' =>
                    $assets,

                'liabilities' =>
                    $liabilities,

                'equity' =>
                    $equity,

                'current_year_earnings' => [

                    'revenue' =>
                        $revenue,

                    'cost_of_goods_sold' =>
                        $costOfGoodsSold,

                    'expense' =>
                        $expense,

                    'other_income' =>
                        $otherIncome,

                    'other_expense' =>
                        $otherExpense,

                    'net_income' =>
                        $currentYearEarnings,

                ],

            ],

            'statistics' =>
                $statistics,

        ];
    }
}