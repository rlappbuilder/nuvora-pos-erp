@php
    $summary = $report['summary'] ?? [];

    $paymentRows = collect($report['payment_rows'] ?? []);

    $totals = $report['totals'] ?? [];

    $filters = $filters ?? [];

    $branches = collect($branches ?? []);

    $branchName = $branchLabel
        ?? (
            $branches->isEmpty()
                ? 'All Branches'
                : $branches->pluck('name')->implode(', ')
        );

    $formatCurrency = fn ($value) =>
        'Rp ' . number_format((float) $value, 2, ',', '.');

    $formatNumber = fn ($value) =>
        number_format((float) $value, 0, ',', '.');

    $formatPercentage = fn ($value) =>
        number_format((float) $value, 2, ',', '.') . '%';

    $sourceName = match ($filters['source'] ?? 'all') {
        'pos' => 'POS',
        'consignment' => 'Consignment',
        default => 'All Sources',
    };

    $paymentMethods = $filters['payment_methods'] ?? [];

    if (! is_array($paymentMethods)) {
        $paymentMethods = [$paymentMethods];
    }

    $paymentMethodName = empty($paymentMethods)
        ? 'All Payment Methods'
        : collect($paymentMethods)
            ->map(fn ($method) => match (strtolower((string) $method)) {
                'cash' => 'Cash',
                'qris' => 'QRIS',
                'debit_card' => 'Debit Card',
                'credit_card' => 'Credit Card',
                'transfer' => 'Bank Transfer',
                'e_wallet', 'ewallet' => 'E-Wallet',
                default => ucwords(str_replace('_', ' ', (string) $method)),
            })
            ->implode(', ');

    $generatedAt = isset($generatedAt)
        ? \Illuminate\Support\Carbon::parse($generatedAt)
        : now();
@endphp

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

    .section-title {
        font-size: 12px;
        font-weight: bold;
        color: #0f172a;
        margin: 15px 0 8px;
    }

    .filters {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }

    .filters td {
        border: none;
        padding: 4px 5px;
    }

    .filter-label {
        font-weight: bold;
        width: 110px;
    }

    .summary-wrapper {
        width: 100%;
        border-collapse: separate;
        border-spacing: 6px 6px;
        margin: 0 -6px 12px;
    }

    .summary-wrapper td {
        width: 25%;
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
        font-size: 15px;
        font-weight: bold;
        color: #0f172a;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
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
        padding: 9px 6px;
        text-align: left;
        border: 1px solid #1769aa;
    }

    .report-table td {
        padding: 8px 6px;
        border: 1px solid #dbe3ef;
        vertical-align: middle;
        font-size: 9px;
    }

    .report-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }

    .report-table tfoot td {
        background: #dbeafe;
        border-top: 2px solid #2563eb;
        font-weight: bold;
    }

    .payment-method {
        font-weight: bold;
        color: #0f172a;
    }

    .source-label {
        display: inline-block;
        padding: 3px 7px;
        border-radius: 10px;
        font-size: 8px;
        font-weight: bold;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .center {
        text-align: center !important;
    }

    .right {
        text-align: right !important;
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
</style>

{{-- HEADER --}}

<table class="header">
    <tr>
        <td style="width: 16%;">
            <div class="brand">NUVORA</div>
            <div class="brand-subtitle">ERP</div>
        </td>

        <td style="width: 39%;">
            <div class="company-name">
                {{ $company->company_name ?? config('app.name', 'Nuvora ERP') }}
            </div>

            <div>Sales Management</div>
        </td>

        <td style="width: 45%;">
            <div class="report-title">
                Sales <span>by Payment Method</span> Report
            </div>

            <div class="report-meta">
                Branch: {{ $branchName }}<br>
                Print Date: {{ $generatedAt->format('d M Y H:i') }}
            </div>
        </td>
    </tr>
</table>


{{-- FILTER INFORMATION --}}

<table class="filters">
    <tr>
        <td class="filter-label">Date From</td>
        <td>: {{ $filters['date_from'] ?? '-' }}</td>

        <td class="filter-label">Date To</td>
        <td>: {{ $filters['date_to'] ?? '-' }}</td>
    </tr>

    <tr>
        <td class="filter-label">Branch</td>
        <td>: {{ $branchName }}</td>

        <td class="filter-label">Sales Source</td>
        <td>: {{ $sourceName }}</td>
    </tr>

    <tr>
        <td class="filter-label">Payment Method</td>
        <td colspan="3">: {{ $paymentMethodName }}</td>
    </tr>
</table>


{{-- KPI SUMMARY --}}

<div class="section-title">KPI Summary</div>

<table class="summary-wrapper">
    <tr>
        <td>
            <div class="summary-label">Total Payment Methods</div>
            <div class="summary-value">
                {{ $formatNumber($summary['total_payment_methods'] ?? 0) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Payment Transactions</div>
            <div class="summary-value">
                {{ $formatNumber($summary['payment_transactions'] ?? 0) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Sales Transactions</div>
            <div class="summary-value">
                {{ $formatNumber($summary['sales_transactions'] ?? 0) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Total Payment Amount</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['total_payment_amount'] ?? 0) }}
            </div>
        </td>
    </tr>

    <tr>
        <td>
            <div class="summary-label">POS Payment Amount</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['pos_payment_amount'] ?? 0) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Consignment Payment Amount</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['consignment_payment_amount'] ?? 0) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Outstanding Receivables</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['outstanding_receivables'] ?? 0) }}
            </div>
            <div style="font-size: 9px; margin-top: 5px; color: #64748b;">
                Current balance
            </div>
        </td>

        <td>
            <div class="summary-label">Average Payment Amount</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['average_payment_amount'] ?? 0) }}
            </div>
        </td>
    </tr>
