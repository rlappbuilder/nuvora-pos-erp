<?php

namespace App\Services\Purchasing\Reports;

use App\Models\Purchasing\PurchaseOrderHeader;
use App\Models\Purchasing\GoodsReceiptHeader;
use App\Models\Purchasing\PurchaseInvoiceHeader;
use App\Models\Purchasing\PurchaseReturnHeader;

class PurchaseReportService
{
    /*
    |--------------------------------------------------------------------------
    | Get Purchase Report
    |--------------------------------------------------------------------------
    */

    public function getReport(array $filters = []): array
    {
        $dateFrom = $filters['date_from']
            ?? now()->startOfMonth()->toDateString();

        $dateTo = $filters['date_to']
            ?? now()->toDateString();

        $rows = [];

        /*
        |--------------------------------------------------------------------------
        | Purchase Orders
        |--------------------------------------------------------------------------
        */

        $purchaseOrders = PurchaseOrderHeader::query()
            ->with([
                'supplier',
                'branch',
            ])
            ->whereIn(
                    'status',
                    [
                        'Posted',
                        'Partially Received',
                        'Fully Received',
                    ]
                )
            ->whereDate(
                'order_date',
                '>=',
                $dateFrom
            )
            ->whereDate(
                'order_date',
                '<=',
                $dateTo
            );

        $this->applyFilters(
            $purchaseOrders,
            $filters
        );

        foreach (
            $purchaseOrders
                ->orderBy('order_date')
                ->orderBy('number')
                ->get()
            as $purchaseOrder
        ) {

            $amount =
                round(
                    (float) $purchaseOrder->grand_total,
                    2
                );

            if ($amount <= 0) {
                continue;
            }

            $rows[] = [
                'date' =>
                    optional(
                        $purchaseOrder->order_date
                    )->toDateString(),

                'document' =>
                    $purchaseOrder->number,

                'supplier_id' =>
                    $purchaseOrder->supplier_id,

                'supplier_name' =>
                    $purchaseOrder->supplier?->name
                    ?? '-',

                'branch_id' =>
                    $purchaseOrder->branch_id,

                'branch_name' =>
                    $purchaseOrder->branch?->name
                    ?? '-',

                'type' =>
                    'Purchase Order',

                'amount' =>
                    $amount,

                'status' =>
                    'Posted',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Goods Receipts
        |--------------------------------------------------------------------------
        */

        $goodsReceipts = GoodsReceiptHeader::query()
            ->with([
                'supplier',
                'branch',
                'details',
            ])
            ->where('status', 'Posted')
            ->whereDate(
                'receipt_date',
                '>=',
                $dateFrom
            )
            ->whereDate(
                'receipt_date',
                '<=',
                $dateTo
            );

        $this->applyFilters(
            $goodsReceipts,
            $filters
        );

        foreach (
            $goodsReceipts
                ->orderBy('receipt_date')
                ->orderBy('grn_number')
                ->get()
            as $goodsReceipt
        ) {

            $amount =
                $goodsReceipt->details
                    ->sum(function ($detail) {

                        return
                            (float) $detail->received_qty
                            *
                            (float) $detail->unit_cost;
                    });

            $amount =
                round($amount, 2);

            if ($amount <= 0) {
                continue;
            }

            $rows[] = [
                'date' =>
                    optional(
                        $goodsReceipt->receipt_date
                    )->toDateString(),

                'document' =>
                    $goodsReceipt->grn_number,

                'supplier_id' =>
                    $goodsReceipt->supplier_id,

                'supplier_name' =>
                    $goodsReceipt->supplier?->name
                    ?? '-',

                'branch_id' =>
                    $goodsReceipt->branch_id,

                'branch_name' =>
                    $goodsReceipt->branch?->name
                    ?? '-',

                'type' =>
                    'Goods Receipt',

                'amount' =>
                    $amount,

                'status' =>
                    'Posted',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Purchase Invoices
        |--------------------------------------------------------------------------
        */

        $purchaseInvoices = PurchaseInvoiceHeader::query()
            ->with([
                'supplier',
                'branch',
            ])
            ->where('status', 'Posted')
            ->whereDate(
                'invoice_date',
                '>=',
                $dateFrom
            )
            ->whereDate(
                'invoice_date',
                '<=',
                $dateTo
            );

        $this->applyFilters(
            $purchaseInvoices,
            $filters
        );

        foreach (
            $purchaseInvoices
                ->orderBy('invoice_date')
                ->orderBy('number')
                ->get()
            as $purchaseInvoice
        ) {

            $amount =
                round(
                    (float) $purchaseInvoice->grand_total,
                    2
                );

            if ($amount <= 0) {
                continue;
            }

            $rows[] = [
                'date' =>
                    optional(
                        $purchaseInvoice->invoice_date
                    )->toDateString(),

                'document' =>
                    $purchaseInvoice->number
                    ?? $purchaseInvoice->invoice_number,

                'supplier_id' =>
                    $purchaseInvoice->supplier_id,

                'supplier_name' =>
                    $purchaseInvoice->supplier?->name
                    ?? '-',

                'branch_id' =>
                    $purchaseInvoice->branch_id,

                'branch_name' =>
                    $purchaseInvoice->branch?->name
                    ?? '-',

                'type' =>
                    'Purchase Invoice',

                'amount' =>
                    $amount,

                'status' =>
                    'Posted',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Purchase Returns
        |--------------------------------------------------------------------------
        */

        $purchaseReturns = PurchaseReturnHeader::query()
            ->with([
                'supplier',
                'branch',
                'details',
            ])
            ->where('status', 'Posted')
            ->whereDate(
                'return_date',
                '>=',
                $dateFrom
            )
            ->whereDate(
                'return_date',
                '<=',
                $dateTo
            );

        $this->applyFilters(
            $purchaseReturns,
            $filters
        );

        foreach (
            $purchaseReturns
                ->orderBy('return_date')
                ->orderBy('return_number')
                ->get()
            as $purchaseReturn
        ) {

            $amount =
                $purchaseReturn->details
                    ->sum(function ($detail) {

                        return
                            (float) $detail->total_cost;
                    });

            $amount =
                round($amount, 2);

            if ($amount <= 0) {
                continue;
            }

            $rows[] = [
                'date' =>
                    optional(
                        $purchaseReturn->return_date
                    )->toDateString(),

                'document' =>
                    $purchaseReturn->return_number,

                'supplier_id' =>
                    $purchaseReturn->supplier_id,

                'supplier_name' =>
                    $purchaseReturn->supplier?->name
                    ?? '-',

                'branch_id' =>
                    $purchaseReturn->branch_id,

                'branch_name' =>
                    $purchaseReturn->branch?->name
                    ?? '-',

                'type' =>
                    'Purchase Return',

                'amount' =>
                    -$amount,

                'status' =>
                    'Posted',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Sort Rows
        |--------------------------------------------------------------------------
        */

        usort(
            $rows,
            function ($a, $b) {

                $dateCompare =
                    strcmp(
                        $a['date'] ?? '',
                        $b['date'] ?? ''
                    );

                if ($dateCompare !== 0) {
                    return $dateCompare;
                }

                return strcmp(
                    $a['document'] ?? '',
                    $b['document'] ?? ''
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */

        $totals = [
            'purchase_orders' =>
                0,

            'goods_receipts' =>
                0,

            'purchase_invoices' =>
                0,

            'purchase_returns' =>
                0,

            'net_purchase' =>
                0,
        ];

        foreach ($rows as $row) {

            $amount =
                round(
                    (float) $row['amount'],
                    2
                );

            switch ($row['type']) {

                case 'Purchase Order':

                    $totals['purchase_orders']
                        += $amount;

                    break;

                case 'Goods Receipt':

                    $totals['goods_receipts']
                        += $amount;

                    break;

                case 'Purchase Invoice':

                    $totals['purchase_invoices']
                        += $amount;

                    break;

                case 'Purchase Return':

                    $totals['purchase_returns']
                        += abs($amount);

                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Net Purchase
        |--------------------------------------------------------------------------
        */

        $totals['net_purchase'] =
            round(
                $totals['purchase_invoices']
                -
                $totals['purchase_returns'],
                2
            );

        /*
        |--------------------------------------------------------------------------
        | Normalize Totals
        |--------------------------------------------------------------------------
        */

        foreach ($totals as &$amount) {
            $amount =
                round(
                    $amount,
                    2
                );
        }

        unset($amount);

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [
            'date_from' =>
                $dateFrom,

            'date_to' =>
                $dateTo,

            'rows' =>
                $rows,

            'totals' =>
                $totals,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Filters
    |--------------------------------------------------------------------------
    */

    protected function applyFilters(
        $query,
        array $filters
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        if (! empty($filters['company_id'])) {

            $query->where(
                'company_id',
                $filters['company_id']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Branch
        |--------------------------------------------------------------------------
        */

        if (! empty($filters['branch_id'])) {

            $query->where(
                'branch_id',
                $filters['branch_id']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Supplier
        |--------------------------------------------------------------------------
        */

        if (! empty($filters['supplier_id'])) {

            $query->where(
                'supplier_id',
                $filters['supplier_id']
            );
        }
    }
}