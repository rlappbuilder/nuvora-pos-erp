@php
    $summary = $report['summary'] ?? [];

    $categories = $report['categories'] ?? collect();

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
        number_format((float) $value, 2, ',', '.');

    $formatPercentage = fn ($value) =>
        number_format((float) $value, 2, ',', '.') . '%';

    $sourceName = match ($filters['source'] ?? 'all') {
        'pos' => 'POS',
        'consignment' => 'Consignment',
        default => 'All Sources',
    };

    $categoryName = $filters['category_label']
        ?? 'All Categories';

    $generatedAt = isset($generatedAt)
        ? \Illuminate\Support\Carbon::parse($generatedAt)
        : now();

    $profitClass = fn ($value) =>
        (float) $value >= 0 ? 'positive' : 'negative';
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
        width: 95px;
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

    .positive {
        color: #059669;
        font-weight: bold;
    }

    .negative {
        color: #dc2626;
        font-weight: bold;
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

    .report-table tfoot td {
        background: #eff6ff;
        border-top: 2px solid #94a3b8;
        font-weight: bold;
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
                {{ $company->company_name ?? config('app.name', 'Nuvora ERP') }}
            </div>

            <div>Sales Management</div>
        </td>

        <td style="width: 45%;">
            <div class="report-title">
                Sales <span>by Category</span> Report
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
        <td class="filter-label">Product Category</td>
        <td colspan="3">: {{ $categoryName }}</td>
    </tr>
</table>

{{-- KPI SUMMARY --}}
<div class="section-title">KPI Summary</div>

<table class="summary-wrapper">
    <tr>
        <td>
            <div class="summary-label">Total Categories</div>
            <div class="summary-value">
                {{ number_format((float) ($summary['total_categories'] ?? 0)) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Total Transactions</div>
            <div class="summary-value">
                {{ number_format((float) ($summary['total_transactions'] ?? 0)) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Total Items Sold</div>
            <div class="summary-value">
                {{ $formatNumber($summary['total_items'] ?? 0) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Gross Sales</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['gross_sales'] ?? 0) }}
            </div>
        </td>
    </tr>

    <tr>
        <td>
            <div class="summary-label">Total Discount</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['discount'] ?? 0) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Net Sales</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['net_sales'] ?? 0) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Cost of Goods Sold</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['cogs'] ?? 0) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Gross Profit</div>
            <div class="summary-value {{ $profitClass($summary['gross_profit'] ?? 0) }}">
                {{ $formatCurrency($summary['gross_profit'] ?? 0) }}
            </div>

            <div style="font-size: 9px; margin-top: 5px; color: #64748b;">
                Margin: {{ $formatPercentage($summary['margin'] ?? 0) }}
            </div>
        </td>
    </tr>
</table>

{{-- CATEGORY DETAILS --}}
<div class="section-title">Sales by Category</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="center" style="width: 4%;">No.</th>
            <th style="width: 18%;">Category</th>
            <th class="right" style="width: 9%;">Total Qty</th>
            <th class="right" style="width: 13%;">Gross Sales</th>
            <th class="right" style="width: 11%;">Discount</th>
            <th class="right" style="width: 13%;">Net Sales</th>
            <th class="right" style="width: 11%;">COGS</th>
            <th class="right" style="width: 13%;">Gross Profit</th>
            <th class="right" style="width: 8%;">Margin</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($categories as $index => $category)
            <tr>
                <td class="center">
                    {{ $index + 1 }}
                </td>

                <td>
                    {{ $category['category_name'] ?? 'Uncategorized' }}
                </td>

                <td class="right">
                    {{ $formatNumber($category['total_qty'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($category['gross_sales'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($category['discount'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($category['net_sales'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($category['cogs'] ?? 0) }}
                </td>

                <td class="right {{ $profitClass($category['gross_profit'] ?? 0) }}">
                    {{ $formatCurrency($category['gross_profit'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatPercentage($category['margin'] ?? 0) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="center">
                    No category sales found.
                </td>
            </tr>
        @endforelse
    </tbody>

    @if ($categories->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="2" class="right">
                    TOTAL
                </td>

                <td class="right">
                    {{ $formatNumber($totals['total_qty'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($totals['gross_sales'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($totals['discount'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($totals['net_sales'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($totals['cogs'] ?? 0) }}
                </td>

                <td class="right {{ $profitClass($totals['gross_profit'] ?? 0) }}">
                    {{ $formatCurrency($totals['gross_profit'] ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatPercentage($totals['margin'] ?? 0) }}
                </td>
            </tr>
        </tfoot>
    @endif
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
                Nuvora ERP — Sales by Category Report
            </td>
        </tr>
    </table>
</div>