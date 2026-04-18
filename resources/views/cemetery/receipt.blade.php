<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cemetery Payment Receipt</title>
    <style>
        @page { size: 80mm auto; margin: 6mm; }
        body { margin:0; font-family: "Courier New", monospace; color:#111; background:#f5f5f5; }
        .wrap { max-width: 360px; margin: 18px auto; background:#fff; border:1px solid #e5e7eb; border-radius: 10px; padding: 16px; }
        .actions { display:flex; gap:8px; justify-content:center; padding: 8px 10px 0; }
        .btn { border:1px solid #cbd5e1; border-radius: 8px; padding:8px 10px; background:#fff; font-size: 12px; font-weight: 700; cursor:pointer; }
        .btn-primary { background:#0f5fa8; border-color:#0f5fa8; color:#fff; }
        .header { text-align:center; margin-bottom: 10px; }
        .header h1 { font-size: 16px; margin:0; letter-spacing: .5px; }
        .header p { margin:4px 0; font-size: 12px; }
        .meta { font-size: 12px; line-height: 1.5; margin-top: 10px; }
        .divider { border-top: 1px dashed #9ca3af; margin: 8px 0; }
        .items { font-size: 12px; }
        .items-head { display:flex; justify-content:space-between; font-weight: 700; margin-bottom: 6px; }
        .item-row { display:flex; justify-content:space-between; margin-bottom: 4px; }
        .item-left { max-width: 60%; }
        .total { display:flex; justify-content:space-between; font-size: 14px; font-weight: 800; }
        .sub { display:flex; justify-content:space-between; font-size: 12px; margin-bottom: 4px; }
        .footer { text-align:center; font-size: 12px; margin-top: 10px; }
        .status-pill { display:inline-block; font-size:11px; font-weight:800; padding:2px 8px; border-radius:999px; border:1px solid #64748b; margin-top:4px; text-transform: uppercase; }
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
        <button id="receiptCloseBtn" class="btn" type="button">Close</button>
    </div>

    <div class="wrap">
        <div class="header">
            <h1>{{ $receipt['business_name'] }}</h1>
            <p>{{ $receipt['address'] }}</p>
            <p>TIN: {{ $receipt['tin'] }}</p>
            <span class="status-pill">{{ strtoupper($receipt['status'] ?? '-') }}</span>
        </div>

        <div class="meta">
            Payment No: {{ $receipt['payment_number'] }}<br>
            Transaction No: {{ $receipt['transaction_number'] }}<br>
            Date: {{ $receipt['date'] }}<br>
            Cashier: {{ $receipt['cashier'] }}<br>
            Payer: {{ $receipt['payer_name'] }}<br>
            Deceased: {{ $receipt['deceased'] }}<br>
            Niche/Lot: {{ $receipt['plot_reference'] }}<br>
            Cemetery: {{ $receipt['cemetery'] }}<br>
            Category: {{ $receipt['category'] }}<br>
            Service: {{ $receipt['service_type'] }}
        </div>

        <div class="divider"></div>

        <div class="items">
            <div class="items-head">
                <span>Charge</span>
                <span>Qty</span>
                <span>Total</span>
            </div>
            @forelse ($receipt['charges'] as $line)
                <div class="item-row">
                    <span class="item-left">{{ $line['item'] }}</span>
                    <span>{{ number_format((float) $line['qty'], 2) }}</span>
                    <span>{{ number_format((float) $line['total'], 2) }}</span>
                </div>
            @empty
                <div class="item-row">
                    <span class="item-left">No charges</span>
                    <span>0.00</span>
                    <span>0.00</span>
                </div>
            @endforelse
        </div>

        <div class="divider"></div>

        <div class="sub"><span>Amount Due:</span><span>{{ number_format((float) $receipt['amount_due'], 2) }}</span></div>
        <div class="sub"><span>Paid Before This Payment:</span><span>{{ number_format((float) ($receipt['paid_before_this'] ?? 0), 2) }}</span></div>
        <div class="sub"><span>Current Balance (Before):</span><span>{{ number_format((float) ($receipt['balance_before_payment'] ?? 0), 2) }}</span></div>
        <div class="sub"><span>Deducted Today:</span><span>{{ number_format((float) $receipt['amount_paid_this'], 2) }}</span></div>
        <div class="sub"><span>Total Paid To Date:</span><span>{{ number_format((float) $receipt['total_paid'], 2) }}</span></div>
        <div class="sub"><span>Remaining Balance (After):</span><span>{{ number_format((float) ($receipt['balance_after_payment'] ?? $receipt['balance']), 2) }}</span></div>

        <div class="divider"></div>

        <div class="total">
            <span>Amount Paid:</span>
            <span>PHP {{ number_format((float) $receipt['amount_paid_this'], 2) }}</span>
        </div>

        <div class="divider"></div>

        <div class="footer">
            Thank you! Please keep this receipt.
        </div>
    </div>

    <script>
        (() => {
            const printBtn = document.getElementById('receiptPrintBtn');
            const closeBtn = document.getElementById('receiptCloseBtn');

            if (printBtn) {
                printBtn.addEventListener('click', () => window.print());
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', () => window.close());
            }

        })();
    </script>
</body>
</html>
