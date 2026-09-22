<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        {{ $title ?? 'Income Statement' }}
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

        /* =========================================================
           TOOLBAR
        ========================================================== */

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


        /* =========================================================
           PAPER
        ========================================================== */

        .paper {
            width: min(850px, calc(100% - 48px));
            min-height: 1100px;
            margin: 32px auto;
            padding: 40px 48px;
            background: #ffffff;
        }


        /* =========================================================
           HEADER
        ========================================================== */

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
            line-height: 1.7;
            color: #6b7280;
        }


        /* =========================================================
           STATEMENT
        ========================================================== */

        .statement {
            margin-top: 28px;
        }

        .statement-section {
            margin-top: 25px;
        }

        .statement-section:first-child {
            margin-top: 0;
        }

        .statement-section-title {
            padding-bottom: 8px;
            border-bottom: 1px solid #9ca3af;
            font-size: 12px;
            font-weight: 700;
        }


        /* =========================================================
           ACCOUNT TYPE
        ========================================================== */

        .account-type {
            margin-top: 14px;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 700;
        }


        /* =========================================================
           ACCOUNT CATEGORY
        ========================================================== */

        .account-category {
            margin-top: 7px;
            margin-bottom: 3px;
            padding-left: 10px;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
        }


        /* =========================================================
           ACCOUNT TABLE

           FIXED COLUMNS:

           CODE | ACCOUNT NAME | RP | AMOUNT

           Semua baris memakai ukuran kolom yang sama.
        ========================================================== */

        .account-row {
            display: grid;

            grid-template-columns:
                70px
                minmax(0, 1fr)
                28px
                105px;

            column-gap: 8px;

            align-items: center;

            width: 100%;

            padding: 5px 0 5px 18px;

            font-size: 12px;
            line-height: 1.45;

            border-bottom: 1px solid #f3f4f6;
        }

        .account-code {
            color: #4b5563;
            font-size: 11px;
            white-space: nowrap;
        }

        .account-name {
            min-width: 0;
            color: #111827;
        }

        .currency {
            text-align: left;
            white-space: nowrap;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }


        /* =========================================================
           TOTAL ROW

           Sama persis dengan kolom detail:
           CODE | NAME | RP | AMOUNT

           Kolom CODE dikosongkan.
        ========================================================== */

        .total-row {
            display: grid;

            grid-template-columns:
                70px
                minmax(0, 1fr)
                28px
                105px;

            column-gap: 8px;

            align-items: center;

            width: 100%;

            margin-top: 9px;
            padding: 8px 10px;

            background: #f1f5f9;

            border-top: 1px solid #d1d5db;

            font-size: 12px;
            font-weight: 700;
        }

        .total-label {
            min-width: 0;
        }


        /* =========================================================
           PROFIT ROW
        ========================================================== */

        .profit-row {
            display: grid;

            grid-template-columns:
                70px
                minmax(0, 1fr)
                28px
                105px;

            column-gap: 8px;

            align-items: center;

            width: 100%;

            margin-top: 7px;
            padding: 9px 10px;

            background: #eaf0f7;

            font-size: 12px;
            font-weight: 700;
        }


        /* =========================================================
           NET INCOME
        ========================================================== */

        .net-income {
            display: grid;

            grid-template-columns:
                70px
                minmax(0, 1fr)
                28px
                105px;

            column-gap: 8px;

            align-items: center;

            width: 100%;

            margin-top: 20px;
            padding: 11px 10px;

            background: #eaf0f7;

            border-top: 2px solid #111827;
            border-bottom: 1px solid #9ca3af;

            font-size: 13px;
            font-weight: 700;
        }

        .net-income .currency,
        .net-income .amount {
            font-size: 13px;
            font-weight: 700;
        }


        /* =========================================================
           BASIS
        ========================================================== */

        .basis {
            margin-top: 28px;
            padding-top: 10px;

            border-top: 1px solid #d1d5db;

            font-size: 10px;
            line-height: 1.5;

            color: #6b7280;
        }


        /* =========================================================
           A4 PORTRAIT
        ========================================================== */

        @page {
            size: A4 portrait;
            margin: 12mm;
        }


        /* =========================================================
           PRINT
        ========================================================== */

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

            .statement-section {
                break-inside: avoid;
            }

            .account-row,
            .total-row,
            .profit-row,
            .net-income {
                break-inside: avoid;
            }
        }
    </style>
</head>


<body>

