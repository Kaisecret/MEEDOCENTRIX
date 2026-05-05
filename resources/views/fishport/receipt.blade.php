<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fishport Receipt</title>
    <style>
        @page { size: 80mm auto; margin: 6mm; }
        body { margin:0; font-family: "Courier New", monospace; color:#111; background:#f5f5f5; }
        .wrap { max-width: 360px; margin: 18px auto; background:#fff; border:1px solid #e5e7eb; border-radius: 10px; padding: 16px; }
        .actions { display:flex; gap:8px; justify-content:center; padding: 8px 10px 0; }
        .btn { border:1px solid #cbd5e1; border-radius: 8px; padding:8px 10px; background:#fff; font-size: 12px; font-weight: 700; cursor:pointer; }
        .btn-primary { background:#0f5fa8; border-color:#0f5fa8; color:#fff; }
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
        @media print {
            body { background:#fff; }
            .actions { display:none; }
            .wrap { border:none; margin:0 auto; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button id="receiptPrintBtn" class="btn btn-primary" type="button">Print</button>
        <button id="receiptSavePdfBtn" class="btn" type="button">Save PDF</button>
        <button id="receiptCloseBtn" class="btn" type="button">Close</button>
    </div>

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

    <script id="fishportReceiptPayload" type="application/json">
        {!! json_encode([
            'pdfUrl' => $pdfUrl ?? '#',
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

    <script>
        (() => {
            const payloadElement = document.getElementById('fishportReceiptPayload');
            let payload = { pdfUrl: '#' };

            if (payloadElement) {
                try {
                    const parsed = JSON.parse(payloadElement.textContent || '{}');
                    payload = { ...payload, ...parsed };
                } catch (error) {
                    console.error('Failed to parse fishport receipt payload.', error);
                }
            }

            const printBtn = document.getElementById('receiptPrintBtn');
            const savePdfBtn = document.getElementById('receiptSavePdfBtn');
            const closeBtn = document.getElementById('receiptCloseBtn');

            if (printBtn) {
                printBtn.addEventListener('click', () => window.print());
            }

            if (savePdfBtn) {
                savePdfBtn.addEventListener('click', () => {
                    window.location.href = payload.pdfUrl || '#';
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', () => window.close());
            }
        })();
    </script>
</body>
</html>
