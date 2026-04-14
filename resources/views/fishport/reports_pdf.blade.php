<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fishport Report</title>
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
        .status-paid { color:#047857; font-weight:700; }
        .status-unpaid { color:#b91c1c; font-weight:700; }
        .note { margin-top: 8px; font-size: 11px; color:#475569; }
        @media print { }
    </style>
</head>
<body>
    <div class="sheet">
        <section class="head">
            <h1>Fishport Transactions Report</h1>
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
            <div class="kpi"><span>Paid / Not Paid</span><strong>{{ number_format($paidTransactions) }} / {{ number_format($notPaidTransactions) }}</strong></div>
            <div class="kpi"><span>Total Amount</span><strong>PHP {{ number_format($totalAmount, 2) }}</strong></div>
        </section>

        <div class="section-title">Weekly Summary</div>
        <table>
            <thead><tr><th>Week</th><th>Transactions</th><th>Paid</th><th>Not Paid</th><th>Total</th></tr></thead>
            <tbody>
            @forelse($weeklySummary as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ number_format($row['transactions']) }}</td>
                    <td>{{ number_format($row['paid']) }}</td>
                    <td>{{ number_format($row['not_paid']) }}</td>
                    <td>PHP {{ number_format($row['total'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No weekly records found.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="section-title">Monthly Summary</div>
        <table>
            <thead><tr><th>Month</th><th>Transactions</th><th>Paid</th><th>Not Paid</th><th>Total</th></tr></thead>
            <tbody>
            @forelse($monthlySummary as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ number_format($row['transactions']) }}</td>
                    <td>{{ number_format($row['paid']) }}</td>
                    <td>{{ number_format($row['not_paid']) }}</td>
                    <td>PHP {{ number_format($row['total'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No monthly records found.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="section-title">Detailed Transactions @if(($pdfTotalRows ?? $totalTransactions) > ($pdfDisplayedRows ?? $totalTransactions)) (First {{ number_format($pdfDisplayedRows ?? 0) }} Rows) @endif</div>
        <table>
            <thead>
            <tr>
                <th>Log ID</th><th>Payment No.</th><th>Date</th><th>Time</th><th>Vessel</th><th>ARR/DEP</th><th>Origin</th><th>Status</th><th>Total</th><th>Payer</th><th>Encoder</th>
            </tr>
            </thead>
            <tbody>
            @forelse($transactions as $row)
                <tr>
                    <td><strong>{{ $row['log_id'] }}</strong></td>
                    <td>{{ $row['payment_no'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['time'] }}</td>
                    <td>{{ $row['vessel'] }}</td>
                    <td>{{ $row['arr_dep'] }}</td>
                    <td>{{ $row['origin'] }}</td>
                    <td>@if($row['is_paid'])<span class="status-paid">Paid</span>@else<span class="status-unpaid">Not Paid</span>@endif</td>
                    <td>PHP {{ number_format($row['total'], 2) }}</td>
                    <td>{{ $row['payer_name'] }}</td>
                    <td>{{ $row['encoder'] }}</td>
                </tr>
            @empty
                <tr><td colspan="11">No detailed records found in selected range.</td></tr>
            @endforelse
            </tbody>
        </table>

        <p class="note">
            This report is printable and optimized for saving as PDF from browser print settings.
        </p>
    </div>
</body>
</html>
