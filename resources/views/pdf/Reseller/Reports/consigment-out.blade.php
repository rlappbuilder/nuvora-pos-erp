<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Consignment Out - {{ $consignmentOut->consignment_out_number }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 25px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 0;
        }

        .header {
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .document-title {
            font-size: 13px;
            font-weight: bold;
        }

        .info-wrapper {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .info-left {
            width: 55%;
        }

        .info-right {
            width: 45%;
            padding-left: 20px !important;
        }

        .info-label {
            display: inline-block;
            width: 75px;
            font-weight: bold;
        }

        .info-right .info-label {
            width: 70px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .items-table th {
            border-top: 1px solid #111827;
            border-bottom: 1px solid #111827;
            padding: 7px 5px;
            font-size: 9px;
            font-weight: bold;
            text-align: left;
        }

        .items-table td {
            border-bottom: 1px solid #d1d5db;
            padding: 7px 5px;
            vertical-align: top;
        }

        .col-no {
            width: 7%;
            text-align: center !important;
        }

        .col-product {
            width: 39%;
        }

        .col-qty {
            width: 10%;
            text-align: right !important;
        }

        .col-unit {
            width: 12%;
        }

        .col-price {
            width: 16%;
            text-align: right !important;
        }

        .col-total {
            width: 16%;
            text-align: right !important;
        }

        .product-name {
            font-weight: bold;
        }

        .product-sku {
            margin-top: 3px;
            font-size: 8px;
            color: #6b7280;
        }

        .grand-total-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .grand-total-table td {
            padding: 7px 5px;
            border-top: 1px solid #111827;
            font-weight: bold;
        }

        .grand-total-label {
            text-align: right;
        }

        .grand-total-value {
            width: 16%;
            text-align: right;
        }

        .remarks {
            margin-top: 25px;
        }

        .remarks-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .remarks-content {
            white-space: pre-line;
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <div class="header">
        <div class="company-name">
            {{ $consignmentOut->company?->name ?? 'RALISA HOMEDRESS' }}
        </div>

        <div class="document-title">
            CONSIGNMENT OUT
        </div>
    </div>

    {{-- Document Information --}}
    <div class="info-wrapper">
        <table class="info-table">
            <tr>
                <td class="info-left">
                    <span class="info-label">No.</span>
                    : {{ $consignmentOut->consignment_out_number }}
                </td>

                <td class="info-right">
                    <span class="info-label">Branch</span>
                    : {{ $consignmentOut->branch?->name ?? '-' }}
                </td>
            </tr>

            <tr>
                <td class="info-left">
                    <span class="info-label">Tanggal</span>
                    :
                    {{ $consignmentOut->transaction_date
                        ? \Carbon\Carbon::parse($consignmentOut->transaction_date)->format('d M Y')
                        : '-' }}
                </td>

                <td class="info-right">
                    <span class="info-label">Warehouse</span>
                    : {{ $consignmentOut->warehouse?->name ?? '-' }}
                </td>
            </tr>

            <tr>
                <td class="info-left">
                    <span class="info-label">Reseller</span>
                    :
                    {{ $consignmentOut->reseller?->name ?? '-' }}
                    @if($consignmentOut->reseller?->reseller_code)
                        ({{ $consignmentOut->reseller->reseller_code }})
                    @endif
                </td>

                <td class="info-right">
                </td>
            </tr>

            <tr>
                <td class="info-left">
                    <span class="info-label">Reference</span>
                    : {{ $consignmentOut->reference_number ?? '-' }}
                </td>

                <td class="info-right">
                </td>
            </tr>
        </table>
    </div>

    {{-- Detail --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-product">Product</th>
                <th class="col-qty">Jml</th>
                <th class="col-unit">Unit</th>
                <th class="col-price">Harga</th>
                <th class="col-total">Total</th>
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
                        {{ rtrim(rtrim(number_format((float) $detail->qty, 2, ',', '.'), '0'), ',') }}
                    </td>

                    <td class="col-unit">
                        {{ $detail->unit?->name ?? '-' }}
                    </td>

                    <td class="col-price">
                        {{ number_format((float) $detail->unit_price, 2, ',', '.') }}
                    </td>

                    <td class="col-total">
                        {{ number_format((float) $detail->total_price, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Grand Total --}}
    <table class="grand-total-table">
        <tr>
            <td class="grand-total-label">
                Grand Total
            </td>

            <td class="grand-total-value">
                {{ number_format(
                    $consignmentOut->details->sum(fn ($detail) => (float) $detail->total_price),
                    2,
                    ',',
                    '.'
                ) }}
            </td>
        </tr>
    </table>

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
    <script>
    window.addEventListener('load', function () {
        window.print()
    })
    </script>
</body>
</html>