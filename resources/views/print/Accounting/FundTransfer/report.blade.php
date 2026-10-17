<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        h2 {
            margin-bottom: 4px;
        }

        .meta {
            margin-bottom: 16px;
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 7px;
        }

        th {
            background: #f3f4f6;
            text-align: left;
        }

        .number {
            text-align: right;
        }

        .total {
            font-weight: bold;
            background: #f9fafb;
        }
    </style>
</head>

<body>
    <h2>{{ $title }}</h2>

    <div class="meta">
        Date range:
        {{ $filters['date_from'] ?? 'Beginning' }}
        —
        {{ $filters['date_to'] ?? 'Today' }}
        <br>

        Generated:
        {{ now()->format('Y-m-d H:i:s') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Transfer Number</th>
                <th>Date</th>
                <th>From Branch</th>
                <th>To Branch</th>
                <th class="number">Transfer Amount</th>
                <th class="number">Settled Amount</th>
                <th class="number">Outstanding</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @php
                $totalTransfer = 0;
                $totalSettled = 0;
                $totalOutstanding = 0;
            @endphp

            @forelse ($transfers as $transfer)
                @php
                    $settled = (float) ($transfer->settled_amount ?? 0);
                    $outstanding = (float) ($transfer->outstanding_amount ?? 0);

                    $totalTransfer += (float) $transfer->amount;
                    $totalSettled += $settled;
                    $totalOutstanding += $outstanding;
                @endphp

                <tr>
                    <td>{{ $transfer->transfer_number }}</td>

                    <td>
                        {{ $transfer->transfer_date
                            ? $transfer->transfer_date->format('Y-m-d')
                            : '-' }}
                    </td>

                    <td>{{ $transfer->fromBranch->name ?? '-' }}</td>
                    <td>{{ $transfer->toBranch->name ?? '-' }}</td>

                    <td class="number">
                        {{ number_format((float) $transfer->amount, 2) }}
                    </td>

                    <td class="number">
                        {{ number_format($settled, 2) }}
                    </td>

                    <td class="number">
                        {{ number_format($outstanding, 2) }}
                    </td>

                    <td>{{ $transfer->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">
                        No data found.
                    </td>
                </tr>
            @endforelse

            <tr class="total">
                <td colspan="4">TOTAL</td>

                <td class="number">
                    {{ number_format($totalTransfer, 2) }}
                </td>

                <td class="number">
                    {{ number_format($totalSettled, 2) }}
                </td>

                <td class="number">
                    {{ number_format($totalOutstanding, 2) }}
                </td>

                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>