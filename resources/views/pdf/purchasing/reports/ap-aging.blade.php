<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>AP Aging</title>

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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 8px 6px;
            text-align: right;
            font-size: 9px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        th:first-child {
            text-align: left;
        }

        td {
            padding: 8px 6px;
            border-bottom: 1px solid #f3f4f6;
            text-align: right;
        }

        td:first-child {
            text-align: left;
            font-weight: 500;
        }

        .total td {
            border-top: 1px solid #d1d5db;
            border-bottom: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">
            AP Aging
        </div>

        <div class="subtitle">
            Accounts Payable Aging Report
            &nbsp; | &nbsp;
            As of {{ $report['as_of_date'] }}
        </div>
    </div>

    <table>

        <thead>
            <tr>
                <th>Supplier</th>
                <th>Current</th>
                <th>1–30 Days</th>
                <th>31–60 Days</th>
                <th>61–90 Days</th>
                <th>&gt;90 Days</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($report['rows'] as $row)

                <tr>
                    <td>
                        {{ $row['supplier_name'] }}
                    </td>

                    <td>
                        {{ number_format($row['current'], 2, ',', '.') }}
                    </td>

                    <td>
                        {{ number_format($row['days_1_30'], 2, ',', '.') }}
                    </td>

                    <td>
                        {{ number_format($row['days_31_60'], 2, ',', '.') }}
                    </td>

                    <td>
                        {{ number_format($row['days_61_90'], 2, ',', '.') }}
                    </td>

                    <td>
                        {{ number_format($row['over_90'], 2, ',', '.') }}
                    </td>

                    <td>
                        {{ number_format($row['total'], 2, ',', '.') }}
                    </td>
                </tr>

            @endforeach

            <tr class="total">

                <td>
                    TOTAL
                </td>

                <td>
                    {{ number_format($report['totals']['current'], 2, ',', '.') }}
                </td>

                <td>
                    {{ number_format($report['totals']['days_1_30'], 2, ',', '.') }}
                </td>

                <td>
                    {{ number_format($report['totals']['days_31_60'], 2, ',', '.') }}
                </td>

                <td>
                    {{ number_format($report['totals']['days_61_90'], 2, ',', '.') }}
                </td>

                <td>
                    {{ number_format($report['totals']['over_90'], 2, ',', '.') }}
                </td>

                <td>
                    {{ number_format($report['totals']['total'], 2, ',', '.') }}
                </td>

            </tr>

        </tbody>

    </table>

</body>
</html>