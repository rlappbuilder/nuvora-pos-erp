<?php

namespace App\Services\Purchasing\Reports;

use App\Models\Purchasing\PurchaseInvoiceHeader;
use App\Models\Purchasing\PurchasePaymentDetail;
use App\Models\Purchasing\PurchaseReturnDetail;
use Illuminate\Support\Collection;

class APAgingService
{
    /*
    |--------------------------------------------------------------------------
    | Get AP Aging
    |--------------------------------------------------------------------------
    */

    public function getAging(array $filters = []): array
    {
        $asOfDate = $filters['as_of_date']
            ?? now()->toDateString();

        $query = PurchaseInvoiceHeader::query()
            ->with([
                'supplier',
                'details',
            ])
            ->where('status', 'Posted')
            ->whereDate(
                'invoice_date',
                '<=',
                $asOfDate
            );

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

        $invoices = $query
            ->orderBy('supplier_id')
            ->orderBy('due_date')
            ->orderBy('invoice_date')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Build Supplier Rows
        |--------------------------------------------------------------------------
        */

        $supplierRows = [];

        foreach ($invoices as $invoice) {

            $invoiceAmount =
                round(
                    (float) $invoice->grand_total,
                    2
                );

            if ($invoiceAmount <= 0) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Posted Payment
            |--------------------------------------------------------------------------
            */

            $paidAmount =
                $this->getPostedPaymentAmount(
                    $invoice->id,
                    $asOfDate
                );

            /*
            |--------------------------------------------------------------------------
            | Purchase Return
            |--------------------------------------------------------------------------
            */

            $returnAmount =
                $this->getPostedReturnAmount(
                    $invoice,
                    $asOfDate
                );

            /*
            |--------------------------------------------------------------------------
            | Outstanding
            |--------------------------------------------------------------------------
            */

            $outstanding =
                round(
                    $invoiceAmount
                    - $paidAmount
                    - $returnAmount,
                    2
                );

            /*
            |--------------------------------------------------------------------------
            | Ignore Fully Settled Invoice
            |--------------------------------------------------------------------------
            */

            if ($outstanding <= 0) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Aging
            |--------------------------------------------------------------------------
            */

            $aging =
                $this->calculateAging(
                    $invoice->due_date,
                    $asOfDate,
                    $outstanding
                );

            $supplierId =
                $invoice->supplier_id;

            $supplierName =
                $invoice->supplier?->name
                ?? '-';

            /*
            |--------------------------------------------------------------------------
            | Initialize Supplier
            |--------------------------------------------------------------------------
            */

            if (! isset($supplierRows[$supplierId])) {

                $supplierRows[$supplierId] = [
                    'supplier_id' =>
                        $supplierId,

                    'supplier_name' =>
                        $supplierName,

                    'current' =>
                        0,

                    'days_1_30' =>
                        0,

                    'days_31_60' =>
                        0,

                    'days_61_90' =>
                        0,

                    'over_90' =>
                        0,

                    'total' =>
                        0,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Add Aging
            |--------------------------------------------------------------------------
            */

            $supplierRows[$supplierId]['current']
                += $aging['current'];

            $supplierRows[$supplierId]['days_1_30']
                += $aging['days_1_30'];

            $supplierRows[$supplierId]['days_31_60']
                += $aging['days_31_60'];

            $supplierRows[$supplierId]['days_61_90']
                += $aging['days_61_90'];

            $supplierRows[$supplierId]['over_90']
                += $aging['over_90'];

            $supplierRows[$supplierId]['total']
                += $outstanding;
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Amounts
        |--------------------------------------------------------------------------
        */

        foreach ($supplierRows as &$row) {

            $row['current'] =
                round($row['current'], 2);

            $row['days_1_30'] =
                round($row['days_1_30'], 2);

            $row['days_31_60'] =
                round($row['days_31_60'], 2);

            $row['days_61_90'] =
                round($row['days_61_90'], 2);

            $row['over_90'] =
                round($row['over_90'], 2);

            $row['total'] =
                round($row['total'], 2);
        }

        unset($row);

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */

        $totals = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'over_90' => 0,
            'total' => 0,
        ];

        foreach ($supplierRows as $row) {

            $totals['current']
                += $row['current'];

            $totals['days_1_30']
                += $row['days_1_30'];

            $totals['days_31_60']
                += $row['days_31_60'];

            $totals['days_61_90']
                += $row['days_61_90'];

            $totals['over_90']
                += $row['over_90'];

            $totals['total']
                += $row['total'];
        }

        foreach ($totals as &$amount) {
            $amount = round($amount, 2);
        }

        unset($amount);

        return [
            'as_of_date' =>
                $asOfDate,

            'rows' =>
                array_values($supplierRows),

            'totals' =>
                $totals,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get Posted Payment Amount
    |--------------------------------------------------------------------------
    */

    protected function getPostedPaymentAmount(
        int $invoiceId,
        string $asOfDate
    ): float {

        return round(
            (float) PurchasePaymentDetail::query()
                ->where(
                    'purchase_invoice_header_id',
                    $invoiceId
                )
                ->whereHas(
                    'purchasePayment',
                    function ($query) use ($asOfDate) {

                        $query
                            ->where(
                                'status',
                                'Posted'
                            )
                            ->whereDate(
                                'payment_date',
                                '<=',
                                $asOfDate
                            );
                    }
                )
                ->sum('payment_amount'),
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Get Posted Purchase Return Amount
    |--------------------------------------------------------------------------
    */

   protected function getPostedReturnAmount(
    PurchaseInvoiceHeader $invoice,
    string $asOfDate
): float {
    if (! $invoice->goods_receipt_id) {
        return 0;
    }

    $invoiceDetails = $invoice->details;

    if ($invoiceDetails->isEmpty()) {
        return 0;
    }

    $goodsReceiptDetailIds =
        $invoiceDetails
            ->pluck('goods_receipt_detail_id')
            ->filter()
            ->unique()
            ->values();

    if ($goodsReceiptDetailIds->isEmpty()) {
        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Posted Purchase Return
    |--------------------------------------------------------------------------
    |
    | Purchase Return is GRN based.
    | Only returns against GRN details belonging to
    | this invoice are considered as AP reduction.
    |
    */

    return round(
        (float) PurchaseReturnDetail::query()
            ->whereIn(
                'goods_receipt_detail_id',
                $goodsReceiptDetailIds
            )
            ->whereHas(
                'purchaseReturn',
                function ($query) use ($invoice, $asOfDate) {

                    $query
                        ->where(
                            'status',
                            'Posted'
                        )
                        ->where(
                            'supplier_id',
                            $invoice->supplier_id
                        )
                        ->where(
                            'company_id',
                            $invoice->company_id
                        )
                        ->where(
                            'branch_id',
                            $invoice->branch_id
                        )
                        ->whereDate(
                            'return_date',
                            '<=',
                            $asOfDate
                        );
                }
            )
            ->sum('total_cost'),
        2
    );
}

    /*
    |--------------------------------------------------------------------------
    | Calculate Aging
    |--------------------------------------------------------------------------
    */

    protected function calculateAging(
        $dueDate,
        string $asOfDate,
        float $amount
    ): array {

        $amount =
            round($amount, 2);

        $result = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'over_90' => 0,
        ];

        if (! $dueDate) {

            $result['current'] =
                $amount;

            return $result;
        }

        $dueDate =
            \Carbon\Carbon::parse(
                $dueDate
            );

        $asOf =
            \Carbon\Carbon::parse(
                $asOfDate
            );

        /*
        |--------------------------------------------------------------------------
        | Current / Not Yet Due
        |--------------------------------------------------------------------------
        */

        if ($dueDate->greaterThanOrEqualTo($asOf)) {

            $result['current'] =
                $amount;

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | Overdue Days
        |--------------------------------------------------------------------------
        */

        $daysOverdue =
            $dueDate->diffInDays(
                $asOf
            );

        if ($daysOverdue <= 30) {

            $result['days_1_30'] =
                $amount;

        } elseif ($daysOverdue <= 60) {

            $result['days_31_60'] =
                $amount;

        } elseif ($daysOverdue <= 90) {

            $result['days_61_90'] =
                $amount;

        } else {

            $result['over_90'] =
                $amount;
        }

        return $result;
    }
}