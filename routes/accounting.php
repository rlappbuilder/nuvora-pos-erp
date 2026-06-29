<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Accounting\CashBankController;

Route::middleware('auth')

->group(

    function () {

        /*
        |--------------------------------------------------------------------------
        | CASH BANK
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'cash-banks',

            CashBankController::class

        );

    }

);