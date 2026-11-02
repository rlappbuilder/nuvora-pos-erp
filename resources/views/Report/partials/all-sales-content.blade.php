@php
    $summary = $report['summary'] ?? [];

    // Keep paginator intact.
    $transactions = $report['transactions'] ?? collect();

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

    $sourceName = match ($filters['source'] ?? 'all') {
        'pos' => 'POS',
        'consignment' => 'Consignment',
        default => 'All Sources',
    };

    $generatedAt = isset($generatedAt)
        ? \Illuminate\Support\Carbon::parse($generatedAt)
        : now();

    $customerFilter = $filters['customer_name']
        ?? (!empty($filters['customer_id']) ? 'Selected Customer' : 'All');

    $resellerFilter = $filters['reseller_name']
        ?? (!empty($filters['reseller_id']) ? 'Selected Reseller' : 'All');

    $cashierFilter = $filters['cashier_name']
        ?? (!empty($filters['cashier_id']) ? 'Selected Cashier' : 'All');

    $paymentMethodLabels = [
        'cash' => 'Cash',
        'qris' => 'QRIS',
        'debit_card' => 'Debit Card',
        'transfer' => 'Bank Transfer',
        'e_wallet' => 'E-Wallet',
    ];

    $paymentMethod = !empty($filters['payment_method'])
        ? ($paymentMethodLabels[$filters['payment_method']] ?? $filters['payment_method'])
        : 'All';

    $customerLabel = fn ($transaction) =>
        ($transaction->partner_type ?? '') === 'Reseller'
            ? 'Reseller'
            : 'Customer';

    $cashierLabel = fn ($transaction) =>
        ($transaction->source ?? '') === 'POS'
            ? ($transaction->cashier_name ?? '-')
            : '-';

    $rowNumber = method_exists($transactions, 'firstItem')
        ? ($transactions->firstItem() ?? 0)
        : 0;
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

    .transaction-number {
        font-weight: bold;
        color: #0f172a;
    }

    .transaction-date {
        color: #64748b;
        font-size: 9px;
        margin-bottom: 3px;
    }

    .source-label {
        font-weight: bold;
    }

    .pagination {
        margin: 15px 0;
        text-align: center;
        font-size: 10px;
    }

    .pagination a,
    .pagination span {
        display: inline-block;
        padding: 5px 9px;
        margin: 0 2px;
        border: 1px solid #dbe3ef;
        text-decoration: none;
        color: #2563eb;
    }

    .pagination .active {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    .pagination .disabled {
        color: #94a3b8;
    }

    @media print {
        .pagination {
            display: none !important;
        }
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
                {{ $company->name ?? config('app.name', 'Nuvora ERP') }}
            </div>

            <div>Sales Management</div>
        </td>

        <td style="width: 45%;">
            <div class="report-title">
                All <span>Sales</span> Report
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
        <td class="filter-label">Customer</td>
        <td>: {{ $customerFilter }}</td>

        <td class="filter-label">Reseller</td>
        <td>: {{ $resellerFilter }}</td>
    </tr>

    <tr>
        <td class="filter-label">Cashier</td>
        <td>: {{ $cashierFilter }}</td>

        <td class="filter-label">Payment Method</td>
        <td>: {{ $paymentMethod }}</td>
    </tr>
</table>

{{-- SUMMARY --}}
<table class="summary-wrapper">
    <tr>
        <td>
            <div class="summary-label">Total Transactions</div>
            <div class="summary-value">
                {{ number_format((float) ($summary['total_transactions'] ?? 0)) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Total Items</div>
            <div class="summary-value">
                {{ $formatNumber($summary['total_items'] ?? 0) }}
            </div>
        </td>

        <td>
            <div class="summary-label">Total Sales</div>
            <div class="summary-value">
                {{ $formatCurrency($summary['total_sales'] ?? 0) }}
            </div>
        </td>
    </tr>
</table>

{{-- TRANSACTION DETAILS --}}
<div class="section-title">Transaction Details</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="center" style="width: 4%;">No.</th>
            <th style="width: 18%;">Transaction Information</th>
            <th style="width: 14%;">Source / Cashier</th>
            <th style="width: 20%;">Branch / Customer</th>
            <th class="center" style="width: 7%;">Items</th>
            <th class="right" style="width: 9%;">Total Qty</th>
            <th class="right" style="width: 12%;">Discount</th>
            <th class="right" style="width: 16%;">Grand Total</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($transactions as $transaction)
            @php
                $rowNumber++;
            @endphp

            <tr>
                <td class="center">
                    {{ $rowNumber }}
                </td>

                <td>
                    <div class="transaction-date">
                        {{ !empty($transaction->transaction_date)
                            ? \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('d M Y')
                            : '-' }}
                    </div>

                    <div class="transaction-number">
                        {{ $transaction->transaction_number ?? '-' }}
                    </div>
                </td>

                <td>
                    <div class="source-label">
                        {{ $transaction->source ?? '-' }}
                    </div>

                    @if (($transaction->source ?? '') === 'POS')
                        <div>
                            {{ $cashierLabel($transaction) }}
                        </div>
                    @else
                        <div>-</div>
                    @endif
                </td>

                <td>
                    <div>
                        {{ $transaction->branch_name ?? '-' }}
                    </div>

                    <div>
                        {{ $customerLabel($transaction) }}:
                        {{ $transaction->customer_name ?? '-' }}
                    </div>
                </td>

                <td class="center">
                    {{ number_format((float) ($transaction->total_items ?? 0)) }}
                </td>

                <td class="right">
                    {{ $formatNumber($transaction->total_qty ?? 0) }}
                </td>

                <td class="right">
                    {{ $formatCurrency($transaction->discount ?? 0) }}
                </td>

                <td class="right positive">
                    {{ $formatCurrency($transaction->grand_total ?? 0) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="center">
                    No sales transactions found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- PRINT PAGINATION --}}
@if ($transactions instanceof \Illuminate\Pagination\AbstractPaginator)
    <div class="pagination">
        <span>
            Showing {{ $transactions->firstItem() ?? 0 }}
            - {{ $transactions->lastItem() ?? 0 }}
            of {{ $transactions->total() }} transactions
        </span>

        @if ($transactions->previousPageUrl())
            <a href="{{ $transactions->previousPageUrl() }}">
                Previous
            </a>
        @else
            <span class="disabled">Previous</span>
        @endif

        <span class="active">
            Page {{ $transactions->currentPage() }}
            of {{ $transactions->lastPage() }}
        </span>

        @if ($transactions->nextPageUrl())
            <a href="{{ $transactions->nextPageUrl() }}">
                Next
            </a>
        @else
            <span class="disabled">Next</span>
        @endif
    </div>
@endif

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
                Nuvora ERP — All Sales Report
            </td>
        </tr>
    </table>
</div>