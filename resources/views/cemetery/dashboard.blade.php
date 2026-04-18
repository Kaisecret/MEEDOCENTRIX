@extends('layouts.app')

@section('content')
<style>
    .db {
        --db-primary:#155f8f;
        --db-primary-dk:#0f4b73;
        --db-green:#047857;
        --db-red:#dc2626;
        --db-amber:#d97706;
        --db-border:#e2e8f0;
        --db-soft:#f8fafc;
        --db-text:#334155;
        --db-muted:#64748b;
        --db-head:#0f172a;
        font-family:'Inter',system-ui,-apple-system,sans-serif;
        color:var(--db-text);
        display:grid;
        gap:16px;
    }

    .db-hero {
        background:linear-gradient(120deg,var(--db-primary),#1f86ba);
        color:#fff;
        border-radius:12px;
        padding:1.25rem 1.4rem;
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        flex-wrap:wrap;
        box-shadow:0 8px 20px rgba(15,23,42,.16);
    }
    .db-hero h2 {
        margin:0 0 .32rem;
        font-size:1.6rem;
        font-weight:800;
        letter-spacing:-.02em;
    }
    .db-hero p {
        margin:0;
        color:rgba(255,255,255,.88);
        font-size:.92rem;
    }
    .db-hero-right {
        display:flex;
        flex-direction:column;
        align-items:flex-end;
        gap:4px;
    }
    .db-hero-date {
        font-size:.82rem;
        color:rgba(255,255,255,.74);
    }
    .db-hero-time {
        font-size:1.45rem;
        font-weight:800;
    }
    .db-hero-actions {
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        margin-top:.65rem;
    }
    .db-hero-btn {
        min-height:36px;
        border-radius:9px;
        border:1px solid rgba(255,255,255,.45);
        background:rgba(255,255,255,.18);
        color:#fff;
        text-decoration:none;
        padding:0 .8rem;
        font-size:.82rem;
        font-weight:700;
        display:inline-flex;
        align-items:center;
        gap:7px;
        transition:all .2s ease;
    }
    .db-hero-btn:hover {
        color:#fff;
        transform:translateY(-1px);
        background:rgba(255,255,255,.28);
    }

    .db-filter-card {
        border:1px solid var(--db-border);
        border-radius:12px;
        background:#fff;
        box-shadow:0 1px 3px rgba(0,0,0,.04);
        padding:14px 16px;
        display:grid;
        gap:12px;
    }
    .db-filter-row {
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }
    .db-filter-pill {
        border:1px solid #cbd5e1;
        background:#fff;
        color:#155f8f;
        border-radius:999px;
        min-height:34px;
        padding:0 14px;
        font-size:.82rem;
        font-weight:700;
        cursor:pointer;
        transition:all .2s ease;
    }
    .db-filter-pill:hover { background:#f0f7fd; }
    .db-filter-pill.is-active {
        background:#155f8f;
        border-color:#155f8f;
        color:#fff;
        box-shadow:0 4px 12px rgba(21,95,143,.18);
    }
    .db-filter-range {
        display:flex;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
    }
    .db-filter-range[hidden] { display:none !important; }
    .db-filter-input {
        min-height:36px;
        border:1px solid #cbd5e1;
        border-radius:9px;
        padding:0 .72rem;
        font-size:.86rem;
        color:#334155;
        background:#fff;
    }
    .db-filter-input:focus {
        outline:none;
        border-color:#155f8f;
        box-shadow:0 0 0 3px rgba(21,95,143,.14);
    }
    .db-filter-apply {
        border:1px solid #155f8f;
        background:#155f8f;
        color:#fff;
        border-radius:9px;
        min-height:36px;
        padding:0 .9rem;
        font-size:.84rem;
        font-weight:700;
        cursor:pointer;
    }
    .db-filter-summary { font-size:.84rem; color:#64748b; }

    .db-kpi-grid {
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:12px;
    }
    .db-kpi {
        border:1px solid var(--db-border);
        border-radius:12px;
        background:#fff;
        padding:1rem 1rem .95rem;
        display:grid;
        gap:5px;
        box-shadow:0 1px 3px rgba(0,0,0,.05);
        position:relative;
        overflow:hidden;
    }
    .db-kpi::before {
        content:'';
        position:absolute;
        left:0;
        top:0;
        width:4px;
        height:100%;
    }
    .db-kpi-blue::before { background:var(--db-primary); }
    .db-kpi-green::before { background:#10b981; }
    .db-kpi-red::before { background:var(--db-red); }
    .db-kpi-amber::before { background:#f59e0b; }
    .db-kpi-label {
        font-size:.76rem;
        font-weight:700;
        color:var(--db-muted);
        text-transform:uppercase;
        letter-spacing:.03em;
    }
    .db-kpi-value {
        font-size:1.38rem;
        font-weight:800;
        color:var(--db-head);
        line-height:1.1;
    }
    .db-kpi-sub {
        font-size:.79rem;
        color:var(--db-muted);
    }

    .db-card {
        border:1px solid var(--db-border);
        border-radius:12px;
        background:#fff;
        box-shadow:0 1px 3px rgba(15,23,42,.06);
        overflow:hidden;
    }
    .db-card-head {
        border-bottom:1px solid var(--db-border);
        padding:.94rem 1rem;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        flex-wrap:wrap;
        background:var(--db-soft);
    }
    .db-card-head h3 {
        margin:0;
        font-size:1.03rem;
        color:var(--db-head);
        display:flex;
        align-items:center;
        gap:8px;
    }
    .db-card-body { padding:1rem; }
    .db-link-btn {
        min-height:34px;
        border-radius:8px;
        border:1px solid #cbd5e1;
        background:#fff;
        color:var(--db-primary);
        text-decoration:none;
        padding:0 .74rem;
        font-size:.81rem;
        font-weight:700;
        display:inline-flex;
        align-items:center;
        gap:6px;
    }
    .db-link-btn:hover { background:#f8fafc; color:var(--db-primary-dk); }

    .db-twin {
        display:grid;
        grid-template-columns:minmax(0,1.6fr) minmax(0,1fr);
        gap:12px;
    }
    .db-chart-wrap {
        position:relative;
        width:100%;
    }
    .db-chart-wrap canvas {
        width:100% !important;
        display:block;
    }
    .db-chart-kpi {
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:8px;
        margin-top:10px;
    }
    .db-chart-kpi-item {
        border:1px solid var(--db-border);
        border-radius:9px;
        padding:.55rem .62rem;
        background:#f8fbff;
    }
    .db-chart-kpi-item span {
        display:block;
        color:var(--db-muted);
        font-size:.75rem;
        font-weight:700;
        text-transform:uppercase;
    }
    .db-chart-kpi-item strong {
        color:var(--db-head);
        font-size:1rem;
        font-weight:800;
    }
    .db-actions-grid {
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:10px;
    }
    .db-action-card {
        border:1px solid var(--db-border);
        border-radius:10px;
        padding:.85rem .9rem;
        text-decoration:none;
        color:inherit;
        display:grid;
        gap:4px;
        transition:all .2s ease;
        background:#fff;
    }
    .db-action-card:hover {
        transform:translateY(-1px);
        border-color:#bfdbfe;
        background:#f8fbff;
    }
    .db-action-card strong { color:var(--db-head); font-size:.9rem; }
    .db-action-card span { color:var(--db-muted); font-size:.8rem; }

    .db-progress-wrap { display:grid; gap:8px; }
    .db-progress-label {
        display:flex;
        justify-content:space-between;
        align-items:center;
        font-size:.83rem;
    }
    .db-progress-label span:first-child { color:var(--db-muted); font-weight:600; }
    .db-progress-label span:last-child { color:var(--db-head); font-weight:700; }
    .db-progress-bar-bg {
        height:8px;
        background:#e2e8f0;
        border-radius:999px;
        overflow:hidden;
    }
    .db-progress-bar-fill {
        height:100%;
        border-radius:999px;
        transition:width .6s ease;
    }
    .db-progress-bar-fill.green { background:#10b981; }
    .db-progress-bar-fill.blue { background:var(--db-primary); }
    .db-progress-bar-fill.red { background:var(--db-red); }

    .db-table-wrap { overflow:auto; }
    .db-table {
        width:100%;
        border-collapse:collapse;
        min-width:980px;
    }
    .db-table th {
        background:#eef5fb;
        color:#103250;
        text-align:left;
        font-size:.75rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.03em;
        padding:.7rem .8rem;
        border-bottom:1px solid var(--db-border);
    }
    .db-table td {
        border-bottom:1px solid #eef2f7;
        padding:.7rem .8rem;
        font-size:.84rem;
        color:var(--db-text);
        vertical-align:top;
    }
    .db-table tbody tr:hover { background:#f8fafc; }
    .db-empty {
        text-align:center;
        padding:1.4rem;
        color:var(--db-muted);
        font-size:.9rem;
    }
    .db-badge {
        display:inline-flex;
        align-items:center;
        border-radius:999px;
        border:1px solid;
        padding:.2rem .52rem;
        font-size:.7rem;
        font-weight:700;
        text-transform:uppercase;
    }
    .db-badge-paid { border-color:#86efac; background:#ecfdf5; color:#065f46; }
    .db-badge-pending { border-color:#fde68a; background:#fffbeb; color:#92400e; }
    .db-badge-partial { border-color:#bfdbfe; background:#eff6ff; color:#1d4ed8; }
    .db-badge-unpaid { border-color:#fecaca; background:#fef2f2; color:#b91c1c; }
    .db-badge-cancelled { border-color:#fecaca; background:#fff1f2; color:#9f1239; }

    .db-status-list { display:grid; gap:8px; }
    .db-status-row {
        display:flex;
        justify-content:space-between;
        align-items:center;
        border-bottom:1px solid #f1f5f9;
        padding:.52rem 0;
    }
    .db-status-row:last-child { border-bottom:none; }
    .db-status-label { color:var(--db-muted); font-size:.84rem; }
    .db-status-value { color:var(--db-head); font-weight:700; font-size:.88rem; }

    @media (max-width:1100px) {
        .db-kpi-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .db-twin { grid-template-columns:1fr; }
    }
    @media (max-width:680px) {
        .db-kpi-grid { grid-template-columns:1fr; }
        .db-actions-grid { grid-template-columns:1fr; }
        .db-hero-time { font-size:1.18rem; }
    }
</style>

@php
    $occupiedPlots = (int) ($summary['occupied_plots'] ?? 0);
    $availablePlots = (int) ($summary['available_plots'] ?? 0);
    $totalPlots = $occupiedPlots + $availablePlots;
    $occupancyRate = $totalPlots > 0 ? (int) round(($occupiedPlots / $totalPlots) * 100) : 0;
    $servicesPeriod = (int) ($summary['services_period'] ?? 0);
    $transactionsPeriod = (int) ($summary['transactions_period'] ?? 0);
    $paymentsPeriod = (float) ($summary['payments_period'] ?? 0);
    $totalCollected = (float) ($summary['total_collected'] ?? 0);
    $activityPointCount = (int) collect($activityStats)->count();
@endphp

<div class="db" data-server-rendered-page="dashboard" data-page-title="Cemetery Dashboard">
    <section class="db-hero">
        <div>
            <h2><i class="fa-solid fa-cross" style="margin-right:9px;opacity:.86;"></i>Cemetery Dashboard</h2>
            <p>Office monitoring for occupant records, services, transactions, and payment collection.</p>
            <div class="db-hero-actions">
                <a class="db-hero-btn" href="{{ route('cemetery.records') }}"><i class="fa-solid fa-users"></i> Occupant Records</a>
                <a class="db-hero-btn" href="{{ route('cemetery.services') }}"><i class="fa-solid fa-book-journal-whills"></i> Service Logs</a>
                <a class="db-hero-btn" href="{{ route('cemetery.transactions') }}"><i class="fa-solid fa-receipt"></i> Transactions</a>
                <a class="db-hero-btn" href="{{ route('cemetery.payments') }}"><i class="fa-solid fa-cash-register"></i> Payments</a>
            </div>
        </div>
        <div class="db-hero-right">
            <span class="db-hero-date">{{ $filterLabel }}</span>
            <span class="db-hero-date">{{ $displayRange }}</span>
            <span class="db-hero-time" id="db-clock">{{ now()->format('h:i A') }}</span>
        </div>
    </section>

    <section class="db-filter-card">
        <form id="dbFilterForm" method="GET" action="{{ route('cemetery.dashboard') }}">
            <input type="hidden" id="dbPeriodInput" name="period" value="{{ $period }}">
            <div class="db-filter-row">
                <button type="button" class="db-filter-pill {{ $period === 'today' ? 'is-active' : '' }}" data-period="today">Today</button>
                <button type="button" class="db-filter-pill {{ $period === 'month' ? 'is-active' : '' }}" data-period="month">This Month</button>
                <button type="button" class="db-filter-pill {{ $period === 'range' ? 'is-active' : '' }}" data-period="range">Custom Range</button>
                <div id="dbFilterRangeFields" class="db-filter-range" {{ $period === 'range' ? '' : 'hidden' }}>
                    <input class="db-filter-input" type="date" id="dbDateFrom" name="date_from" value="{{ $dateFrom }}">
                    <span style="color:#64748b;font-size:.85rem;">to</span>
                    <input class="db-filter-input" type="date" id="dbDateTo" name="date_to" value="{{ $dateTo }}">
                    <button type="submit" class="db-filter-apply">Apply Range</button>
                </div>
            </div>
        </form>
        <div class="db-filter-summary">
            Showing data for <strong>{{ $filterLabel }}</strong>: {{ $displayRange }}
        </div>
    </section>

    <section class="db-kpi-grid">
        <article class="db-kpi db-kpi-blue">
            <div class="db-kpi-label">Total Occupants</div>
            <div class="db-kpi-value">{{ number_format((int) ($summary['total_occupants'] ?? 0)) }}</div>
            <div class="db-kpi-sub">Master deceased records</div>
        </article>
        <article class="db-kpi db-kpi-green">
            <div class="db-kpi-label">Occupied Niches/Lots</div>
            <div class="db-kpi-value">{{ number_format($occupiedPlots) }}</div>
            <div class="db-kpi-sub">{{ $occupancyRate }}% occupancy</div>
        </article>
        <article class="db-kpi db-kpi-amber">
            <div class="db-kpi-label">Available Niches/Lots</div>
            <div class="db-kpi-value">{{ number_format($availablePlots) }}</div>
            <div class="db-kpi-sub">{{ number_format($totalPlots) }} total mapped plots</div>
        </article>
        <article class="db-kpi db-kpi-red">
            <div class="db-kpi-label">Overdue Maintenance</div>
            <div class="db-kpi-value">{{ number_format((int) ($summary['overdue_maintenance'] ?? 0)) }}</div>
            <div class="db-kpi-sub">Needs follow-up collection</div>
        </article>
        <article class="db-kpi db-kpi-blue">
            <div class="db-kpi-label">Services ({{ $filterLabel }})</div>
            <div class="db-kpi-value">{{ number_format($servicesPeriod) }}</div>
            <div class="db-kpi-sub">{{ $displayRange }}</div>
        </article>
        <article class="db-kpi db-kpi-green">
            <div class="db-kpi-label">Transactions ({{ $filterLabel }})</div>
            <div class="db-kpi-value">{{ number_format($transactionsPeriod) }}</div>
            <div class="db-kpi-sub">{{ $displayRange }}</div>
        </article>
        <article class="db-kpi db-kpi-amber">
            <div class="db-kpi-label">Collected ({{ $filterLabel }})</div>
            <div class="db-kpi-value">PHP {{ number_format($paymentsPeriod, 2) }}</div>
            <div class="db-kpi-sub">{{ $displayRange }}</div>
        </article>
        <article class="db-kpi db-kpi-blue">
            <div class="db-kpi-label">Total Collected</div>
            <div class="db-kpi-value">PHP {{ number_format($totalCollected, 2) }}</div>
            <div class="db-kpi-sub">All-time recorded payments</div>
        </article>
    </section>

    @php
        $pendingStatusCount = (int) ($statusCounts['pending'] ?? 0);
        $unpaidStatusCount = (int) ($statusCounts['unpaid'] ?? 0);
        $partialStatusCount = (int) ($statusCounts['partial'] ?? 0);
        $paidStatusCount = (int) ($statusCounts['paid'] ?? 0);
        $overdueStatusCount = (int) ($statusCounts['overdue'] ?? 0);
        $cancelledStatusCount = (int) ($statusCounts['cancelled'] ?? 0);
        $statusTotalCount = $pendingStatusCount + $unpaidStatusCount + $partialStatusCount + $paidStatusCount + $overdueStatusCount + $cancelledStatusCount;
    @endphp

    <section class="db-twin">
        <article class="db-card">
            <div class="db-card-head">
                <h3><i class="fa-solid fa-chart-column" style="color:var(--db-primary);"></i>Activity Trend</h3>
                <span style="font-size:.8rem;color:var(--db-muted);">{{ $filterLabel }} ({{ $activityPointCount }} day{{ $activityPointCount === 1 ? '' : 's' }})</span>
            </div>
            <div class="db-card-body">
                <div class="db-chart-wrap" style="height:240px;">
                    <canvas id="cemeteryActivityChart"></canvas>
                </div>
                <div class="db-chart-kpi">
                    <div class="db-chart-kpi-item">
                        <span>Transactions</span>
                        <strong>{{ number_format((int) collect($activityStats)->sum('transactions')) }}</strong>
                    </div>
                    <div class="db-chart-kpi-item">
                        <span>Services</span>
                        <strong>{{ number_format((int) collect($activityStats)->sum('services')) }}</strong>
                    </div>
                </div>
            </div>
        </article>

        <article class="db-card">
            <div class="db-card-head">
                <h3><i class="fa-solid fa-circle-half-stroke" style="color:var(--db-primary);"></i>Transaction Status Mix</h3>
                <span style="font-size:.8rem;color:var(--db-muted);">{{ $filterLabel }}</span>
            </div>
            <div class="db-card-body" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                <div class="db-chart-wrap" style="height:170px;width:170px;flex-shrink:0;">
                    <canvas id="cemeteryStatusChart"></canvas>
                </div>
                <div style="flex:1;min-width:170px;display:grid;gap:6px;">
                    <div class="db-status-row"><span class="db-status-label">Pending</span><span class="db-status-value">{{ number_format($pendingStatusCount) }}</span></div>
                    <div class="db-status-row"><span class="db-status-label">Unpaid</span><span class="db-status-value">{{ number_format($unpaidStatusCount) }}</span></div>
                    <div class="db-status-row"><span class="db-status-label">Partial</span><span class="db-status-value">{{ number_format($partialStatusCount) }}</span></div>
                    <div class="db-status-row"><span class="db-status-label">Paid</span><span class="db-status-value">{{ number_format($paidStatusCount) }}</span></div>
                    <div class="db-status-row"><span class="db-status-label">Overdue</span><span class="db-status-value">{{ number_format($overdueStatusCount) }}</span></div>
                    <div class="db-status-row"><span class="db-status-label">Cancelled</span><span class="db-status-value">{{ number_format($cancelledStatusCount) }}</span></div>
                    <div class="db-status-row"><span class="db-status-label">Total</span><span class="db-status-value">{{ number_format($statusTotalCount) }}</span></div>
                </div>
            </div>
        </article>
    </section>

    <section class="db-twin">
        <article class="db-card">
            <div class="db-card-head">
                <h3><i class="fa-solid fa-list-check" style="color:var(--db-primary);"></i>Quick Work Area</h3>
            </div>
            <div class="db-card-body">
                <div class="db-actions-grid">
                    <a class="db-action-card" href="{{ route('cemetery.records') }}">
                        <strong><i class="fa-solid fa-users" style="margin-right:6px;color:#155f8f;"></i>Occupant Records</strong>
                        <span>Encode deceased, location, and contact details.</span>
                    </a>
                    <a class="db-action-card" href="{{ route('cemetery.services') }}">
                        <strong><i class="fa-solid fa-book-journal-whills" style="margin-right:6px;color:#155f8f;"></i>Service Logs</strong>
                        <span>Capture interment and related cemetery services.</span>
                    </a>
                    <a class="db-action-card" href="{{ route('cemetery.transactions') }}">
                        <strong><i class="fa-solid fa-receipt" style="margin-right:6px;color:#155f8f;"></i>Transactions</strong>
                        <span>Generate computed charges and fee items.</span>
                    </a>
                    <a class="db-action-card" href="{{ route('cemetery.payments') }}">
                        <strong><i class="fa-solid fa-cash-register" style="margin-right:6px;color:#155f8f;"></i>Payment Collection</strong>
                        <span>Record OR number, amount paid, and status.</span>
                    </a>
                </div>
            </div>
        </article>

        <article class="db-card">
            <div class="db-card-head">
                <h3><i class="fa-solid fa-chart-simple" style="color:var(--db-primary);"></i>Collection Snapshot</h3>
            </div>
            <div class="db-card-body">
                <div class="db-progress-wrap">
                    <div class="db-progress-label">
                        <span>Plot Occupancy</span>
                        <span>{{ $occupancyRate }}%</span>
                    </div>
                    <div class="db-progress-bar-bg">
                        <div class="db-progress-bar-fill blue js-db-progress-fill" data-width="{{ $occupancyRate }}"></div>
                    </div>
                </div>
                <div class="db-progress-wrap" style="margin-top:12px;">
                    @php
                        $periodRatio = $totalCollected > 0 ? (int) round(($paymentsPeriod / $totalCollected) * 100) : 0;
                    @endphp
                    <div class="db-progress-label">
                        <span>{{ $filterLabel }} Share of Total Collection</span>
                        <span>{{ max(0, min(100, $periodRatio)) }}%</span>
                    </div>
                    <div class="db-progress-bar-bg">
                        <div class="db-progress-bar-fill green js-db-progress-fill" data-width="{{ max(0, min(100, $periodRatio)) }}"></div>
                    </div>
                </div>
                <div class="db-status-list" style="margin-top:12px;">
                    <div class="db-status-row">
                        <span class="db-status-label">Services ({{ $filterLabel }})</span>
                        <span class="db-status-value">{{ number_format($servicesPeriod) }}</span>
                    </div>
                    <div class="db-status-row">
                        <span class="db-status-label">Transactions ({{ $filterLabel }})</span>
                        <span class="db-status-value">{{ number_format($transactionsPeriod) }}</span>
                    </div>
                    <div class="db-status-row">
                        <span class="db-status-label">Maintenance Overdue</span>
                        <span class="db-status-value">{{ number_format((int) ($summary['overdue_maintenance'] ?? 0)) }}</span>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="db-card">
        <div class="db-card-head">
            <h3><i class="fa-solid fa-chart-line" style="color:var(--db-primary);"></i>Monthly Collection Trend</h3>
            <span style="font-size:.8rem;color:var(--db-muted);">{{ $displayRange }}</span>
        </div>
        <div class="db-card-body">
            <div class="db-chart-wrap" style="height:260px;">
                <canvas id="cemeteryCollectionTrendChart"></canvas>
            </div>
        </div>
    </section>

    <section class="db-card">
        <div class="db-card-head">
            <h3><i class="fa-solid fa-clock-rotate-left" style="color:var(--db-primary);"></i>Recent Cemetery Transactions ({{ $filterLabel }})</h3>
            <a class="db-link-btn" href="{{ route('cemetery.transactions') }}"><i class="fa-solid fa-arrow-right"></i> View All</a>
        </div>
        <div class="db-table-wrap">
            <table class="db-table">
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
                        @php
                            $status = strtolower((string) ($transaction->status ?? 'pending'));
                            $badge = match ($status) {
                                'paid' => 'db-badge-paid',
                                'partial' => 'db-badge-partial',
                                'cancelled' => 'db-badge-cancelled',
                                'unpaid', 'overdue' => 'db-badge-unpaid',
                                default => 'db-badge-pending',
                            };
                        @endphp
                        <tr>
                            <td><strong>{{ $transaction->transaction_no }}</strong></td>
                            <td>{{ optional($transaction->transaction_date)->format('Y-m-d') ?: '-' }}</td>
                            <td>{{ $transaction->site?->site_name ?: '-' }}</td>
                            <td>{{ $transaction->transactionType?->type_name ?: '-' }}</td>
                            <td>{{ $transaction->deceased_name }}</td>
                            <td><strong>PHP {{ number_format((float) $transaction->amount_due, 2) }}</strong></td>
                            <td><span class="db-badge {{ $badge }}">{{ strtoupper($status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="db-empty">No transactions found for {{ strtolower($filterLabel) }}. Try adjusting the date filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script id="dbCemeteryActivityJson" type="application/json">@json($activityStats)</script>
<script id="dbCemeteryStatusJson" type="application/json">@json($statusCounts)</script>
<script id="dbCemeteryMonthlyCollectionsJson" type="application/json">@json($monthlyCollections)</script>

<script>
(function () {
    const clockEl = document.getElementById('db-clock');
    if (clockEl) {
        const tick = () => {
            const now = new Date();
            let h = now.getHours();
            const m = now.getMinutes();
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            clockEl.textContent = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')} ${ampm}`;
        };
        tick();
        setInterval(tick, 1000);
    }

    document.querySelectorAll('.js-db-progress-fill').forEach((bar) => {
        const rawWidth = Number.parseFloat(bar.dataset.width || '0');
        const width = Number.isFinite(rawWidth) ? Math.max(0, Math.min(100, rawWidth)) : 0;
        bar.style.width = `${width}%`;
    });

    const filterForm = document.getElementById('dbFilterForm');
    const periodInput = document.getElementById('dbPeriodInput');
    const rangeFields = document.getElementById('dbFilterRangeFields');
    const dateFromInput = document.getElementById('dbDateFrom');
    const dateToInput = document.getElementById('dbDateTo');
    const periodButtons = Array.from(document.querySelectorAll('.db-filter-pill[data-period]'));

    const syncFilterUI = () => {
        const selected = periodInput ? periodInput.value : 'month';
        periodButtons.forEach((btn) => {
            btn.classList.toggle('is-active', btn.dataset.period === selected);
        });
        if (rangeFields) {
            rangeFields.hidden = selected !== 'range';
        }
        const requireRange = selected === 'range';
        if (dateFromInput) dateFromInput.required = requireRange;
        if (dateToInput) dateToInput.required = requireRange;
    };

    periodButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!periodInput || !filterForm) return;
            const selected = btn.dataset.period || 'month';
            periodInput.value = selected;
            syncFilterUI();
            if (selected !== 'range') {
                filterForm.requestSubmit();
            }
        });
    });

    if (filterForm) {
        filterForm.addEventListener('submit', (event) => {
            if (!periodInput) return;
            if (periodInput.value !== 'range') return;
            const from = dateFromInput ? dateFromInput.value : '';
            const to = dateToInput ? dateToInput.value : '';
            if (!from || !to || from > to) {
                event.preventDefault();
                alert('Please select a valid custom date range.');
            }
        });
    }

    syncFilterUI();

    const parseJsonScript = (id, fallback) => {
        const el = document.getElementById(id);
        if (!el) return fallback;
        try {
            return JSON.parse(el.textContent || '');
        } catch (error) {
            console.error(`Invalid JSON in #${id}`, error);
            return fallback;
        }
    };

    if (!window.Chart) {
        return;
    }

    const PRIMARY = '#155f8f';
    const GREEN = '#10b981';
    const RED = '#dc2626';
    const AMBER = '#f59e0b';
    const SLATE = '#64748b';

    const activityData = parseJsonScript('dbCemeteryActivityJson', []);
    const statusData = parseJsonScript('dbCemeteryStatusJson', {});
    const monthlyCollections = parseJsonScript('dbCemeteryMonthlyCollectionsJson', []);

    const activityCanvas = document.getElementById('cemeteryActivityChart');
    if (activityCanvas) {
        new Chart(activityCanvas, {
            type: 'bar',
            data: {
                labels: activityData.map((row) => `${row.label}\n${row.date}`),
                datasets: [
                    {
                        label: 'Transactions',
                        data: activityData.map((row) => Number(row.transactions || 0)),
                        backgroundColor: PRIMARY,
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Services',
                        data: activityData.map((row) => Number(row.services || 0)),
                        backgroundColor: GREEN,
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 10 } },
                        grid: { color: '#eef2f7' },
                    },
                },
            },
        });
    }

    const statusCanvas = document.getElementById('cemeteryStatusChart');
    if (statusCanvas) {
        const pending = Number(statusData.pending || 0);
        const unpaid = Number(statusData.unpaid || 0);
        const partial = Number(statusData.partial || 0);
        const paid = Number(statusData.paid || 0);
        const overdue = Number(statusData.overdue || 0);
        const cancelled = Number(statusData.cancelled || 0);

        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Unpaid', 'Partial', 'Paid', 'Overdue', 'Cancelled'],
                datasets: [{
                    data: [pending, unpaid, partial, paid, overdue, cancelled],
                    backgroundColor: [SLATE, AMBER, '#3b82f6', GREEN, RED, '#7f1d1d'],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '66%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.parsed}` } },
                },
            },
        });
    }

    const monthlyCanvas = document.getElementById('cemeteryCollectionTrendChart');
    if (monthlyCanvas) {
        const chart = new Chart(monthlyCanvas, {
            type: 'line',
            data: {
                labels: monthlyCollections.map((row) => row.label),
                datasets: [{
                    label: 'Collected Amount',
                    data: monthlyCollections.map((row) => Number(row.amount || 0)),
                    borderColor: PRIMARY,
                    backgroundColor: 'rgba(21,95,143,0.14)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: PRIMARY,
                    fill: true,
                    tension: 0.33,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' PHP ' + Number(ctx.parsed.y).toLocaleString('en-PH', { minimumFractionDigits: 2 }),
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 10 },
                            callback: (value) => 'PHP ' + Number(value).toLocaleString('en-PH'),
                        },
                        grid: { color: '#eef2f7' },
                    },
                },
            },
        });

        chart.options.plugins.tooltip.callbacks.label = (ctx) =>
            ' PHP ' + Number(ctx.parsed.y).toLocaleString('en-PH', { minimumFractionDigits: 2 });
        chart.options.scales.y.ticks.callback = (value) =>
            'PHP ' + Number(value).toLocaleString('en-PH');
        chart.update();
    }
})();
</script>
@endsection
