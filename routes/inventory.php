<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Inventory\OpeningStockController;
use App\Http\Controllers\Inventory\StockBalanceController;
use App\Http\Controllers\Inventory\StockCardController;

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

                    Route::put(
                        '/{openingStock}',
                        'update'
                    )->name('update');

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

        Route::get(

            '/stock-card',

            [

                StockCardController::class,

                'index'

            ]

        )->name(

            'stock-card.index'

        );
      
        /*
        |--------------------------------------------------------------------------
        | Inventory Adjustment
        |--------------------------------------------------------------------------
        */

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
            /*
        |--------------------------------------------------------------------------
        | Stock Transfer
        |--------------------------------------------------------------------------
        */

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

            '/transfers/{stockTransfer}/complete',

            [

                StockTransferController::class,

                'complete'

            ]

        )

        ->name(

            'stock-transfers.complete'

        );

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
            /*
        |--------------------------------------------------------------------------
        | Stock Issue
        |--------------------------------------------------------------------------
        */

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

    }

);