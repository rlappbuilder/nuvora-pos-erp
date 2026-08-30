<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\MasterData\Company;
class FiscalYearService
{
    /*
    |--------------------------------------------------------------------------
    | Create Fiscal Year
    |--------------------------------------------------------------------------
    */

    public function createFiscalYear(
        array $data
    ): FiscalYear {

        return DB::transaction(function () use ($data) {

            $companyId =
                (int) $data['company_id'];

            $year =
                (int) $data['year'];


            /*
            |--------------------------------------------------------------------------
            | Duplicate Fiscal Year
            |--------------------------------------------------------------------------
            */

            $exists =
                FiscalYear::withTrashed()
                    ->where(
                        'company_id',
                        $companyId
                    )
                    ->where(
                        'year',
                        $year
                    )
                    ->exists();


            if ($exists) {

                throw ValidationException::withMessages([
                    'year' =>
                        'Fiscal year already exists for this company.',
                ]);

            }


            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            $startDate =
                Carbon::create(
                    $year,
                    1,
                    1
                )->startOfDay();


            $endDate =
                Carbon::create(
                    $year,
                    12,
                    31
                )->endOfDay();


            /*
            |--------------------------------------------------------------------------
            | Create Fiscal Year
            |--------------------------------------------------------------------------
            */

            $fiscalYear =
                FiscalYear::create([

                    'company_id' =>
                        $companyId,

                    'year' =>
                        $year,

                    'start_date' =>
                        $startDate->toDateString(),

                    'end_date' =>
                        $endDate->toDateString(),

                    'status' =>
                        'Open',

                    'is_closed' =>
                        false,

                    'description' =>
                        $data['description'] ?? null,

                    'created_by' =>
                        $data['created_by'] ?? auth()->id(),

                    'updated_by' =>
                        $data['updated_by'] ?? auth()->id(),

                ]);


            /*
            |--------------------------------------------------------------------------
            | Generate Accounting Periods
            |--------------------------------------------------------------------------
            */

            $this->generatePeriods(
                $fiscalYear
            );


            return $fiscalYear->load(
                'periods'
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Generate Periods
    |--------------------------------------------------------------------------
    */

    public function generatePeriods(
        FiscalYear $fiscalYear
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Generation
        |--------------------------------------------------------------------------
        */

        if (
            $fiscalYear
                ->periods()
                ->exists()
        ) {

            return;

        }


        $year =
            (int) $fiscalYear->year;


        /*
        |--------------------------------------------------------------------------
        | Generate 12 Monthly Periods
        |--------------------------------------------------------------------------
        */

        for (
            $month = 1;
            $month <= 12;
            $month++
        ) {

            $startDate =
                Carbon::create(
                    $year,
                    $month,
                    1
                )->startOfDay();


            $endDate =
                $startDate
                    ->copy()
                    ->endOfMonth()
                    ->endOfDay();


            AccountingPeriod::create([

                'company_id' =>
                    $fiscalYear->company_id,

                'fiscal_year_id' =>
                    $fiscalYear->id,

                'period_number' =>
                    $month,

                'name' =>
                    $startDate->format('F Y'),

                'start_date' =>
                    $startDate->toDateString(),

                'end_date' =>
                    $endDate->toDateString(),

                'status' =>
                    'Open',

                'created_by' =>
                    auth()->id(),

                'updated_by' =>
                    auth()->id(),

            ]);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Find Period By Date
    |--------------------------------------------------------------------------
    */

    public function findPeriodByDate(
        int $companyId,
        $date,
        bool $openOnly = false
    ): ?AccountingPeriod {

        $query =
            AccountingPeriod::query()
                ->byCompany(
                    $companyId
                )
                ->forDate(
                    $date
                );


        if ($openOnly) {

            $query->open();

        }


        return $query
            ->with('fiscalYear')
            ->first();

    }


    /*
    |--------------------------------------------------------------------------
    | Get Open Period
    |--------------------------------------------------------------------------
    */

    public function getOpenPeriod(
        int $companyId,
        $date
    ): AccountingPeriod {

        $period =
            $this->findPeriodByDate(
                $companyId,
                $date,
                true
            );


        if (!$period) {

            throw ValidationException::withMessages([
                'date' =>
                    'No open accounting period exists for the selected date.',
            ]);

        }


        return $period;

    }


    /*
    |--------------------------------------------------------------------------
    | Close Period
    |--------------------------------------------------------------------------
    */

    public function closePeriod(
        AccountingPeriod $period,
        ?int $userId = null
    ): AccountingPeriod {

        if (
            $period->status === 'Closed'
        ) {

            throw ValidationException::withMessages([
                'period' =>
                    'Accounting period is already closed.',
            ]);

        }


        $period->update([

            'status' =>
                'Closed',

            'closed_at' =>
                now(),

            'closed_by' =>
                $userId ?? auth()->id(),

            'updated_by' =>
                $userId ?? auth()->id(),

        ]);


        return $period->fresh();

    }


    /*
    |--------------------------------------------------------------------------
    | Reopen Period
    |--------------------------------------------------------------------------
    */

    public function reopenPeriod(
        AccountingPeriod $period,
        ?int $userId = null
    ): AccountingPeriod {

        /*
        |--------------------------------------------------------------------------
        | Fiscal Year Check
        |--------------------------------------------------------------------------
        */

        $fiscalYear =
            $period->fiscalYear;


        if (
            $fiscalYear &&
            $fiscalYear->status === 'Closed'
        ) {

            throw ValidationException::withMessages([
                'period' =>
                    'Cannot reopen a period belonging to a closed fiscal year.',
            ]);

        }


        $period->update([

            'status' =>
                'Open',

            'closed_at' =>
                null,

            'closed_by' =>
                null,

            'updated_by' =>
                $userId ?? auth()->id(),

        ]);


        return $period->fresh();

    }


    /*
    |--------------------------------------------------------------------------
    | Close Fiscal Year
    |--------------------------------------------------------------------------
    */

    public function closeFiscalYear(
        FiscalYear $fiscalYear,
        ?int $userId = null
    ): FiscalYear {

        if (
            $fiscalYear->status === 'Closed'
        ) {

            throw ValidationException::withMessages([
                'fiscal_year' =>
                    'Fiscal year is already closed.',
            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | All Periods Must Exist
        |--------------------------------------------------------------------------
        */

        $periodCount =
            $fiscalYear
                ->periods()
                ->count();


        if (
            $periodCount !== 12
        ) {

            throw ValidationException::withMessages([
                'fiscal_year' =>
                    'Fiscal year must contain all 12 accounting periods before closing.',
            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | All Periods Must Be Closed
        |--------------------------------------------------------------------------
        */

        $openPeriods =
            $fiscalYear
                ->periods()
                ->open()
                ->count();


        if (
            $openPeriods > 0
        ) {

            throw ValidationException::withMessages([
                'fiscal_year' =>
                    'All accounting periods must be closed before closing the fiscal year.',
            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Close Fiscal Year
        |--------------------------------------------------------------------------
        */

        $userId =
            $userId ?? auth()->id();


        $fiscalYear->update([

            'status' =>
                'Closed',

            'is_closed' =>
                true,

            'closed_at' =>
                now(),

            'closed_by' =>
                $userId,

            'updated_by' =>
                $userId,

        ]);


        return $fiscalYear->fresh();

    }


    /*
    |--------------------------------------------------------------------------
    | Reopen Fiscal Year
    |--------------------------------------------------------------------------
    */

    public function reopenFiscalYear(
        FiscalYear $fiscalYear,
        ?int $userId = null
    ): FiscalYear {

        $fiscalYear->update([

            'status' =>
                'Open',

            'is_closed' =>
                false,

            'closed_at' =>
                null,

            'closed_by' =>
                null,

            'updated_by' =>
                $userId ?? auth()->id(),

        ]);


        return $fiscalYear->fresh();

    }
/*
|--------------------------------------------------------------------------
| Update Fiscal Year
|--------------------------------------------------------------------------
*/

public function updateFiscalYear(
    FiscalYear $fiscalYear,
    array $data
): FiscalYear {

    /*
    |--------------------------------------------------------------------------
    | Closed Fiscal Year
    |--------------------------------------------------------------------------
    */

    if (
        $fiscalYear->status === 'Closed'
    ) {

        throw ValidationException::withMessages([
            'fiscal_year' =>
                'Closed fiscal year cannot be edited.',
        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    $fiscalYear->update([

        'description' =>
            $data['description'] ?? null,

        'updated_by' =>
            $data['updated_by'] ?? auth()->id(),

    ]);


    return $fiscalYear->fresh([
        'periods',
    ]);

}
public function formData(): array
{
    return [

        'companies' => Company::query()
            ->where('status', true)
            ->orderBy('company_name')
            ->get([
                'id',
                'company_name',
            ]),

    ];
}
}