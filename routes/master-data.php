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
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\TaxController;
use App\Http\Controllers\MasterData\CurrencyController;
use App\Http\Controllers\Product\ProductAttributeController;
use App\Http\Controllers\Product\ProductAttributeValueController;
use App\Http\Controllers\Product\ProductVariantController;
use App\Http\Controllers\Product\ProductVariantUnitController;
use App\Http\Controllers\MasterData\ProductVariantPriceController;
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
        
                /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        Route::prefix('products')
            ->name('products.')
                ->group(function () {
                 Route::get(
                    'preview-code',
                    [ProductController::class, 'previewCode']
                )->name('preview-code');
                Route::get(
                    'generate-sku',
                    [ProductController::class, 'generateSku']
                )->name('generate-sku');

                Route::get(
                    '{product}/duplicate',
                    [ProductController::class, 'duplicate']
                )->name('duplicate');

                Route::delete(
                    'bulk-delete',
                    [ProductController::class, 'bulkDelete']
                )->name('bulk-delete');

                Route::patch(
                    'bulk-activate',
                    [ProductController::class, 'bulkActivate']
                )->name('bulk-activate');

                Route::patch(
                    'bulk-deactivate',
                    [ProductController::class, 'bulkDeactivate']
                )->name('bulk-deactivate');
            });

        Route::resource(
            'products',
            ProductController::class
        );
        /*
        /*
            |--------------------------------------------------------------------------
            | Product Variant Prices
            |--------------------------------------------------------------------------
            */
            Route::get(
                    'product-variant-prices/check',[
                        ProductVariantPriceController::class,'checkPrice',
                    ]
                )->name('product-variant-prices.check');

            Route::get(
                'product-variant-prices/latest',
                [ProductVariantPriceController::class,'latestPrice', ]

              )->name('product-variant-prices.latest');
              
                Route::get('product-variant-prices/{productVariantPrice}/history',
                [ProductVariantPriceController::class,'history',]
                )->name('product-variant-prices.history'
                );

            Route::resource(
                'product-variant-prices',
                ProductVariantPriceController::class
            );
           

            Route::post(
                'product-variant-prices/bulk-delete',
                [
                    ProductVariantPriceController::class,
                    'bulkDelete',
                ]
            )->name(
                'product-variant-prices.bulk-delete'
            );

            Route::post(
                'product-variant-prices/bulk-activate',
                [
                    ProductVariantPriceController::class,
                    'bulkActivate',
                ]
            )->name(
                'product-variant-prices.bulk-activate'
            );

            Route::post(
                'product-variant-prices/bulk-deactivate',
                [
                    ProductVariantPriceController::class,
                    'bulkDeactivate',
                ]
            )->name(
                'product-variant-prices.bulk-deactivate'
            );
        /*
        |--------------------------------------------------------------------------
        | Product Variant
        |--------------------------------------------------------------------------
        */
            Route::prefix('product-variants')
                ->name('product-variants.')
                ->group(function () {

                Route::get(
                    'preview/{product}',
                    [ProductVariantController::class, 'preview']
                )->name('preview');
                    Route::delete(
                        'bulk-delete',
                        [ProductVariantController::class, 'bulkDelete']
                    )->name('bulk-delete');

                    Route::patch(
                        'bulk-activate',
                        [ProductVariantController::class, 'bulkActivate']
                    )->name('bulk-activate');

                    Route::patch(
                        'bulk-deactivate',
                        [ProductVariantController::class, 'bulkDeactivate']
                    )->name('bulk-deactivate');

                });

            Route::resource(
                'product-variants',
                ProductVariantController::class
            );

        /*

        /** end product Variant */
          /*
        
        |--------------------------------------------------------------------------
        | Product Attributes
        |--------------------------------------------------------------------------
        */
        Route::prefix('product-variant-units')
        ->name('product-variant-units.')
        ->group(function () {

            Route::get(
                '/available-units/{productVariant}',
                [ProductVariantUnitController::class, 'availableUnits']
            )->name('available-units');

            Route::get(
                '/',
                [ProductVariantUnitController::class, 'index']
            )->name('index');

            Route::post(
                '/',
                [ProductVariantUnitController::class, 'store']
            )->name('store');

            Route::put(
                '/{productVariantUnit}',
                [ProductVariantUnitController::class, 'update']
            )->name('update');

            Route::delete(
                '/{productVariantUnit}',
                [ProductVariantUnitController::class, 'destroy']
            )->name('destroy');

            Route::post(
                '/bulk-delete',
                [ProductVariantUnitController::class, 'bulkDelete']
            )->name('bulk-delete');

            Route::post(
                '/bulk-activate',
                [ProductVariantUnitController::class, 'bulkActivate']
            )->name('bulk-activate');

            Route::post(
                '/bulk-deactivate',
                [ProductVariantUnitController::class, 'bulkDeactivate']
            )->name('bulk-deactivate');

        });
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
        | Product Attributes
        |--------------------------------------------------------------------------
        */

       Route::prefix('product-attribute-values')
    ->name('product-attribute-values.')
    ->controller(ProductAttributeValueController::class)
    ->group(function () {

        Route::get('/', 'index')->name('index');

        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');

        // Bulk routes HARUS sebelum route parameter
        Route::delete('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        Route::post('/bulk-activate', 'bulkActivate')->name('bulk-activate');
        Route::post('/bulk-deactivate', 'bulkDeactivate')->name('bulk-deactivate');

        Route::get('/{product_attribute_value}', 'show')->name('show');
        Route::get('/{product_attribute_value}/edit', 'edit')->name('edit');
        Route::put('/{product_attribute_value}', 'update')->name('update');
        Route::delete('/{product_attribute_value}', 'destroy')->name('destroy');
        Route::get('/{product_attribute_value}/duplicate', 'duplicate')
            ->name('duplicate');
    }); /*
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