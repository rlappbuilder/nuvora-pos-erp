<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Inventory\OpeningStockController;
use App\Http\Controllers\Inventory\StockBalanceController;
use App\Http\Controllers\Inventory\StockCardController;
use App\Http\Controllers\Inventory\StockOpnameController;

use App\Http\Controllers\Inventory\InventoryAdjustmentController;
use App\Http\Controllers\Inventory\StockTransferController;
use App\Http\Controllers\Inventory\StockIssueController;

Route::middleware(

    'auth'

)

->prefix(

    'inventory'

)

->group(

    function () {

        /*
        |--------------------------------------------------------------------------
        | Opening Stock
        |--------------------------------------------------------------------------
        */

                    Route::prefix('opening-stocks')
                ->name('opening-stocks.')
                ->controller(OpeningStockController::class)
                ->group(function () {

                    Route::get(
                        '/',
                        'index'
                    )->name('index');

                    Route::post(
                        '/',
                        'store'
                    )->name('store');

                    Route::get(
                        '/{openingStock}',
                        'show'
                    )->name('show');

                    Route::put('/{openingStock}',
                        'update')
                        ->name('update');

                    Route::get(
                        '/{openingStock}/data',
                        [OpeningStockController::class, 'showData']
                    )->name('data');
                    Route::post(
                        '/{openingStock}/reject',
                        [OpeningStockController::class, 'reject']
                    )->name('reject');
                    Route::post(
                        '{openingStock}/post',
                        [OpeningStockController::class, 'post']
                    )->name('post');
                    Route::post(
                        'opening-stocks/{openingStock}/reject',
                        [OpeningStockController::class, 'reject']
                    )->name('opening-stocks.reject');
                    Route::delete(
                        '/{openingStock}',
                        'destroy'
                    )->name('destroy');

                    Route::post(
                        '/{openingStock}/duplicate',
                        'duplicate'
                    )->name('duplicate');

                    Route::delete(
                        '/bulk-delete',
                        'bulkDelete'
                    )->name('bulk-delete');

                });

        /*
        |--------------------------------------------------------------------------
        | Stock Balance
        |--------------------------------------------------------------------------
        */

        Route::get(

            '/stock-balance',

            [

                StockBalanceController::class,

                'index'

            ]

        )->name(

            'stock-balance.index'

        );

        /*
        |--------------------------------------------------------------------------
        | Stock Card
        |--------------------------------------------------------------------------
        */

        Route::get('/stock-card', [StockCardController::class,'index']
        )->name('stock-card.index');
      

       /*
        |--------------------------------------------------------------------------
        | Stock Opname
        |--------------------------------------------------------------------------
        */

        Route::get(
            'opnames',
            [StockOpnameController::class, 'index']
        )->name('stock-opnames.index');


        Route::post(
            'opnames',
            [StockOpnameController::class, 'store']
        )->name('stock-opnames.store');


        Route::get(
            'opnames/create',
            [StockOpnameController::class, 'create']
        )->name('stock-opnames.create');


        Route::get(
            'opnames/warehouse/{warehouse}',
            [StockOpnameController::class, 'warehouseStocks']
        )->name('stock-opnames.warehouse-stocks');


        Route::delete(
            'opnames/bulk-delete',
            [StockOpnameController::class, 'bulkDelete']
        )->name('stock-opnames.bulk-delete');


        Route::get(
            'opnames/{stockOpname}',
            [StockOpnameController::class, 'show']
        )->name('stock-opnames.show');


        Route::put(
            'opnames/{stockOpname}',
            [StockOpnameController::class, 'update']
        )->name('stock-opnames.update');


        Route::delete(
            'opnames/{stockOpname}',
            [StockOpnameController::class, 'destroy']
        )->name('stock-opnames.destroy');


        Route::get(
            'opnames/{stockOpname}/data',
            [StockOpnameController::class, 'data']
        )->name('stock-opnames.data');


        Route::post(
            'opnames/{stockOpname}/post',
            [StockOpnameController::class, 'post']
        )->name('stock-opnames.post');


        Route::post(
            'opnames/{stockOpname}/cancel',
            [StockOpnameController::class, 'cancel']
        )->name('stock-opnames.cancel');


        Route::post(
            'opnames/{stockOpname}/duplicate',
            [StockOpnameController::class, 'duplicate']
        )->name('stock-opnames.duplicate');
        /*
        |--------------------------------------------------------------------------
        | Inventory Adjustment
        |--------------------------------------------------------------------------
        */

        Route::delete('inventory-adjustments/{inventoryAdjustment}',[InventoryAdjustmentController::class,'destroy',]
        )->name('inventory-adjustments.destroy');
        
        Route::post('inventory-adjustments/{inventoryAdjustment}/duplicate',[InventoryAdjustmentController::class, 'duplicate']
        )->name('inventory-adjustments.duplicate' );

            Route::get('/adjustments/{inventoryAdjustment}/data',[InventoryAdjustmentController::class,'data']
        )->name('inventory-adjustments.data');

        Route::get('inventory-adjustments/stock',[InventoryAdjustmentController::class, 'stock']
        )->name('inventory-adjustments.stock');

        Route::get('/adjustments', [InventoryAdjustmentController::class,'index'])
        ->name('inventory-adjustments.index');

        Route::get('/adjustments/create',[InventoryAdjustmentController::class,'create'])
        ->name('inventory-adjustments.create');

        Route::post('/adjustments',[InventoryAdjustmentController::class,'store'])
            ->name('inventory-adjustments.store');

            Route::put('/adjustments/{inventoryAdjustment}',[InventoryAdjustmentController::class,'update']
            )->name('inventory-adjustments.update');

        Route::get('/adjustments/{inventoryAdjustment}',[InventoryAdjustmentController::class,'show'])
        ->name('inventory-adjustments.show');

        Route::post('/adjustments/{inventoryAdjustment}/post',[InventoryAdjustmentController::class,'post'])

        ->name('inventory-adjustments.post');
        
        Route::post('/adjustments/{inventoryAdjustment}/cancel',[InventoryAdjustmentController::class,'cancel'] )

        ->name('inventory-adjustments.cancel');
           
        Route::post('/adjustments/{inventoryAdjustment}/resubmit',[InventoryAdjustmentController::class,'resubmit'])
            ->name('inventory-adjustments.resubmit');

        Route::get('/adjustments/warehouse/{warehouse}',[InventoryAdjustmentController::class,'getWarehouseStocks'])

        ->name('inventory-adjustments.warehouse-stocks');
            /*
        |--------------------------------------------------------------------------
        | Stock Transfer
        |--------------------------------------------------------------------------
        */
        Route::get(
    'stock-transfers/{stockTransfer}/data',[StockTransferController::class,'data',]
          )->name('stock-transfers.data' );
        Route::get('/transfers',[StockTransferController::class,'index'])

        ->name('stock-transfers.index');

        Route::get('/transfers/create',[StockTransferController::class,'create'])

        ->name('stock-transfers.create');

            Route::put('inventory/transfers/{stockTransfer}',[StockTransferController::class, 'update']
            )->name('stock-transfers.update');

        Route::post('/transfers',[StockTransferController::class,'store'])
        ->name('stock-transfers.store');

        Route::post('inventory/transfers/{stockTransfer}/duplicate',[StockTransferController::class,'duplicate', ]
        )->name('stock-transfers.duplicate');


        Route::delete('inventory/transfers/{stockTransfer}',[StockTransferController::class,'destroy',]
        )->name('stock-transfers.destroy');

        Route::get('/transfers/{stockTransfer}',[StockTransferController::class,'show'])
        ->name('stock-transfers.show');

        Route::post('/transfers/{stockTransfer}/post',[StockTransferController::class,'post'])

        ->name('stock-transfers.post');

        Route::post('/transfers/{stockTransfer}/complete',[StockTransferController::class,'complete'])

        ->name('stock-transfers.complete');

        Route::post('/transfers/{stockTransfer}/cancel',[StockTransferController::class, 'cancel'] )

        ->name('stock-transfers.cancel');

        Route::get('/transfers/warehouse/{warehouse}',[StockTransferController::class,'getWarehouseStocks'])
        ->name('stock-transfers.warehouse-stocks' );
            /*
        |--------------------------------------------------------------------------
        | Stock Issue
        |--------------------------------------------------------------------------
        */

       Route::get(
            '/issues',
            [StockIssueController::class, 'index']
        )->name('stock-issues.index');


        Route::get(
            '/issues/create',
            [StockIssueController::class, 'create']
        )->name('stock-issues.create');


        Route::post(
            '/issues',
            [StockIssueController::class, 'store']
        )->name('stock-issues.store');


        Route::get(
            '/issues/{stockIssue}',
            [StockIssueController::class, 'show']
        )->name('stock-issues.show');


        Route::put(
            '/issues/{stockIssue}',
            [StockIssueController::class, 'update']
        )->name('stock-issues.update');


        Route::post(
            '/issues/{stockIssue}/post',
            [StockIssueController::class, 'post']
        )->name('stock-issues.post');


        Route::post(
            '/issues/{stockIssue}/cancel',
            [StockIssueController::class, 'cancel']
        )->name('stock-issues.cancel');


        Route::get(
            '/issues/warehouse/{warehouse}',
            [StockIssueController::class, 'getWarehouseStocks']
        )->name('stock-issues.warehouse-stocks');


        Route::get(
            '/issues/{stockIssue}/data',
            [StockIssueController::class, 'data']
        )->name('stock-issues.data');


        Route::post(
            '/issues/{stockIssue}/duplicate',
            [StockIssueController::class, 'duplicate']
        )->name('stock-issues.duplicate');


        Route::delete(
            '/issues/{stockIssue}',
            [StockIssueController::class, 'destroy']
        )->name('stock-issues.destroy');

    }

);