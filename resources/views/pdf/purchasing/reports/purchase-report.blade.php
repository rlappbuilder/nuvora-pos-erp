<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>
        Purchase Report
    </title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        .header {
            margin-bottom: 20px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 10px;
        }

        .summary {
            margin-bottom: 20px;
        }

        .summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            width: 20%;
            padding: 8px 6px;
            border-bottom: 1px solid #f3f4f6;
        }

        .summary-label {
            color: #6b7280;
            font-size: 9px;
        }

        .summary-value {
            margin-top: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
        }

        table.report th {
            padding: 8px 6px;
            text-align: right;
            font-size: 9px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        table.report th:first-child,
        table.report th:nth-child(2),
        table.report th:nth-child(3),
        table.report th:nth-child(4),
        table.report th:nth-child(5),
        table.report th:last-child {
            text-align: left;
        }

        table.report th.amount {
            text-align: right;
        }

        table.report td {
            padding: 8px 6px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        table.report td.amount {
            text-align: right;
        }

        table.report td.document {
            font-weight: 500;
        }

        .total-row td {
            border-top: 1px solid #d1d5db;
            border-bottom: none;
            font-weight: bold;
        }

        .negative {
            text-align: right;
        }
    </style>
</head>

<body>

    <div class="header">

        <div class="title">
            Purchase Report
        </div>

        <div class="subtitle">
            Purchasing Transaction Report
            &nbsp; | &nbsp;
            {{ $report['date_from'] }}
            &nbsp;–&nbsp;
            {{ $report['date_to'] }}
        </div>

    </div>


    <div class="summary">

        <table>

            <tr>

                <td>
                    <div class="summary-label">
                        Purchase Orders
                    </div>

                    <div class="summary-value">
                        Rp {{ number_format(
                            $report['totals']['purchase_orders'],
                            2,
                            ',',
                            '.'
                        ) }}
                    </div>
                </td>

                <td>
                    <div class="summary-label">
                        Goods Receipts
                    </div>

                    <div class="summary-value">
                        Rp {{ number_format(
                            $report['totals']['goods_receipts'],
                            2,
                            ',',
                            '.'
                        ) }}
                    </div>
                </td>

                <td>
                    <div class="summary-label">
                        Purchase Invoices
                    </div>

                    <div class="summary-value">
                        Rp {{ number_format(
                            $report['totals']['purchase_invoices'],
                            2,
                            ',',
                            '.'
                        ) }}
                    </div>
                </td>

                <td>
                    <div class="summary-label">
                        Purchase Returns
                    </div>

                    <div class="summary-value">
                        Rp {{ number_format(
                            $report['totals']['purchase_returns'],
                            2,
                            ',',
                            '.'
                        ) }}
                    </div>
                </td>

                <td>
                    <div class="summary-label">
                        Net Purchase
                    </div>

                    <div class="summary-value">
                        Rp {{ number_format(
                            $report['totals']['net_purchase'],
                            2,
                            ',',
                            '.'
                        ) }}
                    </div>
                </td>

            </tr>

        </table>

    </div>


    <table class="report">

        <thead>

            <tr>

                <th>
                    Date
                </th>

                <th>
                    Document
                </th>

                <th>
                    Supplier
                </th>

                <th>
                    Branch
                </th>

                <th>
                    Type
                </th>

                <th class="amount">
                    Amount
                </th>

                <th>
                    Status
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach ($report['rows'] as $row)

                <tr>

                    <td>
                        {{ $row['date'] }}
                    </td>

                    <td class="document">
                        {{ $row['document'] }}
                    </td>

                    <td>
                        {{ $row['supplier_name'] }}
                    </td>

                    <td>
                        {{ $row['branch_name'] }}
                    </td>

                    <td>
                        {{ $row['type'] }}
                    </td>

                    <td class="amount">
                        @if ($row['amount'] < 0)
                            -Rp {{ number_format(
                                abs($row['amount']),
                                2,
                                ',',
                                '.'
                            ) }}
                        @else
                            Rp {{ number_format(
                                $row['amount'],
                                2,
                                ',',
                                '.'
                            ) }}
                        @endif
                    </td>

                    <td>
                        {{ $row['status'] }}
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</body>
</html>