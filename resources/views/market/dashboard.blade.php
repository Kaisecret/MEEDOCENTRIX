@extends('layouts.app')

@section('content')
<style>
    #contentArea {
        padding-top: 10px;
    }

    .mkd {
        --mkd-primary: #155f8f;
        --mkd-primary-deep: #0f4b73;
        --mkd-secondary: #1a7fd4;
        --mkd-surface: #ffffff;
        --mkd-soft: #f8fafc;
        --mkd-border: #e2e8f0;
        --mkd-text: #334155;
        --mkd-muted: #64748b;
        --mkd-head: #0f172a;
        --mkd-green: #047857;
        --mkd-amber: #b45309;
        --mkd-red: #b91c1c;
        --mkd-blue: #1d4ed8;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: var(--mkd-text);
        display: grid;
        gap: 10px;
    }

    .mkd-filter {
        border: 1px solid var(--mkd-border);
        border-radius: 14px;
        background: var(--mkd-surface);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        padding: 10px;
        display: grid;
        gap: 10px;
    }

    .mkd-filter-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .mkd-pill {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: var(--mkd-primary);
        border-radius: 999px;
        min-height: 35px;
        padding: 0 .92rem;
        font-size: .82rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .18s ease;
    }

    .mkd-pill:hover {
        background: #f0f7fd;
    }

    .mkd-pill.is-active {
        background: var(--mkd-primary);
        border-color: var(--mkd-primary);
        color: #fff;
        box-shadow: 0 6px 16px rgba(21, 95, 143, .2);
    }

    .mkd-range-fields {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mkd-range-fields[hidden] {
        display: none !important;
    }

    .mkd-input {
        min-height: 36px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        padding: 0 .72rem;
        font-size: .86rem;
        color: var(--mkd-text);
    }

    .mkd-input:focus {
        outline: none;
        border-color: var(--mkd-primary);
        box-shadow: 0 0 0 3px rgba(21, 95, 143, .13);
    }

    .mkd-apply {
        border: 1px solid var(--mkd-primary);
        background: var(--mkd-primary);
        color: #fff;
        border-radius: 9px;
        min-height: 36px;
        padding: 0 .9rem;
        font-size: .84rem;
        font-weight: 700;
        cursor: pointer;
    }

    .mkd-filter-note {
        color: var(--mkd-muted);
        font-size: .83rem;
    }

    .mkd-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .mkd-kpi {
        border: 1px solid var(--mkd-border);
        border-radius: 13px;
        background: var(--mkd-surface);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        padding: 10px;
        display: grid;
        gap: 10px;
        animation: mkd-fade .34s ease both;
    }

    .mkd-kpi:nth-child(1) { animation-delay: .02s; }
    .mkd-kpi:nth-child(2) { animation-delay: .06s; }
    .mkd-kpi:nth-child(3) { animation-delay: .1s; }
    .mkd-kpi:nth-child(4) { animation-delay: .14s; }

    .mkd-kpi-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .mkd-kpi-title {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--mkd-muted);
        font-weight: 800;
    }

    .mkd-kpi-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .98rem;
    }

    .mkd-kpi-icon.blue { background: #eff6ff; color: var(--mkd-blue); }
    .mkd-kpi-icon.green { background: #ecfdf5; color: var(--mkd-green); }
    .mkd-kpi-icon.amber { background: #fffbeb; color: var(--mkd-amber); }
    .mkd-kpi-icon.red { background: #fef2f2; color: var(--mkd-red); }

    .mkd-kpi-value {
        font-size: 1.45rem;
        line-height: 1.05;
        letter-spacing: -.02em;
        color: var(--mkd-head);
        font-weight: 800;
    }

    .mkd-kpi-sub {
        font-size: .8rem;
        color: var(--mkd-muted);
    }

    .mkd-kpi-sub b {
        color: var(--mkd-head);
    }

    .mkd-grid-twin {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr);
        gap: 10px;
    }

    .mkd-card {
        border: 1px solid var(--mkd-border);
        border-radius: 14px;
        background: var(--mkd-surface);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        overflow: hidden;
    }

    .mkd-card-head {
        border-bottom: 1px solid var(--mkd-border);
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mkd-card-head h3 {
        margin: 0;
        color: var(--mkd-head);
        font-size: 1rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .mkd-card-head span {
        color: var(--mkd-muted);
        font-size: .8rem;
        font-weight: 600;
    }

    .mkd-card-body {
        padding: 10px;
    }

    .mkd-chart-wrap {
        position: relative;
        width: 100%;
        height: 240px;
    }

    .mkd-chart-wrap canvas {
        display: block;
        width: 100% !important;
    }

    .mkd-chart-frame {
        height: 390px;
        padding: 14px 16px 10px 10px;
        border: 1px solid #dbe7f5;
        border-radius: 4px;
        background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .9), 0 10px 24px rgba(21, 95, 143, .06);
    }

    .mkd-chart-frame--daily {
        height: 420px;
    }

    .mkd-chart-frame--revenue {
        height: 400px;
    }

    .mkd-chart-frame canvas {
        height: 100% !important;
    }

    .mkd-chart-wrap--small {
        height: 120px;
        width: 120px;
        flex-shrink: 0;
    }

    .mkd-donut-shell {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mkd-donut-shell.is-empty .mkd-chart-wrap--small {
        display: none;
    }

    .mkd-metric {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
        padding: .62rem 0;
        font-size: .85rem;
    }

    .mkd-metric:last-child {
        border-bottom: 0;
    }

    .mkd-metric b {
        color: var(--mkd-head);
        font-weight: 800;
    }

    .mkd-progress {
        display: grid;
        gap: 10px;
    }

    .mkd-progress-row {
        display: grid;
        gap: 5px;
    }

    .mkd-progress-meta {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        font-size: .82rem;
        color: var(--mkd-muted);
        font-weight: 600;
    }

    .mkd-progress-meta b {
        color: var(--mkd-head);
    }

    .mkd-progress-track {
        height: 8px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .mkd-progress-fill {
        height: 100%;
        width: 0;
        border-radius: 999px;
        transition: width .6s ease;
    }

    .mkd-progress-fill.green { background: #10b981; }
    .mkd-progress-fill.amber { background: #f59e0b; }
    .mkd-progress-fill.blue { background: var(--mkd-primary); }

    .mkd-table-wrap {
        overflow: auto;
    }

    .mkd-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
    }

    .mkd-table th {
        background: #eef5fb;
        color: #103250;
        border-bottom: 1px solid var(--mkd-border);
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 800;
        padding: 10px;
        text-align: left;
    }

    .mkd-table td {
        border-bottom: 1px solid #f1f5f9;
        padding: 10px;
        font-size: .87rem;
        color: var(--mkd-text);
        vertical-align: middle;
    }

    .mkd-table tbody tr:hover td {
        background: #f8fafc;
    }

    .mkd-tag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid transparent;
        padding: .18rem .56rem;
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        font-weight: 800;
    }

    .mkd-tag-accepted { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .mkd-tag-awaiting { background: #ecfeff; border-color: #a5f3fc; color: #0e7490; }
    .mkd-tag-pending { background: #fffbeb; border-color: #fde68a; color: #b45309; }
    .mkd-tag-rejected { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
    .mkd-tag-cancelled { background: #f8fafc; border-color: #cbd5e1; color: #475569; }

    .mkd-link {
        color: var(--mkd-primary);
        text-decoration: none;
        font-weight: 700;
        font-size: .82rem;
    }

    .mkd-link:hover {
        text-decoration: underline;
    }

    .mkd-empty {
        text-align: center;
        color: var(--mkd-muted);
        padding: 2rem 1rem;
        font-size: .9rem;
    }

    @keyframes mkd-fade {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 1100px) {
        .mkd-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .mkd-grid-twin { grid-template-columns: 1fr; }
    }

    @media (max-width: 680px) {
        .mkd-kpi-grid { grid-template-columns: 1fr; }
        .mkd-chart-frame--daily { height: 340px; padding: 10px 10px 6px 4px; }
        .mkd-chart-frame--revenue { height: 320px; padding: 10px 10px 6px 4px; }
    }
</style>

<div class="mkd" data-server-rendered-page="dashboard" data-page-title="Market Dashboard" data-live-refresh-ms="120000">
    <section class="mkd-filter">
        <form id="mkdFilterForm" method="GET" action="{{ route('market.dashboard') }}">
            <input type="hidden" id="mkdPeriodInput" name="period" value="{{ $period }}">
            <div class="mkd-filter-row">
                <button type="button" class="mkd-pill {{ $period === 'today' ? 'is-active' : '' }}" data-period="today">Today</button>
                <button type="button" class="mkd-pill {{ $period === 'week' ? 'is-active' : '' }}" data-period="week">This Week</button>
                <button type="button" class="mkd-pill {{ $period === 'month' ? 'is-active' : '' }}" data-period="month">This Month</button>
                <button type="button" class="mkd-pill {{ $period === 'range' ? 'is-active' : '' }}" data-period="range">Custom Range</button>
                <div id="mkdRangeFields" class="mkd-range-fields" {{ $period === 'range' ? '' : 'hidden' }}>
                    <input class="mkd-input" type="date" id="mkdDateFrom" name="date_from" value="{{ $dateFrom }}">
                    <span style="color:#64748b;font-size:.84rem;">to</span>
                    <input class="mkd-input" type="date" id="mkdDateTo" name="date_to" value="{{ $dateTo }}">
                    <button type="submit" class="mkd-apply">Apply Range</button>
                </div>
            </div>
        </form>
        <div class="mkd-filter-note">Showing data for <b>{{ $filterLabel }}</b>: {{ $displayRange }}</div>
    </section>

    <section class="mkd-kpi-grid">
        <article class="mkd-kpi">
            <div class="mkd-kpi-head">
                <span class="mkd-kpi-title">Occupied Stalls</span>
                <span class="mkd-kpi-icon green"><i class="fa-solid fa-warehouse"></i></span>
            </div>
            <div class="mkd-kpi-value">{{ number_format($activeStalls) }}</div>
            <div class="mkd-kpi-sub"><b>{{ $occupancyPercent }}%</b> occupancy ({{ number_format($totalStalls) }} total stalls)</div>
        </article>

        <article class="mkd-kpi">
            <div class="mkd-kpi-head">
                <span class="mkd-kpi-title">Active Tenants</span>
                <span class="mkd-kpi-icon blue"><i class="fa-solid fa-users"></i></span>
            </div>
            <div class="mkd-kpi-value">{{ number_format($activeTenants) }}</div>
            <div class="mkd-kpi-sub">{{ number_format($activeLeases) }} active lease{{ $activeLeases === 1 ? '' : 's' }}</div>
        </article>

        <article class="mkd-kpi">
            <div class="mkd-kpi-head">
                <span class="mkd-kpi-title">Collected Amount</span>
                <span class="mkd-kpi-icon amber"><i class="fa-solid fa-peso-sign"></i></span>
            </div>
            <div class="mkd-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($acceptedAmount, 2) }}</div>
            <div class="mkd-kpi-sub">{{ number_format($acceptedDispatchCount) }} accepted transaction{{ $acceptedDispatchCount === 1 ? '' : 's' }}</div>
        </article>

        <article class="mkd-kpi">
            <div class="mkd-kpi-head">
                <span class="mkd-kpi-title">Collector Queue</span>
                <span class="mkd-kpi-icon red"><i class="fa-solid fa-paper-plane"></i></span>
            </div>
            <div class="mkd-kpi-value">{{ number_format($openQueueCount) }}</div>
            <div class="mkd-kpi-sub"><b>{{ number_format($readyLeaseCount) }}</b> ready to send</div>
        </article>
    </section>

    <section class="mkd-grid-twin">
        <article class="mkd-card">
            <div class="mkd-card-head">
                <h3><i class="fa-solid fa-chart-area" style="color:var(--mkd-primary);"></i>Daily Queue Movement</h3>
                <span>{{ $displayRange }}</span>
            </div>
            <div class="mkd-card-body">
                <div class="mkd-chart-wrap mkd-chart-frame mkd-chart-frame--daily"><canvas id="mkdDailyChart"></canvas></div>
            </div>
        </article>

        <div style="display:grid;gap:10px;">
            <article class="mkd-card">
                <div class="mkd-card-head">
                    <h3><i class="fa-solid fa-circle-half-stroke" style="color:var(--mkd-primary);"></i>Status Mix</h3>
                    <span>{{ $filterLabel }}</span>
                </div>
                @php
                    $statusTotal = $acceptedDispatchCount + $awaitingDispatchCount + $pendingDispatchCount;
                @endphp
                <div class="mkd-card-body mkd-donut-shell {{ $statusTotal === 0 ? 'is-empty' : '' }}">
                    <div class="mkd-chart-wrap mkd-chart-wrap--small"><canvas id="mkdStatusDonut"></canvas></div>
                    <div style="flex:1;min-width:140px;">
                        <div class="mkd-metric"><span>Accepted</span><b>{{ number_format($acceptedDispatchCount) }}</b></div>
                        <div class="mkd-metric"><span>Awaiting</span><b>{{ number_format($awaitingDispatchCount) }}</b></div>
                        <div class="mkd-metric"><span>Pending / Rejected</span><b>{{ number_format($pendingDispatchCount) }}</b></div>
                    </div>
                </div>
            </article>

            <article class="mkd-card">
                <div class="mkd-card-head">
                    <h3><i class="fa-solid fa-gauge-high" style="color:var(--mkd-primary);"></i>Collection Progress</h3>
                    <span>{{ $filterLabel }}</span>
                </div>
                <div class="mkd-card-body mkd-progress">
                    <div class="mkd-progress-row">
                        <div class="mkd-progress-meta">
                            <span>Accepted</span>
                            <span><b>{{ $acceptedPercent }}%</b></span>
                        </div>
                        <div class="mkd-progress-track">
                            <div class="mkd-progress-fill green js-mkd-progress" data-width="{{ $acceptedPercent }}"></div>
                        </div>
                    </div>
                    <div class="mkd-progress-row">
                        <div class="mkd-progress-meta">
                            <span>Awaiting + Pending</span>
                            <span><b>{{ 100 - $acceptedPercent }}%</b></span>
                        </div>
                        <div class="mkd-progress-track">
                            <div class="mkd-progress-fill amber js-mkd-progress" data-width="{{ 100 - $acceptedPercent }}"></div>
                        </div>
                    </div>
                    <div class="mkd-metric">
                        <span>Pending Amount</span>
                        <b>PHP {{ number_format($pendingAmount, 2) }}</b>
                    </div>
                    <div class="mkd-metric">
                        <span>Transactions in Range</span>
                        <b>{{ number_format($totalDispatchCount) }}</b>
                    </div>
                    <div class="mkd-metric">
                        <span>Previous Period</span>
                        <b>{{ number_format($previousDispatchCount) }}</b>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="mkd-grid-twin">
        <article class="mkd-card">
            <div class="mkd-card-head">
                <h3><i class="fa-solid fa-chart-line" style="color:var(--mkd-primary);"></i>Collected Revenue Trend</h3>
                <span>Accepted transactions only</span>
            </div>
            <div class="mkd-card-body">
                <div class="mkd-chart-wrap mkd-chart-frame mkd-chart-frame--revenue"><canvas id="mkdRevenueChart"></canvas></div>
            </div>
        </article>

        <article class="mkd-card">
            <div class="mkd-card-head">
                <h3><i class="fa-solid fa-clock-rotate-left" style="color:var(--mkd-primary);"></i>Recent Market Activity</h3>
                <a class="mkd-link" href="{{ route('market.records') }}">View full ledger</a>
            </div>
            @if ($recentItems->isEmpty())
                <div class="mkd-empty">
                    <i class="fa-solid fa-folder-open" style="font-size:1.45rem;color:#cbd5e1;display:block;margin-bottom:8px;"></i>
                    No market activity recorded for this filter range.
                </div>
            @else
                <div class="mkd-table-wrap">
                    <table class="mkd-table">
                        <thead>
                            <tr>
                                <th>Stall</th>
                                <th>Tenant</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentItems as $row)
                                @php
                                    $statusClass = match ($row['status']) {
                                        'accepted' => 'mkd-tag-accepted',
                                        'collected_pending_confirmation' => 'mkd-tag-awaiting',
                                        'rejected' => 'mkd-tag-rejected',
                                        'cancelled' => 'mkd-tag-cancelled',
                                        default => 'mkd-tag-pending',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $row['stall_no'] }}</strong><br>
                                        <span style="font-size:.78rem;color:var(--mkd-muted);">{{ $row['location'] }}</span>
                                    </td>
                                    <td>
                                        {{ $row['tenant_name'] }}<br>
                                        <span style="font-size:.78rem;color:var(--mkd-muted);">Collector: {{ $row['collector'] }}</span>
                                    </td>
                                    <td><span class="mkd-tag {{ $statusClass }}">{{ $row['status_label'] }}</span></td>
                                    <td><strong>PHP {{ number_format($row['amount'], 2) }}</strong></td>
                                    <td style="white-space:nowrap;">{{ $row['updated_at'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>
    </section>
</div>

<script id="mkdDailyStatsJson" type="application/json">@json($dailyStatusStats)</script>
<script id="mkdMonthlyAmountJson" type="application/json">@json($monthlyAmount)</script>
<script id="mkdStatusCountsJson" type="application/json">@json(['accepted' => $acceptedDispatchCount, 'awaiting' => $awaitingDispatchCount, 'pending' => $pendingDispatchCount])</script>

<script>
(function () {
    const parseJsonScript = (id, fallback) => {
        const node = document.getElementById(id);
        if (!node) return fallback;
        try {
            return JSON.parse(node.textContent || '');
        } catch (error) {
            return fallback;
        }
    };

    document.querySelectorAll('.js-mkd-progress').forEach((node) => {
        const value = Number.parseFloat(node.dataset.width || '0');
        const width = Number.isFinite(value) ? Math.max(0, Math.min(100, value)) : 0;
        node.style.width = `${width}%`;
    });

    const filterForm = document.getElementById('mkdFilterForm');
    const periodInput = document.getElementById('mkdPeriodInput');
    const rangeFields = document.getElementById('mkdRangeFields');
    const dateFromInput = document.getElementById('mkdDateFrom');
    const dateToInput = document.getElementById('mkdDateTo');
    const periodButtons = Array.from(document.querySelectorAll('.mkd-pill[data-period]'));

    const syncFilterUI = () => {
        const selected = periodInput ? periodInput.value : 'month';
        periodButtons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.period === selected);
        });
        if (rangeFields) rangeFields.hidden = selected !== 'range';
        const isRange = selected === 'range';
        if (dateFromInput) dateFromInput.required = isRange;
        if (dateToInput) dateToInput.required = isRange;
    };

    periodButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (!periodInput || !filterForm) return;
            const selected = button.dataset.period || 'month';
            periodInput.value = selected;
            syncFilterUI();
            if (selected !== 'range') filterForm.requestSubmit();
        });
    });

    if (filterForm) {
        filterForm.addEventListener('submit', (event) => {
            if (!periodInput || periodInput.value !== 'range') return;
            const fromValue = dateFromInput ? dateFromInput.value : '';
            const toValue = dateToInput ? dateToInput.value : '';
            if (!fromValue || !toValue || fromValue > toValue) {
                event.preventDefault();
                alert('Please choose a valid custom date range.');
            }
        });
    }

    syncFilterUI();

    const dailyStats = parseJsonScript('mkdDailyStatsJson', []);
    const monthlyAmount = parseJsonScript('mkdMonthlyAmountJson', []);
    const statusCounts = parseJsonScript('mkdStatusCountsJson', { accepted: 0, awaiting: 0, pending: 0 });

    const MKD_PRIMARY = '#155f8f';
    const MKD_TOTAL = '#1e3a8a';
    const MKD_ACCEPTED = '#10b981';
    const MKD_AWAITING = '#1a7fd4';
    const MKD_PENDING = '#f59e0b';

    const mkdTooltip = {
        backgroundColor: 'rgba(15,23,42,.94)',
        titleColor: '#f8fafc',
        bodyColor: '#e2e8f0',
        padding: 12,
        cornerRadius: 10,
        boxPadding: 6,
        titleFont: { weight: '700', size: 12 },
        bodyFont: { weight: '600', size: 12 },
    };

    const mkdBuildGradient = (canvas, top, bottom) => {
        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.parentElement?.clientHeight || 390);
        gradient.addColorStop(0, top);
        gradient.addColorStop(1, bottom);
        return gradient;
    };

    const mkdSoftLineGlowPlugin = {
        id: 'mkdSoftLineGlow',
        beforeDatasetDraw(chart, args) {
            const dataset = chart.data.datasets?.[args.index];
            if (!dataset || dataset.type !== 'line' || dataset.enableGlow !== true) {
                return;
            }

            const ctx = chart.ctx;
            ctx.save();
            ctx.shadowColor = dataset.glowColor || 'rgba(30,58,138,.22)';
            ctx.shadowBlur = Number(dataset.glowBlur || 10);
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 1;
        },
        afterDatasetDraw(chart, args) {
            const dataset = chart.data.datasets?.[args.index];
            if (!dataset || dataset.type !== 'line' || dataset.enableGlow !== true) {
                return;
            }

            chart.ctx.restore();
        }
    };

    const dailyCanvas = document.getElementById('mkdDailyChart');
    const acceptedSeries = dailyStats.map((entry) => Number(entry.accepted) || 0);
    const awaitingSeries = dailyStats.map((entry) => Number(entry.awaiting) || 0);
    const pendingSeries = dailyStats.map((entry) => Number(entry.pending) || 0);
    const dailyTotalSeries = dailyStats.map((entry) => Number(entry.total) || 0);

    new Chart(dailyCanvas, {
        type: 'line',
        data: {
            labels: dailyStats.map((entry) => entry.date || entry.label),
            datasets: [{
                label: 'Total',
                data: dailyTotalSeries,
                borderColor: '#155f8f',
                backgroundColor: (context) => {
                    const { ctx, chartArea } = context.chart;
                    if (!chartArea) return 'rgba(21,95,143,0.14)';
                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, 'rgba(21,95,143,0.28)');
                    gradient.addColorStop(1, 'rgba(21,95,143,0.02)');
                    return gradient;
                },
                borderWidth: 2.8,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#155f8f',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                tension: 0.38,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 10, right: 8, left: 0, bottom: 0 } },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: { ...mkdTooltip, mode: 'index', intersect: false },
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#64748b', font: { size: 11, weight: '600' }, maxRotation: 0, autoSkipPadding: 12, padding: 8 },
                },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: '#64748b', font: { size: 10, weight: '600' }, padding: 8 },
                    grid: { color: 'rgba(148,163,184,.22)', drawBorder: false, borderDash: [4, 4] },
                }
            }
        }
    });

    new Chart(document.getElementById('mkdStatusDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Accepted', 'Awaiting', 'Pending'],
            datasets: [{
                data: [
                    Number.parseInt(statusCounts.accepted ?? 0, 10) || 0,
                    Number.parseInt(statusCounts.awaiting ?? 0, 10) || 0,
                    Number.parseInt(statusCounts.pending ?? 0, 10) || 0
                ],
                backgroundColor: [MKD_ACCEPTED, MKD_PRIMARY, MKD_PENDING],
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '66%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    const revenueCanvas = document.getElementById('mkdRevenueChart');
    const revenueValues = monthlyAmount.map((entry) => Number(entry.amount) || 0);

    new Chart(revenueCanvas, {
        type: 'bar',
        data: {
            labels: monthlyAmount.map((entry) => entry.label),
            datasets: [
                {
                    type: 'bar',
                    label: 'Collected Amount',
                    data: revenueValues,
                    backgroundColor: mkdBuildGradient(revenueCanvas, 'rgba(79,128,237,.82)', 'rgba(79,128,237,.58)'),
                    hoverBackgroundColor: '#4f80ed',
                    borderColor: 'rgba(37,99,235,.22)',
                    borderWidth: 1,
                    borderRadius: 0,
                    borderSkipped: false,
                    barPercentage: .56,
                    categoryPercentage: .76,
                    maxBarThickness: 58,
                    minBarLength: 4,
                    order: 2,
                },
                {
                    type: 'line',
                    label: 'Trend',
                    data: revenueValues,
                    borderColor: 'rgba(216,124,124,.78)',
                    backgroundColor: 'rgba(216,124,124,.16)',
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: '#d87c7c',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    fill: false,
                    tension: .34,
                    cubicInterpolationMode: 'monotone',
                    borderCapStyle: 'round',
                    borderJoinStyle: 'round',
                    enableGlow: true,
                    glowColor: 'rgba(216,124,124,.2)',
                    glowBlur: 10,
                    order: 1,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 10, right: 8, left: 0, bottom: 0 } },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...mkdTooltip,
                    displayColors: false,
                    filter: (ctx) => ctx.dataset.type === 'bar',
                    callbacks: {
                        label: (ctx) => ' PHP ' + Number(ctx.parsed.y).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                x: {
                    offset: true,
                    grid: { color: 'rgba(148,163,184,.12)', drawBorder: false, drawTicks: false },
                    ticks: { color: '#64748b', font: { size: 10, weight: '600' }, maxRotation: 0, autoSkipPadding: 12, padding: 8 },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#64748b',
                        font: { size: 10, weight: '600' },
                        padding: 8,
                        callback: (value) => 'PHP ' + Number(value).toLocaleString('en-PH', { notation: 'compact', maximumFractionDigits: 1 })
                    },
                    grid: { color: 'rgba(148,163,184,.24)', drawBorder: false },
                }
            }
        },
        plugins: [mkdSoftLineGlowPlugin]
    });

})();
</script>
@endsection
