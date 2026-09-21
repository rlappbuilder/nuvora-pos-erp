<?php

namespace App\Services\Consignment;

use App\Models\Reseller\ConsignmentOut\ConsignmentOut;
use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader;
use Illuminate\Support\Collection;

class ResellerMutationService
{
    /*
    |--------------------------------------------------------------------------
    | Get Report
    |--------------------------------------------------------------------------
    */

    public function getReport(array $filters): array
    {
        $resellerId = $filters['reseller_id'] ?? null;

        if (! $resellerId) {
            return $this->emptyReport();
        }

        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Get Transactions
        |--------------------------------------------------------------------------
        */

        $outRows = $this->getConsignmentOutRows(
            $filters
        );

        $settlementRows = $this->getSettlementRows(
            $filters
        );

        /*
        |--------------------------------------------------------------------------
        | Combine
        |--------------------------------------------------------------------------
        */

        $rows = $outRows
            ->concat($settlementRows)
            ->sortBy([
                ['date', 'asc'],
                ['reference', 'asc'],
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Calculate Balance
        |--------------------------------------------------------------------------
        */

        $balanceQty = 0;

        $balanceValue = 0;

        $processedRows = collect();

        foreach ($rows as $row) {

            $balanceQty +=
                $row['in'] -
                $row['out'];

            $balanceValue +=
                $row['debit'] -
                $row['credit'];

            $row['balance_qty'] =
                round(
                    $balanceQty,
                    2
                );

            $row['balance_value'] =
                round(
                    $balanceValue,
                    2
                );

            $processedRows->push(
                $row
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [

            'consignment_qty' =>
                round(
                    $processedRows->sum('in'),
                    2
                ),

            'settled_qty' =>
                round(
                    $processedRows
                        ->where(
                            'description',
                            'Sales Settlement'
                        )
                        ->sum('out'),
                    2
                ),

            'current_stock_qty' =>
                round(
                    $balanceQty,
                    2
                ),

            'consignment_value' =>
                round(
                    $processedRows->sum('debit'),
                    2
                ),

            'settled_value' =>
                round(
                    $processedRows->sum('credit'),
                    2
                ),

            'current_stock_value' =>
                round(
                    $balanceValue,
                    2
                ),

        ];

        return [

            'type' =>
                'reseller_mutation',

            'title' =>
                'Reseller Mutation',

            'rows' =>
                $processedRows,

            'summary' =>
                $summary,

            'filters' =>
                [
                    'reseller_id' =>
                        $resellerId,

                    'date_from' =>
                        $dateFrom,

                    'date_to' =>
                        $dateTo,
                ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Consignment Out
    |--------------------------------------------------------------------------
    */

    protected function getConsignmentOutRows(
        array $filters
    ): Collection {

        $query =
            ConsignmentOut::query()
                ->with([
                    'reseller',
                    'details.variant.product',
                    'details.unit',
                ])
                ->where(
                    'status',
                    'Posted'
                );

        $this->applyHeaderFilters(
            $query,
            $filters,
            'transaction_date'
        );

        $rows = collect();

        $headers =
            $query
                ->orderBy(
                    'transaction_date'
                )
                ->orderBy(
                    'id'
                )
                ->get();

        foreach ($headers as $header) {

            foreach (
                $header->details
                as $detail
            ) {

                $qty =
                    (float) $detail->qty;

                $unitPrice =
                    (float) $detail->unit_price;

                $total =
                    round(
                        $qty *
                        $unitPrice,
                        2
                    );

                $rows->push([

                    'date' =>
                        $header->transaction_date,

                    'reference' =>
                        $header->consignment_out_number,

                    'description' =>
                        'Consignment Out',

                    'in' =>
                        $qty,

                    'out' =>
                        0,

                    'price' =>
                        $unitPrice,

                    'debit' =>
                        $total,

                    'credit' =>
                        0,

                    'reseller_id' =>
                        $header->reseller_id,

                ]);
            }
        }

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Sales Settlement
    |--------------------------------------------------------------------------
    */

    protected function getSettlementRows(
        array $filters
    ): Collection {

        $query =
            ConsignmentSettlementHeader::query()
                ->with([
                    'reseller',
                    'details.variant.product',
                    'details.unit',
                ])
                ->where(
                    'status',
                    'Posted'
                );

        $this->applyHeaderFilters(
            $query,
            $filters,
            'settlement_date'
        );

        $rows = collect();

        $headers =
            $query
                ->orderBy(
                    'settlement_date'
                )
                ->orderBy(
                    'id'
                )
                ->get();

        foreach ($headers as $header) {

            foreach (
                $header->details
                as $detail
            ) {

                $qty =
                    (float) $detail->qty_sold;

                $unitPrice =
                    (float) $detail->unit_price;

                $total =
                    (float) $detail->total_amount;

                $rows->push([

                    'date' =>
                        $header->settlement_date,

                    'reference' =>
                        $header->settlement_number,

                    'description' =>
                        'Sales Settlement',

                    'in' =>
                        0,

                    'out' =>
                        $qty,

                    'price' =>
                        $unitPrice,

                    'debit' =>
                        0,

                    'credit' =>
                        round(
                            $total,
                            2
                        ),

                    'reseller_id' =>
                        $header->reseller_id,

                ]);
            }
        }

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | Header Filters
    |--------------------------------------------------------------------------
    */

    protected function applyHeaderFilters(
        $query,
        array $filters,
        string $dateColumn
    ): void {

        if (
            ! empty(
                $filters['reseller_id']
            )
        ) {

            $query->where(
                'reseller_id',
                $filters['reseller_id']
            );
        }

        if (
            ! empty(
                $filters['branch_id']
            )
        ) {

            $query->where(
                'branch_id',
                $filters['branch_id']
            );
        }

        if (
            ! empty(
                $filters['date_from']
            )
        ) {

            $query->whereDate(
                $dateColumn,
                '>=',
                $filters['date_from']
            );
        }

        if (
            ! empty(
                $filters['date_to']
            )
        ) {

            $query->whereDate(
                $dateColumn,
                '<=',
                $filters['date_to']
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Empty Report
    |--------------------------------------------------------------------------
    */

    protected function emptyReport(): array
    {
        return [

            'type' =>
                'reseller_mutation',

            'title' =>
                'Reseller Mutation',

            'rows' =>
                collect(),

            'summary' =>
                [

                    'consignment_qty' =>
                        0,

                    'settled_qty' =>
                        0,

                    'current_stock_qty' =>
                        0,

                    'consignment_value' =>
                        0,

                    'settled_value' =>
                        0,

                    'current_stock_value' =>
                        0,

                ],

        ];
    }
}