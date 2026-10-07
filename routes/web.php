<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\User\UserContextController;
use App\Http\Controllers\POS\CashierDepositController;
use App\Http\Controllers\POS\CashierSessionController;
use App\Http\Controllers\POS\CashierController;
use App\Http\Controllers\POS\PosTransactionController;
use App\Http\Controllers\POS\BackdateSaleController;
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {

    return Inertia::render('Dashboard');

})
->middleware([
    'auth',
    'verified'
])
->name('dashboard');

Route::middleware(
    'auth'
)
->group(function () {

    Route::get(
        '/profile',
        [
            ProfileController::class,
            'edit'
        ]
    )->name(
        'profile.edit'
    );

    Route::patch(
        '/profile',
        [
            ProfileController::class,
            'update'
        ]
    )->name(
        'profile.update'
    );

    Route::delete(
        '/profile',
        [
            ProfileController::class,
            'destroy'
        ]
    )->name(
        'profile.destroy'
    );

    Route::post(
        'user/context/branch',
        [
            UserContextController::class,
            'switchBranch',
        ]
    )->name('user.context.branch');


    /*
    |--------------------------------------------------------------------------
    | Cashier Deposits
    |--------------------------------------------------------------------------
    */

    Route::prefix('pos/cashier-deposits')
        ->name('pos.cashier-deposits.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Store
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/',
                [
                    CashierDepositController::class,
                    'store',
                ]
            )->name('store');


            /*
            |--------------------------------------------------------------------------
            | Submit
            |--------------------------------------------------------------------------
            */

            Route::post(
                '{cashierDeposit}/submit',
                [
                    CashierDepositController::class,
                    'submit',
                ]
            )->name('submit');


            /*
            |--------------------------------------------------------------------------
            | Approve
            |--------------------------------------------------------------------------
            */

            Route::post(
                '{cashierDeposit}/approve',
                [
                    CashierDepositController::class,
                    'approve',
                ]
            )->name('approve');


            /*
            |--------------------------------------------------------------------------
            | Reject
            |--------------------------------------------------------------------------
            */

            Route::post(
                '{cashierDeposit}/reject',
                [
                    CashierDepositController::class,
                    'reject',
                ]
            )->name('reject');


            /*
            |--------------------------------------------------------------------------
            | Receive
            |--------------------------------------------------------------------------
            */

            Route::post(
                '{cashierDeposit}/receive',
                [
                    CashierDepositController::class,
                    'receive',
                ]
            )->name('receive');


            /*
            |--------------------------------------------------------------------------
            | Post
            |--------------------------------------------------------------------------
            */

            Route::post(
                '{cashierDeposit}/post',
                [
                    CashierDepositController::class,
                    'post',
                ]
            )->name('post');
            Route::get(
                        '{cashierDeposit}/print',
                        [CashierDepositController::class, 'print']
                    )->name('print');

            /*
            |--------------------------------------------------------------------------
            | Cancel
            |--------------------------------------------------------------------------
            */

            Route::post(
                '{cashierDeposit}/cancel',
                [
                    CashierDepositController::class,
                    'cancel',
                ]
            )->name('cancel');

        });


    /*
    |--------------------------------------------------------------------------
    | Cashier
    |--------------------------------------------------------------------------
    */

    Route::get(
        'pos/cashier',
        [
            CashierController::class,
            'index',
        ]
    )->name('pos.cashier.index');


    /*
    |--------------------------------------------------------------------------
    | Cashier Sessions
    |--------------------------------------------------------------------------
    */

    Route::prefix('pos/cashier-sessions')
        ->name('pos.cashier-sessions.')
        ->group(function () {

            Route::post(
                '/',
                [
                    CashierSessionController::class,
                    'store',
                ]
            )->name('store');

            Route::post(
                '/close',
                [
                    CashierSessionController::class,
                    'close',
                ]
            )->name('close');

            Route::get(
                '{session}/print',
                [
                    CashierSessionController::class,
                    'print'
                ]
            )->name('print');

        });


    /*
    |--------------------------------------------------------------------------
    | POS Transactions
    |--------------------------------------------------------------------------
    */

    Route::prefix('pos/transactions')
        ->name('pos.transactions.')
        ->group(function () {

            Route::get('/', [
                PosTransactionController::class,
                'index'
            ])->name('index');

            Route::post('/', [
                PosTransactionController::class,
                'store'
            ])->name('store');

        });


    /*
    |--------------------------------------------------------------------------
    | Backdate Sales
    |--------------------------------------------------------------------------
    */

    Route::prefix('pos/backdate-sales')
        ->name('pos.backdate-sales.')
        ->group(function () {

            Route::get('/', [
                BackdateSaleController::class,
                'index'
            ])->name('index');

            Route::post('/', [
                BackdateSaleController::class,
                'store'
            ])->name('store');

        });

});

require __DIR__.'/master-data.php';

require __DIR__.'/inventory.php';

require __DIR__.'/purchasing.php';

require __DIR__.'/accounting.php';

require __DIR__.'/auth.php';

require __DIR__ . '/reseller.php';

require __DIR__ . '/user.php';