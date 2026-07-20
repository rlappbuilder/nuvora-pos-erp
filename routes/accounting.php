<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Accounting\CashBankController;
use App\Http\Controllers\Accounting\ChartOfAccountController;

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