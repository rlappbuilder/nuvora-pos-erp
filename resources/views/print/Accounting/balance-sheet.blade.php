<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        {{ $title ?? 'Balance Sheet' }}
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #f3f4f6;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .toolbar-title {
            font-size: 14px;
            font-weight: 700;
        }

        .toolbar-actions {
            display: flex;
            gap: 8px;
        }

        .toolbar button {
            border: 0;
            padding: 8px 14px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            background: #2563eb;
            color: #ffffff;
        }

        .toolbar .back {
            background: #6b7280;
        }

        .paper {
            width: min(1200px, calc(100% - 48px));
            min-height: 1100px;
            margin: 32px auto;
            padding: 40px 48px;
            background: #ffffff;
        }

        .company {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .3px;
            margin-bottom: 4px;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 13px;
            color: #6b7280;
        }

        .filter {
            margin-top: 8px;
            font-size: 12px;
            color: #6b7280;
        }

        .section {
            margin-top: 26px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        /*
        |--------------------------------------------------------------------------
        | Balance Sheet
        |--------------------------------------------------------------------------
        */

        .balance-sheet {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-top: 26px;
        }

        .column {
            min-width: 0;
        }

        .column-title {
            font-size: 13px;
            font-weight: 700;
            padding-bottom: 10px;
            border-bottom: 1px solid #d1d5db;
        }

        .account-type {
            margin-top: 16px;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 700;
        }

        .account-category {
            margin-top: 8px;
            margin-bottom: 3px;
            padding-left: 10px;
            font-size: 11px;
            font-weight: 600;
            color: #374151;
        }

        .account-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            padding: 5px 0 5px 18px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }

        .account-name {
            min-width: 0;
        }

        .account-code {
            color: #6b7280;
            font-size: 10px;
            margin-right: 7px;
        }

        .number {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .subtotal-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            margin-top: 10px;
            padding: 8px 0;
            border-top: 1px solid #d1d5db;
            font-size: 11px;
            font-weight: 700;
        }

        .total-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            margin-top: 14px;
            padding: 10px;
            background: #f9fafb;
            border-top: 1px solid #d1d5db;
            font-size: 12px;
            font-weight: 700;
        }

        .earnings-row {
            margin-top: 8px;
            padding-left: 18px;
        }

        .balance-status {
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
        }

        .balance-status-title {
            font-weight: 700;
        }

        .balance-status-detail {
            margin-top: 4px;
            color: #6b7280;
        }

        .balance-status.unbalanced .balance-status-title {
            color: #991b1b;
        }

        .basis {
            margin-top: 26px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
        }

        @page {
            size: A4 landscape;
            margin: 12mm;
        }

        @media print {

            html,
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none;
            }

            .paper {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
            }

            .balance-sheet {
                break-inside: avoid;
            }

            .column {
                break-inside: avoid;
            }

            .account-row,
            .subtotal-row,
            .total-row {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>

<div class="toolbar">

    <div class="toolbar-title">
        Balance Sheet
    </div>

    <div class="toolbar-actions">

         <button
            type="button"
            onclick="window.close()"
        >
            ← Back
        </button>

        <button
            type="button"
            onclick="window.print()"
        >
            Print
        </button>

    </div>

</div>


<div class="paper">

    {{-- HEADER --}}

    <div class="company">
        NUVORA ERP
    </div>

    <div class="title">
        BALANCE SHEET
    </div>

    <div class="subtitle">
        Statement of Financial Position
    </div>

    <div class="filter">

        <strong>As of:</strong>

        @if (!empty($filters['as_of_date']))

            {{ \Carbon\Carbon::parse(
                $filters['as_of_date']
            )->format('d M Y') }}

        @else

            -

        @endif


        &nbsp;&nbsp;|&nbsp;&nbsp;


        <strong>Branch:</strong>

        {{ $filters['branch_label'] ?? 'All Branch' }}


        &nbsp;&nbsp;|&nbsp;&nbsp;


        <strong>Fiscal Year:</strong>

        {{ $filters['fiscal_year_label'] ?? 'All Fiscal Year' }}


        &nbsp;&nbsp;|&nbsp;&nbsp;


        <strong>Period:</strong>

        {{ $filters['accounting_period_label'] ?? 'All Period' }}

    </div>


    {{-- BALANCE SHEET --}}

    <div class="balance-sheet">


        {{-- =========================================================
             ASSETS
        ========================================================== --}}

        <div class="column">

            <div class="column-title">
                ASSETS
            </div>


            @php
                $assets =
                    collect($report['assets'] ?? [])
                        ->filter(function ($row) use ($showZeroBalance) {
                            return $showZeroBalance
                                || abs((float) ($row['balance'] ?? 0)) >= 0.005;
                        });

                $assetTypes =
                    $assets->groupBy('account_type');
            @endphp


            @forelse ($assetTypes as $typeName => $typeAccounts)

                <div class="account-type">
                    {{ $typeName ?: 'Other' }}
                </div>


                @php
                    $categories =
                        $typeAccounts->groupBy(
                            'account_category'
                        );
                @endphp


                @foreach ($categories as $categoryName => $accounts)

                    <div class="account-category">
                        {{ $categoryName ?: 'Other' }}
                    </div>


                    @foreach ($accounts as $account)

                        <div class="account-row">

                            <div class="account-name">

                                <span class="account-code">
                                    {{ $account['code'] }}
                                </span>

                                {{ $account['name'] }}

                            </div>

                            <div class="number">
                                Rp
                                {{ number_format(
                                    $account['balance'] ?? 0,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </div>

                        </div>

                    @endforeach

                @endforeach

            @empty

                <div
                    style="
                        padding: 14px 0;
                        font-size: 11px;
                        color: #6b7280;
                    "
                >
                    No asset data.
                </div>

            @endforelse


            {{-- TOTAL ASSETS --}}

            <div class="total-row">

                <div>
                    TOTAL ASSETS
                </div>

                <div class="number">
                    Rp
                    {{ number_format(
                        $statistics['total_assets'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>

        </div>


        {{-- =========================================================
             LIABILITIES & EQUITY
        ========================================================== --}}

        <div class="column">

            <div class="column-title">
                LIABILITIES &amp; EQUITY
            </div>


            {{-- LIABILITIES --}}

            @php
                $liabilities =
                    collect($report['liabilities'] ?? [])
                        ->filter(function ($row) use ($showZeroBalance) {
                            return $showZeroBalance
                                || abs((float) ($row['balance'] ?? 0)) >= 0.005;
                        });

                $liabilityTypes =
                    $liabilities->groupBy('account_type');
            @endphp


            @forelse ($liabilityTypes as $typeName => $typeAccounts)

                <div class="account-type">
                    {{ $typeName ?: 'Other' }}
                </div>


                @php
                    $categories =
                        $typeAccounts->groupBy(
                            'account_category'
                        );
                @endphp


                @foreach ($categories as $categoryName => $accounts)

                    <div class="account-category">
                        {{ $categoryName ?: 'Other' }}
                    </div>


                    @foreach ($accounts as $account)

                        <div class="account-row">

                            <div class="account-name">

                                <span class="account-code">
                                    {{ $account['code'] }}
                                </span>

                                {{ $account['name'] }}

                            </div>

                            <div class="number">
                                Rp
                                {{ number_format(
                                    $account['balance'] ?? 0,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </div>

                        </div>

                    @endforeach

                @endforeach

            @empty

                <div
                    style="
                        padding: 14px 0;
                        font-size: 11px;
                        color: #6b7280;
                    "
                >
                    No liability data.
                </div>

            @endforelse


            {{-- TOTAL LIABILITIES --}}

            <div class="subtotal-row">

                <div>
                    TOTAL LIABILITIES
                </div>

                <div class="number">
                    Rp
                    {{ number_format(
                        $statistics['total_liabilities'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>


            {{-- EQUITY --}}

            <div
                class="account-type"
                style="margin-top: 20px;"
            >
                EQUITY
            </div>


            @php
                $equity =
                    collect($report['equity'] ?? [])
                        ->filter(function ($row) use ($showZeroBalance) {
                            return $showZeroBalance
                                || abs((float) ($row['balance'] ?? 0)) >= 0.005;
                        });

                $equityTypes =
                    $equity->groupBy('account_type');
            @endphp


            @forelse ($equityTypes as $typeName => $typeAccounts)

                <div class="account-type">
                    {{ $typeName ?: 'Other' }}
                </div>


                @php
                    $categories =
                        $typeAccounts->groupBy(
                            'account_category'
                        );
                @endphp


                @foreach ($categories as $categoryName => $accounts)

                    <div class="account-category">
                        {{ $categoryName ?: 'Other' }}
                    </div>


                    @foreach ($accounts as $account)

                        <div class="account-row">

                            <div class="account-name">

                                <span class="account-code">
                                    {{ $account['code'] }}
                                </span>

                                {{ $account['name'] }}

                            </div>

                            <div class="number">
                                Rp
                                {{ number_format(
                                    $account['balance'] ?? 0,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </div>

                        </div>

                    @endforeach

                @endforeach

            @empty

                <div
                    style="
                        padding: 14px 0;
                        font-size: 11px;
                        color: #6b7280;
                    "
                >
                    No equity data.
                </div>

            @endforelse


            {{-- CURRENT YEAR EARNINGS --}}

            <div class="account-row earnings-row">

                <div class="account-name">
                    Current Year Earnings
                </div>

                <div class="number">
                    Rp
                    {{ number_format(
                        $statistics['current_year_earnings'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>


            {{-- TOTAL EQUITY --}}

            <div class="subtotal-row">

                <div>
                    TOTAL EQUITY
                </div>

                <div class="number">
                    Rp
                    {{ number_format(
                        $statistics['total_equity'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>


            {{-- TOTAL LIABILITIES & EQUITY --}}

            <div class="total-row">

                <div>
                    TOTAL LIABILITIES &amp; EQUITY
                </div>

                <div class="number">
                    Rp
                    {{ number_format(
                        $statistics['total_liabilities_equity'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>

        </div>

    </div>


    {{-- BALANCE STATUS --}}

    <div
        class="balance-status
        {{ !empty($statistics['is_balanced'])
            ? 'balanced'
            : 'unbalanced' }}"
    >

        <div class="balance-status-title">

            @if (!empty($statistics['is_balanced']))

                Balance Sheet is balanced

            @else

                Balance Sheet is not balanced

            @endif

        </div>

        <div class="balance-status-detail">

            Assets =
            Liabilities + Equity

            &nbsp;&nbsp;|&nbsp;&nbsp;

            Difference:

            Rp
            {{ number_format(
                $statistics['difference'] ?? 0,
                0,
                ',',
                '.'
            ) }}

        </div>

    </div>


    {{-- BASIS --}}

    <div class="basis">

        <strong>Statement Basis:</strong>

        Only active posting accounts belonging to the
        authenticated user's company are included.

        Zero-balance accounts are
        {{ !empty($showZeroBalance)
            ? 'included'
            : 'excluded' }}.

    </div>

</div>

</body>

</html>