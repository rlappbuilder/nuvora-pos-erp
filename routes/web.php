<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MasterData\CategoryController;
use App\Http\Controllers\MasterData\BrandController;
use App\Http\Controllers\MasterData\UnitController;
use App\Http\Controllers\MasterData\ColorController;
use App\Http\Controllers\MasterData\SizeController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\BranchController;
use App\Http\Controllers\MasterData\WarehouseController;
use App\Http\Controllers\MasterData\ProductController;
use App\Http\Controllers\Inventory\OpeningStockController;
use App\Http\Controllers\Inventory\StockBalanceController;
use App\Http\Controllers\Inventory\StockCardController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
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
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
/**  oute master data */
Route::prefix('master-data')
    ->group(function () {

        Route::get(
            '/categories',
            [CategoryController::class, 'index']
        )->name('categories.index');

        Route::post(
            '/categories',
            [CategoryController::class, 'store']
        )->name('categories.store');

        Route::put(
            '/categories/{category}',
            [CategoryController::class, 'update']
        )->name('categories.update');

        Route::delete(
            '/categories/{category}',
            [CategoryController::class, 'destroy']
        )->name('categories.destroy');

        

    });
/** end route master data  categories*/
       /** route Produk */
       Route::prefix('master-data')
    ->group(function () {

        Route::resource(
            'products',
            ProductController::class
        );

    });
    /** end route prduct */

   /** route branches */
   Route::prefix('master-data')->group(function () {
        Route::resource(
            'branches',
            BranchController::class
        );
  });
   /** route warehouse */
   Route::prefix('master-data')->group(function () {

    Route::resource(
        'warehouses',
        WarehouseController::class
    );

});
    /** route Master data brand */
    Route::prefix('master-data')->group(function () {
        Route::resource(
    'brands',
    BrandController::class
        );
  });

     /** route Master data unit */
    Route::prefix('master-data')->group(function () {
        Route::resource(
    'units',
    UnitController::class
);
  });

  /** route Master data Color */
Route::prefix('master-data')->group(function () {

    Route::resource(

        'colors',

        \App\Http\Controllers\MasterData\ColorController::class

    );

});

/** route Master data Size */
Route::prefix('master-data')->group(function () {

    Route::resource(

        'sizes',

        SizeController::class

    );

});

/** Company Master Data */
Route::prefix('master-data')->group(function () {

    Route::resource(

        'companies',

        CompanyController::class

    );
    /** supplier */
    Route::resource(
    'suppliers',
    SupplierController::class
    );
    /**  customer prefix  */
 Route::resource(
        'customers',
        CustomerController::class
    );

});

/** purchasing */
/*
|--------------------------------------------------------------------------
| Purchasing
|--------------------------------------------------------------------------
*/

Route::prefix('purchasing')

    ->group(function () {

        Route::resource(

            'purchase-orders',

            PurchaseOrderController::class

        );
        Route::resource(
            'purchase-orders',
            PurchaseOrderController::class
        );

    });
/**  customer prefix  */

/** stock balance stock card */
Route::prefix(
    'inventory'
)->group(function () {

    Route::get(

        'stock-balance',

        [
            StockBalanceController::class,
            'index'
        ]

    )->name(
        'stock-balance.index'
    );
    
    Route::get(

    'stock-card',

    [
        StockCardController::class,
        'index'
    ]

)->name(
    'stock-card.index'
);

});
/** end stock */
/** stock movement */
Route::prefix(
    'inventory'
)->group(function () {

    Route::get(

        'opening-stock',

        [
            OpeningStockController::class,
            'create'
        ]

    )->name(
        'opening-stock.create'
    );

    Route::post(

        'opening-stock',

        [
            OpeningStockController::class,
            'store'
        ]

    )->name(
        'opening-stock.store'
    );

});
require __DIR__.'/auth.php';
