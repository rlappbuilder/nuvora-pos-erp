<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Purchasing\PurchaseOrderController;
use App\Http\Controllers\Purchasing\GoodsReceiptController;
use App\Http\Controllers\Purchasing\PurchaseInvoiceController;

Route::middleware(

    'auth'

)

->prefix(

    'purchasing'

)

->group(

    function () {

        /*
        |--------------------------------------------------------------------------
        | Purchase Order
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'purchase-orders',

            PurchaseOrderController::class

        );

        Route::post(

            '/purchase-orders/{purchaseOrder}/approve',

            [

                PurchaseOrderController::class,

                'approve'

            ]

        )

        ->name(

            'purchase-orders.approve'

        );

        Route::post(

            '/purchase-orders/{purchaseOrder}/cancel',

            [

                PurchaseOrderController::class,

                'cancel'

            ]

        )

        ->name(

            'purchase-orders.cancel'

        );

        /*
        |--------------------------------------------------------------------------
        | Goods Receipt
        |--------------------------------------------------------------------------
        */

        Route::resource(

            'goods-receipts',

            GoodsReceiptController::class

        );

        Route::post(

            '/goods-receipts/{goodsReceipt}/post',

            [

                GoodsReceiptController::class,

                'post'

            ]

        )

        ->name(

            'goods-receipts.post'

        );

        Route::post(

            '/goods-receipts/{goodsReceipt}/complete',

            [

                GoodsReceiptController::class,

                'complete'

            ]

        )

        ->name(

            'goods-receipts.complete'

        );

        Route::post(

            '/goods-receipts/{goodsReceipt}/cancel',

            [

                GoodsReceiptController::class,

                'cancel'

            ]

        )

        ->name(

            'goods-receipts.cancel'

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

                'post'

            ]

        )

        ->name(

            'purchase-invoices.post'

        );

        Route::post(

            '/purchase-invoices/{purchaseInvoice}/complete',

            [

                PurchaseInvoiceController::class,

                'complete'

            ]

        )

        ->name(

            'purchase-invoices.complete'

        );

        Route::post(

            '/purchase-invoices/{purchaseInvoice}/cancel',

            [

                PurchaseInvoiceController::class,

                'cancel'

            ]

        )

        ->name(

            'purchase-invoices.cancel'

        ); 

        |--------------------------------------------------------------------------
        */

       
    }

);