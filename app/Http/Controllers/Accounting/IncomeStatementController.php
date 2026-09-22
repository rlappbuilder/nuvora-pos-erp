<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Models\MasterData\Branch;
use App\Services\Accounting\IncomeStatementService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomeStatementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request,
        IncomeStatementService $incomeStatementService
    ) {
        $companyId =
            auth()->user()->company_id;


        $branchId =
            $request->integer('branch_id');

        $fiscalYearId =
            $request->integer('fiscal_year_id');

        $accountingPeriodId =
            $request->integer('accounting_period_id');

        $dateFrom =
            $request->input(
                'date_from'
            );

        $dateTo =
            $request->input(
                'date_to'
            ) ?: now()->toDateString();


        /*
        |--------------------------------------------------------------------------
        | Validate Branch Belongs To Current Company
        |--------------------------------------------------------------------------
        */

        if (
            $branchId
            && ! Branch::query()
                ->whereKey($branchId)
                ->where(
                    'company_id',
                    $companyId
                )
                ->exists()
        ) {
            $branchId = null;
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Fiscal Year Belongs To Current Company
        |--------------------------------------------------------------------------
        */

        if (
            $fiscalYearId
            && ! FiscalYear::query()
                ->whereKey($fiscalYearId)
                ->where(
                    'company_id',
                    $companyId
                )
                ->exists()
        ) {
            $fiscalYearId = null;
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Accounting Period Belongs To Current Company
        |--------------------------------------------------------------------------
        */

        if (
            $accountingPeriodId
            && ! AccountingPeriod::query()
                ->whereKey($accountingPeriodId)
                ->where(
                    'company_id',
                    $companyId
                )
                ->exists()
        ) {
            $accountingPeriodId = null;
        }


        /*
        |--------------------------------------------------------------------------
        | Generate Report
        |--------------------------------------------------------------------------
        */

        $result =
            $incomeStatementService->generate(
                $branchId,
                $fiscalYearId,
                $accountingPeriodId,
                $dateFrom,
                $dateTo
            );


        /*
        |--------------------------------------------------------------------------
        | Form Data
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Accounting/IncomeStatement/Index',
            [

                'title' =>
                    'Income Statement',

                'report' =>
                    $result['report'],

                'statistics' =>
                    $result['statistics'],

                'filters' => [

                    'branch_id' =>
                        $branchId ?: '',

                    'fiscal_year_id' =>
                        $fiscalYearId ?: '',

                    'accounting_period_id' =>
                        $accountingPeriodId ?: '',

                    'date_from' =>
                        $dateFrom ?: '',

                    'date_to' =>
                        $dateTo,

                ],

                ...$this->formData(
                    $companyId
                ),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    private function formData(
        int $companyId
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        $branches =
            Branch::query()
                ->where(
                    'company_id',
                    $companyId
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'company_id',
                    'name',
                ])
                ->map(
                    fn ($branch) => [

                        'id' =>
                            $branch->id,

                        'company_id' =>
                            $branch->company_id,

                        'label' =>
                            $branch->name,

                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Fiscal Years
        |--------------------------------------------------------------------------
        */

        $fiscalYears =
            FiscalYear::query()
                ->where(
                    'company_id',
                    $companyId
                )
                ->open()
                ->orderByDesc('year')
                ->get([
                    'id',
                    'company_id',
                    'year',
                    'start_date',
                    'end_date',
                ])
                ->map(
                    fn ($year) => [

                        'id' =>
                            $year->id,

                        'company_id' =>
                            $year->company_id,

                        'year' =>
                            $year->year,

                        'start_date' =>
                            $year->start_date,

                        'end_date' =>
                            $year->end_date,

                        'label' =>
                            (string) $year->year,

                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Accounting Periods
        |--------------------------------------------------------------------------
        */

        $accountingPeriods =
            AccountingPeriod::query()
                ->where(
                    'company_id',
                    $companyId
                )
                ->open()
                ->orderBy(
                    'fiscal_year_id'
                )
                ->orderBy(
                    'period_number'
                )
                ->get([
                    'id',
                    'company_id',
                    'fiscal_year_id',
                    'period_number',
                    'name',
                    'start_date',
                    'end_date',
                ])
                ->map(
                    fn ($period) => [

                        'id' =>
                            $period->id,

                        'company_id' =>
                            $period->company_id,

                        'fiscal_year_id' =>
                            $period->fiscal_year_id,

                        'period_number' =>
                            $period->period_number,

                        'name' =>
                            $period->name,

                        'start_date' =>
                            $period->start_date,

                        'end_date' =>
                            $period->end_date,

                        'label' =>
                            $period->name,

                    ]
                )
                ->values();


        return [

            'branches' =>
                $branches,

            'fiscalYears' =>
                $fiscalYears,

            'accountingPeriods' =>
                $accountingPeriods,

        ];
    }

    /*
|--------------------------------------------------------------------------
| Print
|--------------------------------------------------------------------------
*/

public function print(
    Request $request,
    IncomeStatementService $incomeStatementService
) {

    $companyId =
        auth()->user()->company_id;


    $branchId =
        $request->integer(
            'branch_id'
        );

    $fiscalYearId =
        $request->integer(
            'fiscal_year_id'
        );

    $accountingPeriodId =
        $request->integer(
            'accounting_period_id'
        );

    $dateFrom =
        $request->input(
            'date_from'
        );

    $dateTo =
        $request->input(
            'date_to'
        )
        ?: now()->toDateString();


    /*
    |--------------------------------------------------------------------------
    | Validate Branch Belongs To Company
    |--------------------------------------------------------------------------
    */

    if (
        $branchId
        &&
        ! Branch::query()
            ->where(
                'id',
                $branchId
            )
            ->where(
                'company_id',
                $companyId
            )
            ->exists()
    ) {

        $branchId =
            null;

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Fiscal Year Belongs To Company
    |--------------------------------------------------------------------------
    */

    if (
        $fiscalYearId
        &&
        ! FiscalYear::query()
            ->where(
                'id',
                $fiscalYearId
            )
            ->where(
                'company_id',
                $companyId
            )
            ->exists()
    ) {

        $fiscalYearId =
            null;

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Accounting Period Belongs To Company
    |--------------------------------------------------------------------------
    */

    if (
        $accountingPeriodId
        &&
        ! AccountingPeriod::query()
            ->where(
                'id',
                $accountingPeriodId
            )
            ->where(
                'company_id',
                $companyId
            )
            ->exists()
    ) {

        $accountingPeriodId =
            null;

    }


    /*
    |--------------------------------------------------------------------------
    | Generate Report
    |--------------------------------------------------------------------------
    */

    $result =
        $incomeStatementService->generate(
            $branchId,
            $fiscalYearId,
            $accountingPeriodId,
            $dateFrom,
            $dateTo
        );


    /*
    |--------------------------------------------------------------------------
    | Selected Filter Labels
    |--------------------------------------------------------------------------
    */

    $branch =
        $branchId
            ? Branch::query()
                ->where(
                    'id',
                    $branchId
                )
                ->where(
                    'company_id',
                    $companyId
                )
                ->first()
            : null;


    $fiscalYear =
        $fiscalYearId
            ? FiscalYear::query()
                ->where(
                    'id',
                    $fiscalYearId
                )
                ->where(
                    'company_id',
                    $companyId
                )
                ->first()
            : null;


    $accountingPeriod =
        $accountingPeriodId
            ? AccountingPeriod::query()
                ->where(
                    'id',
                    $accountingPeriodId
                )
                ->where(
                    'company_id',
                    $companyId
                )
                ->first()
            : null;


    /*
    |--------------------------------------------------------------------------
    | Print View
    |--------------------------------------------------------------------------
    */

    return view(
        'print.Accounting.income-statement',
        [

            'title' =>
                'Income Statement',

            'report' =>
                $result['report'],

            'statistics' =>
                $result['statistics'],

            'filters' => [

                'branch_id' =>
                    $branchId ?: '',

                'branch_label' =>
                    $branch?->name,

                'fiscal_year_id' =>
                    $fiscalYearId ?: '',

                'fiscal_year_label' =>
                    $fiscalYear?->year,

                'accounting_period_id' =>
                    $accountingPeriodId ?: '',

                'accounting_period_label' =>
                    $accountingPeriod?->name,

                'date_from' =>
                    $dateFrom ?: '',

                'date_to' =>
                    $dateTo,

            ],

        ]
    );

}
}