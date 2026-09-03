<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalYear;
use App\Models\MasterData\Branch;

class IncomeStatementService
{
    /*
    |--------------------------------------------------------------------------
    | Generate Income Statement
    |--------------------------------------------------------------------------
    */

    public function generate(
        ?int $branchId = null,
        ?int $fiscalYearId = null,
        ?int $accountingPeriodId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {

        $dateTo =
            $dateTo
            ?: now()->toDateString();


        /*
        |--------------------------------------------------------------------------
        | Resolve Date From
        |--------------------------------------------------------------------------
        */

        $dateFrom =
            $dateFrom
            ?: (
                $fiscalYearId
                    ? FiscalYear::query()
                        ->whereKey($fiscalYearId)
                        ->value('start_date')
                    : $dateTo
            );


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
        | Load Income Statement Accounts
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
                                $dateFrom,
                                $dateTo
                            ) {

                                $query
                                    ->whereDate(
                                        'entry_date',
                                        '>=',
                                        $dateFrom
                                    )

                                    ->whereDate(
                                        'entry_date',
                                        '<=',
                                        $dateTo
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
                                $dateFrom,
                                $dateTo
                            ) {

                                $query
                                    ->whereDate(
                                        'entry_date',
                                        '>=',
                                        $dateFrom
                                    )

                                    ->whereDate(
                                        'entry_date',
                                        '<=',
                                        $dateTo
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
                    | Period Balance
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $account->normal_balance === 'Debit'
                    ) {

                        $balance =
                            $debit
                            - $credit;

                    } else {

                        $balance =
                            $credit
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

                ->filter(
                    fn ($row) =>
                        in_array(
                            $row['account_group_code'],
                            [
                                '400000',
                                '500000',
                                '600000',
                                '700000',
                                '800000',
                            ],
                            true
                        )
                )

                ->values();


        /*
        |--------------------------------------------------------------------------
        | Income Statement Groups
        |--------------------------------------------------------------------------
        */

        $revenue =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '400000'
                )
                ->values();

        $costOfGoodsSold =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '500000'
                )
                ->values();

        $operatingExpenses =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '600000'
                )
                ->values();

        $otherIncome =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '700000'
                )
                ->values();

        $otherExpenses =
            $rows
                ->filter(
                    fn ($row) =>
                        $row['account_group_code']
                        === '800000'
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */

        $totalRevenue =
            $revenue->sum('balance');

        $totalCostOfGoodsSold =
            $costOfGoodsSold->sum('balance');

        $grossProfit =
            $totalRevenue
            - $totalCostOfGoodsSold;

        $totalOperatingExpenses =
            $operatingExpenses->sum('balance');

        $operatingIncome =
            $grossProfit
            - $totalOperatingExpenses;

        $totalOtherIncome =
            $otherIncome->sum('balance');

        $totalOtherExpenses =
            $otherExpenses->sum('balance');

        $netIncome =
            $operatingIncome
            + $totalOtherIncome
            - $totalOtherExpenses;


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [

            'total_revenue' =>
                $totalRevenue,

            'total_cost_of_goods_sold' =>
                $totalCostOfGoodsSold,

            'gross_profit' =>
                $grossProfit,

            'total_operating_expenses' =>
                $totalOperatingExpenses,

            'operating_income' =>
                $operatingIncome,

            'total_other_income' =>
                $totalOtherIncome,

            'total_other_expenses' =>
                $totalOtherExpenses,

            'net_income' =>
                $netIncome,

        ];


        /*
        |--------------------------------------------------------------------------
        | Report
        |--------------------------------------------------------------------------
        */

        return [

            'report' => [

                'revenue' =>
                    $revenue,

                'cost_of_goods_sold' =>
                    $costOfGoodsSold,

                'operating_expenses' =>
                    $operatingExpenses,

                'other_income' =>
                    $otherIncome,

                'other_expenses' =>
                    $otherExpenses,

                'gross_profit' =>
                    $grossProfit,

                'operating_income' =>
                    $operatingIncome,

                'net_income' =>
                    $netIncome,

            ],

            'statistics' =>
                $statistics,

        ];
    }
}