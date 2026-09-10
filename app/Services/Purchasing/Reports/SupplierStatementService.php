<?php

namespace App\Services\Purchasing\Reports;

use App\Models\MasterData\Supplier;
use App\Models\Purchasing\PurchaseInvoiceHeader;
use App\Models\Purchasing\PurchasePaymentHeader;
use App\Models\Purchasing\PurchaseReturnDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SupplierStatementService
{
    public function getStatement(array $filters = []): array
    {
        $supplierId = $filters['supplier_id'] ?? null;
        $branchId = $filters['branch_id'] ?? null;
        $companyId = $filters['company_id'] ?? null;

        $dateFrom = $filters['date_from']
            ?? Carbon::now()->startOfMonth()->toDateString();

        $dateTo = $filters['date_to']
            ?? Carbon::now()->toDateString();

        if (! $supplierId) {
            return [
                'supplier' => null,
                'branch_id' => $branchId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'opening_balance' => 0.0,
                'rows' => [],
                'totals' => [
                    'debit' => 0.0,
                    'credit' => 0.0,
                    'closing_balance' => 0.0,
                ],
            ];
        }

        $supplier = Supplier::query()->find($supplierId);

        if (! $supplier) {
            return [
                'supplier' => null,
                'branch_id' => $branchId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'opening_balance' => 0.0,
                'rows' => [],
                'totals' => [
                    'debit' => 0.0,
                    'credit' => 0.0,
                    'closing_balance' => 0.0,
                ],
            ];
        }

        $openingBalance = $this->getOpeningBalance(
            $supplierId,
            $branchId,
            $companyId,
            $dateFrom
        );

        $rows = collect()
            ->merge(
                $this->getInvoiceRows(
                    $supplierId,
                    $branchId,
                    $companyId,
                    $dateFrom,
                    $dateTo
                )
            )
            ->merge(
                $this->getReturnRows(
                    $supplierId,
                    $branchId,
                    $companyId,
                    $dateFrom,
                    $dateTo
                )
            )
            ->merge(
                $this->getPaymentRows(
                    $supplierId,
                    $branchId,
                    $companyId,
                    $dateFrom,
                    $dateTo
                )
            )
            ->sortBy([
                ['date', 'asc'],
                ['document', 'asc'],
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Running Balance
        |--------------------------------------------------------------------------
        */

        $balance = round($openingBalance, 2);

        $rows = $rows
            ->map(function (array $row) use (&$balance) {

                $debit = round(
                    (float) ($row['debit'] ?? 0),
                    2
                );

                $credit = round(
                    (float) ($row['credit'] ?? 0),
                    2
                );

                $balance = round(
                    $balance + $credit - $debit,
                    2
                );

                $row['debit'] = $debit;
                $row['credit'] = $credit;
                $row['balance'] = $balance;

                return $row;
            })
            ->values();

        $totalDebit = round(
            $rows->sum('debit'),
            2
        );

        $totalCredit = round(
            $rows->sum('credit'),
            2
        );

        $closingBalance = round(
            $openingBalance
                + $totalCredit
                - $totalDebit,
            2
        );

        return [
            'supplier' => [
                'id' => $supplier->id,
                'code' => $supplier->supplier_code,
                'name' => $supplier->name,
            ],

            'branch_id' => $branchId,

            'date_from' => $dateFrom,

            'date_to' => $dateTo,

            'opening_balance' => round(
                $openingBalance,
                2
            ),

            'rows' => $rows->all(),

            'totals' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'closing_balance' => $closingBalance,
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Opening Balance
    |--------------------------------------------------------------------------
    */

    protected function getOpeningBalance(
        int $supplierId,
        $branchId,
        $companyId,
        string $dateFrom
    ): float {

        $invoiceAmount = PurchaseInvoiceHeader::query()
            ->whereIn('status', [
                'Posted',
                'Partially Paid',
                'Paid',
            ])
            ->where('supplier_id', $supplierId)
            ->whereDate(
                'invoice_date',
                '<',
                $dateFrom
            )
            ->when(
                $companyId,
                fn ($query) =>
                    $query->where(
                        'company_id',
                        $companyId
                    )
            )
            ->when(
                $branchId,
                fn ($query) =>
                    $query->where(
                        'branch_id',
                        $branchId
                    )
            )
            ->sum('grand_total');


        $paymentAmount = PurchasePaymentHeader::query()
            ->where('status', 'Posted')
            ->where('supplier_id', $supplierId)
            ->whereDate(
                'payment_date',
                '<',
                $dateFrom
            )
            ->when(
                $companyId,
                fn ($query) =>
                    $query->where(
                        'company_id',
                        $companyId
                    )
            )
            ->when(
                $branchId,
                fn ($query) =>
                    $query->where(
                        'branch_id',
                        $branchId
                    )
            )
            ->sum('total_amount');


        $returnAmount = $this->getReturnAmountBeforeDate(
            $supplierId,
            $branchId,
            $companyId,
            $dateFrom
        );


        return round(
            (float) $invoiceAmount
                - (float) $paymentAmount
                - (float) $returnAmount,
            2
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Invoice Rows
    |--------------------------------------------------------------------------
    */

    protected function getInvoiceRows(
        int $supplierId,
        $branchId,
        $companyId,
        string $dateFrom,
        string $dateTo
    ): Collection {

        return PurchaseInvoiceHeader::query()
            ->whereIn('status', [
                'Posted',
                'Partially Paid',
                'Paid',
            ])
            ->where('supplier_id', $supplierId)
            ->whereBetween(
                'invoice_date',
                [$dateFrom, $dateTo]
            )
            ->when(
                $companyId,
                fn ($query) =>
                    $query->where(
                        'company_id',
                        $companyId
                    )
            )
            ->when(
                $branchId,
                fn ($query) =>
                    $query->where(
                        'branch_id',
                        $branchId
                    )
            )
            ->orderBy('invoice_date')
            ->orderBy('number')
            ->get()
            ->map(
                fn ($invoice) => [

                    'date' =>
                        Carbon::parse(
                            $invoice->invoice_date
                        )->toDateString(),

                    'document' =>
                        $invoice->number,

                    'type' =>
                        'Purchase Invoice',

                    'debit' =>
                        0.0,

                    'credit' =>
                        round(
                            (float) $invoice->grand_total,
                            2
                        ),

                    'balance' =>
                        0.0,

                ]
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Return Rows
    |--------------------------------------------------------------------------
    */

    protected function getReturnRows(
    int $supplierId,
    $branchId,
    $companyId,
    string $dateFrom,
    string $dateTo
    ): Collection {

    /*
    |--------------------------------------------------------------------------
    | Invoice Map
    |--------------------------------------------------------------------------
    |
    | Only invoices that already affect AP are considered.
    |
    | goods_receipt_id => invoice_date
    |
    */

    $invoiceMap = PurchaseInvoiceHeader::query()
        ->whereIn('status', [
            'Posted',
            'Partially Paid',
            'Paid',
        ])
        ->where('supplier_id', $supplierId)
        ->when(
            $companyId,
            fn ($query) =>
                $query->where(
                    'company_id',
                    $companyId
                )
        )
        ->when(
            $branchId,
            fn ($query) =>
                $query->where(
                    'branch_id',
                    $branchId
                )
        )
        ->get([
            'id',
            'goods_receipt_id',
            'invoice_date',
        ])
        ->filter(
            fn ($invoice) =>
                $invoice->goods_receipt_id
        )
        ->mapWithKeys(
            fn ($invoice) => [
                (int) $invoice->goods_receipt_id =>
                    Carbon::parse(
                        $invoice->invoice_date
                    )->toDateString(),
            ]
        );


    if ($invoiceMap->isEmpty()) {
        return collect();
    }


    /*
    |--------------------------------------------------------------------------
    | Posted Purchase Returns
    |--------------------------------------------------------------------------
    */

    $returns = PurchaseReturnDetail::query()
        ->whereHas(
            'purchaseReturn',
            function ($query) use (
                $supplierId,
                $branchId,
                $companyId,
                $dateFrom,
                $dateTo
            ) {

                $query
                    ->where(
                        'supplier_id',
                        $supplierId
                    )
                    ->where(
                        'status',
                        'Posted'
                    )
                    ->whereBetween(
                        'return_date',
                        [$dateFrom, $dateTo]
                    )
                    ->when(
                        $companyId,
                        fn ($query) =>
                            $query->where(
                                'company_id',
                                $companyId
                            )
                    )
                    ->when(
                        $branchId,
                        fn ($query) =>
                            $query->where(
                                'branch_id',
                                $branchId
                            )
                    );
            }
        )
        ->with('purchaseReturn')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | Keep Only AP-Related Returns
    |--------------------------------------------------------------------------
    */

    $apReturnDetails = $returns
        ->filter(
            function ($detail) use ($invoiceMap) {

                $return =
                    $detail->purchaseReturn;

                $goodsReceiptId =
                    (int) $return->goods_receipt_id;

                if (
                    ! $goodsReceiptId
                    || ! $invoiceMap->has(
                        $goodsReceiptId
                    )
                ) {
                    return false;
                }

                $invoiceDate =
                    $invoiceMap->get(
                        $goodsReceiptId
                    );

                $returnDate =
                    Carbon::parse(
                        $return->return_date
                    )->toDateString();

                return $invoiceDate <= $returnDate;
            }
        );


    if ($apReturnDetails->isEmpty()) {
        return collect();
    }


    /*
    |--------------------------------------------------------------------------
    | Group By Return Header
    |--------------------------------------------------------------------------
    */

    return $apReturnDetails
        ->groupBy(
            fn ($detail) =>
                $detail->purchaseReturn->id
        )
        ->map(
            function ($details) {

                $return =
                    $details
                        ->first()
                        ->purchaseReturn;

                return [

                    'date' =>
                        Carbon::parse(
                            $return->return_date
                        )->toDateString(),

                    'document' =>
                        $return->return_number,

                    'type' =>
                        'Purchase Return',

                    'debit' =>
                        round(
                            (float) $details->sum(
                                'total_cost'
                            ),
                            2
                        ),

                    'credit' =>
                        0.0,

                    'balance' =>
                        0.0,

                ];
            }
        )
        ->values();
}


    /*
    |--------------------------------------------------------------------------
    | Payment Rows
    |--------------------------------------------------------------------------
    */

    protected function getPaymentRows(
        int $supplierId,
        $branchId,
        $companyId,
        string $dateFrom,
        string $dateTo
    ): Collection {

        return PurchasePaymentHeader::query()
            ->where('status', 'Posted')
            ->where('supplier_id', $supplierId)
            ->whereBetween(
                'payment_date',
                [$dateFrom, $dateTo]
            )
            ->when(
                $companyId,
                fn ($query) =>
                    $query->where(
                        'company_id',
                        $companyId
                    )
            )
            ->when(
                $branchId,
                fn ($query) =>
                    $query->where(
                        'branch_id',
                        $branchId
                    )
            )
            ->orderBy('payment_date')
            ->orderBy('number')
            ->get()
            ->map(
                fn ($payment) => [

                    'date' =>
                        Carbon::parse(
                            $payment->payment_date
                        )->toDateString(),

                    'document' =>
                        $payment->number,

                    'type' =>
                        'Payment',

                    'debit' =>
                        round(
                            (float) $payment->total_amount,
                            2
                        ),

                    'credit' =>
                        0.0,

                    'balance' =>
                        0.0,

                ]
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Opening Return Amount
    |--------------------------------------------------------------------------
    */

   protected function getReturnAmountBeforeDate(
    int $supplierId,
    $branchId,
    $companyId,
    string $dateFrom
): float {

    /*
    |--------------------------------------------------------------------------
    | Invoice Map
    |--------------------------------------------------------------------------
    |
    | Only AP-valid invoices that existed before dateFrom.
    |
    | goods_receipt_id => invoice_date
    |
    */

    $invoiceMap = PurchaseInvoiceHeader::query()
        ->whereIn('status', [
            'Posted',
            'Partially Paid',
            'Paid',
        ])
        ->where('supplier_id', $supplierId)
        ->whereDate(
            'invoice_date',
            '<',
            $dateFrom
        )
        ->when(
            $companyId,
            fn ($query) =>
                $query->where(
                    'company_id',
                    $companyId
                )
        )
        ->when(
            $branchId,
            fn ($query) =>
                $query->where(
                    'branch_id',
                    $branchId
                )
        )
        ->get([
            'id',
            'goods_receipt_id',
            'invoice_date',
        ])
        ->filter(
            fn ($invoice) =>
                $invoice->goods_receipt_id
        )
        ->mapWithKeys(
            fn ($invoice) => [
                (int) $invoice->goods_receipt_id =>
                    Carbon::parse(
                        $invoice->invoice_date
                    )->toDateString(),
            ]
        );


    if ($invoiceMap->isEmpty()) {
        return 0.0;
    }


    /*
    |--------------------------------------------------------------------------
    | Posted Returns Before Date From
    |--------------------------------------------------------------------------
    */

    $returns = PurchaseReturnDetail::query()
        ->whereHas(
            'purchaseReturn',
            function ($query) use (
                $supplierId,
                $branchId,
                $companyId,
                $dateFrom
            ) {

                $query
                    ->where(
                        'supplier_id',
                        $supplierId
                    )
                    ->where(
                        'status',
                        'Posted'
                    )
                    ->whereDate(
                        'return_date',
                        '<',
                        $dateFrom
                    )
                    ->when(
                        $companyId,
                        fn ($query) =>
                            $query->where(
                                'company_id',
                                $companyId
                            )
                    )
                    ->when(
                        $branchId,
                        fn ($query) =>
                            $query->where(
                                'branch_id',
                                $branchId
                            )
                    );
            }
        )
        ->with('purchaseReturn')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | Calculate AP Return
    |--------------------------------------------------------------------------
    */

    return round(
        (float) $returns
            ->filter(
                function ($detail) use ($invoiceMap) {

                    $return =
                        $detail->purchaseReturn;

                    $goodsReceiptId =
                        (int) $return->goods_receipt_id;

                    if (
                        ! $goodsReceiptId
                        || ! $invoiceMap->has(
                            $goodsReceiptId
                        )
                    ) {
                        return false;
                    }

                    $invoiceDate =
                        $invoiceMap->get(
                            $goodsReceiptId
                        );

                    $returnDate =
                        Carbon::parse(
                            $return->return_date
                        )->toDateString();

                    return $invoiceDate <= $returnDate;
                }
            )
            ->sum('total_cost'),
        2
    );
}
}