@php
    $summary = $report['summary'] ?? [];
    $sources = $report['sources'] ?? [];
    $pos = $sources['pos'] ?? [];
    $consignment = $sources['consignment'] ?? [];
    $trend = $report['sales_trend'] ?? [];

    $branches = $branches ?? collect();
    $filters = $filters ?? [];

    $branchName = $branches->count() === 0
        ? 'All Branches'
        : $branches->pluck('name')->implode(', ');

    $formatCurrency = fn ($value) =>
        'Rp ' . number_format((float) $value, 2, ',', '.');

    $formatNumber = fn ($value) =>
        number_format((float) $value, 2, ',', '.');
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

    .positive {
        color: #059669;
        font-weight: bold;
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
                {{ config('app.name', 'Nuvora ERP') }}
            </div>

            <div>Sales Management</div>
        </td>

        <td style="width: 45%;">
            <div class="report-title">
                Sales <span>Summary</span> Report
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
        <td>: {{ $filters['date_from'] ?? '-' }}</td>

        <td class="filter-label">Date To</td>
        <td>: {{ $filters['date_to'] ?? '-' }}</td>
    </tr>

    <tr>
        <td class="filter-label">Branch</td>
        <td>: {{ $branchName }}</td>

        <td class="filter-label">Cashier</td>
        <td>: {{ $filters['cashier_id'] ?? 'All' }}</td>
    </tr>

    <tr>
        <td class="filter-label">Customer</td>
        <td>: {{ $filters['customer_id'] ?? 'All' }}</td>

        <td class="filter-label">Report Type</td>
        <td>: Sales Summary</td>
    </tr>
</table>

{{-- SUMMARY --}}
<table class="summary-wrapper">
    <tr>
        <td>
            <div class="summary-label">Total Revenue</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['total_revenue'] ?? 0) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Total Transactions</div>
            <div class="summary-value">
                {{ number_format((float) ($summary['total_transactions'] ?? 0)) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Gross Profit</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['gross_profit'] ?? 0) }}
            </div>
        </td>
    </tr>
</table>

{{-- SALES SOURCES --}}
<div class="section-title">Sales Sources</div>

<table class="report-table">
    <thead>
        <tr>
            <th style="width: 20%;">Sales Source</th>
            <th class="center">Transactions</th>
            <th class="right">Revenue</th>
            <th class="right">Items Sold</th>
            <th class="right">Discount</th>
            <th class="right">COGS</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>POS Sales</td>
            <td class="center">
                {{ number_format((float) ($pos['transactions'] ?? 0)) }}
            </td>
            <td class="right">
                {{ $formatCurrency($pos['revenue'] ?? 0) }}
            </td>
            <td class="right">
                {{ $formatNumber($pos['items'] ?? 0) }}
            </td>
            <td class="right">
                {{ $formatCurrency($pos['discount'] ?? 0) }}
            </td>
            <td class="right">
                {{ $formatCurrency($pos['cogs'] ?? 0) }}
            </td>
        </tr>

        <tr>
            <td>Consignment Settlement</td>
            <td class="center">
                {{ number_format((float) ($consignment['transactions'] ?? 0)) }}
            </td>
            <td class="right">
                {{ $formatCurrency($consignment['revenue'] ?? 0) }}
            </td>
            <td class="right">
                {{ $formatNumber($consignment['items'] ?? 0) }}
            </td>
            <td class="right">-</td>
            <td class="right">
                {{ $formatCurrency($consignment['cogs'] ?? 0) }}
            </td>
        </tr>
    </tbody>
</table>

{{-- FINANCIAL SUMMARY --}}
<div class="section-title">Financial Summary</div>

<table class="report-table">
    <tbody>
        <tr>
            <td style="width: 65%;">Total Revenue</td>
            <td class="right">
                {{ $formatCurrency($summary['total_revenue'] ?? 0) }}
            </td>
        </tr>

        <tr>
            <td>Total Discount</td>
            <td class="right">
                {{ $formatCurrency($summary['total_discount'] ?? 0) }}
            </td>
        </tr>

        <tr>
            <td>Total Cost of Goods Sold (COGS)</td>
            <td class="right">
                {{ $formatCurrency($summary['total_cogs'] ?? 0) }}
            </td>
        </tr>

        <tr>
            <td><strong>Gross Profit</strong></td>
            <td class="right positive">
                {{ $formatCurrency($summary['gross_profit'] ?? 0) }}
            </td>
        </tr>

        <tr>
            <td>Gross Profit Margin</td>
            <td class="right">
                {{ $formatNumber($summary['gross_profit_margin'] ?? 0) }}%
            </td>
        </tr>
    </tbody>
</table>

{{-- DAILY SALES TREND --}}
<div class="section-title">Daily Sales Trend</div>

<table class="report-table">
    <thead>
        <tr>
            <th style="width: 10%;">No.</th>
            <th>Date</th>
            <th class="right">POS Revenue</th>
            <th class="right">Consignment Revenue</th>
            <th class="right">Total Revenue</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($trend as $item)
            <tr>
                <td class="center">{{ $loop->iteration }}</td>

                <td>
                    {{ \Illuminate\Support\Carbon::parse($item['date'])->format('d M Y') }}
                </td>

                <td class="right">
                    {{ $formatCurrency($item['pos'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($item['consignment'] ?? 0) }}
                </td>

                <td class="right">
                    <strong>
                        {{ $formatCurrency($item['revenue'] ?? 0) }}
                    </strong>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="center">
                    No sales data found.
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
                Printed at: {{ now()->format('d/m/Y H:i:s') }}
            </td>

            <td class="right">
                Nuvora ERP — Sales Summary Report
            </td>
        </tr>
    </table>
</div>