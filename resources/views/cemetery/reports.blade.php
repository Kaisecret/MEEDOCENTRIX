@extends('layouts.app')

@section('content')
<style>
    .crp-page { display:grid; gap:16px; font-family:'Inter',system-ui,sans-serif; color:#334155; }
    .crp-hero {
        border:1px solid #dbe6f0; border-radius:12px; padding:1.1rem 1.3rem;
        background:linear-gradient(120deg,#0f5f8f,#1f86ba); color:#fff;
        display:grid; grid-template-columns:1fr auto; gap:10px; align-items:center;
    }
    .crp-hero h2 { margin:0 0 .2rem; font-size:1.35rem; }
    .crp-hero p { margin:0; opacity:.92; font-size:.9rem; }
    .crp-btn {
        min-height:38px; border-radius:9px; border:1px solid rgba(255,255,255,.45);
        background:rgba(255,255,255,.2); color:#fff; font-weight:700; padding:0 .85rem;
        display:inline-flex; align-items:center; gap:8px;
    }
    .crp-btn:hover { background:rgba(255,255,255,.3); }
    .crp-card { border:1px solid #e2e8f0; border-radius:12px; background:#fff; overflow:hidden; box-shadow:0 1px 3px rgba(15,23,42,.06); }
    .crp-card-head { padding:1rem 1.1rem; border-bottom:1px solid #e2e8f0; background:#f8fafc; }
    .crp-card-head h3 { margin:0; font-size:1rem; color:#0f172a; }
    .crp-filter-grid { display:grid; grid-template-columns:1.4fr 1fr 1fr auto; gap:8px; }
    .crp-control { min-height:38px; border:1px solid #cbd5e1; border-radius:9px; padding:.45rem .65rem; font-size:.86rem; width:100%; }
    .crp-control:focus { outline:none; border-color:#155f8f; box-shadow:0 0 0 3px rgba(21,95,143,.12); }
    .crp-actions { display:inline-flex; gap:6px; }
    .crp-stat-grid { display:grid; gap:10px; grid-template-columns:repeat(4,minmax(0,1fr)); }
    .crp-stat { border:1px solid #e2e8f0; border-radius:10px; background:#fff; padding:.7rem .8rem; }
    .crp-stat span { font-size:.74rem; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:.03em; }
    .crp-stat strong { display:block; margin-top:.28rem; color:#0f172a; font-size:1rem; }
    .crp-section { border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; background:#fff; }
    .crp-section-head { border-bottom:1px solid #e2e8f0; background:#f8fafc; padding:.9rem 1rem; }
    .crp-section-head h4 { margin:0; color:#0f172a; font-size:.95rem; }
    .crp-table-wrap { overflow:auto; }
    .crp-table { width:100%; border-collapse:collapse; min-width:980px; }
    .crp-table th {
        background:#eef5fb; border-bottom:1px solid #dce5ef; color:#12314d; text-align:left;
        font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; padding:.68rem;
    }
    .crp-table td { border-bottom:1px solid #eef2f7; padding:.68rem; font-size:.84rem; color:#334155; vertical-align:top; }
    .crp-badge {
        display:inline-flex; align-items:center; border:1px solid; border-radius:999px; padding:.2rem .5rem;
        font-size:.7rem; font-weight:700; text-transform:uppercase;
    }
    .crp-badge-paid { border-color:#86efac; background:#ecfdf5; color:#065f46; }
    .crp-badge-unpaid { border-color:#fecaca; background:#fff1f2; color:#b91c1c; }
    .crp-badge-partial { border-color:#bfdbfe; background:#eff6ff; color:#1d4ed8; }
    .crp-badge-overdue { border-color:#fde68a; background:#fffbeb; color:#92400e; }
    @media (max-width:1100px) {
        .crp-hero { grid-template-columns:1fr; }
        .crp-filter-grid { grid-template-columns:1fr 1fr; }
        .crp-actions { grid-column:1 / -1; }
        .crp-stat-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media (max-width:680px) {
        .crp-filter-grid, .crp-stat-grid { grid-template-columns:1fr; }
    }
    @media print {
        .sidebar, .topbar, .crp-hero button, .crp-actions { display:none !important; }
        .main-wrapper, .content-area { margin:0 !important; padding:0 !important; }
        .crp-page { gap:10px; }
        .crp-table th, .crp-table td { font-size:10px; padding:6px; }
    }
</style>

<div class="crp-page" data-server-rendered-page="cemetery_reports" data-page-title="Cemetery Reports">
    <section class="crp-hero">
        <div>
            <h2>Cemetery Reports</h2>
            <p>Generate print-ready official summaries for occupants, services, transactions, and payments.</p>
        </div>
        <button type="button" class="crp-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Report</button>
    </section>

    <section class="crp-card">
        <div class="crp-card-head"><h3>Report Filters</h3></div>
        <div style="padding:1rem;">
            <form method="GET" action="{{ route('cemetery.reports') }}" class="crp-filter-grid">
                <select class="crp-control" name="cemetery_site_id">
                    <option value="">All Cemeteries</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" @selected((string) $selectedSiteId === (string) $site->id)>{{ $site->site_name }}</option>
                    @endforeach
                </select>
                <input class="crp-control" type="date" name="date_from" value="{{ $dateFrom }}">
                <input class="crp-control" type="date" name="date_to" value="{{ $dateTo }}">
                <div class="crp-actions">
                    <button class="crp-control" style="background:#155f8f; color:#fff; border-color:#155f8f; font-weight:700;" type="submit"><i class="fa-solid fa-filter"></i> Apply</button>
                    <a class="crp-control" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none; color:#334155;" href="{{ route('cemetery.reports') }}"><i class="fa-solid fa-rotate-left"></i>&nbsp;Reset</a>
                </div>
            </form>
        </div>
    </section>

    <section class="crp-stat-grid">
        <article class="crp-stat"><span>Occupant Records</span><strong>{{ number_format((int) $summary['occupant_total']) }}</strong></article>
        <article class="crp-stat"><span>Service Logs</span><strong>{{ number_format((int) $summary['service_total']) }}</strong></article>
        <article class="crp-stat"><span>Transactions</span><strong>{{ number_format((int) $summary['transaction_total']) }}</strong></article>
        <article class="crp-stat"><span>Payments</span><strong>{{ number_format((int) $summary['payment_total']) }}</strong></article>
        <article class="crp-stat"><span>Total Amount Due</span><strong>PHP {{ number_format((float) $summary['amount_due_total'], 2) }}</strong></article>
        <article class="crp-stat"><span>Total Collected</span><strong>PHP {{ number_format((float) $summary['amount_collected_total'], 2) }}</strong></article>
        <article class="crp-stat"><span>Overdue Maintenance</span><strong>{{ number_format((int) $summary['overdue_maintenance_total']) }}</strong></article>
        <article class="crp-stat"><span>Overdue Payments</span><strong>{{ number_format((int) $summary['overdue_payment_total']) }}</strong></article>
    </section>

    <section class="crp-section">
        <div class="crp-section-head"><h4>Occupant Maintenance Report</h4></div>
        <div class="crp-table-wrap">
            <table class="crp-table">
                <thead><tr><th>Record No.</th><th>Cemetery</th><th>Deceased</th><th>Niche/Lot</th><th>Contact</th><th>Maintenance Status</th><th>Coverage End</th></tr></thead>
                <tbody>
                    @forelse($occupants as $record)
                        <tr>
                            <td>{{ $record->record_no }}</td>
                            <td>{{ $record->site?->site_name ?: '-' }}</td>
                            <td>{{ $record->deceased_name }}</td>
                            <td>{{ $record->plot?->plot_reference ?: '-' }}</td>
                            <td>{{ $record->contact?->contact_person ?: '-' }}</td>
                            <td><span class="crp-badge crp-badge-{{ $record->maintenance_fee_status }}">{{ strtoupper($record->maintenance_fee_status) }}</span></td>
                            <td>{{ optional($record->coverage_end_date)->format('Y-m-d') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; padding:1rem;">No occupant data for selected filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="crp-section">
        <div class="crp-section-head"><h4>Cemetery Transactions Report</h4></div>
        <div class="crp-table-wrap">
            <table class="crp-table">
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
                            <td>{{ strtoupper($transaction->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; padding:1rem;">No transaction data for selected filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="crp-section">
        <div class="crp-section-head"><h4>Payment Collection Report</h4></div>
        <div class="crp-table-wrap">
            <table class="crp-table">
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
                            <td><span class="crp-badge crp-badge-{{ $payment->payment_status }}">{{ strtoupper($payment->payment_status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; padding:1rem;">No payment data for selected filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
