@extends('layouts.app')

@section('content')
<style>
    .cdb-page { display:grid; gap:16px; color:#334155; font-family:'Inter',system-ui,sans-serif; }
    .cdb-hero {
        border-radius:12px; border:1px solid #dbe6f0;
        background:linear-gradient(120deg,#155f8f,#1f86ba); color:#fff;
        padding:1.1rem 1.3rem; box-shadow:0 10px 20px rgba(15,23,42,.14);
        display:grid; grid-template-columns:1fr auto; gap:10px; align-items:center;
    }
    .cdb-hero h2 { margin:0 0 .25rem; font-size:1.35rem; font-weight:700; }
    .cdb-hero p { margin:0; color:rgba(255,255,255,.9); font-size:.9rem; }
    .cdb-hero-actions { display:inline-flex; gap:8px; flex-wrap:wrap; }
    .cdb-hero-btn {
        min-height:38px; border-radius:9px; border:1px solid rgba(255,255,255,.45);
        background:rgba(255,255,255,.18); color:#fff; text-decoration:none;
        padding:0 .82rem; font-size:.84rem; font-weight:700; display:inline-flex; align-items:center; gap:8px;
    }
    .cdb-hero-btn:hover { background:rgba(255,255,255,.28); color:#fff; }
    .cdb-stats { display:grid; gap:10px; grid-template-columns:repeat(4,minmax(0,1fr)); }
    .cdb-stat { border:1px solid #e2e8f0; border-radius:11px; background:#fff; box-shadow:0 1px 3px rgba(15,23,42,.06); padding:.72rem .82rem; }
    .cdb-stat span { color:#64748b; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; }
    .cdb-stat strong { display:block; margin-top:.3rem; color:#0f172a; font-size:1.06rem; }
    .cdb-card { border:1px solid #e2e8f0; border-radius:12px; background:#fff; box-shadow:0 1px 3px rgba(15,23,42,.06); overflow:hidden; }
    .cdb-card-head { border-bottom:1px solid #e2e8f0; background:#f8fafc; padding:.95rem 1.1rem; display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .cdb-card-head h3 { margin:0; font-size:1.03rem; color:#0f172a; }
    .cdb-link-btn {
        min-height:34px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; color:#155f8f;
        text-decoration:none; padding:0 .75rem; font-size:.82rem; font-weight:700; display:inline-flex; align-items:center; gap:6px;
    }
    .cdb-link-btn:hover { background:#f8fafc; color:#0f4d74; }
    .cdb-table-wrap { overflow:auto; }
    .cdb-table { width:100%; border-collapse:collapse; min-width:980px; }
    .cdb-table th {
        background:#eef5fb; border-bottom:1px solid #dce5ef; color:#12314d; text-align:left;
        font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; padding:.7rem;
    }
    .cdb-table td { border-bottom:1px solid #eef2f7; padding:.7rem; font-size:.84rem; color:#334155; vertical-align:top; }
    .cdb-badge {
        display:inline-flex; align-items:center; border-radius:999px; border:1px solid; padding:.2rem .52rem;
        font-size:.71rem; font-weight:700; text-transform:uppercase;
    }
    .cdb-badge-pending { border-color:#fde68a; background:#fffbeb; color:#92400e; }
    .cdb-badge-paid { border-color:#86efac; background:#ecfdf5; color:#065f46; }
    .cdb-badge-partial { border-color:#bfdbfe; background:#eff6ff; color:#1d4ed8; }
    .cdb-badge-cancelled { border-color:#fecaca; background:#fff1f2; color:#9f1239; }
    .cdb-empty { text-align:center; padding:1.3rem; color:#64748b; font-size:.9rem; }
    @media (max-width:1120px) {
        .cdb-hero { grid-template-columns:1fr; }
        .cdb-stats { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media (max-width:680px) {
        .cdb-stats { grid-template-columns:1fr; }
    }
</style>

<div class="cdb-page" data-server-rendered-page="dashboard" data-page-title="Cemetery Dashboard">
    <section class="cdb-hero">
        <div>
            <h2>Cemetery Operations Dashboard</h2>
            <p>Live office monitoring for occupant, service, transaction, and payment records.</p>
        </div>
        <div class="cdb-hero-actions">
            <a class="cdb-hero-btn" href="{{ route('cemetery.records') }}"><i class="fa-solid fa-users"></i> Occupant Records</a>
            <a class="cdb-hero-btn" href="{{ route('cemetery.services') }}"><i class="fa-solid fa-book-journal-whills"></i> Service Logs</a>
            <a class="cdb-hero-btn" href="{{ route('cemetery.transactions') }}"><i class="fa-solid fa-receipt"></i> Transactions</a>
            <a class="cdb-hero-btn" href="{{ route('cemetery.payments') }}"><i class="fa-solid fa-cash-register"></i> Payments</a>
        </div>
    </section>

    <section class="cdb-stats">
        <article class="cdb-stat"><span>Total Occupants</span><strong>{{ number_format((int) $summary['total_occupants']) }}</strong></article>
        <article class="cdb-stat"><span>Occupied Niches/Lots</span><strong>{{ number_format((int) $summary['occupied_plots']) }}</strong></article>
        <article class="cdb-stat"><span>Available Niches/Lots</span><strong>{{ number_format((int) $summary['available_plots']) }}</strong></article>
        <article class="cdb-stat"><span>Services Today</span><strong>{{ number_format((int) $summary['services_today']) }}</strong></article>
        <article class="cdb-stat"><span>Transactions Today</span><strong>{{ number_format((int) $summary['transactions_today']) }}</strong></article>
        <article class="cdb-stat"><span>Payments Today</span><strong>PHP {{ number_format((float) $summary['payments_today'], 2) }}</strong></article>
        <article class="cdb-stat"><span>Total Collected</span><strong>PHP {{ number_format((float) $summary['total_collected'], 2) }}</strong></article>
        <article class="cdb-stat"><span>Overdue Maintenance</span><strong>{{ number_format((int) $summary['overdue_maintenance']) }}</strong></article>
    </section>

    <section class="cdb-card">
        <div class="cdb-card-head">
            <h3>Recent Cemetery Transactions</h3>
            <a class="cdb-link-btn" href="{{ route('cemetery.transactions') }}"><i class="fa-solid fa-arrow-right"></i> View All</a>
        </div>
        <div class="cdb-table-wrap">
            <table class="cdb-table">
                <thead>
                    <tr>
                        <th>Transaction No.</th>
                        <th>Date</th>
                        <th>Cemetery</th>
                        <th>Type</th>
                        <th>Deceased</th>
                        <th>Amount Due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $transaction)
                        <tr>
                            <td><strong>{{ $transaction->transaction_no }}</strong></td>
                            <td>{{ optional($transaction->transaction_date)->format('Y-m-d') ?: '-' }}</td>
                            <td>{{ $transaction->site?->site_name ?: '-' }}</td>
                            <td>{{ $transaction->transactionType?->type_name ?: '-' }}</td>
                            <td>{{ $transaction->deceased_name }}</td>
                            <td><strong>PHP {{ number_format((float) $transaction->amount_due, 2) }}</strong></td>
                            <td><span class="cdb-badge cdb-badge-{{ $transaction->status }}">{{ strtoupper($transaction->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="cdb-empty">No transaction records yet. Start by creating records in Occupant or Service tabs, then post a transaction.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
