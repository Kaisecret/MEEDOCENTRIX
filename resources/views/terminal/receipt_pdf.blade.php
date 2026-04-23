@php
    $log = $payment->parkingLog;
    $vehicle = $log?->vehicle;
    $type = $vehicle?->type;
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Terminal Receipt {{ $payment->or_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .sheet { border: 1px solid #cbd5e1; padding: 18px; }
        .head { display: table; width: 100%; margin-bottom: 12px; }
        .head-left, .head-right { display: table-cell; vertical-align: top; }
        .head-right { text-align: right; }
        h1 { margin: 0; font-size: 20px; }
        .sub { color: #475569; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { width: 170px; background: #f8fafc; }
        .total { font-size: 18px; font-weight: 700; }
        .foot { margin-top: 12px; font-size: 11px; color: #475569; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="head">
            <div class="head-left">
                <h1>Terminal Fee Collection Office</h1>
                <div class="sub">Parking Fee Official Receipt</div>
            </div>
            <div class="head-right">
                <div><strong>OR No:</strong> {{ $payment->or_number }}</div>
                <div><strong>Date:</strong> {{ optional($payment->payment_date)->format('m/d/Y h:i A') }}</div>
            </div>
        </div>

        <table>
            <tr><th>Log Number</th><td>{{ $log?->log_number ?? '-' }}</td></tr>
            <tr><th>Plate Number</th><td>{{ $vehicle?->plate_number ?? '-' }}</td></tr>
            <tr><th>Operator</th><td>{{ $vehicle?->operator_name ?: '-' }}</td></tr>
            <tr><th>Vehicle Type</th><td>{{ $type?->name ?? '-' }}</td></tr>
            <tr><th>Entry Time</th><td>{{ optional($log?->entry_at)->format('m/d/Y h:i A') ?: '-' }}</td></tr>
            <tr><th>Exit Time</th><td>{{ optional($log?->exit_at)->format('m/d/Y h:i A') ?: '-' }}</td></tr>
            <tr><th>Billed Hours</th><td>{{ number_format((float) $payment->billed_hours_snapshot, 2) }}</td></tr>
            <tr><th>Rate</th><td>PHP {{ number_format((float) $payment->parking_rate_snapshot, 2) }} / hr</td></tr>
            <tr><th>Remarks</th><td>{{ $payment->remarks ?: '-' }}</td></tr>
            <tr><th>Total Paid</th><td class="total">PHP {{ number_format((float) $payment->paid_amount, 2) }}</td></tr>
        </table>

        <div class="foot">
            Recorded by: {{ $payment->recordedBy?->name ?? 'Terminal Personnel' }}
        </div>
    </div>
</body>
</html>

