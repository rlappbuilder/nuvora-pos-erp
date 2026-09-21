<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        {{ $report['title'] ?? 'Consignment Report' }}
    </title>

    <style>
        @page {
            size: A4 landscape;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .toolbar {
            width: min(1200px, calc(100% - 48px));
            margin: 20px auto 0;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .toolbar button {
            border: 0;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }

        .back {
            background: #6b7280;
            color: #fff;
        }

        .print {
            background: #2563eb;
            color: #fff;
        }

        .paper {
            width: min(1200px, calc(100% - 48px));
            min-height: 1100px;
            margin: 16px auto 32px;
            padding: 40px 48px;
            background: #fff;
        }

        .company {
            font-size: 14px;
            font-weight: 700;
        }

        .title {
            margin-top: 4px;
            font-size: 22px;
            font-weight: 700;
        }

        .subtitle {
            margin-top: 4px;
            font-size: 13px;
            color: #6b7280;
        }

        .filter {
            margin-top: 16px;
            font-size: 12px;
            color: #4b5563;
        }

        .filter span {
            margin-right: 20px;
        }

        .section {
            margin-top: 28px;
        }

        .section-title {
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 8px;
            text-align: left;
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            font-weight: 700;
            white-space: nowrap;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        td.number,
        th.number {
            text-align: right;
        }

        .total td {
            font-weight: 700;
            border-top: 1px solid #9ca3af;
        }

        .summary-table {
            width: auto;
            min-width: 600px;
        }

        .summary-table th,
        .summary-table td {
            padding: 8px 14px;
        }

        .empty {
            padding: 20px;
            text-align: center;
            color: #6b7280;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .paper {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
            }

            tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>

<div class="toolbar">

    <button
        type="button"
        class="back"
        onclick="window.close()"
    >
        Back
    </button>

    <button
        type="button"
        class="print"
        onclick="window.print()"
    >
        Print
    </button>

</div>


<div class="paper">

    <div class="company">
        NUVORA ERP
    </div>

    <div class="title">
        {{ strtoupper($report['title'] ?? 'CONSIGNMENT REPORT') }}
    </div>

    <div class="subtitle">
        Consignment Reporting
    </div>


    <div class="filter">

        <span>
            <strong>Period:</strong>
            {{ $filters['date_from'] ?? '-' }}
            -
            {{ $filters['date_to'] ?? '-' }}
        </span>

        <span>
            <strong>Reseller:</strong>
            {{ $report['reseller']['name'] ?? 'All Reseller' }}
        </span>

        <span>
            <strong>Branch:</strong>
            {{ $report['branch']['name'] ?? 'All Branch' }}
        </span>

    </div>


    @php
        $type = $report['type'] ?? 'all_activity';
        $rows = $report['rows'] ?? [];
    @endphp


    {{-- ========================================================= --}}
    {{-- ALL ACTIVITY --}}
    {{-- ========================================================= --}}

    @if ($type === 'all_activity')

        <div class="section">

            <div class="section-title">
                ALL ACTIVITY
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Activity</th>
                        <th>Number</th>
                        <th>Reseller</th>
                        <th>Product</th>
                        <th>Unit</th>
                        <th class="number">Qty</th>
                        <th class="number">Amount</th>
                    </tr>
                </thead>

                <tbody>

                @forelse ($rows as $row)

                    <tr>

                        <td>
                            {{ $row['date'] ?? '' }}
                        </td>

                        <td>
                            {{ $row['activity'] ?? '' }}
                        </td>

                        <td>
                            {{ $row['number'] ?? '' }}
                        </td>

                        <td>
                            {{ $row['reseller'] ?? '' }}
                        </td>

                        <td>
                            {{ $row['product'] ?? '-' }}
                        </td>

                        <td>
                            {{ $row['unit'] ?? '-' }}
                        </td>

                        <td class="number">
                            {{ $row['qty_label'] ?? '-' }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['amount'] ?? 0), 2) }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="empty">
                            No activity data.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>


        <div class="section">

            <div class="section-title">
                SUMMARY
            </div>

            <table class="summary-table">

                <thead>
                    <tr>
                        <th>Consignment Out</th>
                        <th>Sold</th>
                        <th>Return</th>
                        <th>Payment</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>

                        <td class="number">
                            {{ $report['summary']['consignment_out_qty'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ $report['summary']['sold_qty'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ $report['summary']['return_qty'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($report['summary']['payment_amount'] ?? 0), 2) }}
                        </td>

                    </tr>
                </tbody>

            </table>

        </div>


    {{-- ========================================================= --}}
    {{-- CONSIGNMENT OUT --}}
    {{-- ========================================================= --}}

    @elseif ($type === 'consignment_out')

        <div class="section">

            <div class="section-title">
                CONSIGNMENT OUT
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Number</th>
                        <th>Reseller</th>
                        <th>Branch</th>
                        <th>Warehouse</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Unit</th>
                        <th class="number">Qty</th>
                        <th class="number">Price</th>
                        <th class="number">Total</th>
                    </tr>
                </thead>

                <tbody>

                @forelse ($rows as $row)

                    <tr>

                        <td>{{ $row['date'] ?? '' }}</td>
                        <td>{{ $row['number'] ?? '' }}</td>
                        <td>{{ $row['reseller'] ?? '' }}</td>
                        <td>{{ $row['branch'] ?? '' }}</td>
                        <td>{{ $row['warehouse'] ?? '' }}</td>
                        <td>{{ $row['product'] ?? '' }}</td>
                        <td>{{ $row['variant'] ?? '' }}</td>
                        <td>{{ $row['unit'] ?? '' }}</td>

                        <td class="number">
                            {{ $row['qty'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['unit_price'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['total'] ?? 0), 2) }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="11" class="empty">
                            No consignment out data.
                        </td>
                    </tr>

                @endforelse

                </tbody>

                @if (count($rows) > 0)

                    <tfoot>

                        <tr class="total">

                            <td colspan="8">
                                TOTAL
                            </td>

                            <td class="number">
                                {{ collect($rows)->sum('qty') }}
                            </td>

                            <td></td>

                            <td class="number">
                                {{ number_format((float) collect($rows)->sum('total'), 2) }}
                            </td>

                        </tr>

                    </tfoot>

                @endif

            </table>

        </div>


    {{-- ========================================================= --}}
    {{-- SALES SETTLEMENT --}}
    {{-- ========================================================= --}}

    @elseif ($type === 'sales_settlement')

        <div class="section">

            <div class="section-title">
                SALES SETTLEMENT
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Number</th>
                        <th>Reseller</th>
                        <th>Branch</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Unit</th>
                        <th class="number">Qty</th>
                        <th class="number">Price</th>
                        <th class="number">Sales</th>
                    </tr>
                </thead>

                <tbody>

                @forelse ($rows as $row)

                    <tr>

                        <td>{{ $row['date'] ?? '' }}</td>
                        <td>{{ $row['number'] ?? '' }}</td>
                        <td>{{ $row['reseller'] ?? '' }}</td>
                        <td>{{ $row['branch'] ?? '' }}</td>
                        <td>{{ $row['product'] ?? '' }}</td>
                        <td>{{ $row['variant'] ?? '' }}</td>
                        <td>{{ $row['unit'] ?? '' }}</td>

                        <td class="number">
                            {{ $row['qty'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['unit_price'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['sales'] ?? 0), 2) }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="10" class="empty">
                            No sales settlement data.
                        </td>
                    </tr>

                @endforelse

                </tbody>

                @if (count($rows) > 0)

                    <tfoot>

                        <tr class="total">

                            <td colspan="7">
                                TOTAL
                            </td>

                            <td class="number">
                                {{ collect($rows)->sum('qty') }}
                            </td>

                            <td></td>

                            <td class="number">
                                {{ number_format((float) collect($rows)->sum('sales'), 2) }}
                            </td>

                        </tr>

                    </tfoot>

                @endif

            </table>

        </div>


        <div class="section">

            <div class="section-title">
                SUMMARY
            </div>

            <table class="summary-table">

                <thead>
                    <tr>
                        <th>Documents</th>
                        <th>Qty</th>
                        <th>Sales</th>
                        <th>Payment</th>
                        <th>Receivable</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>

                        <td class="number">
                            {{ $report['summary']['documents'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ $report['summary']['qty'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($report['summary']['sales'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($report['summary']['payment'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($report['summary']['receivable'] ?? 0), 2) }}
                        </td>

                    </tr>
                </tbody>

            </table>

        </div>


    {{-- ========================================================= --}}
    {{-- RECEIVABLE PAYMENT --}}
    {{-- ========================================================= --}}

    @elseif ($type === 'receivable_payment')

        <div class="section">

            <div class="section-title">
                RECEIVABLE PAYMENT
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Number</th>
                        <th>Reseller</th>
                        <th>Branch</th>
                        <th>Settlement</th>
                        <th>Payment Method</th>
                        <th>Payment Account</th>
                        <th class="number">Payment</th>
                        <th class="number">Settlement Amount</th>
                        <th class="number">Previous Paid</th>
                        <th class="number">Previous Outstanding</th>
                        <th class="number">Outstanding After</th>
                    </tr>
                </thead>

                <tbody>

                @forelse ($rows as $row)

                    <tr>

                        <td>{{ $row['date'] ?? '' }}</td>
                        <td>{{ $row['number'] ?? '' }}</td>
                        <td>{{ $row['reseller'] ?? '' }}</td>
                        <td>{{ $row['branch'] ?? '' }}</td>
                        <td>{{ $row['settlement_number'] ?? '' }}</td>
                        <td>{{ $row['payment_method'] ?? '' }}</td>
                        <td>{{ $row['payment_account'] ?? '' }}</td>

                        <td class="number">
                            {{ number_format((float) ($row['payment'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['settlement_amount'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['previous_paid'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['previous_outstanding'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['outstanding_after'] ?? 0), 2) }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="12" class="empty">
                            No receivable payment data.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>


    {{-- ========================================================= --}}
    {{-- CONSIGNMENT RETURN --}}
    {{-- ========================================================= --}}

    @elseif ($type === 'consignment_return')

        <div class="section">

            <div class="section-title">
                CONSIGNMENT RETURN
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Number</th>
                        <th>Reseller</th>
                        <th>Branch</th>
                        <th>Warehouse</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Unit</th>
                        <th class="number">Qty</th>
                        <th class="number">Unit Cost</th>
                        <th class="number">Total Cost</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>

                @forelse ($rows as $row)

                    <tr>

                        <td>{{ $row['date'] ?? '' }}</td>
                        <td>{{ $row['number'] ?? '' }}</td>
                        <td>{{ $row['reseller'] ?? '' }}</td>
                        <td>{{ $row['branch'] ?? '' }}</td>
                        <td>{{ $row['warehouse'] ?? '' }}</td>
                        <td>{{ $row['product'] ?? '' }}</td>
                        <td>{{ $row['variant'] ?? '' }}</td>
                        <td>{{ $row['unit'] ?? '' }}</td>

                        <td class="number">
                            {{ $row['qty'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['unit_cost'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['total_cost'] ?? 0), 2) }}
                        </td>

                        <td>{{ $row['remarks'] ?? '' }}</td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="12" class="empty">
                            No consignment return data.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>


    {{-- ========================================================= --}}
    {{-- STOCK POSITION --}}
    {{-- ========================================================= --}}

    @elseif ($type === 'stock_position')

        <div class="section">

            <div class="section-title">
                STOCK POSITION
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Reseller</th>
                        <th>Branch</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Unit</th>
                        <th class="number">On Hand</th>
                        <th class="number">Available</th>
                        <th class="number">Consignment Price</th>
                        <th class="number">Stock Value</th>
                    </tr>
                </thead>

                <tbody>

                @forelse ($rows as $row)

                    <tr>

                        <td>{{ $row['reseller'] ?? '' }}</td>
                        <td>{{ $row['branch'] ?? '' }}</td>
                        <td>{{ $row['product'] ?? '' }}</td>
                        <td>{{ $row['variant'] ?? '' }}</td>
                        <td>{{ $row['unit'] ?? '' }}</td>

                        <td class="number">
                            {{ $row['on_hand'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ $row['available'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['consignment_price'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['stock_value'] ?? 0), 2) }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="9" class="empty">
                            No stock position data.
                        </td>
                    </tr>

                @endforelse

                </tbody>

                @if (count($rows) > 0)

                    <tfoot>

                        <tr class="total">

                            <td colspan="5">
                                TOTAL
                            </td>

                            <td class="number">
                                {{ collect($rows)->sum('on_hand') }}
                            </td>

                            <td class="number">
                                {{ collect($rows)->sum('available') }}
                            </td>

                            <td></td>

                            <td class="number">
                                {{ number_format((float) collect($rows)->sum('stock_value'), 2) }}
                            </td>

                        </tr>

                    </tfoot>

                @endif

            </table>

        </div>


    {{-- ========================================================= --}}
    {{-- RECEIVABLE --}}
    {{-- ========================================================= --}}

    @elseif ($type === 'receivable')

        <div class="section">

            <div class="section-title">
                RECEIVABLE
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Number</th>
                        <th>Reseller</th>
                        <th class="number">Debit</th>
                        <th class="number">Credit</th>
                        <th class="number">Balance</th>
                    </tr>
                </thead>

                <tbody>

                @forelse ($rows as $row)

                    <tr>

                        <td>{{ $row['date'] ?? '' }}</td>
                        <td>{{ $row['type'] ?? '' }}</td>
                        <td>{{ $row['number'] ?? '' }}</td>
                        <td>{{ $row['reseller'] ?? '' }}</td>

                        <td class="number">
                            {{ number_format((float) ($row['debit'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['credit'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['balance'] ?? 0), 2) }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="empty">
                            No receivable data.
                        </td>
                    </tr>

                @endforelse

                </tbody>

                @if (count($rows) > 0)

                    <tfoot>

                        <tr class="total">

                            <td colspan="4">
                                TOTAL
                            </td>

                            <td class="number">
                                {{ number_format((float) collect($rows)->sum('debit'), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) collect($rows)->sum('credit'), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($report['summary']['outstanding'] ?? 0), 2) }}
                            </td>

                        </tr>

                    </tfoot>

                @endif

            </table>

        </div>


    {{-- ========================================================= --}}
    {{-- SALES ANALYSIS --}}
    {{-- ========================================================= --}}

    @elseif ($type === 'sales_analysis')

        <div class="section">

            <div class="section-title">
                SALES ANALYSIS
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Reseller</th>
                        <th class="number">Sold</th>
                        <th class="number">Sales</th>
                        <th class="number">HPP Nuvora</th>
                        <th class="number">Gross Profit</th>
                        <th class="number">Margin %</th>
                    </tr>
                </thead>

                <tbody>

                @forelse ($rows as $row)

                    <tr>

                        <td>{{ $row['reseller'] ?? '' }}</td>

                        <td class="number">
                            {{ $row['qty'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['sales'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['hpp'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['profit'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['margin'] ?? 0), 2) }}%
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="empty">
                            No sales analysis data.
                        </td>
                    </tr>

                @endforelse

                </tbody>

                @if (count($rows) > 0)

                    <tfoot>

                        <tr class="total">

                            <td>
                                TOTAL
                            </td>

                            <td class="number">
                                {{ $report['summary']['qty'] ?? 0 }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($report['summary']['sales'] ?? 0), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($report['summary']['hpp'] ?? 0), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($report['summary']['profit'] ?? 0), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($report['summary']['margin'] ?? 0), 2) }}%
                            </td>

                        </tr>

                    </tfoot>

                @endif

            </table>

        </div>


        <div class="section">

            <div class="section-title">
                PRODUCT DETAIL
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Reseller</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Unit</th>
                        <th class="number">Sold</th>
                        <th class="number">Price</th>
                        <th class="number">Sales</th>
                        <th class="number">HPP</th>
                        <th class="number">Unit HPP</th>
                        <th class="number">Profit</th>
                        <th class="number">Margin %</th>
                    </tr>
                </thead>

                <tbody>

                @foreach ($rows as $row)

                    @foreach ($row['details'] ?? [] as $detail)

                        <tr>

                            <td>{{ $row['reseller'] ?? '' }}</td>

                            <td>{{ $detail['product'] ?? '' }}</td>

                            <td>{{ $detail['variant'] ?? '' }}</td>

                            <td>{{ $detail['unit'] ?? '' }}</td>

                            <td class="number">
                                {{ $detail['qty'] ?? 0 }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($detail['unit_price'] ?? 0), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($detail['sales'] ?? 0), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($detail['hpp'] ?? 0), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($detail['unit_hpp'] ?? 0), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($detail['profit'] ?? 0), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($detail['margin'] ?? 0), 2) }}%
                            </td>

                        </tr>

                    @endforeach

                @endforeach

                </tbody>

            </table>

        </div>


    {{-- ========================================================= --}}
    {{-- PROFIT ANALYSIS --}}
    {{-- ========================================================= --}}

    @elseif ($type === 'profit_analysis')

        <div class="section">

            <div class="section-title">
                PROFIT ANALYSIS
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Settlement</th>
                        <th>Reseller</th>
                        <th>Branch</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Unit</th>
                        <th class="number">Qty</th>
                        <th class="number">Sales</th>
                        <th class="number">HPP</th>
                        <th class="number">Unit HPP</th>
                        <th class="number">Price</th>
                        <th class="number">Profit</th>
                        <th class="number">Margin %</th>
                    </tr>
                </thead>

                <tbody>

                @forelse ($rows as $row)

                    <tr>

                        <td>{{ $row['date'] ?? '' }}</td>
                        <td>{{ $row['number'] ?? '' }}</td>
                        <td>{{ $row['reseller'] ?? '' }}</td>
                        <td>{{ $row['branch'] ?? '' }}</td>
                        <td>{{ $row['product'] ?? '' }}</td>
                        <td>{{ $row['variant'] ?? '' }}</td>
                        <td>{{ $row['unit'] ?? '' }}</td>

                        <td class="number">
                            {{ $row['qty'] ?? 0 }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['sales'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['hpp'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['unit_hpp'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['unit_price'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['profit'] ?? 0), 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) ($row['margin'] ?? 0), 2) }}%
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="14" class="empty">
                            No profit analysis data.
                        </td>
                    </tr>

                @endforelse

                </tbody>

                @if (count($rows) > 0)

                    <tfoot>

                        <tr class="total">

                            <td colspan="7">
                                TOTAL
                            </td>

                            <td class="number">
                                {{ $report['summary']['qty'] ?? 0 }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($report['summary']['sales'] ?? 0), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($report['summary']['hpp'] ?? 0), 2) }}
                            </td>

                            <td></td>

                            <td></td>

                            <td class="number">
                                {{ number_format((float) ($report['summary']['profit'] ?? 0), 2) }}
                            </td>

                            <td class="number">
                                {{ number_format((float) ($report['summary']['margin'] ?? 0), 2) }}%
                            </td>

                        </tr>

                    </tfoot>

                @endif

            </table>

        </div>

    @endif


    <div style="margin-top: 28px; font-size: 11px; color: #6b7280;">
        Report Basis: Only Posted transactions are included.
    </div>

</div>

</body>
</html>