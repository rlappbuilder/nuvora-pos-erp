<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>Reseller Statement</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #f3f4f6;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .toolbar-title {
            font-size: 14px;
            font-weight: 700;
        }

        .toolbar-actions {
            display: flex;
            gap: 8px;
        }

        .toolbar button {
            border: 0;
            padding: 8px 14px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            background: #2563eb;
            color: #ffffff;
        }

        .toolbar .back {
            background: #6b7280;
        }

        .paper {
            width: min(1200px, calc(100% - 48px));
            min-height: 1100px;
            margin: 32px auto;
            padding: 40px 48px;
            background: #ffffff;
        }

        .company {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .3px;
            margin-bottom: 4px;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 13px;
            color: #6b7280;
        }

        .filter {
            margin-top: 8px;
            font-size: 12px;
            color: #6b7280;
        }

        .section {
            margin-top: 26px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .summary-table th {
            padding: 9px 10px;
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            text-align: left;
            font-size: 11px;
        }

        .summary-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
            font-weight: 600;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .data-table th {
            padding: 8px 8px;
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            text-align: left;
            font-weight: 700;
            white-space: nowrap;
        }

        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .data-table .number {
            text-align: right;
            white-space: nowrap;
        }

        .data-table .center {
            text-align: center;
        }

        .total-row td {
            font-weight: 700;
            background: #f9fafb;
            border-top: 1px solid #d1d5db;
        }

        .product-name {
            font-weight: 600;
        }

        .variant {
            margin-top: 2px;
            color: #6b7280;
            font-size: 10px;
        }

        .basis {
            margin-top: 26px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
        }

        @page {
            size: A4 landscape;
            margin: 12mm;
        }

        @media print {
            html,
            body {
                background: #ffffff;
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

            .section {
                break-inside: avoid;
            }

            tr {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>



<div class="paper">

    <div class="company">
        NUVORA ERP
    </div>

    <div class="title">
        RESELLER STATEMENT
    </div>

    <div class="subtitle">
        Reseller Stock & Sales Summary
    </div>

    <div class="filter">
        <strong>Reseller:</strong>
        {{ $statement['reseller']['name'] ?? 'All Reseller' }}

        &nbsp;&nbsp;|&nbsp;&nbsp;

        <strong>Period:</strong>
        {{ !empty($filters['date_from'])
            ? \Carbon\Carbon::parse($filters['date_from'])->format('d M Y')
            : '-' }}
        -
        {{ !empty($filters['date_to'])
            ? \Carbon\Carbon::parse($filters['date_to'])->format('d M Y')
            : '-' }}

        &nbsp;&nbsp;|&nbsp;&nbsp;

        <strong>Branch:</strong>
        {{ $statement['branch']['name'] ?? 'All Branch' }}
    </div>

    {{-- SUMMARY --}}
    <div class="section">

        <div class="section-title">
            SUMMARY
        </div>

        <table class="summary-table">
            <thead>
                <tr>
                    <th>Current Stock</th>
                    <th>Stock Value</th>
                    <th>Sold</th>
                    <th>Sales</th>
                    <th>Payment</th>
                    <th>Outstanding</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        {{ number_format($statement['summary']['current_stock'] ?? 0, 2, ',', '.') }}
                        pcs
                    </td>

                    <td>
                        Rp {{ number_format($statement['summary']['stock_value'] ?? 0, 0, ',', '.') }}
                    </td>

                    <td>
                        {{ number_format($statement['summary']['sold'] ?? 0, 2, ',', '.') }}
                        pcs
                    </td>

                    <td>
                        Rp {{ number_format($statement['summary']['sales'] ?? 0, 0, ',', '.') }}
                    </td>

                    <td>
                        Rp {{ number_format($statement['summary']['payment'] ?? 0, 0, ',', '.') }}
                    </td>

                    <td>
                        Rp {{ number_format($statement['summary']['outstanding'] ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

    {{-- STOCK POSITION --}}
    <div class="section">

        <div class="section-title">
            STOCK POSITION
        </div>

        <table class="data-table">

            <thead>
                <tr>
                    <th width="28%">Product</th>
                    <th class="number">Opening</th>
                    <th class="number">Consignment</th>
                    <th class="number">Sold</th>
                    <th class="number">Return</th>
                    <th class="number">Closing</th>
                    <th class="number">Price</th>
                    <th class="number">Stock Value</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($statement['stock'] ?? [] as $row)

                    <tr>

                        <td>
                            <div class="product-name">
                                {{ $row['product'] }}
                            </div>

                            @if (!empty($row['variant']))
                                <div class="variant">
                                    {{ $row['variant'] }}
                                </div>
                            @endif
                        </td>

                        <td class="number">
                            {{ number_format($row['opening'], 2, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ number_format($row['consignment'], 2, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ number_format($row['sold'], 2, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ number_format($row['return'], 2, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ number_format($row['closing'], 2, ',', '.') }}
                        </td>

                        <td class="number">
                            Rp {{ number_format($row['price'], 0, ',', '.') }}
                        </td>

                        <td class="number">
                            Rp {{ number_format($row['stock_value'], 0, ',', '.') }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" style="text-align:center;">
                            No stock data.
                        </td>
                    </tr>

                @endforelse

            </tbody>

            @if (count($statement['stock'] ?? []) > 0)

                <tfoot>

                    <tr class="total-row">

                        <td>
                            TOTAL
                        </td>

                        <td class="number">
                            {{ number_format(collect($statement['stock'])->sum('opening'), 2, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ number_format(collect($statement['stock'])->sum('consignment'), 2, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ number_format(collect($statement['stock'])->sum('sold'), 2, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ number_format(collect($statement['stock'])->sum('return'), 2, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ number_format(collect($statement['stock'])->sum('closing'), 2, ',', '.') }}
                        </td>

                        <td></td>

                        <td class="number">
                            Rp {{ number_format(collect($statement['stock'])->sum('stock_value'), 0, ',', '.') }}
                        </td>

                    </tr>

                </tfoot>

            @endif

        </table>

    </div>

    {{-- SALES & PAYMENT --}}
    <div class="section">

        <div class="section-title">
            SALES & PAYMENT
        </div>

        <table class="data-table">

            <thead>

                <tr>
                    <th width="30%">Product</th>
                    <th class="number">Sold</th>
                    <th class="number">Price</th>
                    <th class="number">Sales Total</th>
                    <th class="number">Payment</th>
                    <th class="number">Outstanding</th>
                </tr>

            </thead>

            <tbody>

                @forelse ($statement['sales'] ?? [] as $row)

                    <tr>

                        <td>
                            <div class="product-name">
                                {{ $row['product'] }}
                            </div>

                            @if (!empty($row['variant']))
                                <div class="variant">
                                    {{ $row['variant'] }}
                                </div>
                            @endif
                        </td>

                        <td class="number">
                            {{ number_format($row['sold'], 2, ',', '.') }}
                        </td>

                        <td class="number">
                            Rp {{ number_format($row['price'], 0, ',', '.') }}
                        </td>

                        <td class="number">
                            Rp {{ number_format($row['sales_total'], 0, ',', '.') }}
                        </td>

                        <td class="number">
                            Rp {{ number_format($row['payment'], 0, ',', '.') }}
                        </td>

                        <td class="number">
                            Rp {{ number_format($row['outstanding'], 0, ',', '.') }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" style="text-align:center;">
                            No sales data.
                        </td>
                    </tr>

                @endforelse

            </tbody>

            @if (count($statement['sales'] ?? []) > 0)

                <tfoot>

                    <tr class="total-row">

                        <td>
                            TOTAL
                        </td>

                        <td class="number">
                            {{ number_format(collect($statement['sales'])->sum('sold'), 2, ',', '.') }}
                        </td>

                        <td></td>

                        <td class="number">
                            Rp {{ number_format(collect($statement['sales'])->sum('sales_total'), 0, ',', '.') }}
                        </td>

                        <td class="number">
                            Rp {{ number_format(collect($statement['sales'])->sum('payment'), 0, ',', '.') }}
                        </td>

                        <td class="number">
                            Rp {{ number_format(collect($statement['sales'])->sum('outstanding'), 0, ',', '.') }}
                        </td>

                    </tr>

                </tfoot>

            @endif

        </table>

    </div>

    <div class="basis">
        <strong>Statement Basis:</strong>
        Only Posted transactions are included.
    </div>

</div>

</body>
</html>