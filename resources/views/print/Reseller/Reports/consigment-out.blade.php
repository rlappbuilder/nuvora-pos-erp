<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Consignment Out -
        {{ $consignmentOut->consignment_out_number }}
    </title>

    <style>

        /* =========================================================
           Page
        ========================================================= */

        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
        }


        /* =========================================================
           Toolbar
        ========================================================= */

        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;

            display: flex;
            align-items: center;
            justify-content: space-between;

            width: 100%;
            height: 56px;

            padding: 0 24px;

            background: #ffffff;

            border-bottom: 1px solid #e5e7eb;

            box-shadow:
                0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .toolbar-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            border: 1px solid #e5e7eb;
            border-radius: 8px;

            background: #ffffff;

            padding: 8px 14px;

            font-family: inherit;
            font-size: 13px;
            font-weight: 500;

            color: #374151;

            cursor: pointer;
        }

        .toolbar-button:hover {
            background: #f9fafb;
        }

        .toolbar-print {
            background: #111827;
            border-color: #111827;
            color: #ffffff;
        }

        .toolbar-print:hover {
            background: #1f2937;
        }


        /* =========================================================
           Paper - Screen Preview
        ========================================================= */

        .paper {
            width: min(1000px, calc(100% - 48px));
            min-height: 1100px;

            margin: 32px auto;

            padding: 40px 48px;

            background: #ffffff;
        }


        /* =========================================================
           Header
        ========================================================= */

        .header {
            margin-bottom: 24px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            line-height: 1.3;

            text-align: left;
        }

        .document-title {
            margin-top: 4px;

            font-size: 16px;
            font-weight: bold;
            line-height: 1.3;

            text-align: left;
        }


        /* =========================================================
           Document Information
        ========================================================= */

        .info-wrapper {
            width: 100%;

            margin-bottom: 24px;
        }

        .info-table {
            width: 100%;

            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;

            vertical-align: top;

            font-size: 13px;
            line-height: 1.5;

            text-align: left;
        }

        .info-left {
            width: 55%;
        }

        .info-right {
            width: 45%;

            padding-left: 24px !important;
        }

        .info-label {
            display: inline-block;

            width: 80px;

            font-weight: bold;

            text-align: left;
        }

        .info-right .info-label {
            width: 75px;
        }


        /* =========================================================
           Detail Table
        ========================================================= */

        .items-table {
            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;
        }

        .items-table th {
            padding: 10px 8px;

            border-top: 1px solid #111827;
            border-bottom: 1px solid #111827;

            font-size: 12px;
            font-weight: bold;

            text-align: left;
            vertical-align: middle;
        }

        .items-table td {
            padding: 10px 8px;

            border-bottom: 1px solid #d1d5db;

            font-size: 12px;

            text-align: left;
            vertical-align: top;
        }


        /* =========================================================
           Column Alignment
        ========================================================= */

        .col-no {
            width: 7%;

            text-align: center !important;
        }

        .col-product {
            width: 39%;

            text-align: left !important;
        }

        .col-qty {
            width: 10%;

            text-align: right !important;
        }

        .col-unit {
            width: 12%;

            text-align: left !important;
        }

        .col-price {
            width: 16%;

            text-align: right !important;
        }

        .col-total {
            width: 16%;

            text-align: right !important;
        }


        /* =========================================================
           Product
        ========================================================= */

        .product-name {
            font-size: 12px;
            font-weight: bold;

            line-height: 1.4;

            text-align: left;
        }

        .product-sku {
            margin-top: 3px;

            font-size: 10px;

            line-height: 1.3;

            color: #6b7280;

            text-align: left;
        }


        /* =========================================================
           Grand Total
        ========================================================= */

        .grand-total-table {
            width: 100%;

            margin-top: 12px;

            border-collapse: collapse;
        }

        .grand-total-table td {
            padding: 10px 8px;

            border-top: 1px solid #111827;

            font-size: 12px;
            font-weight: bold;

            vertical-align: middle;
        }

        .grand-total-label {
            text-align: right !important;
        }

        .grand-total-value {
            width: 16%;

            text-align: right !important;
        }


        /* =========================================================
           Remarks
        ========================================================= */

        .remarks {
            margin-top: 28px;

            text-align: left;
        }

        .remarks-title {
            margin-bottom: 6px;

            font-size: 12px;
            font-weight: bold;
        }

        .remarks-content {
            font-size: 12px;

            line-height: 1.5;

            white-space: pre-line;

            text-align: left;
        }


        /* =========================================================
           Print
        ========================================================= */

        @media print {

            html,
            body {
                width: 210mm;
                min-height: 297mm;

                margin: 0;
                padding: 0;

                background: #ffffff;
            }

            .print-toolbar {
                display: none !important;
            }

            .paper {
                width: 210mm;
                min-height: 297mm;

                margin: 0;

                padding: 25px 30px;

                background: #ffffff;

                box-shadow: none;
            }

            .company-name {
                font-size: 16px;
            }

            .document-title {
                font-size: 13px;
            }

            .info-table td {
                font-size: 10px;
            }

            .items-table th {
                font-size: 9px;

                padding: 7px 5px;
            }

            .items-table td {
                font-size: 9px;

                padding: 7px 5px;
            }

            .product-name {
                font-size: 9px;
            }

            .product-sku {
                font-size: 8px;
            }

            .grand-total-table td {
                font-size: 9px;

                padding: 7px 5px;
            }

            .remarks-title,
            .remarks-content {
                font-size: 9px;
            }

            .items-table tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }

        }

    </style>

