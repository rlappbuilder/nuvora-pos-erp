<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Close Session Report -
        {{ $session->session_number }}
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            background: #f1f5f9;
            color: #0f172a;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 15mm;
            background: #ffffff;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 30px;
            padding-bottom: 18px;
            border-bottom: 2px solid #0f172a;
        }

        .brand {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .report-title {
            margin-top: 3px;
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .report-number {
            text-align: right;
        }

        .report-number-label {
            color: #94a3b8;
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .report-number-value {
            margin-top: 3px;
            font-size: 13px;
            font-weight: 700;
        }

        .section {
            margin-top: 24px;
        }

        .section-title {
            margin-bottom: 10px;
            color: #334155;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .info-item {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-item:nth-child(odd) {
            border-right: 1px solid #e2e8f0;
        }

        .info-item:nth-last-child(-n + 2) {
            border-bottom: 0;
        }

        .label {
            color: #94a3b8;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .value {
            margin-top: 3px;
            color: #1e293b;
            font-size: 12px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 9px 10px;
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-align: left;
            text-transform: uppercase;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .font-semibold {
            font-weight: 600;
        }

        .font-bold {
            font-weight: 700;
        }

        .total-row td {
            border-top: 2px solid #0f172a;
            border-bottom: 0;
            color: #0f172a;
            font-weight: 700;
        }

        .reconciliation {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .reconciliation-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .reconciliation-header {
            padding: 10px 12px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .reconciliation-body {
            padding: 10px 12px;
        }

        .money-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 7px 0;
        }

        .money-row + .money-row {
            border-top: 1px solid #f1f5f9;
        }

        .money-label {
            color: #64748b;
        }

        .money-value {
            color: #1e293b;
            font-weight: 600;
            text-align: right;
        }

        .expected-row {
            margin-top: 5px;
            padding-top: 10px;
            border-top: 2px solid #cbd5e1;
        }

        .expected-row .money-label,
        .expected-row .money-value {
            color: #0f172a;
            font-weight: 700;
        }

        .actual-row .money-label,
        .actual-row .money-value {
            color: #0f172a;
            font-size: 13px;
            font-weight: 700;
        }

        .difference {
            margin-top: 10px;
            padding: 12px;
            border-radius: 7px;
            background: #f8fafc;
        }

        .difference-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
        }

        .status-balanced {
            color: #047857;
        }

        .status-short {
            color: #dc2626;
        }

        .status-short .status-dot {
            background: #ef4444;
        }

        .status-over {
            color: #b45309;
        }

        .status-over .status-dot {
            background: #f59e0b;
        }

        .note-box {
            min-height: 70px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #475569;
            white-space: pre-line;
        }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            margin-top: 55px;
        }

        .signature {
            text-align: center;
        }

        .signature-title {
            color: #64748b;
            font-size: 10px;
            font-weight: 600;
        }

        .signature-line {
            height: 55px;
            margin-top: 25px;
            border-bottom: 1px solid #334155;
        }

        .signature-name {
            margin-top: 6px;
            color: #334155;
            font-size: 10px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 9px;
            text-align: center;
        }

        .no-print {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 10;
        }

        .print-button {
            padding: 9px 15px;
            border: 0;
            border-radius: 7px;
            background: #2563eb;
            color: #ffffff;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .page {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                padding: 15mm;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <!-- Manual Print Button -->
    <div class="no-print">
        <button
            type="button"
            class="print-button"
            onclick="window.print()"
        >
            Print Report
        </button>
    </div>

    <main class="page">

        <!-- ============================================================= -->
        <!-- HEADER -->
        <!-- ============================================================= -->

        <header class="header">

            <div>
                <div class="brand">
                    NUVORA POS
                </div>

                <div class="report-title">
                    Close Session Report
                </div>
            </div>

            <div class="report-number">

                <div class="report-number-label">
                    Session Number
                </div>

                <div class="report-number-value">
                    {{ $session->session_number }}
                </div>

            </div>

        </header>


        <!-- ============================================================= -->
        <!-- SESSION INFORMATION -->
        <!-- ============================================================= -->

        <section class="section">

            <div class="section-title">
                Session Information
            </div>

            <div class="info-grid">

                <div class="info-item">

                    <div class="label">
                        Session Number
                    </div>

                    <div class="value">
                        {{ $session->session_number }}
                    </div>

                </div>

                <div class="info-item">

                    <div class="label">
                        Cashier
                    </div>

                    <div class="value">
                        {{ $session->user?->name ?? '-' }}
                    </div>

                </div>

                <div class="info-item">

                    <div class="label">
                        Branch
                    </div>

                    <div class="value">
                        {{ $session->branch?->name ?? '-' }}
                    </div>

                </div>

                <div class="info-item">

                    <div class="label">
                        Warehouse
                    </div>

                    <div class="value">
                        {{ $session->warehouse?->name ?? '-' }}
                    </div>

                </div>

                <div class="info-item">

                    <div class="label">
                        Opened At
                    </div>

                    <div class="value">
                        {{
                            $session->opened_at
                                ? $session->opened_at->format('d/m/Y H:i:s')
                                : '-'
                        }}
                    </div>

                </div>

                <div class="info-item">

                    <div class="label">
                        Closed At
                    </div>

                    <div class="value">
                        {{
                            $session->closed_at
                                ? $session->closed_at->format('d/m/Y H:i:s')
                                : '-'
                        }}
                    </div>

                </div>

            </div>

        </section>


        <!-- ============================================================= -->
        <!-- TRANSACTION SUMMARY -->
        <!-- ============================================================= -->

        <section class="section">

            <div class="section-title">
                Transaction Summary
            </div>

            <table>

                <thead>
                    <tr>
                        <th>
                            Payment Method
                        </th>

                        <th class="text-right">
                            Transactions
                        </th>

                        <th class="text-right">
                            Amount
                        </th>
                    </tr>
                </thead>

                <tbody>

                    @php
                        $paymentRows = [
                            'cash' => 'Cash',
                            'qris' => 'QRIS',
                            'debit_card' => 'Debit Card',
                            'e_wallet' => 'E-Wallet',
                            'transfer' => 'Transfer',
                        ];
                    @endphp

                    @foreach ($paymentRows as $key => $label)

                        <tr>

                            <td class="font-semibold">
                                {{ $label }}
                            </td>

                            <td class="text-right">
                                {{
                                    $summary['transactions'][$key]['transaction_count']
                                    ?? 0
                                }}
                            </td>

                            <td class="text-right font-semibold">
                                Rp
                                {{
                                    number_format(
                                        $summary['transactions'][$key]['amount'] ?? 0,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </td>

                        </tr>

                    @endforeach

                    <tr class="total-row">

                        <td>
                            Total Sales
                        </td>

                        <td class="text-right">
                            {{
                                $summary['transactions']['total']['transaction_count']
                                ?? 0
                            }}
                        </td>

                        <td class="text-right">
                            Rp
                            {{
                                number_format(
                                    $summary['transactions']['total']['amount'] ?? 0,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}
                        </td>

                    </tr>

                </tbody>

            </table>

        </section>


        <!-- ============================================================= -->
        <!-- CASH RECONCILIATION -->
        <!-- ============================================================= -->

        <section class="section">

            <div class="section-title">
                Cash Reconciliation
            </div>

            <div class="reconciliation">

                <!-- Cash Movement -->

                <div class="reconciliation-box">

                    <div class="reconciliation-header">
                        Cash Movement
                    </div>

                    <div class="reconciliation-body">

                        <div class="money-row">

                            <span class="money-label">
                                Opening Cash
                            </span>

                            <span class="money-value">
                                Rp
                                {{
                                    number_format(
                                        $summary['cash_movement']['opening_cash'] ?? 0,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </span>

                        </div>

                        <div class="money-row">

                            <span class="money-label">
                                + Cash Sales
                            </span>

                            <span class="money-value">
                                Rp
                                {{
                                    number_format(
                                        $summary['cash_movement']['cash_sales'] ?? 0,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </span>

                        </div>

                        <div class="money-row">

                            <span class="money-label">
                                - Cash Deposit
                            </span>

                            <span class="money-value">
                                Rp
                                {{
                                    number_format(
                                        $summary['cash_movement']['cash_deposit'] ?? 0,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </span>

                        </div>

                        <div class="money-row expected-row">

                            <span class="money-label">
                                Expected Cash
                            </span>

                            <span class="money-value">
                                Rp
                                {{
                                    number_format(
                                        $expectedCash,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </span>

                        </div>

                    </div>

                </div>


                <!-- Actual Cash -->

                <div class="reconciliation-box">

                    <div class="reconciliation-header">
                        Cash Reconciliation
                    </div>

                    <div class="reconciliation-body">

                        <div class="money-row">

                            <span class="money-label">
                                Expected Cash
                            </span>

                            <span class="money-value">
                                Rp
                                {{
                                    number_format(
                                        $expectedCash,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </span>

                        </div>

                        <div class="money-row actual-row">

                            <span class="money-label">
                                Actual Cash
                            </span>

                            <span class="money-value">
                                Rp
                                {{
                                    number_format(
                                        $actualCash,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </span>

                        </div>

                        <div class="difference">

                            <div class="difference-row">

                                <span class="money-label">
                                    Difference
                                </span>

                                <span class="money-value">
                                    {{
                                        $difference > 0
                                            ? '+Rp '
                                            : (
                                                $difference < 0
                                                    ? '-Rp '
                                                    : 'Rp '
                                            )
                                    }}

                                    {{
                                        number_format(
                                            abs($difference),
                                            0,
                                            ',',
                                            '.'
                                        )
                                    }}
                                </span>

                            </div>

                            <div
                                class="status
                                    {{
                                        $reconciliationStatus === 'Balanced'
                                            ? 'status-balanced'
                                            : (
                                                $reconciliationStatus === 'Cash Short'
                                                    ? 'status-short'
                                                    : 'status-over'
                                            )
                                    }}"
                                style="margin-top: 10px;"
                            >

                                <span class="status-dot"></span>

                                {{ $reconciliationStatus }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- ============================================================= -->
        <!-- CLOSING NOTE -->
        <!-- ============================================================= -->

        <section class="section">

            <div class="section-title">
                Closing Note
            </div>

            <div class="note-box">
                {{ $session->closing_note ?: 'No closing note.' }}
            </div>

        </section>


        <!-- ============================================================= -->
        <!-- SIGNATURE -->
        <!-- ============================================================= -->

        <section class="signature-section">

            <div class="signature">

                <div class="signature-title">
                    Cashier
                </div>

                <div class="signature-line"></div>

                <div class="signature-name">
                    {{ $session->user?->name ?? '-' }}
                </div>

            </div>


            <div class="signature">

                <div class="signature-title">
                    Supervisor
                </div>

                <div class="signature-line"></div>

                <div class="signature-name">
                    &nbsp;
                </div>

            </div>

        </section>


        <!-- ============================================================= -->
        <!-- FOOTER -->
        <!-- ============================================================= -->

        <footer class="footer">
            Generated by Nuvora POS
        </footer>

    </main>


    <script>
        window.addEventListener('load', function () {
            window.setTimeout(function () {
                window.print()
            }, 300)
        })
    </script>

</body>
</html>