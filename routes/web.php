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
use App\Http\Controllers\Purchasing\GoodsReceiptController;
use App\Http\Controllers\Inventory\InventoryAdjustmentController;
use App\Http\Controllers\Inventory\StockTransferController;
use App\Http\Controllers\Inventory\StockIssueController;

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

        Route::patch(
    'purchase-orders/{purchaseOrder}/submit',
    [PurchaseOrderController::class, 'submit']
        )->name('purchase-orders.submit');

        Route::patch(
            'purchase-orders/{purchaseOrder}/approve',
            [PurchaseOrderController::class, 'approve']
        )->name('purchase-orders.approve');

        Route::patch(
            'purchase-orders/{purchaseOrder}/reject',
            [PurchaseOrderController::class, 'reject']
        )->name('purchase-orders.reject');
        Route::patch(
            'purchase-orders/{purchaseOrder}/reopen',
            [PurchaseOrderController::class, 'reopen']
        )->name('purchase-orders.reopen');
        Route::patch(
            'purchase-orders/{purchaseOrder}/cancel',
            [PurchaseOrderController::class, 'cancel']
        )->name('purchase-orders.cancel');
       

    });
/**  customer goog receipt  */
Route::prefix('purchasing')

    ->group(function () {

        Route::resource(
            'purchase-orders',
            PurchaseOrderController::class
        );

        Route::resource(
            'goods-receipts',
            GoodsReceiptController::class
        );

        Route::get(

             'purchase-orders/{purchaseOrder}/create-goods-receipt',

            [

                GoodsReceiptController::class,

                'createFromPurchaseOrder'

            ]

            )->name(
                'goods-receipts.create-from-po'
            );

            Route::resource(
            'goods-receipts',
            GoodsReceiptController::class
        );
        Route::patch(
            'goods-receipts/{goodsReceipt}/post',
            [
                GoodsReceiptController::class,
                'post'
            ]
        )->name(
            'goods-receipts.post'
        );

        Route::patch(
            'goods-receipts/{goodsReceipt}/post',
            [
                GoodsReceiptController::class,
                'post'
            ]
        )->name(
            'goods-receipts.post'
        );

        Route::patch(
            'goods-receipts/{goodsReceipt}/cancel',
            [
                GoodsReceiptController::class,
                'cancel'
            ]
        )->name(
            'goods-receipts.cancel'
        );
    });
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

/** route stock adjusment */
Route::prefix(
    'inventory'
)

->middleware([
    'auth'
])

->group(function () {

    Route::get(

        '/adjustments',

        [

            InventoryAdjustmentController::class,

            'index'

        ]

    )

    ->name(
        'inventory-adjustments.index'
    );

    Route::get(

        '/adjustments/create',

        [

            InventoryAdjustmentController::class,

            'create'

        ]

    )

    ->name(
        'inventory-adjustments.create'
    );

    Route::post(

        '/adjustments',

        [

            InventoryAdjustmentController::class,

            'store'

        ]

    )

    ->name(
        'inventory-adjustments.store'
    );

    Route::get(

        '/adjustments/{inventoryAdjustment}',

        [

            InventoryAdjustmentController::class,

            'show'

        ]

    )

    ->name(
        'inventory-adjustments.show'
    );

    Route::post(

        '/adjustments/{inventoryAdjustment}/post',

        [

            InventoryAdjustmentController::class,

            'post'

        ]

    )

    ->name(
        'inventory-adjustments.post'
    );

    Route::post(

        '/adjustments/{inventoryAdjustment}/cancel',

        [

            InventoryAdjustmentController::class,

            'cancel'
        ]

    )

    ->name(
        'inventory-adjustments.cancel'
    );


   Route::get(

    '/adjustments/warehouse/{warehouse}',

    [

        InventoryAdjustmentController::class,

        'getWarehouseStocks'

    ]

)

->name(
    'inventory-adjustments.warehouse-stocks'
);

});

/** Stock Transfer */

Route::prefix(
    'inventory'
)

->middleware([
    'auth'
])

->group(function () {

    Route::get(

        '/transfers',

        [

            StockTransferController::class,

            'index'

        ]

    )

    ->name(
        'stock-transfers.index'
    );

    Route::get(

        '/transfers/create',

        [

            StockTransferController::class,

            'create'

        ]

    )

    ->name(
        'stock-transfers.create'
    );

    Route::post(

        '/transfers',

        [

            StockTransferController::class,

            'store'

        ]

    )

    ->name(
        'stock-transfers.store'
    );

    Route::get(

        '/transfers/{stockTransfer}',

        [

            StockTransferController::class,

            'show'

        ]

    )

    ->name(
        'stock-transfers.show'
    );

    Route::post(

        '/transfers/{stockTransfer}/post',

        [

            StockTransferController::class,

            'post'

        ]

    )

    ->name(
        'stock-transfers.post'
    );

    Route::post(

    '/inventory/transfers/{stockTransfer}/complete',

    [
        StockTransferController::class, 'complete'
    ]
        )->name('stock-transfers.complete');

    Route::post(

        '/transfers/{stockTransfer}/cancel',

        [

            StockTransferController::class,

            'cancel'

        ]

    )

    ->name(
        'stock-transfers.cancel'
    );
        Route::get(

        '/transfers/warehouse/{warehouse}',

        [

            StockTransferController::class,

            'getWarehouseStocks'

        ]

    )

    ->name(
        'stock-transfers.warehouse-stocks'
    );

            Route::post(

            '/inventory/transfers/{stockTransfer}/cancel',

            [

                StockTransferController::class,

                'cancel'

            ]

        )->name(

            'stock-transfers.cancel'

        );

});

/** end route stock adjusment */

/** route issue */
/** route stock issue */

Route::prefix(

    'inventory'

)

->middleware([

    'auth'

])

->group(function () {

    Route::get(

        '/issues',

        [

            StockIssueController::class,

            'index'

        ]

    )

    ->name(

        'stock-issues.index'

    );

    Route::get(

        '/issues/create',

        [

            StockIssueController::class,

            'create'

        ]

    )

    ->name(

        'stock-issues.create'

    );

    Route::post(

        '/issues',

        [

            StockIssueController::class,

            'store'

        ]

    )

    ->name(

        'stock-issues.store'

    );

    Route::get(

        '/issues/{stockIssue}',

        [

            StockIssueController::class,

            'show'

        ]

    )

    ->name(

        'stock-issues.show'

    );

    Route::post(

        '/issues/{stockIssue}/post',

        [

            StockIssueController::class,

            'post'

        ]

    )

    ->name(

        'stock-issues.post'

    );

    Route::post(

        '/issues/{stockIssue}/complete',

        [

            StockIssueController::class,

            'complete'

        ]

    )

    ->name(

        'stock-issues.complete'

    );

    Route::post(

        '/issues/{stockIssue}/cancel',

        [

            StockIssueController::class,

            'cancel'

        ]

    )

    ->name(

        'stock-issues.cancel'

    );

    Route::get(

        '/issues/warehouse/{warehouse}',

        [

            StockIssueController::class,

            'getWarehouseStocks'

        ]

    )

    ->name(

        'stock-issues.warehouse-stocks'

    );

});
/** end route issue */
require __DIR__.'/auth.php';
