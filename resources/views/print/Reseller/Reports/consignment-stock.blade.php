<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Consignment Stock</title>

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

        .filter {
            margin-top: 12px;
            color: #6b7280;
            font-size: 12px;
        }

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

        .total td {
            border-top: 1px solid #9ca3af;
            border-bottom: 0;
            font-weight: 700;
        }

        @media print {

            @page {
                size: A4 landscape;
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

        }

    </style>

</head>


<body>


    <!-- Toolbar -->

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


    <!-- Paper -->

    <div class="paper">

        <div class="title">
            Consignment Stock
        </div>


        <div class="subtitle">
            Current Nuvora-owned stock held at reseller locations
        </div>


        <div class="filter">

            Date:
            {{ $filters['date_from'] ?? 'All' }}

            &nbsp;–&nbsp;

            {{ $filters['date_to'] ?? 'All' }}

            &nbsp;&nbsp; | &nbsp;&nbsp;

            Reseller:
            {{ $filters['reseller_id'] ?? 'All' }}

            &nbsp;&nbsp; | &nbsp;&nbsp;

            Branch:
            {{ $filters['branch_id'] ?? 'All' }}

        </div>


        <table>

            <thead>

                <tr>

                    <th class="center">
                        No
                    </th>

                    <th>
                        Reseller / Location
                    </th>

                    <th>
                        Product
                    </th>

                    <th>
                        Unit
                    </th>

                    <th class="right">
                        On Hand
                    </th>

                    <th class="right">
                        Available
                    </th>

                    <th class="right">
                        Average Cost
                    </th>

                    <th class="right">
                        Stock Value
                    </th>

                    <th class="right">
                        Consignment Price
                    </th>

                    <th class="right">
                        Consignment Value
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse(
                    $consignmentStock->getCollection()
                    as $index => $row
                )

                    <tr>

                        <td class="center">
                            {{ $index + 1 }}
                        </td>


                        <td>

                          <div class="main">
                                {{ data_get($row, 'reseller.name', '-') }}
                            </div>

                            <div class="muted">

                                {{ data_get($row, 'branch.name', '-') }}

                                ·

                                {{ data_get($row, 'warehouse.name', '-') }}

                            </div>

                        </td>


                        <td>

                       <div class="main">

                            {{
                                data_get(
                                    $row,
                                    'product.name',
                                    data_get(
                                        $row,
                                        'variant.product.name',
                                        '-'
                                    )
                                )
                            }}

                        </div>

                        <div class="muted">

                            SKU:
                            {{ data_get($row, 'variant.sku', '-') }}

                        </div>

                        </td>


                        <td>

                            @forelse(
                                $row['units'] ?? []
                                as $unit
                            )

                                {{ $unit['unit_name'] ?? '-' }}

                                @if(!$loop->last)
                                    ,
                                @endif

                            @empty

                                -

                            @endforelse

                        </td>


                        <td class="right">

                            {{
                                number_format(
                                    $row['on_hand_qty'] ?? 0,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <td class="right">

                            {{
                                number_format(
                                    $row['available_qty'] ?? 0,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <td class="right">

                            Rp
                            {{
                                number_format(
                                    $row['average_cost'] ?? 0,
                                    2,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <td class="right">

                            Rp
                            {{
                                number_format(
                                    $row['stock_value'] ?? 0,
                                    2,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <td class="right">

                            Rp
                            {{
                                number_format(
                                    $row['consignment_price'] ?? 0,
                                    2,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <td class="right">

                            Rp
                            {{
                                number_format(
                                    $row['consignment_value'] ?? 0,
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
                            colspan="10"
                            class="center"
                        >
                            No consignment stock found.
                        </td>

                    </tr>

                @endforelse


                @if(
                    $consignmentStock
                        ->getCollection()
                        ->count() > 0
                )

                    <tr class="total">

                        <td colspan="4">
                            Total
                        </td>


                        <td class="right">

                            {{
                                number_format(
                                    $consignmentStock
                                        ->getCollection()
                                        ->sum('on_hand_qty'),
                                    0,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <td class="right">

                            {{
                                number_format(
                                    $consignmentStock
                                        ->getCollection()
                                        ->sum('available_qty'),
                                    0,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <td></td>


                        <td class="right">

                            Rp
                            {{
                                number_format(
                                    $consignmentStock
                                        ->getCollection()
                                        ->sum('stock_value'),
                                    2,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>


                        <td></td>


                        <td class="right">

                            Rp
                            {{
                                number_format(
                                    $consignmentStock
                                        ->getCollection()
                                        ->sum('consignment_value'),
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

    </div>


</body>

</html>