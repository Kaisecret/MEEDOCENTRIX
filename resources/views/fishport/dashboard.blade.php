@extends('layouts.app')

@section('content')
<style>
    #contentArea {
        padding-top: 12px;
    }

    /* â”€â”€ Design tokens â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db { --db-primary:#155f8f; --db-primary-dk:#0f4b73; --db-green:#047857; --db-red:#dc2626; --db-amber:#d97706; --db-border:#e2e8f0; --db-soft:#f8fafc; --db-text:#334155; --db-muted:#64748b; --db-head:#0f172a; font-family:'Inter',system-ui,-apple-system,sans-serif; color:var(--db-text); display:grid; gap:10px; }

    /* â”€â”€ KPI CARDS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-kpi-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
    .db-kpi { border:1px solid var(--db-border); border-radius:14px; background:#fff; padding:.8rem .9rem .85rem; display:grid; gap:2px; box-shadow:0 1px 2px rgba(15,35,60,.04); position:relative; overflow:hidden; transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
    .db-kpi:hover { transform:translateY(-3px); box-shadow:0 14px 28px rgba(15,35,60,.09); border-color:#cfdae6; }
    .db-kpi::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; }
    .db-kpi::after { content:''; position:absolute; right:-30px; top:-30px; width:120px; height:120px; border-radius:50%; opacity:.06; pointer-events:none; transition:opacity .22s ease, transform .22s ease; }
    .db-kpi:hover::after { opacity:.1; transform:scale(1.1); }
    .db-kpi-blue::before  { background:linear-gradient(90deg,var(--db-primary),#4ea3e0); }
    .db-kpi-blue::after   { background:radial-gradient(circle,var(--db-primary),transparent 70%); }
    .db-kpi-green::before { background:linear-gradient(90deg,#10b981,#34d399); }
    .db-kpi-green::after  { background:radial-gradient(circle,#10b981,transparent 70%); }
    .db-kpi-red::before   { background:linear-gradient(90deg,var(--db-red),#f87171); }
    .db-kpi-red::after    { background:radial-gradient(circle,var(--db-red),transparent 70%); }
    .db-kpi-amber::before { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
    .db-kpi-amber::after  { background:radial-gradient(circle,#f59e0b,transparent 70%); }
    .db-kpi-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.95rem; margin-bottom:6px; position:relative; box-shadow:inset 0 0 0 1px rgba(255,255,255,.6); }
    .db-kpi-icon-blue  { background:linear-gradient(135deg,rgba(21,95,143,.16),rgba(21,95,143,.06)); color:var(--db-primary); }
    .db-kpi-icon-green { background:linear-gradient(135deg,rgba(16,185,129,.18),rgba(16,185,129,.06)); color:var(--db-green); }
    .db-kpi-icon-red   { background:linear-gradient(135deg,rgba(220,38,38,.16),rgba(220,38,38,.06)); color:var(--db-red); }
    .db-kpi-icon-amber { background:linear-gradient(135deg,rgba(245,158,11,.18),rgba(245,158,11,.06)); color:var(--db-amber); }
    .db-kpi-label { font-size:.68rem; font-weight:800; color:var(--db-muted); text-transform:uppercase; letter-spacing:.06em; }
    .db-kpi-value { font-size:1.35rem; font-weight:800; color:var(--db-head); letter-spacing:-.02em; line-height:1.1; font-variant-numeric:tabular-nums; margin-top:3px; }
    .db-kpi-sub { font-size:.72rem; color:var(--db-muted); margin-top:3px; display:flex; align-items:center; gap:5px; }
    .db-kpi-sub .up   { color:#059669; font-weight:700; }
    .db-kpi-sub .down { color:var(--db-red); font-weight:700; }
    .db-kpi-sub .warn { color:var(--db-amber); font-weight:700; }

    /* â”€â”€ CARD SHELL â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-card { border:1px solid var(--db-border); border-radius:16px; background:#fff; box-shadow:0 1px 2px rgba(15,35,60,.04); overflow:hidden; transition:box-shadow .22s ease, transform .22s ease; }
    .db-card:hover { box-shadow:0 10px 26px rgba(15,35,60,.07); }
    .db-card-head { border-bottom:1px solid var(--db-border); padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; background:linear-gradient(180deg,#fff,#f5f9fd); }
    .db-card-head h3 { position:relative; margin:0; padding-left:14px; font-size:1.05rem; font-weight:800; color:var(--db-head); display:flex; align-items:center; gap:9px; letter-spacing:-.01em; }
    .db-card-head h3::before { content:''; position:absolute; left:0; top:.15rem; bottom:.15rem; width:4px; border-radius:4px; background:linear-gradient(180deg,var(--db-primary),#4ea3e0); box-shadow:0 0 8px rgba(21,95,143,.2); }
    .db-card-head h3 i { position:relative; font-size:1rem; }
    .db-card-head > span { font-size:.78rem; font-weight:800; color:var(--db-primary); padding:5px 12px; background:rgba(21,95,143,.08); border:1px solid rgba(21,95,143,.14); border-radius:999px; text-transform:uppercase; letter-spacing:.04em; }
    .db-card-body { padding:1.1rem 1.25rem; }

    /* â”€â”€ 2-COL GRID â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-twin { display:grid; grid-template-columns:minmax(0,1.6fr) minmax(0,1fr); gap:8px; }
    .db-activity-grid { grid-template-columns:minmax(0,2.3fr) minmax(280px,.8fr); }
    .db-side-metrics { display:grid; gap:8px; }
    .db-activity-grid > .db-side-metrics { grid-column:1 / -1; grid-template-columns:repeat(2,minmax(0,1fr)); }
    .db-triple { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
    .db-revenue-card { display:flex; flex-direction:column; }
    .db-revenue-card-body { flex:1; display:flex; align-items:stretch; justify-content:stretch; }

    /* â”€â”€ CHART WRAPPERS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-chart-wrap { position:relative; width:100%; }
    .db-chart-wrap canvas { display:block; width:100% !important; }
    .db-activity-chart-frame,
    .db-revenue-chart-frame {
        padding:14px 14px 10px 10px;
        border:1px solid #e3edf7;
        border-radius:12px;
        background:linear-gradient(180deg,#ffffff 0%,#f9fbff 100%);
        box-shadow:inset 0 1px 0 rgba(255,255,255,.9), 0 4px 14px rgba(21,95,143,.05);
    }
    .db-activity-chart-frame { height:390px; }
    .db-revenue-chart-frame { height:350px; }
    .db-activity-chart-frame canvas,
    .db-revenue-chart-frame canvas { height:100% !important; }
    .db-revenue-card .db-revenue-chart-frame { width:100%; height:100%; min-height:560px; margin:0; }

    /* â”€â”€ NOT-PAID TABLE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-table { width:100%; border-collapse:collapse; }
    .db-table th { background:linear-gradient(180deg,#f1f6fb,#e8f0f7); color:#0f3a64; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; font-weight:800; padding:.78rem 1rem; text-align:left; border-bottom:1px solid #d5e0ec; }
    .db-table td { padding:.82rem 1rem; border-bottom:1px solid #eef3f8; font-size:.9rem; color:#23405f; vertical-align:middle; line-height:1.3; transition:background-color .15s ease; }
    .db-table tbody tr:last-child td { border-bottom:none; }
    .db-table tbody tr:hover { background:#f5fafd; }
    .db-table td strong { color:#1a3657; }
    .db-log-number { display:block; margin-bottom:.3rem; font-weight:800; font-size:.93rem; letter-spacing:.01em; line-height:1.18; color:#17395f; }
    .db-vessel-name { display:block; font-size:.9rem; font-weight:600; color:#1f3f63; line-height:1.28; }
    .db-log-date { white-space:nowrap; font-size:.84rem; font-weight:600; color:#3d5877; }
    .db-amount { white-space:nowrap; font-size:.95rem; font-weight:800; color:#14355b; letter-spacing:.01em; }
    .db-amount-pending { color:var(--db-muted); font-size:.8rem; }
    .db-badge { border-radius:999px; padding:.24rem .68rem; font-size:.7rem; font-weight:800; display:inline-flex; align-items:center; justify-content:center; letter-spacing:.05em; text-transform:uppercase; }
    .db-badge-arr { background:linear-gradient(135deg,#d1fae5,#ecfdf5); color:#047857; border:1px solid #a7f3d0; box-shadow:0 1px 2px rgba(4,120,87,.08); }
    .db-badge-dep { background:linear-gradient(135deg,#dbeafe,#eff6ff); color:#1d4ed8; border:1px solid #bfdbfe; box-shadow:0 1px 2px rgba(29,78,216,.08); }
    .db-badge-unpaid { background:linear-gradient(135deg,#fee2e2,#fef2f2); color:var(--db-red); border:1px solid #fecaca; }
    .db-unpaid-link { font-size:.8rem; color:var(--db-primary); text-decoration:none; font-weight:700; display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:999px; background:rgba(21,95,143,.06); transition:all .2s ease; }
    .db-unpaid-link:hover { color:#fff; background:var(--db-primary); transform:translateX(2px); }

    /* â”€â”€ PROGRESS BAR â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-progress-wrap { display:grid; gap:10px; }
    .db-progress-label { display:flex; justify-content:space-between; align-items:center; }
    .db-progress-label span:first-child { font-size:.9rem; font-weight:700; color:var(--db-text); }
    .db-progress-label span:last-child  { font-size:1.02rem; font-weight:850; color:var(--db-head); font-variant-numeric:tabular-nums; letter-spacing:-.01em; }
    .db-progress-bar-bg { height:12px; background:#eef2f7; border-radius:999px; overflow:hidden; box-shadow:inset 0 1px 3px rgba(0,0,0,.06); }
    .db-progress-bar-fill { height:100%; border-radius:999px; transition:width .8s cubic-bezier(.4,0,.2,1); position:relative; box-shadow:0 1px 2px rgba(0,0,0,.08); }
    .db-progress-bar-fill::after { content:''; position:absolute; inset:0; border-radius:999px; background:linear-gradient(180deg,rgba(255,255,255,.35),transparent 55%); }
    .db-progress-bar-fill.green  { background:linear-gradient(90deg,#10b981,#34d399); }
    .db-progress-bar-fill.blue   { background:linear-gradient(90deg,var(--db-primary),#4ea3e0); }
    .db-progress-bar-fill.red    { background:linear-gradient(90deg,var(--db-red),#f87171); }

    /* â”€â”€ METRIC ROWS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-metric-row { display:flex; justify-content:space-between; align-items:center; padding:.62rem .8rem; border-radius:9px; font-size:.95rem; transition:background-color .18s ease; }
    .db-metric-row + .db-metric-row { margin-top:2px; }
    .db-metric-row:hover { background:#f5f9fd; }
    .db-metric-row span:first-child { color:#475569; font-weight:600; display:inline-flex; align-items:center; }
    .db-metric-row strong { color:var(--db-head); font-weight:800; font-size:1.05rem; font-variant-numeric:tabular-nums; }

    /* â”€â”€ HERO BANNER â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-filter-card { border:1px solid var(--db-border); border-radius:16px; background:linear-gradient(135deg,#ffffff 0%,#f7fbfe 100%); box-shadow:0 1px 3px rgba(0,0,0,.04); padding:14px 16px; display:grid; gap:10px; position:relative; overflow:hidden; }
    .db-filter-card::before { content:''; position:absolute; top:0; left:0; bottom:0; width:4px; background:linear-gradient(180deg,var(--db-primary),#4ea3e0); }
    .db-filter-row { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .db-filter-pill { border:1px solid #cbd5e1; background:#fff; color:#155f8f; border-radius:999px; min-height:40px; padding:0 18px; font-size:.9rem; font-weight:700; cursor:pointer; transition:all .22s ease; display:inline-flex; align-items:center; justify-content:center; gap:8px; }
    .db-filter-pill:hover { background:#f0f7fd; border-color:#9bbdd8; transform:translateY(-1px); box-shadow:0 4px 10px rgba(21,95,143,.08); }
    .db-filter-pill.is-active { background:linear-gradient(135deg,var(--db-primary),#1e7ab3); border-color:#0f4b73; color:#fff; box-shadow:0 6px 16px rgba(21,95,143,.28); }
    .db-filter-pill i { font-size:.82rem; opacity:.95; }
    .db-filter-range { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .db-filter-range[hidden] { display:none !important; }
    .db-filter-input { min-height:40px; border:1px solid #cbd5e1; border-radius:10px; padding:0 .85rem; font-size:.92rem; color:#334155; background:#fff; transition:border-color .2s ease, box-shadow .2s ease; }
    .db-filter-input:focus { outline:none; border-color:#155f8f; box-shadow:0 0 0 3px rgba(21,95,143,.14); }
    .db-filter-apply { border:1px solid #0f4b73; background:linear-gradient(135deg,var(--db-primary),#1e7ab3); color:#fff; border-radius:10px; min-height:40px; padding:0 1.15rem; font-size:.9rem; font-weight:700; cursor:pointer; box-shadow:0 4px 10px rgba(21,95,143,.2); transition:transform .18s ease, box-shadow .18s ease; }
    .db-filter-apply:hover { transform:translateY(-1px); box-shadow:0 6px 14px rgba(21,95,143,.28); }
    .db-filter-summary { font-size:.92rem; color:#475569; }
    .db-filter-summary strong { color:var(--db-head); }

    /* â”€â”€ EMPTY STATE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .db-empty { padding:2rem 1.3rem; text-align:center; color:var(--db-muted); font-size:.98rem; background:linear-gradient(180deg,#fff,#f8fcf9); }
    .db-empty i { display:block; margin-bottom:10px; }

    /* â”€â”€ RESPONSIVE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    @media (max-width:1024px) {
        .db-kpi-grid  { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .db-twin      { grid-template-columns:1fr; }
        .db-activity-grid { grid-template-columns:1fr; }
        .db-activity-grid > .db-side-metrics { grid-template-columns:1fr; }
        .db-triple    { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media (max-width:640px) {
        .db-kpi-grid { grid-template-columns:1fr; }
        .db-triple   { grid-template-columns:1fr; }
        .db-filter-row { align-items:flex-start; }
        .db-activity-chart-frame { height:320px; padding:8px 8px 6px 4px; }
        .db-revenue-chart-frame { height:290px; padding:8px 8px 6px 4px; }
        .db-revenue-card .db-revenue-chart-frame { min-height:320px; }
    }
</style>

<div class="db">

    <section class="db-filter-card">
        <form id="dbFilterForm" method="GET" action="{{ route('fishport.dashboard') }}">
            <input type="hidden" id="dbPeriodInput" name="period" value="{{ $period }}">
            <div class="db-filter-row">
                <button type="button" class="db-filter-pill {{ $period === 'today' ? 'is-active' : '' }}" data-period="today"><i class="fa-solid fa-sun"></i>Today</button>
                <button type="button" class="db-filter-pill {{ $period === 'week' ? 'is-active' : '' }}" data-period="week"><i class="fa-regular fa-calendar"></i>This Week</button>
                <button type="button" class="db-filter-pill {{ $period === 'month' ? 'is-active' : '' }}" data-period="month"><i class="fa-solid fa-calendar-days"></i>This Month</button>
                <button type="button" class="db-filter-pill {{ $period === 'range' ? 'is-active' : '' }}" data-period="range"><i class="fa-solid fa-calendar-check"></i>Custom Range</button>
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
            <div class="db-kpi-value">PHP {{ number_format($paidTodayAmount, 2) }}</div>
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
            <div class="db-kpi-value">PHP {{ number_format($totalRevenue, 2) }}</div>
            <div class="db-kpi-sub">{{ $displayRange }}</div>
        </div>
    </div>

    {{-- â”€â”€ ROW 2: Weekly Chart + Donut + Progress â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="db-twin db-activity-grid">

        {{-- Right column: donut + progress --}}
        <div class="db-side-metrics">

            {{-- ARR vs DEP Donut --}}
            <div class="db-card">
                <div class="db-card-head">
                    <h3><i class="fa-solid fa-circle-half-stroke" style="color:var(--db-primary);"></i>ARR vs DEP</h3>
                    <span style="font-size:.8rem;color:var(--db-muted);">{{ $filterLabel }}</span>
                </div>
                <div class="db-card-body" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                    <div class="db-chart-wrap" style="height:110px;width:110px;flex-shrink:0;">
                        <canvas id="donutChart"></canvas>
                    </div>
                    <div style="flex:1;min-width:120px;display:grid;gap:4px;">
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
                <div class="db-card-body" style="display:grid;gap:10px;">
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
        <div class="db-card db-revenue-card">
            <div class="db-card-head">
                <h3><i class="fa-solid fa-chart-area" style="color:var(--db-primary);"></i>Revenue Trend</h3>
                <span style="font-size:.8rem;color:var(--db-muted);">{{ $displayRange }}</span>
            </div>
            <div class="db-card-body db-revenue-card-body">
                <div class="db-chart-wrap db-revenue-chart-frame">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Not-Paid Transactions --}}
        <div class="db-card">
            <div class="db-card-head">
                <h3><i class="fa-solid fa-clock" style="color:#dc2626;"></i>Unpaid Transactions ({{ $filterLabel }})</h3>
                <a href="{{ route('fishport.records', ['saved_status' => 'not_paid']) }}" class="db-unpaid-link">View all <i class="fa-solid fa-arrow-right" style="font-size:.72rem;"></i></a>
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
                                        <strong class="db-log-number">{{ $row['log_number'] }}</strong>
                                        <span class="db-badge {{ $row['arr_dep'] === 'ARR' ? 'db-badge-arr' : 'db-badge-dep' }}">{{ $row['arr_dep'] }}</span>
                                    </td>
                                    <td><span class="db-vessel-name">{{ $row['vessel'] }}</span></td>
                                    <td><span class="db-log-date">{{ $row['log_date'] }}</span></td>
                                    <td>
                                        @if($row['grand_total'] > 0)
                                            <strong class="db-amount">PHP {{ number_format($row['grand_total'], 2) }}</strong>
                                        @else
                                            <span class="db-amount-pending">Pending entry</span>
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
    // Show the page title in the top white bar for this dashboard.
    window.addEventListener('load', () => {
        const breadcrumb = document.querySelector('.breadcrumb');
        if (breadcrumb) breadcrumb.hidden = false;

        const titleEl = document.getElementById('pageTitle');
        if (titleEl) titleEl.textContent = 'Fishport';
    });

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

    const darkTooltip = {
        backgroundColor: 'rgba(15,23,42,.94)',
        titleColor: '#f8fafc',
        bodyColor: '#e2e8f0',
        padding: 12,
        cornerRadius: 10,
        boxPadding: 6,
        titleFont: { weight: '700', size: 12 },
        bodyFont: { weight: '600', size: 12 },
    };

    const buildVerticalGradient = (canvas, top, bottom) => {
        const ctx = canvas.getContext('2d');
        const g = ctx.createLinearGradient(0, 0, 0, canvas.parentElement?.clientHeight || 340);
        g.addColorStop(0, top);
        g.addColorStop(1, bottom);
        return g;
    };

    // â”€â”€ Weekly Bar + Line Combo Chart â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const weeklyCanvas = document.getElementById('weeklyChart');
    if (weeklyCanvas) {
        const weeklyData = parseJsonScript('dbDailyStatsJson', []);
        const arrivalsSeries = weeklyData.map(d => Number(d.arrivals) || 0);
        const departuresSeries = weeklyData.map(d => Number(d.departures) || 0);

        new Chart(weeklyCanvas, {
            type: 'bar',
            data: {
                labels: weeklyData.map(d => d.label + '\n' + d.date),
                datasets: [
                    {
                        type: 'bar',
                        label: 'Arrivals',
                        data: arrivalsSeries,
                        backgroundColor: buildVerticalGradient(weeklyCanvas, 'rgba(16,185,129,.9)', 'rgba(16,185,129,.62)'),
                        hoverBackgroundColor: GREEN,
                        borderColor: 'rgba(5,150,105,.24)',
                        borderWidth: 1,
                        borderRadius: 0,
                        borderSkipped: false,
                        barPercentage: 0.58,
                        categoryPercentage: 0.76,
                        maxBarThickness: 46,
                        order: 2,
                    },
                    {
                        type: 'bar',
                        label: 'Departures',
                        data: departuresSeries,
                        backgroundColor: buildVerticalGradient(weeklyCanvas, 'rgba(21,95,143,.9)', 'rgba(21,95,143,.62)'),
                        hoverBackgroundColor: PRIMARY,
                        borderColor: 'rgba(21,95,143,.28)',
                        borderWidth: 1,
                        borderRadius: 0,
                        borderSkipped: false,
                        barPercentage: 0.58,
                        categoryPercentage: 0.76,
                        maxBarThickness: 46,
                        order: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 10, right: 8, left: 0, bottom: 0 } },
                animation: { duration: 800, easing: 'easeOutQuart' },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { font: { size: 11, weight: '700' }, boxWidth: 8, boxHeight: 8, usePointStyle: true, padding: 14, color: '#475569' },
                    },
                    tooltip: { ...darkTooltip, mode: 'index', intersect: false, displayColors: true },
                },
                scales: {
                    x: {
                        offset: true,
                        grid: { color: 'rgba(148,163,184,.12)', drawBorder: false, drawTicks: false },
                        ticks: { font: { size: 10, weight: '600' }, color: '#64748b', maxRotation: 0, autoSkipPadding: 12, padding: 8 },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 10, weight: '600' }, color: '#64748b', padding: 8 },
                        grid: { color: 'rgba(148,163,184,.24)', drawBorder: false },
                    },
                },
            },
        });
    }

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
                hoverBackgroundColor: ['#0ea372', '#0f4b73'],
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 8,
                spacing: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            radius: '94%',
            animation: { animateRotate: true, animateScale: true, duration: 800, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...darkTooltip,
                    callbacks: {
                        label: (ctx) => {
                            const total = (ctx.dataset.data || []).reduce((s, v) => s + Number(v || 0), 0);
                            const pct = total > 0 ? ((Number(ctx.parsed) / total) * 100).toFixed(1) : '0.0';
                            return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        },
                    },
                },
            },
        },
    });

    // â”€â”€ Monthly Revenue Bar + Line Chart â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const revenueCanvas = document.getElementById('revenueChart');
    const monthlyData = parseJsonScript('dbMonthlyRevenueJson', []);
    const revenueValues = monthlyData.map(d => Number(d.revenue) || 0);
    const revenueBarGradient = buildVerticalGradient(revenueCanvas, 'rgba(79,128,237,.82)', 'rgba(79,128,237,.58)');
    const revenueChart = new Chart(revenueCanvas, {
        type: 'bar',
        data: {
            labels: monthlyData.map(d => d.label),
            datasets: [
                {
                    type: 'bar',
                    label: 'Revenue (PHP)',
                    data: revenueValues,
                    backgroundColor: revenueBarGradient,
                    hoverBackgroundColor: '#4f80ed',
                    borderColor: 'rgba(37,99,235,.22)',
                    borderWidth: 1,
                    borderRadius: 0,
                    borderSkipped: false,
                    barPercentage: 0.56,
                    categoryPercentage: 0.76,
                    maxBarThickness: 52,
                    minBarLength: 4,
                    order: 2,
                },
                {
                    type: 'line',
                    label: 'Trend',
                    data: revenueValues,
                    borderColor: 'rgba(216,124,124,.78)',
                    backgroundColor: 'rgba(216,124,124,.16)',
                    borderWidth: 1.6,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: '#d87c7c',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    fill: false,
                    tension: 0.28,
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 10, right: 8, left: 0, bottom: 0 } },
            animation: { duration: 900, easing: 'easeOutQuart' },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...darkTooltip,
                    displayColors: false,
                    filter: ctx => ctx.dataset.type === 'bar',
                    callbacks: {
                        label: ctx => ' PHP ' + Number(ctx.parsed.y).toLocaleString('en-PH', { minimumFractionDigits: 2 }),
                    },
                },
            },
            scales: {
                x: {
                    offset: true,
                    grid: { color: 'rgba(148,163,184,.12)', drawBorder: false, drawTicks: false },
                    ticks: { font: { size: 10, weight: '600' }, color: '#64748b', maxRotation: 0, autoSkipPadding: 12, padding: 8 },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 10, weight: '600' },
                        color: '#64748b',
                        padding: 8,
                        callback: v => 'PHP ' + Number(v).toLocaleString('en-PH', { notation: 'compact', maximumFractionDigits: 1 }),
                    },
                    grid: { color: 'rgba(148,163,184,.24)', drawBorder: false },
                },
            },
        },
    });

    // Tooltip currency stays in English en-PH locale (already set above).

    // Pull fresh DB data continuously (disabled for custom range mode).
    if (!periodInput || periodInput.value !== 'range') {
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                window.location.reload();
            }
        }, 120000);
    }
})();
</script>
@endsection