</head>


<body>


    <!-- =========================================================
         Print Toolbar
    ========================================================= -->

    <div class="print-toolbar">

        <button
            type="button"
            class="toolbar-button"
            onclick="window.close()"
        >

            <span>
                ←
            </span>

            Back

        </button>


        <button
            type="button"
            class="toolbar-button toolbar-print"
            onclick="window.print()"
        >

            <span>
                🖨
            </span>

            Print

        </button>

    </div>


    <!-- =========================================================
         A4 Document
    ========================================================= -->

    <div class="paper">


        <!-- =====================================================
             Header
        ===================================================== -->

        <div class="header">

            <div class="company-name">

                {{ $consignmentOut->company?->name ?? 'RALISA HOMEDRESS' }}

            </div>


            <div class="document-title">

                CONSIGNMENT OUT

            </div>

        </div>


        <!-- =====================================================
             Document Information
        ===================================================== -->

        <div class="info-wrapper">

            <table class="info-table">

                <tr>

                    <td class="info-left">

                        <span class="info-label">
                            No.
                        </span>

                        :
                        {{ $consignmentOut->consignment_out_number }}

                    </td>


                    <td class="info-right">

                        <span class="info-label">
                            Branch
                        </span>

                        :
                        {{ $consignmentOut->branch?->name ?? '-' }}

                    </td>

                </tr>


                <tr>

                    <td class="info-left">

                        <span class="info-label">
                            Tanggal
                        </span>

                        :

                        {{ $consignmentOut->transaction_date
                            ? \Carbon\Carbon::parse(
                                $consignmentOut->transaction_date
                            )->format('d M Y')
                            : '-'
                        }}

                    </td>


                    <td class="info-right">

                        <span class="info-label">
                            Warehouse
                        </span>

                        :
                        {{ $consignmentOut->warehouse?->name ?? '-' }}

                    </td>

                </tr>


                <tr>

                    <td class="info-left">

                        <span class="info-label">
                            Reseller
                        </span>

                        :

                        {{ $consignmentOut->reseller?->name ?? '-' }}

                        @if($consignmentOut->reseller?->reseller_code)

                            (
                            {{ $consignmentOut->reseller->reseller_code }}
                            )

                        @endif

                    </td>


                    <td class="info-right">
                    </td>

                </tr>


                <tr>

                    <td class="info-left">

                        <span class="info-label">
                            Reference
                        </span>

                        :
                        {{ $consignmentOut->reference_number ?? '-' }}

                    </td>


                    <td class="info-right">
                    </td>

                </tr>

            </table>

        </div>


        <!-- =====================================================
             Detail Table
        ===================================================== -->

        <table class="items-table">

            <thead>

                <tr>

                    <th class="col-no">
                        No
                    </th>

                    <th class="col-product">
                        Product
                    </th>

                    <th class="col-qty">
                        Jml
                    </th>

                    <th class="col-unit">
                        Unit
                    </th>

                    <th class="col-price">
                        Harga
                    </th>

                    <th class="col-total">
                        Total
                    </th>

                </tr>

            </thead>


            <tbody>

                @foreach($consignmentOut->details as $index => $detail)

                    <tr>

                        <td class="col-no">
                            {{ $index + 1 }}
                        </td>


                        <td class="col-product">

                            <div class="product-name">

                                {{ $detail->variant?->product?->name ?? '-' }}

                            </div>


                            <div class="product-sku">

                                {{ $detail->variant?->sku ?? '-' }}

                            </div>

                        </td>


                        <td class="col-qty">

                            {{
                                rtrim(
                                    rtrim(
                                        number_format(
                                            (float) $detail->qty,
                                            2,
                                            ',',
                                            '.'
                                        ),
                                        '0'
                                    ),
                                    ','
                                )
                            }}

                        </td>


                        <td class="col-unit">

                            {{ $detail->unit?->name ?? '-' }}

                        </td>


                        <td class="col-price">

                            {{
                                number_format(
                                    (float) $detail->unit_price,
                                    2,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <td class="col-total">

                            {{
                                number_format(
                                    (float) $detail->total_price,
                                    2,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>


        <!-- =====================================================
             Grand Total
        ===================================================== -->

        <table class="grand-total-table">

            <tr>

                <td class="grand-total-label">

                    Grand Total

                </td>


                <td class="grand-total-value">

                    {{
                        number_format(
                            $consignmentOut->details->sum(
                                fn ($detail) =>
                                    (float) $detail->total_price
                            ),
                            2,
                            ',',
                            '.'
                        )
                    }}

                </td>

            </tr>

        </table>


        <!-- =====================================================
             Remarks
        ===================================================== -->

        @if($consignmentOut->remarks)

            <div class="remarks">

                <div class="remarks-title">
                    Remarks
                </div>


                <div class="remarks-content">

                    {{ $consignmentOut->remarks }}

                </div>

            </div>

        @endif


    </div>


</body>

</html>