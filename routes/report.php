<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\AllSalesController;
use App\Http\Controllers\Report\SalesByCategoryController;
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



                 /*
                |--------------------------------------------------------------------------
                | Sales by Category
                |--------------------------------------------------------------------------
                */

                Route::get(
                    'by-category',
                    [SalesByCategoryController::class, 'parameters']
                )->name('by-category.parameters');

                Route::post(
                    'by-category/generate',
                    [SalesByCategoryController::class, 'generate']
                )->name('by-category.generate');

                Route::get(
                    'by-category/results',
                    [SalesByCategoryController::class, 'results']
                )->name('by-category.results');

                Route::get(
                    'by-category/print',
                    [SalesByCategoryController::class, 'print']
                )->name('by-category.print');

                Route::get(
                    'by-category/pdf',
                    [SalesByCategoryController::class, 'exportPdf']
                )->name('by-category.pdf');

                Route::get(
                    'by-category/excel',
                    [SalesByCategoryController::class, 'exportExcel']
                )->name('by-category.excel');

        });

});