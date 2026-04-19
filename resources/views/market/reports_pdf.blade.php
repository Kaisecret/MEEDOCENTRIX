<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Market Report</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        body { margin:0; font-family: "Segoe UI", Arial, sans-serif; color:#0f172a; }
        .sheet { max-width: 1000px; margin: 0 auto; }
        .head { border: 1px solid #cbd5e1; border-radius: 10px; padding: 14px 16px; background: #f8fafc; margin-bottom: 10px; }
        .head h1 { margin: 0 0 6px; font-size: 22px; }
        .meta { font-size: 12px; color:#475569; line-height: 1.5; }
        .grid { display:grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap:8px; margin-bottom: 10px; }
        .kpi { border:1px solid #cbd5e1; border-radius: 8px; padding:8px 10px; }
        .kpi span { display:block; font-size:11px; text-transform: uppercase; color:#64748b; font-weight:700; margin-bottom: 3px; letter-spacing: .04em; }
        .kpi strong { font-size:17px; }
        .section-title { margin: 12px 0 6px; font-size: 14px; font-weight: 800; }
        table { width:100%; border-collapse: collapse; font-size: 11px; }
        th { background: #e2e8f0; color:#0f172a; text-transform: uppercase; letter-spacing:.04em; font-size: 10px; text-align: left; padding: 6px; border:1px solid #cbd5e1; }
        td { padding:6px; border:1px solid #e2e8f0; vertical-align: top; }
        .sub { color:#64748b; font-size:10px; }
        .status-accepted { color:#047857; font-weight:700; }
        .status-awaiting { color:#0e7490; font-weight:700; }
        .status-pending { color:#b45309; font-weight:700; }
        .status-rejected { color:#b91c1c; font-weight:700; }
        .status-cancelled { color:#475569; font-weight:700; }
        .note { margin-top: 8px; font-size: 11px; color:#475569; }
    </style>
</head>
<body>
    <div class="sheet">
        <section class="head">
            <h1>Public Market Transactions Report</h1>
            <div class="meta">
                Range: <strong>{{ $rangeLabel }}</strong> ({{ $rangeStart->format('F d, Y') }} to {{ $rangeEnd->format('F d, Y') }})<br>
                Generated: {{ $generatedAt->format('F d, Y h:i A') }}<br>
                Records: {{ number_format($totalTransactions) }} transaction(s)
                @if(($pdfTotalRows ?? $totalTransactions) > ($pdfDisplayedRows ?? $totalTransactions))
                    <br>Detailed rows shown: {{ number_format($pdfDisplayedRows ?? 0) }} of {{ number_format($pdfTotalRows ?? 0) }} (PDF limit: {{ number_format($pdfMaxRows ?? 100) }})
                @endif
            </div>
        </section>

        <section class="grid">
            <div class="kpi"><span>Total Transactions</span><strong>{{ number_format($totalTransactions) }}</strong></div>
            <div class="kpi"><span>Accepted / Pending / Awaiting</span><strong>{{ number_format($acceptedTransactions) }} / {{ number_format($pendingTransactions) }} / {{ number_format($awaitingTransactions) }}</strong></div>
            <div class="kpi"><span>Total Amount</span><strong>PHP {{ number_format($totalAmount, 2) }}</strong></div>
        </section>

        <div class="section-title">Weekly Summary</div>
        <table>
            <thead><tr><th>Week</th><th>Transactions</th><th>Accepted</th><th>Pending</th><th>Awaiting</th><th>Cancelled</th><th>Total</th></tr></thead>
            <tbody>
            @forelse($weeklySummary as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ number_format($row['transactions']) }}</td>
                    <td>{{ number_format($row['accepted']) }}</td>
                    <td>{{ number_format($row['pending']) }}</td>
                    <td>{{ number_format($row['awaiting']) }}</td>
                    <td>{{ number_format($row['cancelled']) }}</td>
                    <td>PHP {{ number_format($row['total'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No weekly records found.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="section-title">Monthly Summary</div>
        <table>
            <thead><tr><th>Month</th><th>Transactions</th><th>Accepted</th><th>Pending</th><th>Awaiting</th><th>Cancelled</th><th>Total</th></tr></thead>
            <tbody>
            @forelse($monthlySummary as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ number_format($row['transactions']) }}</td>
                    <td>{{ number_format($row['accepted']) }}</td>
                    <td>{{ number_format($row['pending']) }}</td>
                    <td>{{ number_format($row['awaiting']) }}</td>
                    <td>{{ number_format($row['cancelled']) }}</td>
                    <td>PHP {{ number_format($row['total'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No monthly records found.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="section-title">Detailed Transactions @if(($pdfTotalRows ?? $totalTransactions) > ($pdfDisplayedRows ?? $totalTransactions)) (First {{ number_format($pdfDisplayedRows ?? 0) }} Rows) @endif</div>
        <table>
            <thead>
            <tr>
                <th>Stall</th><th>Tenant</th><th>Contract</th><th>Payment No.</th><th>Date</th><th>Time</th><th>Status</th><th>Total</th><th>Payer</th><th>Collector</th>
            </tr>
            </thead>
            <tbody>
            @forelse($transactions as $row)
                <tr>
                    <td>
                        <strong>{{ $row['stall_no'] }}</strong>
                        <div class="sub">{{ $row['location'] }}</div>
                    </td>
                    <td>
                        {{ $row['tenant_name'] }}
                        <div class="sub">{{ $row['business_name'] }}</div>
                    </td>
                    <td>{{ $row['contract_no'] }}</td>
                    <td>{{ $row['payment_no'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['time'] }}</td>
                    <td>
                        @if($row['status_key'] === 'accepted')
                            <span class="status-accepted">Accepted</span>
                        @elseif($row['status_key'] === 'collected_pending_confirmation')
                            <span class="status-awaiting">Awaiting</span>
                        @elseif($row['status_key'] === 'rejected')
                            <span class="status-rejected">Rejected</span>
                        @elseif($row['status_key'] === 'cancelled')
                            <span class="status-cancelled">Cancelled</span>
                        @else
                            <span class="status-pending">Pending</span>
                        @endif
                    </td>
                    <td>PHP {{ number_format($row['amount'], 2) }}</td>
                    <td>{{ $row['payer_name'] }}</td>
                    <td>{{ $row['collector'] }}</td>
                </tr>
            @empty
                <tr><td colspan="10">No detailed records found in selected range.</td></tr>
            @endforelse
            </tbody>
        </table>

        <p class="note">
            This report is printable and optimized for saving as PDF from browser print settings.
        </p>
    </div>
</body>
</html>
