<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Consignment\ConsignmentSettlementController;
use App\Http\Controllers\Reseller\ResellerController;
use App\Http\Controllers\Consignment\ConsignmentOutController;
use App\Http\Controllers\Reseller\ConsignmentStockController;
use App\Http\Controllers\Consignment\ConsignmentReceivableController;
use App\Http\Controllers\Consignment\ConsignmentReturnController;
use App\Http\Controllers\Consignment\ResellerStatementController;
use App\Http\Controllers\Consignment\ConsignmentReportController;
use App\Http\Controllers\Consignment\ResellerMutationController;
                /*
                |--------------------------------------------------------------------------
                | Reseller Routes
                |--------------------------------------------------------------------------
                */

                Route::middleware(['auth'])->group(function () {

                    /*
                    |--------------------------------------------------------------------------
                    | Code Generator
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/resellers/preview-code',
                        [ResellerController::class, 'previewCode']
                    )->name('resellers.preview-code');

                    Route::post(
                        '/resellers/sync-code',
                        [ResellerController::class, 'syncCode']
                    )->name('resellers.sync-code');


                    /*
                    |--------------------------------------------------------------------------
                    | Reseller Bulk Actions
                    |--------------------------------------------------------------------------
                    */

                    Route::delete(
                        '/resellers/bulk-delete',
                        [ResellerController::class, 'bulkDelete']
                    )->name('resellers.bulk-delete');

                    Route::patch(
                        '/resellers/bulk-activate',
                        [ResellerController::class, 'bulkActivate']
                    )->name('resellers.bulk-activate');

                    Route::patch(
                        '/resellers/bulk-deactivate',
                        [ResellerController::class, 'bulkDeactivate']
                    )->name('resellers.bulk-deactivate');


                    /*
                    |--------------------------------------------------------------------------
                    | Reseller Duplicate
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/resellers/{reseller}/duplicate',
                        [ResellerController::class, 'duplicate']
                    )->name('resellers.duplicate');


                    /*
                    |--------------------------------------------------------------------------
                    | Consignment Out
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/consignment-outs/preview-code',
                        [ConsignmentOutController::class, 'previewCode']
                    )->name('consignment-outs.preview-code');

                    Route::delete(
                        '/consignment-outs/bulk-delete',
                        [ConsignmentOutController::class, 'bulkDelete']
                    )->name('consignment-outs.bulk-delete');

                    Route::get(
                        '/consignment-outs/{consignmentOut}/duplicate',
                        [ConsignmentOutController::class, 'duplicate']
                    )->name('consignment-outs.duplicate');

                    Route::post(
                        '/consignment-outs/{consignmentOut}/submit',
                        [ConsignmentOutController::class, 'submit']
                    )->name('consignment-outs.submit');

                    Route::post(
                        '/consignment-outs/{consignmentOut}/approve',
                        [ConsignmentOutController::class, 'approve']
                    )->name('consignment-outs.approve');

                    Route::post(
                        '/consignment-outs/{consignmentOut}/reject',
                        [ConsignmentOutController::class, 'reject']
                    )->name('consignment-outs.reject');

                    Route::post(
                        '/consignment-outs/{consignmentOut}/post',
                        [ConsignmentOutController::class, 'post']
                    )->name('consignment-outs.post');

                    Route::post(
                        '/consignment-outs/{consignmentOut}/cancel',
                        [ConsignmentOutController::class, 'cancel']
                    )->name('consignment-outs.cancel');

                    Route::get(
                        '/consignment-outs/{consignmentOut}/data',
                        [ConsignmentOutController::class, 'showData']
                    )->name('consignment-outs.data');
     /*
                    |--------------------------------------------------------------------------
                    | Consignment Out
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/consignment-outs/{consignmentOut}/print',
                        [ConsignmentOutController::class, 'print']
                    )->name('consignment-outs.print');

                    Route::get(
                        '/consignment-outs/{consignmentOut}/pdf',
                        [ConsignmentOutController::class, 'pdf']
                    )->name('consignment-outs.pdf');

                    Route::get(
                        '/consignment-outs/{consignmentOut}/excel',
                        [ConsignmentOutController::class, 'excel']
                    )->name('consignment-outs.excel');

                        /*
                    |--------------------------------------------------------------------------
                    | Consignment Stock
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/consignment-stock/movements',
                        [ConsignmentStockController::class, 'movements']
                    )->name('consignment-stock.movements');

                    Route::get(
                        '/consignment-stock',
                        [ConsignmentStockController::class, 'index']
                    )->name('consignment-stock.index');
                    Route::get(
                        '/consignment-stock/print',
                        [ConsignmentStockController::class, 'print']
                    )->name('consignment-stock.print');

                    Route::get(
                        '/consignment-stock/pdf',
                        [ConsignmentStockController::class, 'pdf']
                    )->name('consignment-stock.pdf');

                    Route::get(
                        '/consignment-stock/excel',
                        [ConsignmentStockController::class, 'excel']
                    )->name('consignment-stock.excel');

                /*
                |--------------------------------------------------------------------------
                | Consignment Settlement
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/consignment-settlements/preview-code',
                    [ConsignmentSettlementController::class, 'previewCode']
                )->name('consignment-settlements.preview-code');

                Route::post(
                    '/consignment-settlements/{settlement}/cancel',
                    [ConsignmentSettlementController::class, 'cancel']
                )->name('consignment-settlements.cancel');

                Route::get(
                    '/consignment-settlements/{settlement}/data',
                    [ConsignmentSettlementController::class, 'showData']
                )->name('consignment-settlements.data');


                /*
                |--------------------------------------------------------------------------
                | Consignment Settlement Export
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/consignment-settlements/{settlement}/print',
                    [ConsignmentSettlementController::class, 'print']
                )->name('consignment-settlements.print');

                Route::get(
                    '/consignment-settlements/{settlement}/pdf',
                    [ConsignmentSettlementController::class, 'pdf']
                )->name('consignment-settlements.pdf');

                Route::get(
                    '/consignment-settlements/{settlement}/excel',
                    [ConsignmentSettlementController::class, 'excel']
                )->name('consignment-settlements.excel');


                /*
                |--------------------------------------------------------------------------
                | Consignment Settlement Resource
                |--------------------------------------------------------------------------
                */

                Route::resource(
                    'consignment-settlements',
                    ConsignmentSettlementController::class
                )->only([
                    'index',
                    'create',
                    'store',
                    'show',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Consignment Receivable
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/consignment-receivables/preview-code',
                    [ConsignmentReceivableController::class, 'previewCode']
                )->name('consignment-receivables.preview-code');

                Route::post(
                    '/consignment-receivables/{consignmentReceivable}/submit',
                    [ConsignmentReceivableController::class, 'submit']
                )->name('consignment-receivables.submit');

                Route::post(
                    '/consignment-receivables/{consignmentReceivable}/approve',
                    [ConsignmentReceivableController::class, 'approve']
                )->name('consignment-receivables.approve');

                Route::post(
                    '/consignment-receivables/{consignmentReceivable}/reject',
                    [ConsignmentReceivableController::class, 'reject']
                )->name('consignment-receivables.reject');

                Route::post(
                    '/consignment-receivables/{consignmentReceivable}/post',
                    [ConsignmentReceivableController::class, 'post']
                )->name('consignment-receivables.post');

                Route::post(
                    '/consignment-receivables/{consignmentReceivable}/cancel',
                    [ConsignmentReceivableController::class, 'cancel']
                )->name('consignment-receivables.cancel');

                Route::get(
                    '/consignment-receivables/{consignmentReceivable}/data',
                    [ConsignmentReceivableController::class, 'showData']
                )->name('consignment-receivables.data');
                
                Route::get(
                    'consignment-receivables/{consignmentReceivable}/print',
                    [ConsignmentReceivableController::class, 'print']
                )->name('consignment-receivables.print');

                /*
                |--------------------------------------------------------------------------
                | Consignment Receivable Export
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/consignment-receivables/{consignmentReceivable}/print',
                    [ConsignmentReceivableController::class, 'print']
                )->name('consignment-receivables.print');

                Route::get(
                    '/consignment-receivables/{consignmentReceivable}/pdf',
                    [ConsignmentReceivableController::class, 'pdf']
                )->name('consignment-receivables.pdf');

                Route::get(
                    '/consignment-receivables/{consignmentReceivable}/excel',
                    [ConsignmentReceivableController::class, 'excel']
                )->name('consignment-receivables.excel');

                /*
                |--------------------------------------------------------------------------
                | Consignment Return
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/consignment-returns/preview-code',
                    [ConsignmentReturnController::class, 'previewCode']
                )->name('consignment-returns.preview-code');


                Route::post(
                    '/consignment-returns/{consignmentReturn}/submit',
                    [ConsignmentReturnController::class, 'submit']
                )->name('consignment-returns.submit');


                Route::post(
                    '/consignment-returns/{consignmentReturn}/approve',
                    [ConsignmentReturnController::class, 'approve']
                )->name('consignment-returns.approve');


                Route::post(
                    '/consignment-returns/{consignmentReturn}/reject',
                    [ConsignmentReturnController::class, 'reject']
                )->name('consignment-returns.reject');


                Route::post(
                    '/consignment-returns/{consignmentReturn}/post',
                    [ConsignmentReturnController::class, 'post']
                )->name('consignment-returns.post');


                Route::post(
                    '/consignment-returns/{consignmentReturn}/cancel',
                    [ConsignmentReturnController::class, 'cancel']
                )->name('consignment-returns.cancel');


                Route::get(
                    '/consignment-returns/{consignmentReturn}/data',
                    [ConsignmentReturnController::class, 'showData']
                )->name('consignment-returns.data');

                /*
                |--------------------------------------------------------------------------
                | Reseller Statement
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/reseller-statements',
                    [ResellerStatementController::class, 'index']
                )->name('reseller-statements.index');

                Route::get(
                    '/reseller-statements/print',
                    [ResellerStatementController::class, 'print']
                )->name('reseller-statements.print');

                Route::get(
                    '/reseller-statements/pdf',
                    [ResellerStatementController::class, 'pdf']
                )->name('reseller-statements.pdf');

                Route::get(
                    '/reseller-statements/excel',
                    [ResellerStatementController::class, 'excel']
                )->name('reseller-statements.excel');
                /*
                |--------------------------------------------------------------------------
                | Consignment Return Print
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/consignment-returns/{consignmentReturn}/print',
                    [ConsignmentReturnController::class, 'print']
                )->name('consignment-returns.print');


                /*
                |--------------------------------------------------------------------------
                | Consignment Return Resource
                |--------------------------------------------------------------------------
                */

                Route::resource(
                    'consignment-returns',
                    ConsignmentReturnController::class
                )->only([
                    'index',
                    'create',
                    'store',
                    'show',
                    'edit',
                    'update',
                ]);
                /*
                |--------------------------------------------------------------------------
                | Consignment Receivable Resource
                |--------------------------------------------------------------------------
                */

                Route::resource(
                    'consignment-receivables',
                    ConsignmentReceivableController::class
                )->only([
                    'index',
                    'create',
                    'store',
                    'show',
                    'edit',
                    'update',
                ]);
               
                Route::get('/consignment-reports',[
                        ConsignmentReportController::class,
                        'index'
                    ]
                )->name('consignment-reports.index');

                Route::get(
                    '/consignment-reports/print',
                    [
                        ConsignmentReportController::class,
                        'print'
                    ]
                )->name('consignment-reports.print');

                Route::get(
                    '/consignment-reports/pdf',
                    [
                        ConsignmentReportController::class,
                        'pdf'
                    ]
                )->name('consignment-reports.pdf');

                Route::get(
                    '/consignment-reports/excel',
                    [
                        ConsignmentReportController::class,
                        'excel'
                    ]
                )->name('consignment-reports.excel');

                /* |--------------------------------------------------------------------------
                | Reseller mutation
                |--------------------------------------------------------------------------
                */

                Route::get('/reseller-mutations', [ResellerMutationController::class, 'index'])
                    ->name('reseller-mutations.index');

                Route::get('/reseller-mutations/print', [ResellerMutationController::class, 'print'])
                    ->name('reseller-mutations.print');

                Route::get('/reseller-mutations/pdf', [ResellerMutationController::class, 'pdf'])
                    ->name('reseller-mutations.pdf');

                Route::get('/reseller-mutations/excel', [ResellerMutationController::class, 'excel'])
                    ->name('reseller-mutations.excel');
                                    /*
                |--------------------------------------------------------------------------
                | Consignment Out Resource
                |--------------------------------------------------------------------------
                */

                Route::resource(
                    'consignment-outs',
                    ConsignmentOutController::class
                );


                /*
                |--------------------------------------------------------------------------
                | Reseller Resource
                |--------------------------------------------------------------------------
                */

                Route::resource(
                    'resellers',
                    ResellerController::class
                );

});