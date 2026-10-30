<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>
        Stock Transfer {{ strtoupper($direction) }} Report
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
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
        }

        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .header td {
            border: none;
            vertical-align: top;
        }

        .brand {
            font-size: 25px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: 1px;
        }

        .brand-subtitle {
            font-size: 10px;
            letter-spacing: 4px;
            color: #64748b;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-title {
            text-align: right;
            font-size: 19px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }

        .report-title span {
            color: #2563eb;
        }

        .report-meta {
            text-align: right;
            line-height: 1.7;
            margin-top: 5px;
        }

        .summary-wrapper {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin: 0 -6px 12px;
        }

        .summary-wrapper td {
            width: 33.33%;
            padding: 12px;
            border: 1px solid #dbe3ef;
            border-radius: 5px;
            background: #f8fafc;
        }

        .summary-label {
            color: #64748b;
            font-size: 10px;
            margin-bottom: 7px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }

        .filters {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .filters td {
            border: none;
            padding: 3px 5px;
        }

        .filter-label {
            font-weight: bold;
            width: 95px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table thead {
            display: table-header-group;
        }

        .report-table tr {
            page-break-inside: avoid;
        }

        .report-table th {
            background: #1769aa;
            color: #fff;
            font-size: 9px;
            padding: 8px 5px;
            text-align: left;
            border: 1px solid #1769aa;
        }

        .report-table td {
            padding: 7px 5px;
            border: 1px solid #dbe3ef;
            vertical-align: middle;
        }

        .report-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .center {
            text-align: center !important;
        }

        .right {
            text-align: right !important;
        }

        .status {
            font-weight: bold;
            text-align: center;
        }

        .footer {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px solid #dbe3ef;
            color: #64748b;
            font-size: 9px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            border: none;
            padding: 3px;
        }

        .signature-wrapper {
            margin-top: 28px;
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signature-wrapper td {
            width: 33.33%;
            text-align: center;
            border: none;
            padding: 5px;
        }

        .signature-space {
            height: 48px;
        }

        .signature-line {
            border-top: 1px solid #64748b;
            display: inline-block;
            min-width: 150px;
            padding-top: 5px;
        }

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
</head>

<body>

    {{-- REPORT DATA --}}

    @php
        $firstTransfer = $transfers->first();

        $branchName = $firstTransfer
            ? (
                $direction === 'in'
                    ? ($firstTransfer->toBranch?->name ?? '-')
                    : ($firstTransfer->fromBranch?->name ?? '-')
            )
            : '-';

        $summary = [
            'total' => $transfers->count(),
            'totalQty' => (float) $transfers->sum('total_qty'),
            'totalAmount' => (float) $transfers->sum('total_cost'),
        ];

        $filters = array_merge([
            'history_search' => '',
            'history_status' => 'all',
            'history_date_from' => '',
            'history_date_to' => '',
        ], $filters ?? []);
    @endphp


    {{-- HEADER --}}

    <table class="header">
        <tr>
            <td style="width: 16%;">
                <div class="brand">NUVORA</div>
                <div class="brand-subtitle">ERP</div>
            </td>

            <td style="width: 39%;">
                <div class="company-name">
                    {{ config('app.name', 'Nuvora ERP') }}
                </div>

                <div>
                    Stock Transfer Management
                </div>
            </td>

            <td style="width: 45%;">
                <div class="report-title">
                    Stock Transfer
                    <span>{{ strtoupper($direction) }}</span>
                    Report
                </div>

                <div class="report-meta">
                    Branch: {{ $branchName }}<br>
                    Print Date: {{ now()->format('d M Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- FILTER INFORMATION --}}

    <table class="filters">
        <tr>
            <td class="filter-label">Date From</td>
            <td>
                : {{ $filters['history_date_from'] ?: 'All' }}
            </td>

            <td class="filter-label">Date To</td>
            <td>
                : {{ $filters['history_date_to'] ?: 'All' }}
            </td>
        </tr>

        <tr>
            <td class="filter-label">Status</td>
            <td>
                : {{ ucfirst($filters['history_status']) }}
            </td>

            <td class="filter-label">Search</td>
            <td>
                : {{ $filters['history_search'] ?: 'All' }}
            </td>
        </tr>
    </table>

    {{-- SUMMARY --}}

    <table class="summary-wrapper">
        <tr>
            <td>
                <div class="summary-label">
                    Total Transfer
                </div>

                <div class="summary-value">
                    {{ number_format($summary['total']) }}
                </div>
            </td>

            <td>
                <div class="summary-label">
                    Total Quantity
                </div>

                <div class="summary-value">
                    {{ number_format($summary['totalQty'], 2) }}
                </div>
            </td>

            <td>
                <div class="summary-label">
                    Total Amount
                </div>

                <div class="summary-value">
                    Rp {{ number_format(
                        $summary['totalAmount'],
                        2,
                        ',',
                        '.'
                    ) }}
                </div>
            </td>
        </tr>
    </table>

    {{-- TRANSFER TABLE --}}

    <table class="report-table">
        <thead>
            <tr>
                <th class="center" style="width: 3%;">No.</th>
                <th style="width: 13%;">Transfer Number</th>
                <th style="width: 9%;">Date</th>
                <th style="width: 13%;">Source Branch</th>
                <th style="width: 13%;">Destination Branch</th>
                <th class="center" style="width: 5%;">Items</th>
                <th class="right" style="width: 8%;">Total Qty</th>
                <th class="right" style="width: 14%;">Total Amount</th>
                <th class="center" style="width: 10%;">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($transfers as $transfer)
                <tr>
                    <td class="center">
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        {{ $transfer->number }}
                    </td>

                    <td>
                        {{ $transfer->transaction_date
                            ? \Illuminate\Support\Carbon::parse(
                                $transfer->transaction_date
                            )->format('d M Y')
                            : '-' }}
                    </td>

                    <td>
                        {{ $transfer->fromBranch?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $transfer->toBranch?->name ?? '-' }}
                    </td>

                    <td class="center">
                        {{ $transfer->details_count ?? 0 }}
                    </td>

                    <td class="right">
                        {{ number_format(
                            (float) ($transfer->total_qty ?? 0),
                            2
                        ) }}
                    </td>

                    <td class="right">
                        Rp {{ number_format(
                            (float) ($transfer->total_cost ?? 0),
                            2,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="status">
                        {{ $transfer->status }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="center">
                        No stock transfer data found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SIGNATURES --}}

    <table class="signature-wrapper">
        <tr>
            <td>
                Prepared By
                <div class="signature-space"></div>
                <div class="signature-line">Name / Signature</div>
            </td>

            <td>
                Checked By
                <div class="signature-space"></div>
                <div class="signature-line">Name / Signature</div>
            </td>

            <td>
                Approved By
                <div class="signature-space"></div>
                <div class="signature-line">Name / Signature</div>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    Printed at:
                    {{ now()->format('d/m/Y H:i:s') }}
                </td>

                <td class="right">
                    Nuvora ERP — Stock Transfer Report
                </td>
            </tr>
        </table>
    </div>

</body>
</html>