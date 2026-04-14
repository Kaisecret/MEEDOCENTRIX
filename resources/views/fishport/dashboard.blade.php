@extends('layouts.app')

@section('content')
<style>
    /* â”€â”€ Design tokens â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db { --db-primary:#155f8f; --db-primary-dk:#0f4b73; --db-green:#047857; --db-red:#dc2626; --db-amber:#d97706; --db-border:#e2e8f0; --db-soft:#f8fafc; --db-text:#334155; --db-muted:#64748b; --db-head:#0f172a; font-family:'Inter',system-ui,-apple-system,sans-serif; color:var(--db-text); display:grid; gap:20px; }

    /* â”€â”€ KPI CARDS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-kpi-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
    .db-kpi { border:1px solid var(--db-border); border-radius:12px; background:#fff; padding:1.2rem 1.4rem; display:grid; gap:4px; box-shadow:0 1px 3px rgba(0,0,0,.04); position:relative; overflow:hidden; }
    .db-kpi::before { content:''; position:absolute; top:0; left:0; width:4px; height:100%; border-radius:999px 0 0 999px; }
    .db-kpi-blue::before  { background:var(--db-primary); }
    .db-kpi-green::before { background:#10b981; }
    .db-kpi-red::before   { background:var(--db-red); }
    .db-kpi-amber::before { background:#f59e0b; }
    .db-kpi-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; margin-bottom:8px; }
    .db-kpi-icon-blue  { background:#dbeafe; color:var(--db-primary); }
    .db-kpi-icon-green { background:#d1fae5; color:var(--db-green); }
    .db-kpi-icon-red   { background:#fee2e2; color:var(--db-red); }
    .db-kpi-icon-amber { background:#fef3c7; color:var(--db-amber); }
    .db-kpi-label { font-size:.82rem; font-weight:600; color:var(--db-muted); text-transform:uppercase; letter-spacing:.03em; }
    .db-kpi-value { font-size:1.8rem; font-weight:800; color:var(--db-head); letter-spacing:-.02em; line-height:1; }
    .db-kpi-sub { font-size:.8rem; color:var(--db-muted); margin-top:2px; }
    .db-kpi-sub .up   { color:#059669; font-weight:600; }
    .db-kpi-sub .down { color:var(--db-red); font-weight:600; }
    .db-kpi-sub .warn { color:var(--db-amber); font-weight:600; }

    /* â”€â”€ CARD SHELL â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-card { border:1px solid var(--db-border); border-radius:12px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.04); overflow:hidden; }
    .db-card-head { border-bottom:1px solid var(--db-border); padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .db-card-head h3 { margin:0; font-size:1rem; font-weight:700; color:var(--db-head); display:flex; align-items:center; gap:8px; }
    .db-card-body { padding:1.25rem; }

    /* â”€â”€ 2-COL GRID â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-twin { display:grid; grid-template-columns:minmax(0,1.6fr) minmax(0,1fr); gap:14px; }
    .db-triple { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }

    /* â”€â”€ CHART WRAPPERS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-chart-wrap { position:relative; width:100%; }
    .db-chart-wrap canvas { display:block; width:100% !important; }

    /* â”€â”€ NOT-PAID TABLE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-table { width:100%; border-collapse:collapse; }
    .db-table th { background:#eef5fb; color:#103250; font-size:.75rem; text-transform:uppercase; letter-spacing:.03em; font-weight:700; padding:.75rem 1rem; text-align:left; border-bottom:1px solid var(--db-border); }
    .db-table td { padding:.75rem 1rem; border-bottom:1px solid #f1f5f9; font-size:.88rem; color:var(--db-text); vertical-align:middle; }
    .db-table tbody tr:last-child td { border-bottom:none; }
    .db-table tbody tr:hover { background:#f8fafc; }
    .db-badge { border-radius:999px; padding:.2rem .6rem; font-size:.72rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; }
    .db-badge-arr { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
    .db-badge-dep { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .db-badge-unpaid { background:#fef2f2; color:var(--db-red); border:1px solid #fecaca; }

    /* â”€â”€ PROGRESS BAR â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-progress-wrap { display:grid; gap:8px; }
    .db-progress-label { display:flex; justify-content:space-between; align-items:center; }
    .db-progress-label span:first-child { font-size:.82rem; font-weight:600; color:var(--db-muted); }
    .db-progress-label span:last-child  { font-size:.82rem; font-weight:700; color:var(--db-head); }
    .db-progress-bar-bg { height:8px; background:#e2e8f0; border-radius:999px; overflow:hidden; }
    .db-progress-bar-fill { height:100%; border-radius:999px; transition:width .6s ease; }
    .db-progress-bar-fill.green  { background:#10b981; }
    .db-progress-bar-fill.blue   { background:var(--db-primary); }
    .db-progress-bar-fill.red    { background:var(--db-red); }

    /* â”€â”€ METRIC ROWS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-metric-row { display:flex; justify-content:space-between; align-items:center; padding:.7rem 0; border-bottom:1px solid #f1f5f9; font-size:.88rem; }
    .db-metric-row:last-child { border-bottom:none; }
    .db-metric-row span:first-child { color:var(--db-muted); }
    .db-metric-row strong { color:var(--db-head); font-weight:700; }

    /* â”€â”€ HERO BANNER â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-hero { background:var(--db-primary); color:#fff; border-radius:12px; padding:1.4rem 1.6rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,.1); }
    .db-hero h2 { margin:0 0 .3rem; font-size:1.6rem; font-weight:700; letter-spacing:-.02em; }
    .db-hero p  { margin:0; font-size:.92rem; color:rgba(255,255,255,.85); }
    .db-hero-right { display:flex; flex-direction:column; align-items:flex-end; gap:4px; }
    .db-hero-date { font-size:.85rem; color:rgba(255,255,255,.75); }
    .db-hero-time { font-size:1.5rem; font-weight:800; }
    .db-filter-card { border:1px solid var(--db-border); border-radius:12px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.04); padding:14px 16px; display:grid; gap:12px; }
    .db-filter-row { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .db-filter-pill { border:1px solid #cbd5e1; background:#fff; color:#155f8f; border-radius:999px; min-height:34px; padding:0 14px; font-size:.82rem; font-weight:700; cursor:pointer; transition:all .2s ease; }
    .db-filter-pill:hover { background:#f0f7fd; }
    .db-filter-pill.is-active { background:#155f8f; border-color:#155f8f; color:#fff; box-shadow:0 4px 12px rgba(21,95,143,.18); }
    .db-filter-range { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .db-filter-range[hidden] { display:none !important; }
    .db-filter-input { min-height:36px; border:1px solid #cbd5e1; border-radius:9px; padding:0 .72rem; font-size:.86rem; color:#334155; background:#fff; }
    .db-filter-input:focus { outline:none; border-color:#155f8f; box-shadow:0 0 0 3px rgba(21,95,143,.14); }
    .db-filter-apply { border:1px solid #155f8f; background:#155f8f; color:#fff; border-radius:9px; min-height:36px; padding:0 .9rem; font-size:.84rem; font-weight:700; cursor:pointer; }
    .db-filter-summary { font-size:.84rem; color:#64748b; }

    /* â”€â”€ EMPTY STATE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-empty { padding:2rem; text-align:center; color:var(--db-muted); font-size:.9rem; }

    /* â”€â”€ RESPONSIVE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    @media (max-width:1024px) {
        .db-kpi-grid  { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .db-twin      { grid-template-columns:1fr; }
        .db-triple    { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media (max-width:640px) {
        .db-kpi-grid { grid-template-columns:1fr; }
        .db-triple   { grid-template-columns:1fr; }
        .db-filter-row { align-items:flex-start; }
    }
</style>

<div class="db">

    {{-- â”€â”€ HERO â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="db-hero">
        <div>
            <h2><i class="fa-solid fa-fish-fins" style="margin-right:10px;opacity:.85;"></i>Fishport Dashboard</h2>
            <p>Real-time overview of vessel activity and collection status</p>
        </div>
        <div class="db-hero-right">
            <span class="db-hero-date">{{ $filterLabel }}</span>
            <span class="db-hero-date">{{ $displayRange }}</span>
            <span class="db-hero-time" id="db-clock">{{ now()->format('h:i A') }}</span>
        </div>
    </div>

    <section class="db-filter-card">
        <form id="dbFilterForm" method="GET" action="{{ route('fishport.dashboard') }}">
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

    {{-- â”€â”€ KPI CARDS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="db-kpi-grid">
        {{-- Vessels Today --}}
        <div class="db-kpi db-kpi-blue">
            <div class="db-kpi-icon db-kpi-icon-blue"><i class="fa-solid fa-ship"></i></div>
            <div class="db-kpi-label">Vessels ({{ $filterLabel }})</div>
            <div class="db-kpi-value">{{ $vesselsTodayCount }}</div>
            <div class="db-kpi-sub">
                @if($vesselsTodayCount >= $vesselsTodayPrevCount)
                    <span class="up"><i class="fa-solid fa-arrow-up"></i> {{ $vesselsTodayCount - $vesselsTodayPrevCount }}</span> vs previous period
                @else
                    <span class="down"><i class="fa-solid fa-arrow-down"></i> {{ $vesselsTodayPrevCount - $vesselsTodayCount }}</span> vs previous period
                @endif
            </div>
        </div>

        {{-- Earnings today --}}
        <div class="db-kpi db-kpi-green">
            <div class="db-kpi-icon db-kpi-icon-green"><i class="fa-solid fa-peso-sign"></i></div>
            <div class="db-kpi-label">Paid Collections</div>
            <div class="db-kpi-value" style="font-size:1.4rem;">PHP {{ number_format($paidTodayAmount, 2) }}</div>
            <div class="db-kpi-sub">{{ $filterLabel }} period</div>
        </div>

        {{-- Not Paid --}}
        <div class="db-kpi db-kpi-red">
            <div class="db-kpi-icon db-kpi-icon-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="db-kpi-label">Unpaid Transactions</div>
            <div class="db-kpi-value">{{ $notPaidCount }}</div>
            <div class="db-kpi-sub">
                <span class="warn">PHP {{ number_format($notPaidAmount, 2) }}</span> pending
            </div>
        </div>

        {{-- Monthly Revenue --}}
        <div class="db-kpi db-kpi-amber">
            <div class="db-kpi-icon db-kpi-icon-amber"><i class="fa-solid fa-chart-line"></i></div>
            <div class="db-kpi-label">Total Billings</div>
            <div class="db-kpi-value" style="font-size:1.4rem;">PHP {{ number_format($totalRevenue, 2) }}</div>
            <div class="db-kpi-sub">{{ $displayRange }}</div>
        </div>
    </div>

    {{-- â”€â”€ ROW 2: Weekly Chart + Donut + Progress â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="db-twin">

        {{-- Weekly Arrivals/Departures Bar Chart --}}
        <div class="db-card">
            <div class="db-card-head">
                <h3><i class="fa-solid fa-chart-column" style="color:var(--db-primary);"></i>Vessel Activity Trend</h3>
                <span style="font-size:.8rem;color:var(--db-muted);">{{ $displayRange }}</span>
            </div>
            <div class="db-card-body">
                <div class="db-chart-wrap" style="height:220px;">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Right column: donut + progress --}}
        <div style="display:grid;gap:14px;">

            {{-- ARR vs DEP Donut --}}
            <div class="db-card">
                <div class="db-card-head">
                    <h3><i class="fa-solid fa-circle-half-stroke" style="color:var(--db-primary);"></i>ARR vs DEP</h3>
                    <span style="font-size:.8rem;color:var(--db-muted);">{{ $filterLabel }}</span>
                </div>
                <div class="db-card-body" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                    <div class="db-chart-wrap" style="height:110px;width:110px;flex-shrink:0;">
                        <canvas id="donutChart"></canvas>
                    </div>
                    <div style="flex:1;min-width:120px;display:grid;gap:6px;">
                        <div class="db-metric-row">
                            <span><i class="fa-solid fa-circle" style="color:#10b981;font-size:.55rem;margin-right:4px;"></i>Arrivals</span>
                            <strong>{{ $arrCount }}</strong>
                        </div>
                        <div class="db-metric-row">
                            <span><i class="fa-solid fa-circle" style="color:var(--db-primary);font-size:.55rem;margin-right:4px;"></i>Departures</span>
                            <strong>{{ $depCount }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Progress --}}
            <div class="db-card">
                <div class="db-card-head">
                    <h3><i class="fa-solid fa-circle-check" style="color:var(--db-primary);"></i>Collection Progress</h3>
                    <span style="font-size:.8rem;color:var(--db-muted);">{{ $filterLabel }}</span>
                </div>
                <div class="db-card-body" style="display:grid;gap:14px;">
                    <div class="db-progress-wrap">
                        <div class="db-progress-label">
                            <span>Paid ({{ $monthPaid }}/{{ $monthTotal }})</span>
                            <span>{{ $paidPercent }}%</span>
                        </div>
                        <div class="db-progress-bar-bg">
                            <div class="db-progress-bar-fill green js-db-progress-fill" data-width="{{ $paidPercent }}"></div>
                        </div>
                    </div>
                    <div class="db-progress-wrap">
                        <div class="db-progress-label">
                            <span>Not Paid ({{ $monthTotal - $monthPaid }}/{{ $monthTotal }})</span>
                            <span>{{ 100 - $paidPercent }}%</span>
                        </div>
                        <div class="db-progress-bar-bg">
                            <div class="db-progress-bar-fill red js-db-progress-fill" data-width="{{ 100 - $paidPercent }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- â”€â”€ ROW 3: Monthly Revenue Chart + Not-Paid Table â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="db-twin">

        {{-- Monthly Revenue Line Chart --}}
        <div class="db-card">
            <div class="db-card-head">
                <h3><i class="fa-solid fa-chart-area" style="color:var(--db-primary);"></i>Revenue Trend</h3>
                <span style="font-size:.8rem;color:var(--db-muted);">{{ $displayRange }}</span>
            </div>
            <div class="db-card-body">
                <div class="db-chart-wrap" style="height:220px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Not-Paid Transactions --}}
        <div class="db-card">
            <div class="db-card-head">
                <h3><i class="fa-solid fa-clock" style="color:#dc2626;"></i>Unpaid Transactions ({{ $filterLabel }})</h3>
                <a href="{{ route('fishport.records', ['saved_status' => 'not_paid']) }}" style="font-size:.8rem;color:var(--db-primary);text-decoration:none;font-weight:600;">View all <i class="fa-solid fa-arrow-right" style="font-size:.65rem;"></i></a>
            </div>
            @if($notPaidLogs->isEmpty())
                <div class="db-empty"><i class="fa-solid fa-circle-check" style="font-size:1.5rem;color:#10b981;display:block;margin-bottom:8px;"></i>All transactions are paid!</div>
            @else
                <div style="overflow:auto;">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Log No.</th>
                                <th>Vessel</th>
                                <th>Date</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notPaidLogs as $row)
                                <tr>
                                    <td>
                                        <strong>{{ $row['log_number'] }}</strong><br>
                                        <span class="db-badge {{ $row['arr_dep'] === 'ARR' ? 'db-badge-arr' : 'db-badge-dep' }}">{{ $row['arr_dep'] }}</span>
                                    </td>
                                    <td>{{ $row['vessel'] }}</td>
                                    <td style="white-space:nowrap;font-size:.82rem;">{{ $row['log_date'] }}</td>
                                    <td style="white-space:nowrap;">
                                        @if($row['grand_total'] > 0)
                                            <strong>PHP {{ number_format($row['grand_total'], 2) }}</strong>
                                        @else
                                            <span style="color:var(--db-muted);font-size:.8rem;">Pending entry</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

<script id="dbDailyStatsJson" type="application/json">@json($dailyStats)</script>
<script id="dbMonthlyRevenueJson" type="application/json">@json($monthlyRevenue)</script>
<script id="dbCountsJson" type="application/json">@json(['arr' => $arrCount, 'dep' => $depCount])</script>

<script>
(function () {
    // â”€â”€ Live clock â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const clockEl = document.getElementById('db-clock');
    if (clockEl) {
        const tick = () => {
            const now = new Date();
            let h = now.getHours(), m = now.getMinutes(), s = now.getSeconds();
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            clockEl.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')} ${ampm}`;
        };
        tick();
        setInterval(tick, 1000);
    }

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

    document.querySelectorAll('.js-db-progress-fill').forEach((el) => {
        const width = Number.parseFloat(el.dataset.width || '0');
        const clamped = Number.isFinite(width) ? Math.min(100, Math.max(0, width)) : 0;
        el.style.width = `${clamped}%`;
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

    // â”€â”€ Chart.js shared defaults â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const PRIMARY   = '#155f8f';
    const GREEN     = '#10b981';
    const RED       = '#dc2626';
    const AMBER     = '#f59e0b';
    const SOFT_BLUE = 'rgba(21,95,143,0.12)';

    // â”€â”€ Weekly Bar Chart â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const weeklyData = parseJsonScript('dbDailyStatsJson', []);
    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: weeklyData.map(d => d.label + '\n' + d.date),
            datasets: [
                {
                    label: 'Arrivals',
                    data: weeklyData.map(d => d.arrivals),
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Departures',
                    data: weeklyData.map(d => d.departures),
                    backgroundColor: PRIMARY,
                    borderRadius: 6,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12 } },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f1f5f9' } },
            },
        },
    });

    // â”€â”€ ARR/DEP Donut â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const countData = parseJsonScript('dbCountsJson', { arr: 0, dep: 0 });
    const arrCount = Number.parseInt(countData.arr ?? 0, 10) || 0;
    const depCount = Number.parseInt(countData.dep ?? 0, 10) || 0;
    new Chart(document.getElementById('donutChart'), {
        type: 'doughnut',
        data: {
            labels: ['Arrivals', 'Departures'],
            datasets: [{
                data: [arrCount, depCount],
                backgroundColor: [GREEN, PRIMARY],
                borderWidth: 2,
                borderColor: '#fff',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } },
            },
        },
    });

    // â”€â”€ Monthly Revenue Line Chart â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const monthlyData = parseJsonScript('dbMonthlyRevenueJson', []);
    const revenueChart = new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: monthlyData.map(d => d.label),
            datasets: [{
                label: 'Revenue (PHP)',
                data: monthlyData.map(d => d.revenue),
                borderColor: PRIMARY,
                backgroundColor: SOFT_BLUE,
                borderWidth: 2.5,
                pointBackgroundColor: PRIMARY,
                pointRadius: 4,
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' PHP ' + Number(ctx.parsed.y).toLocaleString('en-PH', { minimumFractionDigits: 2 }),
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 10 },
                        callback: v => 'PHP ' + Number(v).toLocaleString('en-PH'),
                    },
                    grid: { color: '#f1f5f9' },
                },
            },
        },
    });

    // Keep chart currency formatting in English.
    revenueChart.options.plugins.tooltip.callbacks.label = (ctx) =>
        ' PHP ' + Number(ctx.parsed.y).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    revenueChart.options.scales.y.ticks.callback = (v) =>
        'PHP ' + Number(v).toLocaleString('en-PH');
    revenueChart.update();

    // Pull fresh DB data continuously (disabled for custom range mode).
    if (!periodInput || periodInput.value !== 'range') {
        setInterval(() => {
            window.location.reload();
        }, 20000);
    }
})();
</script>
@endsection


