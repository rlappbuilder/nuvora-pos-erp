<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Accounting\FiscalYearController;
use App\Http\Controllers\Accounting\CashBankController;
use App\Http\Controllers\Accounting\ChartOfAccountController;
use App\Http\Controllers\Accounting\AccountingPeriodController;

Route::middleware('auth')

    ->prefix('accounting')

    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | CASH BANK
        |--------------------------------------------------------------------------
        */
         Route::delete(
            '/cash-banks/bulk-delete',
            [CashBankController::class, 'bulkDelete']
        )->name('cash-banks.bulk-delete');


        Route::patch(
            'cash-banks/bulk-activate',
            [CashBankController::class, 'bulkActivate']
        )->name('cash-banks.bulk-activate');

        Route::patch(
            'cash-banks/bulk-deactivate',
            [CashBankController::class, 'bulkDeactivate']
        )->name('cash-banks.bulk-deactivate');

        Route::get(
            '/cash-banks/{cashBank}/export',
            [CashBankController::class, 'export']
        )->name('cash-banks.export');
        
        Route::get(
            '/cash-banks/{cashBank}/duplicate',
            [CashBankController::class, 'duplicate']
        )->name('cash-banks.duplicate');

        Route::get(
            '/cash-banks/{cashBank}/print',
            [CashBankController::class, 'print']
        )->name('cash-banks.print');
            /*
            |--------------------------------------------------------------------------
            | fiscal years
            |--------------------------------------------------------------------------
            */

            Route::get(
                'fiscal-years/create',
                [FiscalYearController::class, 'create']
            )->name('fiscal-years.create');

                            Route::get(
                            '/fiscal-years',
                            [
                                FiscalYearController::class,
                                'index',
                            ]
                        )->name('fiscal-years.index');


                        Route::post(
                            '/fiscal-years',
                            [
                                FiscalYearController::class,
                                'store',
                            ]
                        )->name('fiscal-years.store');


                        Route::get(
                            '/fiscal-years/{fiscalYear}',
                            [
                                FiscalYearController::class,
                                'show',
                            ]
                        )->name('fiscal-years.show');

                        Route::put(
                            '/fiscal-years/{fiscalYear}',
                            [
                                FiscalYearController::class,
                                'update',
                            ]
                        )->name('fiscal-years.update');
                        Route::post(
                            '/fiscal-years/{fiscalYear}/close',
                            [
                                FiscalYearController::class,
                                'close',
                            ]
                        )->name('fiscal-years.close');


                        Route::post(
                            '/fiscal-years/{fiscalYear}/reopen',
                            [
                                FiscalYearController::class,
                                'reopen',
                            ]
                        )->name('fiscal-years.reopen');
             /*
            |--------------------------------------------------------------------------
            | accounting period
            |--------------------------------------------------------------------------
            */

                   Route::get(
                    'accounting-periods',
                    [AccountingPeriodController::class, 'index']
                )->name('accounting-periods.index');

                Route::get(
                    'accounting-periods/{accountingPeriod}',
                    [AccountingPeriodController::class, 'show']
                )->name('accounting-periods.show');

                Route::post(
                    'accounting-periods/{accountingPeriod}/close',
                    [AccountingPeriodController::class, 'close']
                )->name('accounting-periods.close');

                Route::post(
                    'accounting-periods/{accountingPeriod}/reopen',
                    [AccountingPeriodController::class, 'reopen']
                )->name('accounting-periods.reopen');
                /*
            |--------------------------------------------------------------------------
            | CHART OF ACCOUNTS
            |--------------------------------------------------------------------------
            */

          Route::post(
                'chart-of-accounts/bulk-delete',
                [ChartOfAccountController::class, 'bulkDelete']
            )->name('chart-of-accounts.bulk-delete');

            Route::post(
                'chart-of-accounts/bulk-activate',
                [ChartOfAccountController::class, 'bulkActivate']
            )->name('chart-of-accounts.bulk-activate');

            Route::post(
                'chart-of-accounts/bulk-deactivate',
                [ChartOfAccountController::class, 'bulkDeactivate']
            )->name('chart-of-accounts.bulk-deactivate');

            Route::get(
                '/chart-of-accounts/{chartOfAccount}/export',
                [ChartOfAccountController::class, 'export']
            )->name('chart-of-accounts.export');

            Route::get(
                '/chart-of-accounts/{chartOfAccount}/duplicate',
                [ChartOfAccountController::class, 'duplicate'
            ])->name('chart-of-accounts.duplicate');

            Route::get(
                '/chart-of-accounts/{chartOfAccount}/print',
                [ChartOfAccountController::class, 'print']
            )->name('chart-of-accounts.print');

             Route::get(
                'chart-of-accounts/preview-code',
                [ChartOfAccountController::class, 'previewCode']
            )->name('chart-of-accounts.preview-code');

             Route::resource(
                    'chart-of-accounts',
                    ChartOfAccountController::class
                );

         

        Route::resource(

            'cash-banks',

            CashBankController::class

        );

       

    });