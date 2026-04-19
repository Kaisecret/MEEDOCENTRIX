<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cemetery Report</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        body { margin:0; font-family: "Segoe UI", Arial, sans-serif; color:#0f172a; }
        .sheet { max-width: 1000px; margin: 0 auto; }
        .head { border: 1px solid #cbd5e1; border-radius: 10px; padding: 14px 16px; background: #f8fafc; margin-bottom: 10px; }
        .head h1 { margin: 0 0 6px; font-size: 22px; }
        .meta { font-size: 12px; color:#475569; line-height: 1.5; }
        .grid { display:grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap:8px; margin-bottom: 10px; }
        .kpi { border:1px solid #cbd5e1; border-radius: 8px; padding:8px 10px; }
        .kpi span { display:block; font-size:11px; text-transform: uppercase; color:#64748b; font-weight:700; margin-bottom: 3px; letter-spacing: .04em; }
        .kpi strong { font-size:15px; }
        .section-title { margin: 12px 0 6px; font-size: 14px; font-weight: 800; }
        table { width:100%; border-collapse: collapse; font-size: 11px; }
        th { background: #e2e8f0; color:#0f172a; text-transform: uppercase; letter-spacing:.04em; font-size: 10px; text-align: left; padding: 6px; border:1px solid #cbd5e1; }
        td { padding:6px; border:1px solid #e2e8f0; vertical-align: top; }
        .status-paid { color:#047857; font-weight:700; }
        .status-unpaid { color:#b91c1c; font-weight:700; }
        .status-partial { color:#1d4ed8; font-weight:700; }
        .status-overdue { color:#92400e; font-weight:700; }
        .note { margin-top: 8px; font-size: 11px; color:#475569; }
    </style>
</head>
<body>
    @php
        $selectedSiteName = 'All Cemeteries';
        if ((int) $selectedSiteId > 0) {
            $selectedSiteName = (string) ($sites->firstWhere('id', (int) $selectedSiteId)?->site_name ?? 'Selected Cemetery');
        }
        $dateRangeLabel = 'All Dates';
        if ($dateFrom !== '' && $dateTo !== '') {
            $dateRangeLabel = $dateFrom . ' to ' . $dateTo;
        } elseif ($dateFrom !== '') {
            $dateRangeLabel = 'From ' . $dateFrom;
        } elseif ($dateTo !== '') {
            $dateRangeLabel = 'Until ' . $dateTo;
        }
    @endphp

    <div class="sheet">
        <section class="head">
            <h1>Cemetery Reports Summary</h1>
            <div class="meta">
                Cemetery: <strong>{{ $selectedSiteName }}</strong><br>
                Date Range: <strong>{{ $dateRangeLabel }}</strong><br>
                Generated: {{ $generatedAt->format('F d, Y h:i A') }}<br>
                Section row limit: {{ number_format((int) $pdfMaxRows) }} row(s) each
            </div>
        </section>

        <section class="grid">
            <div class="kpi"><span>Occupant Records</span><strong>{{ number_format((int) $summary['occupant_total']) }}</strong></div>
            <div class="kpi"><span>Service Logs</span><strong>{{ number_format((int) $summary['service_total']) }}</strong></div>
            <div class="kpi"><span>Transactions</span><strong>{{ number_format((int) $summary['transaction_total']) }}</strong></div>
            <div class="kpi"><span>Payments</span><strong>{{ number_format((int) $summary['payment_total']) }}</strong></div>
            <div class="kpi"><span>Total Amount Due</span><strong>PHP {{ number_format((float) $summary['amount_due_total'], 2) }}</strong></div>
            <div class="kpi"><span>Total Collected</span><strong>PHP {{ number_format((float) $summary['amount_collected_total'], 2) }}</strong></div>
            <div class="kpi"><span>Overdue Maintenance</span><strong>{{ number_format((int) $summary['overdue_maintenance_total']) }}</strong></div>
            <div class="kpi"><span>Overdue Payments</span><strong>{{ number_format((int) $summary['overdue_payment_total']) }}</strong></div>
        </section>

        <div class="section-title">Occupant Maintenance Report</div>
        <table>
            <thead><tr><th>Record No.</th><th>Cemetery</th><th>Deceased</th><th>Niche/Lot</th><th>Contact</th><th>Maintenance</th><th>Coverage End</th></tr></thead>
            <tbody>
            @forelse($occupants as $record)
                <tr>
                    <td>{{ $record->record_no }}</td>
                    <td>{{ $record->site?->site_name ?: '-' }}</td>
                    <td>{{ $record->deceased_name }}</td>
                    <td>{{ $record->plot?->plot_reference ?: '-' }}</td>
                    <td>{{ $record->contact?->contact_person ?: '-' }}</td>
                    <td>{{ strtoupper((string) $record->maintenance_fee_status) }}</td>
                    <td>{{ optional($record->coverage_end_date)->format('Y-m-d') ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No occupant data for selected filter.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="section-title">Service Logs Report</div>
        <table>
            <thead><tr><th>Service Date</th><th>Cemetery</th><th>Service Type</th><th>Deceased</th><th>Status</th><th>Fee</th></tr></thead>
            <tbody>
            @forelse($services as $service)
                <tr>
                    <td>{{ optional($service->service_date)->format('Y-m-d') ?: '-' }}</td>
                    <td>{{ $service->site?->site_name ?: '-' }}</td>
                    <td>{{ $service->serviceType?->type_name ?: '-' }}</td>
                    <td>{{ $service->deceased_name ?: '-' }}</td>
                    <td>{{ strtoupper((string) $service->status) }}</td>
                    <td>PHP {{ number_format((float) ($service->suggested_fee_total ?? $service->service_fee ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No service data for selected filter.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="section-title">Cemetery Transactions Report</div>
        <table>
            <thead><tr><th>Transaction No.</th><th>Date</th><th>Cemetery</th><th>Type</th><th>Deceased</th><th>Amount Due</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_no }}</td>
                    <td>{{ optional($transaction->transaction_date)->format('Y-m-d') ?: '-' }}</td>
                    <td>{{ $transaction->site?->site_name ?: '-' }}</td>
                    <td>{{ $transaction->transactionType?->type_name ?: '-' }}</td>
                    <td>{{ $transaction->deceased_name }}</td>
                    <td>PHP {{ number_format((float) $transaction->amount_due, 2) }}</td>
                    <td>{{ strtoupper((string) $transaction->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No transaction data for selected filter.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="section-title">Payment Collection Report</div>
        <table>
            <thead><tr><th>Payment Ref.</th><th>Transaction Ref.</th><th>Cemetery</th><th>OR No.</th><th>Payment Date</th><th>Amount Paid</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_no }}</td>
                    <td>{{ $payment->transaction?->transaction_no ?: '-' }}</td>
                    <td>{{ $payment->transaction?->site?->site_name ?: '-' }}</td>
                    <td>{{ $payment->official_receipt_no ?: '-' }}</td>
                    <td>{{ optional($payment->payment_date)->format('Y-m-d') ?: '-' }}</td>
                    <td>PHP {{ number_format((float) $payment->amount_paid, 2) }}</td>
                    <td>
                        @if($payment->payment_status === 'paid')
                            <span class="status-paid">PAID</span>
                        @elseif($payment->payment_status === 'partial')
                            <span class="status-partial">PARTIAL</span>
                        @elseif($payment->payment_status === 'overdue')
                            <span class="status-overdue">OVERDUE</span>
                        @else
                            <span class="status-unpaid">UNPAID</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No payment data for selected filter.</td></tr>
            @endforelse
            </tbody>
        </table>

        <p class="note">This report is printable and optimized for saving as PDF from browser print settings.</p>
    </div>
</body>
</html>
