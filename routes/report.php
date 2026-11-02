<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\AllSalesController;

/*
|--------------------------------------------------------------------------
| Report Center Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Sales Reports
    |--------------------------------------------------------------------------
    */

    Route::prefix('reports/sales')
        ->name('reports.sales.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | All Sales
            |--------------------------------------------------------------------------
            */

            Route::get(
                'all',
                [AllSalesController::class, 'parameters']
            )->name('all.parameters');

            Route::post(
                'all/generate',
                [AllSalesController::class, 'generate']
            )->name('all.generate');

            Route::get(
                'all/results',
                [AllSalesController::class, 'results']
            )->name('all.results');

            Route::get(
                'all/print',
                [AllSalesController::class, 'print']
            )->name('all.print');

            Route::get(
                'all/pdf',
                [AllSalesController::class, 'exportPdf']
            )->name('all.pdf');

            Route::get(
                'all/excel',
                [AllSalesController::class, 'exportExcel']
            )->name('all.excel');

        });

});