<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Production\ProductionOperationController;
use App\Http\Controllers\Production\ProductionBomController;
use App\Http\Controllers\Production\ProductionOrderController;
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Production Operations
    |--------------------------------------------------------------------------
    */

    Route::prefix('production-operations')
        ->name('production-operations.')
        ->controller(ProductionOperationController::class)
        ->group(function () {

            // Bulk Actions
            Route::post('/bulk-delete', 'bulkDelete')
                ->name('bulkDelete');

            Route::post('/bulk-activate', 'bulkActivate')
                ->name('bulkActivate');

            Route::post('/bulk-deactivate', 'bulkDeactivate')
                ->name('bulkDeactivate');

            // CRUD
            Route::get('/', 'index')
                ->name('index');

            Route::get('/create', 'create')
                ->name('create');

            Route::post('/', 'store')
                ->name('store');

            Route::get('/{production_operation}', 'show')
                ->name('show');

            Route::get('/{production_operation}/edit', 'edit')
                ->name('edit');

            Route::put('/{production_operation}', 'update')
                ->name('update');

            Route::delete('/{production_operation}', 'destroy')
                ->name('destroy');

        });

    /*
    |--------------------------------------------------------------------------
    | Production Orders
    |--------------------------------------------------------------------------
    */

    Route::prefix('production-orders')
        ->name('production-orders.')
        ->controller(ProductionOrderController::class)
        ->group(function () {

            // Preview Code
            Route::get('/preview-code', 'previewCode')
                ->name('preview-code');

            // CRUD
            Route::get('/', 'index')
                ->name('index');

            Route::get('/create', 'create')
                ->name('create');

            Route::post('/', 'store')
                ->name('store');

            Route::get('/{order}', 'show')
                ->name('show');

            Route::get('/{order}/edit', 'edit')
                ->name('edit');

            Route::put('/{order}', 'update')
                ->name('update');

                // Workflow
            Route::post('/{order}/submit', 'submit')
                ->name('submit');

            Route::post('/{order}/approve', 'approve')
                ->name('approve');

            Route::post('/{order}/reject', 'reject')
                ->name('reject');

            Route::post('/{order}/start-production', 'startProduction')
                ->name('start-production');

            Route::post('/{order}/cancel', 'cancel')
                ->name('cancel');

        });
    /*
    |--------------------------------------------------------------------------
    | Production Bill of Materials (BOM)
    |--------------------------------------------------------------------------
    */

    Route::prefix('production-boms')
        ->name('production-boms.')
        ->controller(ProductionBomController::class)
        ->group(function () {

            // Preview Code
            Route::get('/preview-code', 'previewCode')
                ->name('preview-code');

            // CRUD
            Route::get('/', 'index')
                ->name('index');

            Route::get('/create', 'create')
                ->name('create');

            Route::post('/', 'store')
                ->name('store');

            Route::get('/{bom}', 'show')
                ->name('show');

            Route::get('/{bom}/edit', 'edit')
                ->name('edit');

            Route::put('/{bom}', 'update')
                ->name('update');

            // Revision
            Route::post('/{bom}/revision', 'createRevision')
                ->name('revision');

            // Version Activation
            Route::post('/versions/{version}/activate', 'activateVersion')
                ->name('activate-version');

            // Deactivate BOM
            Route::post('/{bom}/deactivate', 'deactivate')
                ->name('deactivate');

            // Delete Draft
            Route::delete('/{bom}', 'destroy')
                ->name('destroy');

            Route::get(
                '/{bom}/versions/{version}/print',
                [ProductionBomController::class, 'print']
            )->name('print');

        });

});