<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Bill of Materials' }} - {{ $bom->code }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #e5e7eb;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .toolbar {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .toolbar button {
            padding: 9px 18px;
            border: 0;
            border-radius: 5px;
            background: #2563eb;
            color: #fff;
            cursor: pointer;
            font-size: 12px;
        }

        .toolbar .btn-back {
            background: #4b5563;
        }

        .paper {
            width: 100%;
            max-width: 850px;
            min-height: 1000px;
            margin: 0 auto;
            padding: 35px;
            background: #fff;
        }

        .company-header {
            padding-bottom: 14px;
            border-bottom: 2px solid #111827;
            text-align: center;
        }

        .company-header h1 {
            margin: 0 0 5px;
            font-size: 20px;
            letter-spacing: 1px;
        }

        .company-header p {
            margin: 0;
            color: #6b7280;
            font-size: 10px;
        }

        .report-title {
            margin: 22px 0 4px;
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .report-subtitle {
            margin-bottom: 22px;
            color: #6b7280;
            text-align: center;
            font-size: 11px;
        }

        .section {
            margin-top: 22px;
        }

        .section-title {
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #d1d5db;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px 4px;
            vertical-align: top;
        }

        .info-table .label {
            width: 18%;
            color: #6b7280;
        }

        .info-table .separator {
            width: 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 8px 7px;
            border: 1px solid #d1d5db;
            text-align: left;
            vertical-align: top;
        }

        .data-table th {
            background: #f3f4f6;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        .data-table td {
            font-size: 11px;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .muted {
            color: #6b7280;
        }

        .status {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 3px;
            background: #e5e7eb;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .notes {
            margin-top: 6px;
            color: #6b7280;
            font-size: 10px;
        }

        .revision-notes {
            padding: 10px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            line-height: 1.5;
        }

        .footer {
            margin-top: 35px;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            text-align: right;
            font-size: 10px;
        }

        .empty-row {
            padding: 18px !important;
            color: #6b7280;
            text-align: center !important;
        }

        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .toolbar {
                display: none !important;
            }

            .paper {
                width: 100%;
                max-width: none;
                min-height: auto;
                margin: 0;
                padding: 0;
            }

            .section {
                break-inside: auto;
            }

            .data-table thead {
                display: table-header-group;
            }

            .data-table tr {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>

    <div class="toolbar">
        <button type="button" class="btn-back" onclick="window.close()">
            Back
        </button>

        <button type="button" onclick="window.print()">
            Print
        </button>
    </div>

    <div class="paper">

        {{-- Company Header --}}
        <div class="company-header">
            <h1>NUVORA ERP</h1>
            <p>Enterprise Resource Planning System</p>
        </div>

        <div class="report-title">
            Bill of Materials
        </div>

        <div class="report-subtitle">
            Production BOM Document
        </div>

        {{-- BOM Information --}}
        <div class="section">
            <div class="section-title">BOM Information</div>

            <table class="info-table">
                <tr>
                    <td class="label">BOM Code</td>
                    <td class="separator">:</td>
                    <td><strong>{{ $bom->code }}</strong></td>

                    <td class="label">Version</td>
                    <td class="separator">:</td>
                    <td>
                        <strong>V{{ $version->version }}</strong>
                    </td>
                </tr>

                <tr>
                    <td class="label">BOM Name</td>
                    <td class="separator">:</td>
                    <td>{{ $bom->name }}</td>

                    <td class="label">Status</td>
                    <td class="separator">:</td>
                    <td>
                        <span class="status">
                            {{ ucfirst($version->status) }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <td class="label">Branch</td>
                    <td class="separator">:</td>
                    <td>{{ $bom->branch->name ?? '-' }}</td>

                    <td class="label">BOM Type</td>
                    <td class="separator">:</td>
                    <td>{{ ucfirst($bom->bom_type ?? '-') }}</td>
                </tr>

                <tr>
                    <td class="label">Effective From</td>
                    <td class="separator">:</td>
                    <td>
                        {{ $version->effective_from
                            ? \Carbon\Carbon::parse($version->effective_from)->format('d M Y')
                            : '-' }}
                    </td>

                    <td class="label">Effective Until</td>
                    <td class="separator">:</td>
                    <td>
                        {{ $version->effective_until
                            ? \Carbon\Carbon::parse($version->effective_until)->format('d M Y')
                            : '-' }}
                    </td>
                </tr>
            </table>

            @if($bom->description)
                <div class="notes">
                    <strong>Description:</strong>
                    {{ $bom->description }}
                </div>
            @endif
        </div>

        {{-- Output Information --}}
        <div class="section">
            <div class="section-title">BOM Output</div>

            <table class="info-table">
                <tr>
                    <td class="label">Product</td>
                    <td class="separator">:</td>
                    <td>
                        {{ $bom->productVariant->product->name ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="label">Variant</td>
                    <td class="separator">:</td>
                    <td>
                        {{ $bom->productVariant->name ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="label">SKU</td>
                    <td class="separator">:</td>
                    <td>
                        {{ $bom->productVariant->sku ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="label">Output Quantity</td>
                    <td class="separator">:</td>
                    <td>
                        <strong>
                            {{ number_format((float) $version->output_qty, 4, ',', '.') }}
                            {{ $version->outputUnit->name ?? '' }}
                        </strong>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Material Composition --}}
        <div class="section">
            <div class="section-title">Material Composition</div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No.</th>
                        <th width="27%">Material</th>
                        <th width="12%">SKU</th>
                        <th width="12%" class="text-right">Net Qty</th>
                        <th width="12%" class="text-right">Scrap (%)</th>
                        <th width="14%" class="text-right">Gross Qty</th>
                        <th width="10%">Unit</th>
                        <th width="8%">Operation</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($version->materials as $index => $material)
                        @php
                            $scrapPercent = (float) $material->scrap_percent;
                            $netQty = (float) $material->qty;

                            $grossQty = $scrapPercent < 100
                                ? $netQty / (1 - ($scrapPercent / 100))
                                : null;

                            $operationName = $material
                                ->productionBomOperation
                                ?->productionOperation
                                ?->name ?? 'General Material';
                        @endphp

                        <tr>
                            <td class="text-center">
                                {{ $index + 1 }}
                            </td>

                            <td>
                                {{ $material->componentVariant->product->name ?? '-' }}
                                <br>
                                <span class="muted">
                                    {{ $material->componentVariant->name ?? '-' }}
                                </span>

                                @if($material->notes)
                                    <div class="notes">
                                        {{ $material->notes }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                {{ $material->componentVariant->sku ?? '-' }}
                            </td>

                            <td class="text-right">
                                {{ number_format($netQty, 4, ',', '.') }}
                            </td>

                            <td class="text-right">
                                {{ number_format($scrapPercent, 2, ',', '.') }}%
                            </td>

                            <td class="text-right">
                                {{ $grossQty !== null
                                    ? number_format($grossQty, 4, ',', '.')
                                    : '-' }}
                            </td>

                            <td>
                                {{ $material->unit->name ?? '-' }}
                            </td>

                            <td>
                                {{ $operationName }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-row">
                                No material composition available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="notes">
                Gross Quantity = Net Quantity / (1 - Scrap Percentage)
            </div>
        </div>

        {{-- Routing Operations --}}
        <div class="section">
            <div class="section-title">Routing Operations</div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th width="8%" class="text-center">Sequence</th>
                        <th width="30%">Production Operation</th>
                        <th width="18%" class="text-right">Duration (Minutes)</th>
                        <th>Instructions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($version->operations as $operation)
                        <tr>
                            <td class="text-center">
                                {{ $operation->sequence }}
                            </td>

                            <td>
                                {{ $operation->productionOperation->name ?? '-' }}
                            </td>

                            <td class="text-right">
                                {{ number_format((float) $operation->planned_duration_minutes, 2, ',', '.') }}
                            </td>

                            <td>
                                {{ $operation->instructions ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-row">
                                No routing operations available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Revision Notes --}}
        @if($version->revision_notes)
            <div class="section">
                <div class="section-title">Revision Notes</div>

                <div class="revision-notes">
                    {{ $version->revision_notes }}
                </div>
            </div>
        @endif

        <div class="footer">
            Printed at:
            {{ now()->format('d M Y H:i:s') }}
        </div>

    </div>

</body>
</html>