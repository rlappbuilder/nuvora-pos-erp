<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;

Route::middleware(['auth'])
    ->prefix('users')
    ->name('users.')
    ->group(function () {

        Route::get('/', [
            UserController::class,
            'index',
        ])->name('index');

        Route::post('/', [
            UserController::class,
            'store',
        ])->name('store');

        Route::get('/{user}', [
            UserController::class,
            'show',
        ])->name('show');

        Route::put('/{user}', [
            UserController::class,
            'update',
        ])->name('update');

        Route::delete('/{user}', [
            UserController::class,
            'destroy',
        ])->name('destroy');

        
    });