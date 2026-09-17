<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>
        Consignment Settlement -
        {{ $settlement->settlement_number ?? '-' }}
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f4f6;
            font-family: Arial, sans-serif;
            color: #111827;
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
        }

        .toolbar button {
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 7px;
            padding: 8px 14px;
            font-size: 13px;
            cursor: pointer;
        }

        .toolbar button:hover {
            background: #f9fafb;
        }

        .paper {
            width: min(
                1200px,
                calc(100% - 48px)
            );

            min-height: 1100px;

            margin: 32px auto;

            padding: 40px 48px;

            background: white;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
        }

        .subtitle {
            margin-top: 5px;
            color: #6b7280;
            font-size: 13px;
        }

        /*
        |--------------------------------------------------------------------------
        | Header Information
        |--------------------------------------------------------------------------
        */

        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 60px;
            margin-top: 24px;
            padding: 16px 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 12px;
        }

        .info-label {
            width: 110px;
            flex-shrink: 0;
            color: #6b7280;
        }

        .info-value {
            font-weight: 600;
            color: #111827;
        }

        .info-muted {
            margin-top: 3px;
            color: #6b7280;
            font-size: 10px;
            font-weight: 400;
        }

        /*
        |--------------------------------------------------------------------------
        | Period
        |--------------------------------------------------------------------------
        */

        .period {
            margin-top: 18px;
            color: #6b7280;
            font-size: 12px;
        }

        .period strong {
            color: #111827;
        }

        /*
        |--------------------------------------------------------------------------
        | Table
        |--------------------------------------------------------------------------
        */

        table {
            width: 100%;
            margin-top: 24px;
            border-collapse: collapse;
            font-size: 12px;
        }

        thead th {
            padding: 10px 8px;
            background: #f9fafb;
            border-bottom: 1px solid #d1d5db;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
            text-align: left;
        }

        tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .main {
            font-weight: 600;
            color: #111827;
        }

        .muted {
            margin-top: 3px;
            color: #6b7280;
            font-size: 10px;
        }

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */

        .summary {
            width: 360px;
            margin-left: auto;
            margin-top: 20px;
            font-size: 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 5px 0;
            color: #4b5563;
        }

        .summary-row.grand-total {
            margin-top: 5px;
            padding-top: 9px;
            border-top: 1px solid #9ca3af;
            color: #111827;
            font-weight: 700;
        }

        .summary-row.receivable {
            color: #111827;
            font-weight: 600;
        }

        /*
        |--------------------------------------------------------------------------
        | Remarks
        |--------------------------------------------------------------------------
        */

        .remarks {
            margin-top: 24px;
            padding-top: 14px;
            border-top: 1px solid #e5e7eb;
        }

        .remarks-title {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
        }

        .remarks-content {
            margin-top: 6px;
            white-space: pre-line;
            font-size: 12px;
            color: #374151;
        }

        /*
        |--------------------------------------------------------------------------
        | Footer
        |--------------------------------------------------------------------------
        */

        .footer {
            margin-top: 40px;
            color: #9ca3af;
            font-size: 10px;
            text-align: center;
        }

        /*
        |--------------------------------------------------------------------------
        | Print
        |--------------------------------------------------------------------------
        */

        @media print {

            @page {
                size: A4 portrait;
                margin: 12mm;
            }

            body {
                background: white;
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

            table {
                width: 100%;
            }

            tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .info {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .summary {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .remarks {
                break-inside: avoid;
                page-break-inside: avoid;
            }

        }

    </style>

</head>


<body>


    <!-- ============================================================= -->
    <!-- Toolbar -->
    <!-- ============================================================= -->

    <div class="toolbar">

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


    <!-- ============================================================= -->
    <!-- Paper -->
    <!-- ============================================================= -->

    <div class="paper">

        <!-- Header -->

        <div class="title">
            Consignment Settlement
        </div>


        <div class="subtitle">
            Reseller sales settlement transaction
        </div>


        <!-- ========================================================= -->
        <!-- Transaction Information -->
        <!-- ========================================================= -->

        <div class="info">

            <!-- Settlement -->

            <div class="info-item">

                <div class="info-label">
                    Settlement
                </div>

                <div>

                    <div class="info-value">
                        {{
                            $settlement->settlement_number
                            ?? '-'
                        }}
                    </div>

                    <div class="info-muted">

                        Date:
                        {{
                            $settlement->settlement_date
                                ? \Carbon\Carbon::parse(
                                    $settlement->settlement_date
                                )->format('d/m/Y')
                                : '-'
                        }}

                    </div>

                </div>

            </div>


            <!-- Reseller -->

            <div class="info-item">

                <div class="info-label">
                    Reseller
                </div>

                <div>

                    <div class="info-value">
                        {{
                            data_get(
                                $settlement,
                                'reseller.name',
                                '-'
                            )
                        }}
                    </div>

                    <div class="info-muted">

                        {{
                            data_get(
                                $settlement,
                                'reseller.reseller_code',
                                '-'
                            )
                        }}

                    </div>

                </div>

            </div>


            <!-- Branch -->

            <div class="info-item">

                <div class="info-label">
                    Branch
                </div>

                <div class="info-value">

                    {{
                        data_get(
                            $settlement,
                            'branch.name',
                            '-'
                        )
                    }}

                </div>

            </div>


            <!-- Warehouse -->

            <div class="info-item">

                <div class="info-label">
                    Warehouse
                </div>

                <div class="info-value">

                    {{
                        data_get(
                            $settlement,
                            'warehouse.name',
                            '-'
                        )
                    }}

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Period -->
        <!-- ========================================================= -->

        <div class="period">

            Settlement Period:

            <strong>

                {{
                    $settlement->period_from
                        ? \Carbon\Carbon::parse(
                            $settlement->period_from
                        )->format('d/m/Y')
                        : '-'
                }}

                &nbsp;–&nbsp;

                {{
                    $settlement->period_to
                        ? \Carbon\Carbon::parse(
                            $settlement->period_to
                        )->format('d/m/Y')
                        : '-'
                }}

            </strong>

        </div>


        <!-- ========================================================= -->
        <!-- Details -->
        <!-- ========================================================= -->

        <table>

            <thead>

                <tr>

                    <th
                        class="center"
                        style="width: 40px;"
                    >
                        No
                    </th>

                    <th>
                        Product
                    </th>

                    <th
                        style="width: 100px;"
                    >
                        SKU
                    </th>

                    <th
                        style="width: 80px;"
                    >
                        Unit
                    </th>

                    <th
                        class="right"
                        style="width: 70px;"
                    >
                        Qty
                    </th>

                    <th
                        class="right"
                        style="width: 120px;"
                    >
                        Unit Price
                    </th>

                    <th
                        class="right"
                        style="width: 140px;"
                    >
                        Total
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse(
                    $settlement->details ?? []
                    as $index => $detail
                )

                    <tr>

                        <!-- No -->

                        <td class="center">

                            {{ $index + 1 }}

                        </td>


                        <!-- Product -->

                        <td>

                            <div class="main">

                                {{
                                    data_get(
                                        $detail,
                                        'variant.product.name',
                                        data_get(
                                            $detail,
                                            'variant.name',
                                            '-'
                                        )
                                    )
                                }}

                            </div>

                        </td>


                        <!-- SKU -->

                        <td>

                            {{
                                data_get(
                                    $detail,
                                    'variant.sku',
                                    '-'
                                )
                            }}

                        </td>


                        <!-- Unit -->

                        <td>

                            {{
                                data_get(
                                    $detail,
                                    'unit.name',
                                    '-'
                                )
                            }}

                        </td>


                        <!-- Qty -->

                        <td class="right">

                            {{
                                number_format(
                                    $detail->qty_sold ?? 0,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <!-- Unit Price -->

                        <td class="right">

                            Rp
                            {{
                                number_format(
                                    $detail->unit_price ?? 0,
                                    2,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <!-- Total -->

                        <td class="right">

                            Rp
                            {{
                                number_format(
                                    $detail->total_amount ?? 0,
                                    2,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="7"
                            class="center"
                        >
                            No settlement details found.
                        </td>

                    </tr>

                @endforelse


                @if(
                    $settlement->details
                    &&
                    $settlement->details->count() > 0
                )

                    <tr>

                        <td
                            colspan="4"
                            style="
                                border-bottom: 0;
                                font-weight: 700;
                            "
                        >
                            Total
                        </td>


                        <td
                            class="right"
                            style="
                                border-bottom: 0;
                                font-weight: 700;
                            "
                        >

                            {{
                                number_format(
                                    $settlement->details
                                        ->sum('qty_sold'),
                                    0,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <td
                            style="border-bottom: 0;"
                        ></td>


                        <td
                            class="right"
                            style="
                                border-bottom: 0;
                                font-weight: 700;
                            "
                        >

                            Rp
                            {{
                                number_format(
                                    $settlement->details
                                        ->sum('total_amount'),
                                    2,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>

                    </tr>

                @endif

            </tbody>

        </table>


        <!-- ========================================================= -->
        <!-- Summary -->
        <!-- ========================================================= -->

        <div class="summary">

            <div class="summary-row">

                <span>
                    Subtotal
                </span>

                <span>

                    Rp
                    {{
                        number_format(
                            $settlement->subtotal ?? 0,
                            2,
                            ',',
                            '.'
                        )
                    }}

                </span>

            </div>


            <div class="summary-row">

                <span>
                    Adjustment
                </span>

                <span>

                    Rp
                    {{
                        number_format(
                            $settlement->adjustment_amount ?? 0,
                            2,
                            ',',
                            '.'
                        )
                    }}

                </span>

            </div>


            <div
                class="
                    summary-row
                    grand-total
                "
            >

                <span>
                    Grand Total
                </span>

                <span>

                    Rp
                    {{
                        number_format(
                            $settlement->grand_total ?? 0,
                            2,
                            ',',
                            '.'
                        )
                    }}

                </span>

            </div>


            <div class="summary-row">

                <span>
                    Payment
                </span>

                <span>

                    Rp
                    {{
                        number_format(
                            $settlement->payment_amount ?? 0,
                            2,
                            ',',
                            '.'
                        )
                    }}

                </span>

            </div>


            <div
                class="
                    summary-row
                    receivable
                "
            >

                <span>
                    Receivable
                </span>

                <span>

                    Rp
                    {{
                        number_format(
                            $settlement->receivable_amount ?? 0,
                            2,
                            ',',
                            '.'
                        )
                    }}

                </span>

            </div>


            <div class="summary-row">

                <span>
                    Payment Status
                </span>

                <span>
                    {{
                        $settlement->payment_status
                        ?? '-'
                    }}
                </span>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Remarks -->
        <!-- ========================================================= -->

        @if($settlement->remarks)

            <div class="remarks">

                <div class="remarks-title">
                    Remarks
                </div>


                <div class="remarks-content">

                    {{ $settlement->remarks }}

                </div>

            </div>

        @endif


        <!-- ========================================================= -->
        <!-- Footer -->
        <!-- ========================================================= -->

        <div class="footer">

            Nuvora ERP · Consignment Settlement

        </div>

    </div>


</body>

</html>