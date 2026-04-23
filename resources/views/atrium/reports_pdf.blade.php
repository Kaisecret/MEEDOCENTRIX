<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atrium Report</title>
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
        .status-good { color:#047857; font-weight:700; }
        .status-warn { color:#b45309; font-weight:700; }
        .status-bad { color:#b91c1c; font-weight:700; }
        .note { margin-top: 8px; font-size: 11px; color:#475569; }
    </style>
</head>
<body>
    <div class="sheet">
        <section class="head">
            <h1>{{ $reportTitle }}</h1>
            <div class="meta">
                Range: <strong>{{ $rangeLabel }}</strong> ({{ $rangeStart->format('F d, Y') }} to {{ $rangeEnd->format('F d, Y') }})<br>
                Generated: {{ $generatedAt->format('F d, Y h:i A') }}<br>
                Records: {{ number_format($totalRecords) }}
                @if (($pdfTotalRows ?? $totalRecords) > ($pdfDisplayedRows ?? $totalRecords))
                    <br>Detailed rows shown: {{ number_format($pdfDisplayedRows ?? 0) }} of {{ number_format($pdfTotalRows ?? 0) }} (PDF limit: {{ number_format($pdfMaxRows ?? 100) }})
                @endif
            </div>
        </section>

        <section class="grid">
            <div class="kpi"><span>Total Records</span><strong>{{ number_format($totalRecords) }}</strong></div>
            <div class="kpi"><span>{{ $primaryLabel }} / {{ $secondaryLabel }}</span><strong>{{ number_format($primaryCount) }} / {{ number_format($secondaryCount) }}</strong></div>
            <div class="kpi">
                <span>{{ $metricLabel }}</span>
                <strong>
                    @if ($metricIsCurrency)
                        PHP {{ number_format($metricValue, 2) }}
                    @else
                        {{ number_format($metricValue) }}
                    @endif
                </strong>
            </div>
        </section>

        <div class="section-title">Weekly Summary</div>
        <table>
            <thead><tr><th>Week</th><th>Records</th><th>{{ $primaryLabel }}</th><th>{{ $secondaryLabel }}</th><th>{{ $summaryTotalLabel }}</th></tr></thead>
            <tbody>
                @forelse($weeklySummary as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td>{{ number_format($row['records']) }}</td>
                        <td>{{ number_format($row['primary']) }}</td>
                        <td>{{ number_format($row['secondary']) }}</td>
                        <td>
                            @if ($metricIsCurrency)
                                PHP {{ number_format($row['total'], 2) }}
                            @else
                                {{ number_format($row['total']) }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">No weekly records found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Monthly Summary</div>
        <table>
            <thead><tr><th>Month</th><th>Records</th><th>{{ $primaryLabel }}</th><th>{{ $secondaryLabel }}</th><th>{{ $summaryTotalLabel }}</th></tr></thead>
            <tbody>
                @forelse($monthlySummary as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td>{{ number_format($row['records']) }}</td>
                        <td>{{ number_format($row['primary']) }}</td>
                        <td>{{ number_format($row['secondary']) }}</td>
                        <td>
                            @if ($metricIsCurrency)
                                PHP {{ number_format($row['total'], 2) }}
                            @else
                                {{ number_format($row['total']) }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">No monthly records found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Detailed {{ ucfirst($report) }} Report @if(($pdfTotalRows ?? $totalRecords) > ($pdfDisplayedRows ?? $totalRecords)) (First {{ number_format($pdfDisplayedRows ?? 0) }} Rows) @endif</div>
        <table>
            @if ($report === 'booking')
                <thead><tr><th>Code</th><th>Date</th><th>Contact</th><th>Hall</th><th>Hours</th><th>Due</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td><strong>{{ $row['code'] }}</strong></td>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['contact'] }}</td>
                            <td>{{ $row['hall'] }}</td>
                            <td>{{ number_format($row['hours'], 2) }}</td>
                            <td>PHP {{ number_format($row['amount'], 2) }}</td>
                            <td>
                                <span class="{{ $row['status_class'] === 'ar-tag-good' ? 'status-good' : ($row['status_class'] === 'ar-tag-bad' ? 'status-bad' : 'status-warn') }}">
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No booking records found.</td></tr>
                    @endforelse
                </tbody>
            @elseif ($report === 'collection')
                <thead><tr><th>OR Number</th><th>Date</th><th>Event</th><th>Amount</th><th>Status</th><th>Recorded By</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td><strong>{{ $row['or_number'] }}</strong></td>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['event'] }}</td>
                            <td>PHP {{ number_format($row['amount'], 2) }}</td>
                            <td>
                                <span class="{{ $row['status_class'] === 'ar-tag-good' ? 'status-good' : ($row['status_class'] === 'ar-tag-bad' ? 'status-bad' : 'status-warn') }}">
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                            <td>{{ $row['recorded_by'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No collection records found.</td></tr>
                    @endforelse
                </tbody>
            @else
                <thead><tr><th>Date</th><th>Event</th><th>Time Needed</th><th>Supplies</th><th>Status</th><th>Requested By</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['event'] }}</td>
                            <td>{{ $row['time_needed'] }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($row['supplies'], 120) }}</td>
                            <td>
                                <span class="{{ $row['status_class'] === 'ar-tag-good' ? 'status-good' : ($row['status_class'] === 'ar-tag-bad' ? 'status-bad' : 'status-warn') }}">
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                            <td>{{ $row['requested_by'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No supplies records found.</td></tr>
                    @endforelse
                </tbody>
            @endif
        </table>

        <p class="note">
            This report is printable and optimized for saving as PDF from browser print settings.
        </p>
    </div>
</body>
</html>
