<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\AccountingPeriod;
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
                    $branchId
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
        ?int $branchId = null
    ): array {

        $branches =
            Branch::query()
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


        $companyId = null;

        if ($branchId) {

            $branch =
                $branches->firstWhere(
                    'id',
                    $branchId
                );

            $companyId =
                $branch['company_id']
                ?? null;

        }


        $fiscalYears =
            FiscalYear::query()
                ->when(
                    $companyId,
                    fn ($query) =>
                        $query->where(
                            'company_id',
                            $companyId
                        )
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


        $accountingPeriods =
            AccountingPeriod::query()
                ->when(
                    $companyId,
                    fn ($query) =>
                        $query->where(
                            'company_id',
                            $companyId
                        )
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
}