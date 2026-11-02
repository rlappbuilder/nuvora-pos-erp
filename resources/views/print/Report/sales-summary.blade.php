<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Summary Report</title>
</head>

<body>
    <button
        class="print-button"
        onclick="window.print()"
    >
        Print Report
    </button>

    @include('report.partials.sales-summary-content')

    <style>
        @media screen {
            body {
                padding: 20px;
                background: #fff;
            }

            .print-button {
                display: inline-block;
                padding: 9px 16px;
                margin-bottom: 15px;
                border: none;
                border-radius: 5px;
                background: #2563eb;
                color: #fff;
                cursor: pointer;
            }
        }

        @media print {
            .print-button {
                display: none !important;
            }
        }
    </style>
</body>
</html>