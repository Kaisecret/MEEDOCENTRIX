<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Collector Report</title>
    <style>
        @page { size: A4 portrait; margin: 10mm 10mm 10mm 10mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.45;
            background: #ffffff;
        }
        .sheet {
            width: 100%;
            padding-bottom: 36mm;
        }

        .letterhead {
            border-bottom: 3px solid #155f8f;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .letterhead-row {
            display: table;
            width: 100%;
        }
        .letterhead-left, .letterhead-right {
            display: table-cell;
            vertical-align: middle;
        }
        .letterhead-right { text-align: right; }
        .brand-title {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            color: #0c3a5b;
            letter-spacing: -0.01em;
        }
        .brand-sub {
            margin: 2px 0 0;
            font-size: 11px;
            color: #475569;
            font-weight: 600;
        }
        .doc-title {
            display: inline-block;
            background: #155f8f;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .doc-meta {
            margin-top: 6px;
            font-size: 10px;
            color: #475569;
        }

        .info-bar {
            display: table;
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 12px;
        }
        .info-cell {
            display: table-cell;
            padding: 8px 12px;
            border-right: 1px solid #e2e8f0;
            width: 25%;
            vertical-align: middle;
        }
        .info-cell:last-child { border-right: none; }
        .info-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 11px;
            color: #0f172a;
            font-weight: 700;
        }

        .section { margin-bottom: 14px; page-break-inside: avoid; }
        .section.section-detail { page-break-inside: auto; }
        .section-title {
            background: #0c3a5b;
            color: #ffffff;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-radius: 4px 4px 0 0;
            margin: 0;
        }
        .section-note {
            float: right;
            font-size: 9px;
            font-weight: 500;
            color: #cbd5e1;
            text-transform: none;
            letter-spacing: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            background: #ffffff;
        }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th {
            background: #eaf2f9;
            color: #0c3a5b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-size: 9px;
            text-align: left;
            padding: 7px 8px;
            border: 1px solid #cbd5e1;
            font-weight: 800;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; }
        td.center, th.center { text-align: center; }
        td strong { color: #0c3a5b; }

        .sub {
            color: #64748b;
            font-size: 9px;
            margin-top: 2px;
        }

        .pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .pill-accepted { color: #047857; background: #ecfdf5; border-color: #a7f3d0; }
        .pill-awaiting { color: #0e7490; background: #ecfeff; border-color: #a5f3fc; }
        .pill-rejected { color: #b91c1c; background: #fef2f2; border-color: #fecaca; }
        .pill-pending { color: #b45309; background: #fffbeb; border-color: #fde68a; }

        .empty {
            text-align: center;
            padding: 14px !important;
            color: #94a3b8;
            font-style: italic;
        }

        .footer {
            margin-top: 0;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #64748b;
            display: table;
            width: 100%;
        }
        .footer-left, .footer-right {
            display: table-cell;
        }
        .footer-right { text-align: right; }

        .report-end {
            position: fixed;
            left: 10mm;
            right: 10mm;
            bottom: 3mm;
            page-break-inside: avoid;
        }
        .signatures {
            display: table;
            width: 100%;
            border-spacing: 14px 0;
            margin-bottom: 8px;
        }
        .sig-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            font-size: 10px;
            color: #475569;
        }
        .sig-line {
            height: 22px;
            border-bottom: 1px solid #475569;
            margin: 0 22px 6px;
        }
        .sig-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="sheet">

        <header class="letterhead">
            <div class="letterhead-row">
                <div class="letterhead-left">
                    <h1 class="brand-title">Public Market Collector Office</h1>
                    <p class="brand-sub">Transactions &amp; Collection Report</p>
                </div>
                <div class="letterhead-right">
                    <span class="doc-title">Official Report</span>
                    <div class="doc-meta">Generated: {{ $generatedAt->format('F d, Y h:i A') }}</div>
                </div>
            </div>
        </header>

        <div class="info-bar">
            <div class="info-cell">
                <div class="info-label">Period</div>
                <div class="info-value">{{ $rangeLabel }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Date From</div>
                <div class="info-value">{{ $rangeStart->format('M d, Y') }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Date To</div>
                <div class="info-value">{{ $rangeEnd->format('M d, Y') }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Total Records</div>
                <div class="info-value">{{ number_format($totalTransactions) }} transaction(s)</div>
            </div>
        </div>

        @if ($period === 'week')
            <section class="section">
                <h2 class="section-title">Weekly Summary</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="width:32%;">Week</th>
                            <th class="num" style="width:17%;">Transactions</th>
                            <th class="num" style="width:17%;">Accepted</th>
                            <th class="num" style="width:17%;">Pending</th>
                            <th class="num" style="width:17%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($weeklySummary as $row)
                        <tr>
                            <td><strong>{{ $row['label'] }}</strong></td>
                            <td class="num">{{ number_format($row['transactions']) }}</td>
                            <td class="num">{{ number_format($row['accepted']) }}</td>
                            <td class="num">{{ number_format($row['pending']) }}</td>
                            <td class="num">PHP {{ number_format($row['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">No weekly records found in the selected range.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </section>
        @elseif ($period === 'month')
            <section class="section">
                <h2 class="section-title">Monthly Summary</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="width:32%;">Month</th>
                            <th class="num" style="width:17%;">Transactions</th>
                            <th class="num" style="width:17%;">Accepted</th>
                            <th class="num" style="width:17%;">Pending</th>
                            <th class="num" style="width:17%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($monthlySummary as $row)
                        <tr>
                            <td><strong>{{ $row['label'] }}</strong></td>
                            <td class="num">{{ number_format($row['transactions']) }}</td>
                            <td class="num">{{ number_format($row['accepted']) }}</td>
                            <td class="num">{{ number_format($row['pending']) }}</td>
                            <td class="num">PHP {{ number_format($row['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">No monthly records found in the selected range.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </section>
        @endif

        <section class="section section-detail">
            <h2 class="section-title">
                Detailed Transactions
                @if(($pdfTotalRows ?? $totalTransactions) > ($pdfDisplayedRows ?? $totalTransactions))
                    <span class="section-note">Showing first {{ number_format($pdfDisplayedRows ?? 0) }} of {{ number_format($pdfTotalRows ?? 0) }} rows</span>
                @endif
            </h2>
            <table>
                <thead>
                <tr>
                    <th style="width:9%;">Stall</th>
                    <th style="width:12%;">Tenant</th>
                    <th style="width:13%;">Business</th>
                    <th style="width:10%;">Payment No.</th>
                    <th style="width:8%;">Date</th>
                    <th style="width:7%;">Time</th>
                    <th class="center" style="width:9%;">Status</th>
                    <th class="num" style="width:10%;">Total</th>
                    <th style="width:11%;">Payer</th>
                    <th style="width:11%;">Collector</th>
                </tr>
                </thead>
                <tbody>
                @forelse($transactions as $row)
                    <tr>
                        <td>
                            <strong>{{ $row['stall_no'] }}</strong>
                            <div class="sub">{{ $row['location'] }}</div>
                        </td>
                        <td>{{ $row['tenant_name'] }}</td>
                        <td>{{ $row['business_name'] }}</td>
                        <td>{{ $row['payment_no'] }}</td>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['time'] }}</td>
                        <td class="center">
                            @if($row['status_key'] === 'accepted')
                                <span class="pill pill-accepted">Accepted</span>
                            @elseif($row['status_key'] === 'collected_pending_confirmation')
                                <span class="pill pill-awaiting">Awaiting</span>
                            @elseif($row['status_key'] === 'rejected')
                                <span class="pill pill-rejected">Rejected</span>
                            @else
                                <span class="pill pill-pending">Pending</span>
                            @endif
                        </td>
                        <td class="num">PHP {{ number_format($row['amount'], 2) }}</td>
                        <td>{{ $row['payer_name'] }}</td>
                        <td>{{ $row['collector'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="empty">No detailed records found in the selected range.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>

        <div class="report-end">
            <div class="signatures">
                <div class="sig-cell">
                    <div class="sig-line"></div>
                    <div class="sig-label">Prepared By</div>
                </div>
                <div class="sig-cell">
                    <div class="sig-line"></div>
                    <div class="sig-label">Verified By</div>
                </div>
            </div>

            <footer class="footer">
                <div class="footer-left">
                    Collector Management System &middot; Confidential Report
                </div>
                <div class="footer-right">
                    Generated {{ $generatedAt->format('F d, Y h:i A') }}
                </div>
            </footer>
        </div>

    </div>
</body>
</html>