</table>


{{-- PAYMENT METHOD DETAILS --}}

<div class="section-title">Payment Method Details</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="center" style="width: 5%;">No.</th>
            <th style="width: 25%;">Payment Method</th>
            <th style="width: 15%;">Source</th>
            <th class="right" style="width: 15%;">Payment Transactions</th>
            <th class="right" style="width: 15%;">Sales Transactions</th>
            <th class="right" style="width: 17%;">Payment Amount</th>
            <th class="right" style="width: 8%;">Contribution</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($paymentRows as $index => $item)

            @php
                $source = $item['source_type'] ?? '';

                $sourceLabel = match ($source) {
                    'POS' => 'POS',
                    'Consignment' => 'Consignment',
                    default => $source ?: '-',
                };

                $methodLabel = $item['payment_method_label']
                    ?? $item['payment_method']
                    ?? '-';
            @endphp

            <tr>
                <td class="center">
                    {{ $index + 1 }}
                </td>

                <td>
                    <div class="payment-method">
                        {{ $methodLabel }}
                    </div>
                </td>

                <td>
                    <span class="source-label">
                        {{ $sourceLabel }}
                    </span>
                </td>

                <td class="right">
                    {{ $formatNumber($item['payment_transactions'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatNumber($item['sales_transactions'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($item['payment_amount'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatPercentage($item['percentage'] ?? 0) }}
                </td>
            </tr>

        @empty

            <tr>
                <td colspan="7" class="center">
                    No payment transactions found.
                </td>
            </tr>

        @endforelse
    </tbody>

    @if ($paymentRows->isNotEmpty())

        <tfoot>
            <tr>
                <td colspan="3" class="right">
                    GRAND TOTAL
                </td>

                <td class="right">
                    {{ $formatNumber($totals['payment_transactions'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatNumber($totals['sales_transactions'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($totals['payment_amount'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatPercentage($totals['percentage'] ?? 100) }}
                </td>
            </tr>
        </tfoot>

    @endif
</table>


{{-- FOOTER --}}

<div class="footer">
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">
                Nuvora ERP — Sales Management Report
            </td>

            <td style="width: 50%; text-align: right;">
                Generated at {{ $generatedAt->format('d M Y H:i:s') }}
            </td>
        </tr>
    </table>
</div>