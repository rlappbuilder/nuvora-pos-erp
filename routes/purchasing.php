<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Purchasing\PurchaseRequestController;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
use App\Http\Controllers\Purchasing\GoodsReceiptController;
use App\Http\Controllers\Purchasing\PurchaseInvoiceController;
use App\Http\Controllers\Purchasing\PurchaseReturnController;

Route::middleware('auth')
    ->prefix('purchasing')
    ->group(
        function () {

            /*
            |--------------------------------------------------------------------------
            | Purchase Request
            |--------------------------------------------------------------------------
            */

           

            Route::post(
                '/purchase-requests/{purchaseRequest}/submit',
                [
                    PurchaseRequestController::class,
                    'submit',
                ]
            )
                ->name(
                    'purchase-requests.submit'
                );

            Route::post(
                '/purchase-requests/{purchaseRequest}/approve',
                [
                    PurchaseRequestController::class,
                    'approve',
                ]
            )
                ->name(
                    'purchase-requests.approve'
                );

            Route::post(
                '/purchase-requests/{purchaseRequest}/reject',
                [
                    PurchaseRequestController::class,
                    'reject',
                ]
            )
                ->name(
                    'purchase-requests.reject'
                );

            Route::post(
                '/purchase-requests/{purchaseRequest}/cancel',
                [
                    PurchaseRequestController::class,
                    'cancel',
                ]
            )
                ->name(
                    'purchase-requests.cancel'
                );

            Route::post(
                '/purchase-requests/{purchaseRequest}/duplicate',
                [
                    PurchaseRequestController::class,
                    'duplicate',
                ]
            )
                ->name(
                    'purchase-requests.duplicate'
                );

            Route::post(
                '/purchase-requests/bulk-delete',
                [
                    PurchaseRequestController::class,
                    'bulkDelete',
                ]
            )
                ->name(
                    'purchase-requests.bulk-delete'
                );

            Route::get(
                '/purchase-requests/{purchaseRequest}/data',
                [
                    PurchaseRequestController::class,
                    'showData',
                ]
            )
                ->name(
                    'purchase-requests.data'
                );

                Route::resource(
                'purchase-requests',
                PurchaseRequestController::class
            );
          /*
            |--------------------------------------------------------------------------
            | Purchase Order
            |--------------------------------------------------------------------------
            */
        Route::post(
            '/purchase-orders/{purchaseOrder}/cancel',
            [
                PurchaseOrderController::class,
                'cancel',
            ]
        )->name(
            'purchasing.purchase-orders.cancel'
        );
            

         Route::post(
            '/purchase-orders/{purchaseOrder}/submit',
            [PurchaseOrderController::class, 'submit']
        )->name('purchasing.purchase-orders.submit');

            Route::post(
                '/purchase-orders/{purchaseOrder}/approve',
                [
                    PurchaseOrderController::class,
                    'approve',
                ]
            )
                ->name(
                    'purchase-orders.approve'
                );

            Route::post(
                '/purchase-orders/{purchaseOrder}/reject',
                [
                    PurchaseOrderController::class,
                    'reject',
                ]
            )
                ->name(
                    'purchase-orders.reject'
                );

            Route::post(
                '/purchase-orders/{purchaseOrder}/send',
                [
                    PurchaseOrderController::class,
                    'send',
                ]
            )
                ->name(
                    'purchase-orders.send'
                );

            Route::post(
                '/purchase-orders/{purchaseOrder}/confirm',
                [
                    PurchaseOrderController::class,
                    'confirm',
                ]
            )
                ->name(
                    'purchase-orders.confirm'
                );

            Route::delete(
                '/purchase-orders/bulk-delete',
                [
                    PurchaseOrderController::class,
                    'bulkDelete',
                ]
            )
                ->name(
                    'purchase-orders.bulk-delete'
                );

            Route::get(
                '/purchase-orders/{purchaseOrder}/data',
                [
                    PurchaseOrderController::class,
                    'showData',
                ]
            )
                ->name(
                    'purchase-orders.data'
                );

            Route::post(
                '/purchase-orders/{purchaseOrder}/duplicate',
                [
                    PurchaseOrderController::class,
                    'duplicate',
                ]
            )
                ->name(
                    'purchase-orders.duplicate'
                );

              Route::resource(
                    'purchase-orders',
                    PurchaseOrderController::class
                )
                    ->parameters([
                        'purchase-orders' => 'purchaseOrder',
                    ]);

            /*
            |--------------------------------------------------------------------------
            | Goods Receipt
            |--------------------------------------------------------------------------
            */

          

                Route::post(
                    '/goods-receipts/{goodsReceipt}/submit',
                    [
                        GoodsReceiptController::class,
                        'submit',
                    ]
                )
                    ->name(
                        'goods-receipts.submit'
                    );

                Route::post(
                    '/goods-receipts/{goodsReceipt}/approve',
                    [
                        GoodsReceiptController::class,
                        'approve',
                    ]
                )
                    ->name(
                        'goods-receipts.approve'
                    );

                Route::post(
                    '/goods-receipts/{goodsReceipt}/reject',
                    [
                        GoodsReceiptController::class,
                        'reject',
                    ]
                )
                    ->name(
                        'goods-receipts.reject'
                    );

                Route::post(
                    '/goods-receipts/{goodsReceipt}/post',
                    [
                        GoodsReceiptController::class,
                        'post',
                    ]
                )
                    ->name(
                        'goods-receipts.post'
                    );

                Route::post(
                    '/goods-receipts/{goodsReceipt}/cancel',
                    [
                        GoodsReceiptController::class,
                        'cancel',
                    ]
                )
                    ->name(
                        'goods-receipts.cancel'
                    );

                Route::delete(
                    '/goods-receipts-bulk-delete',
                    [
                        GoodsReceiptController::class,
                        'bulkDelete',
                    ]
                )
                    ->name(
                        'goods-receipts.bulk-delete'
                    );
                Route::get(
                    '/goods-receipts/{goodsReceipt}/data',
                    [
                        GoodsReceiptController::class,
                        'showData',
                    ]
                )
                    ->name(
                        'goods-receipts.data'
                    );
                  Route::resource(
                    'goods-receipts',
                    GoodsReceiptController::class
                );

                /*
                |--------------------------------------------------------------------------
                | Purchase Return
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/purchase-returns/{purchaseReturn}/submit',
                    [
                        PurchaseReturnController::class,
                        'submit',
                    ]
                )
                    ->name(
                        'purchase-returns.submit'
                    );

                Route::post(
                    '/purchase-returns/{purchaseReturn}/approve',
                    [
                        PurchaseReturnController::class,
                        'approve',
                    ]
                )
                    ->name(
                        'purchase-returns.approve'
                    );

                Route::post(
                    '/purchase-returns/{purchaseReturn}/reject',
                    [
                        PurchaseReturnController::class,
                        'reject',
                    ]
                )
                    ->name(
                        'purchase-returns.reject'
                    );

                Route::post(
                    '/purchase-returns/{purchaseReturn}/post',
                    [
                        PurchaseReturnController::class,
                        'post',
                    ]
                )
                    ->name(
                        'purchase-returns.post'
                    );

                Route::post(
                    '/purchase-returns/{purchaseReturn}/cancel',
                    [
                        PurchaseReturnController::class,
                        'cancel',
                    ]
                )
                    ->name(
                        'purchase-returns.cancel'
                    );

                Route::delete(
                    '/purchase-returns-bulk-delete',
                    [
                        PurchaseReturnController::class,
                        'bulkDelete',
                    ]
                )
                    ->name(
                        'purchase-returns.bulk-delete'
                    );

                Route::get(
                    '/purchase-returns/{purchaseReturn}/data',
                    [
                        PurchaseReturnController::class,
                        'showData',
                    ]
                )
                    ->name(
                        'purchase-returns.data'
                    );

                Route::resource(
                    'purchase-returns',
                    PurchaseReturnController::class
                );
            /*
            |--------------------------------------------------------------------------
            | Purchase Invoice
             Route::resource(
                'purchase-invoices',
                PurchaseInvoiceController::class
            );

            Route::post(
                '/purchase-invoices/{purchaseInvoice}/post',
                [
                    PurchaseInvoiceController::class,
                    'post',
                ]
            )
                ->name(
                    'purchase-invoices.post'
                );

            Route::post(
                '/purchase-invoices/{purchaseInvoice}/complete',
                [
                    PurchaseInvoiceController::class,
                    'complete',
                ]
            )
                ->name(
                    'purchase-invoices.complete'
                );

            Route::post(
                '/purchase-invoices/{purchaseInvoice}/cancel',
                [
                    PurchaseInvoiceController::class,
                    'cancel',
                ]
            )
                ->name(
                    'purchase-invoices.cancel'
                );

    
            |--------------------------------------------------------------------------
            */

       }
    );        