<div class="toolbar">

    <div class="toolbar-title">
        Income Statement
    </div>

    <div class="toolbar-actions">

        <button
            type="button"
            class="back"
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

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="company">
        NUVORA ERP
    </div>

    <div class="title">
        INCOME STATEMENT
    </div>

    <div class="subtitle">
        Statement of Profit or Loss
    </div>

    <div class="filter">

        <strong>Date From:</strong>

        @if (!empty($filters['date_from']))

            {{ \Carbon\Carbon::parse(
                $filters['date_from']
            )->format('d M Y') }}

        @else

            -

        @endif

        &nbsp;&nbsp;|&nbsp;&nbsp;

        <strong>Date To:</strong>

        @if (!empty($filters['date_to']))

            {{ \Carbon\Carbon::parse(
                $filters['date_to']
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


    {{-- =========================================================
         INCOME STATEMENT
    ========================================================== --}}

    <div class="statement">


        {{-- =====================================================
             REVENUE
        ====================================================== --}}

        <div class="statement-section">

            <div class="statement-section-title">
                REVENUE
            </div>

            @php

                $revenue =
                    collect($report['revenue'] ?? [])
                        ->filter(function ($row) use ($showZeroAccount) {

                            return $showZeroAccount
                                || abs(
                                    (float) ($row['balance'] ?? 0)
                                ) >= 0.005;

                        });

                $revenueTypes =
                    $revenue->groupBy('account_type');

            @endphp


            @forelse ($revenueTypes as $typeName => $typeAccounts)

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

                            <div class="account-code">
                                {{ $account['code'] }}
                            </div>

                            <div class="account-name">
                                {{ $account['name'] }}
                            </div>

                            <div class="currency">
                                Rp
                            </div>

                            <div class="amount">

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
                        font-size: 12px;
                        color: #6b7280;
                    "
                >
                    No revenue data.
                </div>

            @endforelse


            {{-- TOTAL REVENUE --}}

            <div class="total-row">

                <div></div>

                <div class="total-label">
                    TOTAL REVENUE
                </div>

                <div class="currency">
                    Rp
                </div>

                <div class="amount">

                    {{ number_format(
                        $statistics['total_revenue'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>

        </div>


        {{-- =====================================================
             COST OF GOODS SOLD
        ====================================================== --}}

        <div class="statement-section">

            <div class="statement-section-title">
                COST OF GOODS SOLD
            </div>

            @php

                /*
                 * IMPORTANT:
                 * IncomeStatementService menggunakan:
                 *
                 * report key:
                 * cost_of_goods_sold
                 *
                 * bukan:
                 * cogs
                 */

                $costOfGoodsSold =
                    collect(
                        $report['cost_of_goods_sold'] ?? []
                    )
                    ->filter(function ($row) use ($showZeroAccount) {

                        return $showZeroAccount
                            || abs(
                                (float) ($row['balance'] ?? 0)
                            ) >= 0.005;

                    });

                $cogsTypes =
                    $costOfGoodsSold->groupBy(
                        'account_type'
                    );

            @endphp


            @forelse ($cogsTypes as $typeName => $typeAccounts)

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

                            <div class="account-code">
                                {{ $account['code'] }}
                            </div>

                            <div class="account-name">
                                {{ $account['name'] }}
                            </div>

                            <div class="currency">
                                Rp
                            </div>

                            <div class="amount">

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
                        font-size: 12px;
                        color: #6b7280;
                    "
                >
                    No cost of goods sold data.
                </div>

            @endforelse


            {{-- TOTAL COGS --}}

            <div class="total-row">

                <div></div>

                <div class="total-label">
                    TOTAL COST OF GOODS SOLD
                </div>

                <div class="currency">
                    Rp
                </div>

                <div class="amount">

                    {{ number_format(
                        $statistics['total_cost_of_goods_sold'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>


            {{-- GROSS PROFIT --}}

            <div class="profit-row">

                <div></div>

                <div>
                    GROSS PROFIT
                </div>

                <div class="currency">
                    Rp
                </div>

                <div class="amount">

                    {{ number_format(
                        $statistics['gross_profit'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>

        </div>


        {{-- =====================================================
             OPERATING EXPENSES
        ====================================================== --}}

        <div class="statement-section">

            <div class="statement-section-title">
                OPERATING EXPENSES
            </div>

            @php

                $operatingExpenses =
                    collect(
                        $report['operating_expenses'] ?? []
                    )
                    ->filter(function ($row) use ($showZeroAccount) {

                        return $showZeroAccount
                            || abs(
                                (float) ($row['balance'] ?? 0)
                            ) >= 0.005;

                    });

                $expenseTypes =
                    $operatingExpenses->groupBy(
                        'account_type'
                    );

            @endphp


            @forelse ($expenseTypes as $typeName => $typeAccounts)

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

                            <div class="account-code">
                                {{ $account['code'] }}
                            </div>

                            <div class="account-name">
                                {{ $account['name'] }}
                            </div>

                            <div class="currency">
                                Rp
                            </div>

                            <div class="amount">

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
                        font-size: 12px;
                        color: #6b7280;
                    "
                >
                    No operating expense data.
                </div>

            @endforelse


            {{-- TOTAL OPERATING EXPENSES --}}

            <div class="total-row">

                <div></div>

                <div class="total-label">
                    TOTAL OPERATING EXPENSES
                </div>

                <div class="currency">
                    Rp
                </div>

                <div class="amount">

                    {{ number_format(
                        $statistics['total_operating_expenses'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>


            {{-- OPERATING INCOME --}}

            <div class="profit-row">

                <div></div>

                <div>
                    OPERATING INCOME
                </div>

                <div class="currency">
                    Rp
                </div>

                <div class="amount">

                    {{ number_format(
                        $statistics['operating_income'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>

        </div>


        {{-- =====================================================
             OTHER INCOME
        ====================================================== --}}

        <div class="statement-section">

            <div class="statement-section-title">
                OTHER INCOME
            </div>

            @php

                $otherIncome =
                    collect(
                        $report['other_income'] ?? []
                    )
                    ->filter(function ($row) use ($showZeroAccount) {

                        return $showZeroAccount
                            || abs(
                                (float) ($row['balance'] ?? 0)
                            ) >= 0.005;

                    });

                $otherIncomeTypes =
                    $otherIncome->groupBy(
                        'account_type'
                    );

            @endphp


            @forelse ($otherIncomeTypes as $typeName => $typeAccounts)

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

                            <div class="account-code">
                                {{ $account['code'] }}
                            </div>

                            <div class="account-name">
                                {{ $account['name'] }}
                            </div>

                            <div class="currency">
                                Rp
                            </div>

                            <div class="amount">

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
                        font-size: 12px;
                        color: #6b7280;
                    "
                >
                    No other income data.
                </div>

            @endforelse


            {{-- TOTAL OTHER INCOME --}}

            <div class="total-row">

                <div></div>

                <div class="total-label">
                    TOTAL OTHER INCOME
                </div>

                <div class="currency">
                    Rp
                </div>

                <div class="amount">

                    {{ number_format(
                        $statistics['total_other_income'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>

        </div>


        {{-- =====================================================
             OTHER EXPENSES
        ====================================================== --}}

        <div class="statement-section">

            <div class="statement-section-title">
                OTHER EXPENSES
            </div>

            @php

                $otherExpenses =
                    collect(
                        $report['other_expenses'] ?? []
                    )
                    ->filter(function ($row) use ($showZeroAccount) {

                        return $showZeroAccount
                            || abs(
                                (float) ($row['balance'] ?? 0)
                            ) >= 0.005;

                    });

                $otherExpenseTypes =
                    $otherExpenses->groupBy(
                        'account_type'
                    );

            @endphp


            @forelse ($otherExpenseTypes as $typeName => $typeAccounts)

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

                            <div class="account-code">
                                {{ $account['code'] }}
                            </div>

                            <div class="account-name">
                                {{ $account['name'] }}
                            </div>

                            <div class="currency">
                                Rp
                            </div>

                            <div class="amount">

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
                        font-size: 12px;
                        color: #6b7280;
                    "
                >
                    No other expense data.
                </div>

            @endforelse


            {{-- TOTAL OTHER EXPENSES --}}

            <div class="total-row">

                <div></div>

                <div class="total-label">
                    TOTAL OTHER EXPENSES
                </div>

                <div class="currency">
                    Rp
                </div>

                <div class="amount">

                    {{ number_format(
                        $statistics['total_other_expenses'] ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>

        </div>


        {{-- =====================================================
             NET INCOME
        ====================================================== --}}

        <div class="net-income">

            <div></div>

            <div>
                NET INCOME
            </div>

            <div class="currency">
                Rp
            </div>

            <div class="amount">

                {{ number_format(
                    $statistics['net_income'] ?? 0,
                    0,
                    ',',
                    '.'
                ) }}

            </div>

        </div>

    </div>


    {{-- =========================================================
         BASIS
    ========================================================== --}}

    <div class="basis">

        <strong>Statement Basis:</strong>

        Only active posting accounts belonging to the
        authenticated user's company are included.

        Zero-balance accounts are
        {{ !empty($showZeroAccount)
            ? 'included'
            : 'excluded' }}.

    </div>

</div>

</body>

</html>