<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MasterData\CategoryController;
use App\Http\Controllers\MasterData\BrandController;
use App\Http\Controllers\MasterData\UnitController;
use App\Http\Controllers\MasterData\ColorController;
use App\Http\Controllers\MasterData\SizeController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\BranchController;
use App\Http\Controllers\MasterData\WarehouseController;
use App\Http\Controllers\MasterData\ProductController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\MasterData\CustomerController;

Route::middleware(

    'auth'

)

->prefix(

    'master-data'

)

->group(

    function () {

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        Route::get(

            'categories',

            [

                CategoryController::class,

                'index'

            ]

        )->name(

            'categories.index'

        );

        Route::post(

            'categories',

            [

                CategoryController::class,

                'store'

            ]

        )->name(

            'categories.store'

        );

        Route::put(

            'categories/{category}',

            [

                CategoryController::class,

                'update'

            ]

        )->name(

            'categories.update'

        );

        Route::delete(

            'categories/{category}',

            [

                CategoryController::class,

                'destroy'

            ]

        )->name(

            'categories.destroy'

        );

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'products',

            ProductController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'branches',

            BranchController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Warehouses
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'warehouses',

            WarehouseController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Brands
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'brands',

            BrandController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Units
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'units',

            UnitController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Colors
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'colors',

            ColorController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Sizes
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'sizes',

            SizeController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Companies
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'companies',

            CompanyController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Suppliers
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'suppliers',

            SupplierController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'customers',

            CustomerController::class

        );

    }

);