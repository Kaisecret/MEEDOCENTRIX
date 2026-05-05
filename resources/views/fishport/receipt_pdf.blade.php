<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fishport Receipt</title>
    <style>
        @page { size: 80mm auto; margin: 6mm; }
        body { margin:0; font-family: "Courier New", monospace; color:#111; }
        .wrap { max-width: 360px; margin: 0 auto; padding: 6px; }
        .header { text-align:center; margin-bottom: 10px; }
        .header h1 { font-size: 18px; margin:0; letter-spacing: .5px; }
        .header p { margin:4px 0; font-size: 12px; }
        .meta { font-size: 12px; line-height: 1.5; margin-top: 10px; }
        .divider { border-top: 1px dashed #9ca3af; margin: 8px 0; }
        .items { font-size: 12px; }
        .receipt-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .receipt-table th, .receipt-table td { padding: 4px 6px; font-size: 12px; }
        .receipt-table th { border-bottom: 1px dashed #9ca3af; text-align: left; font-weight: 700; }
        .receipt-table th:nth-child(2), .receipt-table td:nth-child(2) { width: 52px; text-align: center; }
        .receipt-table th:nth-child(3), .receipt-table td:nth-child(3) { width: 88px; text-align: right; }
        .receipt-table td:first-child { word-break: break-word; }
        .total { display:flex; justify-content:space-between; font-size: 14px; font-weight: 800; }
        .footer { text-align:center; font-size: 12px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <h1>{{ $receipt['business_name'] ?? 'Fishport Data Management' }}</h1>
            <p>{{ $receipt['address'] ?? 'San Jose, Antique' }}</p>
            <p>TIN: {{ $receipt['tin'] ?? 'N/A' }}</p>
        </div>

        <div class="meta">
            Payment No: {{ $receipt['payment_number'] ?? '-' }}<br>
            Log No: {{ $receipt['log_number'] ?? '-' }}<br>
            Date: {{ $receipt['date'] ?? '-' }}<br>
            Handled By: {{ $receipt['cashier'] ?? '-' }}<br>
            Payer: {{ $receipt['payer_name'] ?? '-' }}<br>
            Vessel: {{ $receipt['vessel'] ?? '-' }}<br>
            Origin: {{ $receipt['origin'] ?? '-' }} {{ $receipt['arr_dep'] ? '(' . $receipt['arr_dep'] . ')' : '' }}
        </div>

        <div class="divider"></div>

        <div class="items">
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
            @forelse(($receipt['charges'] ?? []) as $line)
                    <tr>
                        <td>{{ $line['item'] ?? 'Charge' }}</td>
                        <td>{{ number_format((float) ($line['qty'] ?? 0), 2) }}</td>
                        <td>{{ number_format((float) ($line['total'] ?? 0), 2) }}</td>
                    </tr>
            @empty
                    <tr>
                        <td>No charges</td>
                        <td>0.00</td>
                        <td>0.00</td>
                    </tr>
            @endforelse
                </tbody>
            </table>
        </div>

        <div class="divider"></div>

        <div class="total">
            <span>Total Due:</span>
            <span>{{ number_format((float) ($receipt['total_due'] ?? 0), 2) }}</span>
        </div>

        <div class="divider"></div>

        <div class="footer">
            Safe voyage to your vessel. Thank you.
        </div>
    </div>
</body>
</html>
