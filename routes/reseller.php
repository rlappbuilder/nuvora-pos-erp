<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reseller\ResellerController;

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
    | Bulk Actions
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
    
        Route::get(
        '/resellers/{reseller}/duplicate',
        [ResellerController::class, 'duplicate']
    )->name('resellers.duplicate');
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