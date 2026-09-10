<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 25px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        .header {
            margin-bottom: 18px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 10px;
            color: #6b7280;
        }

        .supplier-info {
            margin-top: 14px;
            margin-bottom: 14px;
        }

        .supplier-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .supplier-info td {
            padding: 3px 0;
            border: none;
        }

        .label {
            width: 90px;
            color: #6b7280;
        }

        .value {
            font-weight: bold;
        }

        table.statement {
            width: 100%;
            border-collapse: collapse;
        }

        table.statement thead th {
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            border-bottom: 1px solid #9ca3af;
        }

        table.statement tbody td {
            padding: 7px 6px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        table.statement tbody tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .opening {
            margin-bottom: 10px;
        }

        .opening table {
            width: 100%;
            border-collapse: collapse;
        }

        .opening td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
        }

        .opening-label {
            font-weight: bold;
        }

        .closing {
            margin-top: 12px;
        }

        .closing table {
            width: 100%;
            border-collapse: collapse;
        }

        .closing td {
            padding: 8px 6px;
            border-top: 1px solid #9ca3af;
            font-weight: bold;
        }

        .total-row td {
            border-top: 1px solid #9ca3af;
            font-weight: bold;
            padding-top: 9px;
        }

        .negative {
            color: #b91c1c;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">
            Supplier Statement
        </div>

        <div class="subtitle">
            Supplier Payable Ledger
        </div>
    </div>

    <div class="supplier-info">
        <table>
            <tr>
                <td class="label">Supplier</td>
                <td class="value">
                    {{ $report['supplier']['code'] ?? '' }}
                    @if (!empty($report['supplier']['code']))
                        -
                    @endif
                    {{ $report['supplier']['name'] ?? '' }}
                </td>
            </tr>

            <tr>
                <td class="label">Period</td>
                <td class="value">
                    {{ \Carbon\Carbon::parse($filters['date_from'])->format('d M Y') }}
                    –
                    {{ \Carbon\Carbon::parse($filters['date_to'])->format('d M Y') }}
                </td>
            </tr>

            @if (!empty($filters['branch_id']))
                <tr>
                    <td class="label">Branch</td>
                    <td class="value">
                        {{ collect($report['rows'] ?? [])->first()['branch'] ?? '' }}
                    </td>
                </tr>
            @endif
        </table>
    </div>

    <div class="opening">
        <table>
            <tr>
                <td class="opening-label">
                    Opening Balance
                </td>

                <td class="text-right">
                    Rp {{ number_format($report['opening_balance'] ?? 0, 2, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    <table class="statement">

        <thead>
            <tr>
                <th style="width: 11%;">
                    Date
                </th>

                <th style="width: 22%;">
                    Document
                </th>

                <th style="width: 20%;">
                    Type
                </th>

                <th class="text-right" style="width: 15%;">
                    Debit
                </th>

                <th class="text-right" style="width: 15%;">
                    Credit
                </th>

                <th class="text-right" style="width: 17%;">
                    Balance
                </th>
            </tr>
        </thead>

        <tbody>

            @forelse ($report['rows'] ?? [] as $row)

                <tr>
                    <td>
                        {{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}
                    </td>

                    <td>
                        {{ $row['document'] ?? '-' }}
                    </td>

                    <td>
                        {{ $row['type'] ?? '-' }}
                    </td>

                    <td class="text-right">
                        @if (($row['debit'] ?? 0) != 0)
                            Rp {{ number_format($row['debit'], 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="text-right">
                        @if (($row['credit'] ?? 0) != 0)
                            Rp {{ number_format($row['credit'], 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($row['balance'] ?? 0, 2, ',', '.') }}
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="6" class="text-center">
                        No transactions found.
                    </td>
                </tr>

            @endforelse

            <tr class="total-row">
                <td colspan="3">
                    Period Total
                </td>

                <td class="text-right">
                    Rp {{ number_format($report['totals']['debit'] ?? 0, 2, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($report['totals']['credit'] ?? 0, 2, ',', '.') }}
                </td>

                <td class="text-right">
                    -
                </td>
            </tr>

        </tbody>

    </table>

    <div class="closing">
    <table>
        <tr>
            <td>
                Closing Balance
            </td>

            <td class="text-right">
                Rp {{ number_format($report['totals']['closing_balance'] ?? 0, 2, ',', '.') }}
            </td>
        </tr>
    </table>
</div>

</body>

</html>