<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>Reseller Mutation</title>

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
            background: #ffffff;
        }

        .paper {
            width: 100%;
            padding: 0;
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

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .data-table th {
            padding: 7px 7px;
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            text-align: left;
            font-weight: 700;
            white-space: nowrap;
        }

        .data-table td {
            padding: 7px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .data-table .number {
            text-align: right;
            white-space: nowrap;
        }

        .total-row td {
            font-weight: 700;
            background: #f9fafb;
            border-top: 1px solid #d1d5db;
        }

        .opening-row td {
            font-weight: 600;
            background: #f9fafb;
        }

        .basis {
            margin-top: 26px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #6b7280;
        }

        @page {
            size: A4 landscape;
            margin: 12mm;
        }

        tr {
            break-inside: avoid;
        }
    </style>
</head>

<body>

<div class="paper">

    <div class="company">
        NUVORA ERP
    </div>

    <div class="title">
        RESELLER MUTATION
    </div>

    <div class="subtitle">
        Reseller Stock Mutation Report
    </div>

    <div class="filter">

        <strong>Reseller:</strong>
        {{ $report['reseller']['name'] ?? 'Selected Reseller' }}

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
        {{ $report['branch']['name'] ?? 'All Branch' }}

    </div>


    <div class="section">

        <div class="section-title">
            STOCK MUTATION
        </div>

        <table class="data-table">

            <thead>

                <tr>

                    <th>Date</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="number">In</th>
                    <th class="number">Out</th>
                    <th class="number">Price</th>
                    <th class="number">Debit</th>
                    <th class="number">Credit</th>
                    <th class="number">Balance Qty</th>
                    <th class="number">Balance Value</th>

                </tr>

            </thead>

            <tbody>

                @forelse ($report['rows'] ?? [] as $row)

                    <tr
                        @if (($row['reference'] ?? '') === 'OPENING')
                            class="opening-row"
                        @endif
                    >

                        <td>
                            {{ !empty($row['date'])
                                ? \Carbon\Carbon::parse($row['date'])->format('d M Y')
                                : '-' }}
                        </td>

                        <td>
                            {{ $row['reference'] ?? '-' }}
                        </td>

                        <td>
                            {{ $row['description'] ?? '-' }}
                        </td>

                        <td class="number">
                            {{ (float) ($row['in'] ?? 0) == 0
                                ? '-'
                                : number_format($row['in'], 2, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ (float) ($row['out'] ?? 0) == 0
                                ? '-'
                                : number_format($row['out'], 2, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ $row['price'] === null || $row['price'] === ''
                                ? '-'
                                : 'Rp ' . number_format($row['price'], 0, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ (float) ($row['debit'] ?? 0) == 0
                                ? '-'
                                : 'Rp ' . number_format($row['debit'], 0, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ (float) ($row['credit'] ?? 0) == 0
                                ? '-'
                                : 'Rp ' . number_format($row['credit'], 0, ',', '.') }}
                        </td>

                        <td class="number">
                            {{ number_format($row['balance_qty'] ?? 0, 2, ',', '.') }}
                        </td>

                        <td class="number">
                            Rp {{ number_format($row['balance_value'] ?? 0, 0, ',', '.') }}
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="10" style="text-align:center;">
                            No mutation data.
                        </td>

                    </tr>

                @endforelse

            </tbody>

            @if (count($report['rows'] ?? []) > 0)

                @php
                    $lastRow = collect($report['rows'])->last();
                @endphp

                <tfoot>

                    <tr class="total-row">

                        <td colspan="8">
                            ENDING BALANCE
                        </td>

                        <td class="number">
                            {{ number_format($lastRow['balance_qty'] ?? 0, 2, ',', '.') }}
                        </td>

                        <td class="number">
                            Rp {{ number_format($lastRow['balance_value'] ?? 0, 0, ',', '.') }}
                        </td>

                    </tr>

                </tfoot>

            @endif

        </table>

    </div>


    <div class="basis">

        <strong>Report Basis:</strong>
        Only Posted transactions are included.
        Balance Qty = cumulative In - Out.
        Balance Value = cumulative Debit - Credit.

    </div>

</div>

</body>
</html>