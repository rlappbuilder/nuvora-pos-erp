<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Production\ProductionOperationController;

Route::middleware(['auth'])->group(function () {

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
});