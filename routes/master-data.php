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
use App\Http\Controllers\MasterData\TaxController;
use App\Http\Controllers\MasterData\CurrencyController;
use App\Http\Controllers\Product\ProductAttributeController;

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

        Route::get(
            'categories/create',
            [
                CategoryController::class,
                'create'
            ]
        )->name('categories.create');

        Route::post(

            'categories',

            [

                CategoryController::class,

                'store'

            ]

        )->name(

            'categories.store'

        );

        Route::get(
            'categories/{category}/edit',
            [
                CategoryController::class,
                'edit'
            ]
        )->name('categories.edit');

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

       
        Route::get(
            'categories/preview-code',
            [
                CategoryController::class,
                'previewCode'
            ]
        )->name('categories.preview-code');
                Route::get(
                    'categories-trash',
                    [CategoryController::class, 'trash']
                )->name('categories.trash');

                Route::patch(
                    'categories/{category}/restore',
                    [CategoryController::class, 'restore']
                )->withTrashed()->name('categories.restore');

                Route::patch(
                    'categories/restore/bulk',
                    [CategoryController::class, 'bulkRestore']
                )->name('categories.bulk-restore');
                Route::get(
                'categories/sync-code',
                [CategoryController::class, 'syncCode']
            )->name('categories.sync-code');

        Route::post(
        'bulk-delete',
        [CategoryController::class, 'bulkDelete']
            )->name('categories.bulk-delete');

            Route::patch(
                'bulk-activate',
                [CategoryController::class, 'bulkActivate']
            )->name('categories.bulk-activate');

            Route::patch(
                'bulk-deactivate',
                [CategoryController::class, 'bulkDeactivate']
            )->name('categories.bulk-deactivate');
            Route::get(
                'categories/{category}/duplicate',
                [CategoryController::class, 'duplicate']
            )->name('categories.duplicate');

                  
        /* Route Bulk Actin 

   
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
        | Product Attributes
        |--------------------------------------------------------------------------
        */

        Route::get(
            'product-attributes/preview-code',
            [ProductAttributeController::class, 'previewCode']
        )->name('product-attributes.preview-code');

        Route::get(
            'product-attributes/{productAttribute}/duplicate',
            [ProductAttributeController::class, 'duplicate']
        )->name('product-attributes.duplicate');

        Route::delete(
            'product-attributes/bulk-delete',
            [ProductAttributeController::class, 'bulkDelete']
        )->name('product-attributes.bulk-delete');

        Route::patch(
            'product-attributes/bulk-activate',
            [ProductAttributeController::class, 'bulkActivate']
        )->name('product-attributes.bulk-activate');

        Route::patch(
            'product-attributes/bulk-deactivate',
            [ProductAttributeController::class, 'bulkDeactivate']
        )->name('product-attributes.bulk-deactivate');

        Route::resource(
            'product-attributes',
            ProductAttributeController::class
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
        | Tax
        |--------------------------------------------------------------------------
        */
        Route::prefix('taxes')->name('taxes.')->group(function () {

            Route::get('preview-code', [TaxController::class, 'previewCode'])
                ->name('preview-code');

            Route::post('bulk-delete', [TaxController::class, 'bulkDelete'])
                ->name('bulk-delete');

            Route::post('bulk-activate', [TaxController::class, 'bulkActivate'])
                ->name('bulk-activate');

            Route::post('bulk-deactivate', [TaxController::class, 'bulkDeactivate'])
                ->name('bulk-deactivate');

            Route::post('{tax}/duplicate', [TaxController::class, 'duplicate'])
                ->name('duplicate');
        });

        Route::resource('taxes', TaxController::class);
/*
        |--------------------------------------------------------------------------
        | Currency
        |--------------------------------------------------------------------------
        */
        Route::prefix('currencies')->name('currencies.')->group(function () {

            Route::post(
                'sync-code',
                [CurrencyController::class, 'syncCode']
            )->name('sync-code');

            Route::post(
                'bulk-delete',
                [CurrencyController::class, 'bulkDelete']
            )->name('bulk-delete');

            Route::post(
                'bulk-activate',
                [CurrencyController::class, 'bulkActivate']
            )->name('bulk-activate');

            Route::post(
                'bulk-deactivate',
                [CurrencyController::class, 'bulkDeactivate']
            )->name('bulk-deactivate');

            Route::get(
                '{currency}/duplicate',
                [CurrencyController::class, 'duplicate']
            )->name('duplicate');

        });

        Route::resource(
            'currencies',
            CurrencyController::class 
        ); 
        /*
        |--------------------------------------------------------------------------
        | Brands
        |--------------------------------------------------------------------------
        */
            Route::prefix('brands')->name('brands.')->group(function () {

                Route::get(
                    'preview-code',
                    [BrandController::class, 'previewCode']
                )->name('preview-code');

                Route::post(
                    'bulk-delete',
                    [BrandController::class, 'bulkDelete']
                )->name('bulk-delete');

                Route::post(
                    'bulk-activate',
                    [BrandController::class, 'bulkActivate']
                )->name('bulk-activate');

                Route::post(
                    'bulk-deactivate',
                    [BrandController::class, 'bulkDeactivate']
                )->name('bulk-deactivate');

                Route::post(
                    '{brand}/duplicate',
                    [BrandController::class, 'duplicate']
                )->name('duplicate');

            });

            Route::resource(
                'brands',
                BrandController::class
);
        Route::resource(

            'brands',

            BrandController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Units
        |--------------------------------------------------------------------------
        */

        Route::prefix('units')->name('units.')->group(function () {

                Route::get('preview-code', [UnitController::class, 'previewCode'])
                    ->name('preview-code');

                Route::delete('bulk-delete', [UnitController::class, 'bulkDelete'])
                    ->name('bulk-delete');

                Route::patch('bulk-activate', [UnitController::class, 'bulkActivate'])
                    ->name('bulk-activate');

                Route::patch('bulk-deactivate', [UnitController::class, 'bulkDeactivate'])
                    ->name('bulk-deactivate');

                Route::post('{unit}/duplicate', [UnitController::class, 'duplicate'])
                    ->name('duplicate');

            });

            Route::resource('units', UnitController::class);
                    Route::get(
                        'units/preview-code',
                        [UnitController::class, 'previewCode']
                    )->name('units.preview-code');
                    Route::resource(

            'units',

            UnitController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Colors
        |--------------------------------------------------------------------------
        */
        Route::prefix('colors')->name('colors.')->group(function () {

            Route::get('preview-code', [ColorController::class, 'previewCode'])
                ->name('preview-code');

            Route::delete('bulk-delete', [ColorController::class, 'bulkDelete'])
                ->name('bulk-delete');

            Route::patch('bulk-activate', [ColorController::class, 'bulkActivate'])
                ->name('bulk-activate');

            Route::patch('bulk-deactivate', [ColorController::class, 'bulkDeactivate'])
                ->name('bulk-deactivate');

            Route::post('{color}/duplicate', [ColorController::class, 'duplicate'])
                ->name('duplicate');

        });

        Route::resource('colors', ColorController::class);
        Route::resource(

            'colors',

            ColorController::class

        );

        /*
        |--------------------------------------------------------------------------
        | Sizes
        |--------------------------------------------------------------------------
        */
        Route::prefix('sizes')->name('sizes.')->group(function () {

            Route::get('preview-code', [SizeController::class, 'previewCode'])
                ->name('preview-code');

            Route::post('bulk-delete', [SizeController::class, 'bulkDelete'])
                ->name('bulk-delete');

            Route::post('bulk-activate', [SizeController::class, 'bulkActivate'])
                ->name('bulk-activate');

            Route::post('bulk-deactivate', [SizeController::class, 'bulkDeactivate'])
                ->name('bulk-deactivate');

            Route::post('{size}/duplicate', [SizeController::class, 'duplicate'])
                ->name('duplicate');

        });

        Route::resource('sizes', SizeController::class);
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