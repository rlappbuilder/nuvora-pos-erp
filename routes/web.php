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

Route::get('/', function () {

    return Inertia::render('Welcome', [

        'canLogin' => Route::has('login'),

        'canRegister' => Route::has('register'),

        'laravelVersion' => Application::VERSION,

        'phpVersion' => PHP_VERSION,

    ]);

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

    Route::post('user/context/branch',[
                UserContextController::class,
                'switchBranch',
            ] )->name('user.context.branch');

    Route::prefix('pos/cashier-deposits')
    ->name('pos.cashier-deposits.')
    ->group(function () {

        Route::post(
            '/',
            [
                CashierDepositController::class,
                'store',
            ]
        )->name('store');

           Route::post(
            '{cashierDeposit}/post',
            [
                CashierDepositController::class,
                'post',
            ]
        )->name('post');

    });
    Route::get(
        'pos/cashier',
        [
            CashierController::class,
            'index',
        ]
    )->name('pos.cashier.index');
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

    });

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

});

require __DIR__.'/master-data.php';

require __DIR__.'/inventory.php';

require __DIR__.'/purchasing.php';

require __DIR__.'/accounting.php';

require __DIR__.'/auth.php';

require __DIR__ . '/reseller.php';

require __DIR__ . '/user.php';