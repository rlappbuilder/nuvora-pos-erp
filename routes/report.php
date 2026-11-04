<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\AllSalesController;
use App\Http\Controllers\Report\SalesByCategoryController;
use App\Http\Controllers\Report\ConsignmentSalesController;
use App\Http\Controllers\Report\SalesByProductController;
use App\Http\Controllers\Report\SalesByBranchController;


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

            /*
            |--------------------------------------------------------------------------
            | Consignment Sales
            |--------------------------------------------------------------------------
            */

            Route::get(
                'consignment-sales',
                [ConsignmentSalesController::class, 'parameters']
            )->name('consignment-sales.parameters');

            Route::post(
                'consignment-sales/generate',
                [ConsignmentSalesController::class, 'generate']
            )->name('consignment-sales.generate');

            Route::get(
                'consignment-sales/results',
                [ConsignmentSalesController::class, 'results']
            )->name('consignment-sales.results');

            Route::get(
                'consignment-sales/print',
                [ConsignmentSalesController::class, 'print']
            )->name('consignment-sales.print');

            Route::get(
                'consignment-sales/pdf',
                [ConsignmentSalesController::class, 'exportPdf']
            )->name('consignment-sales.pdf');

            Route::get(
                'consignment-sales/excel',
                [ConsignmentSalesController::class, 'exportExcel']
            )->name('consignment-sales.excel');

            /*
            |--------------------------------------------------------------------------
            | Sales by Product
            |--------------------------------------------------------------------------
            */

            Route::get(
                'by-product',
                [SalesByProductController::class, 'parameters']
            )->name('by-product.parameters');

            Route::post(
                'by-product/generate',
                [SalesByProductController::class, 'generate']
            )->name('by-product.generate');

            Route::get(
                'by-product/results',
                [SalesByProductController::class, 'results']
            )->name('by-product.results');

            Route::get(
                'by-product/print',
                [SalesByProductController::class, 'print']
            )->name('by-product.print');

            Route::get(
                'by-product/pdf',
                [SalesByProductController::class, 'exportPdf']
            )->name('by-product.pdf');

            Route::get(
                'by-product/excel',
                [SalesByProductController::class, 'exportExcel']
            )->name('by-product.excel');


            

            Route::get('by-branch', [
                SalesByBranchController::class,
                'parameters'
            ])->name('by-branch.parameters');

            Route::post('by-branch/generate', [
                SalesByBranchController::class,
                'generate'
            ])->name('by-branch.generate');

            Route::get('by-branch/results', [
                SalesByBranchController::class,
                'results'
            ])->name('by-branch.results');

            Route::get('by-branch/print', [
                SalesByBranchController::class,
                'print'
            ])->name('by-branch.print');

            Route::get('by-branch/pdf', [
                SalesByBranchController::class,
                'exportPdf'
            ])->name('by-branch.pdf');

            Route::get('by-branch/excel', [
                SalesByBranchController::class,
                'exportExcel'
            ])->name('by-branch.excel');

        });

});