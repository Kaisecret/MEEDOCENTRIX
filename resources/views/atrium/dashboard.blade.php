@extends('layouts.app')

@section('content')
<style>
    #contentArea {
        padding-top: 10px;
    }

    .atrd {
        --atrd-primary: #0f5fa8;
        --atrd-primary-deep: #0a4880;
        --atrd-accent: #1a7fd4;
        --atrd-head: #0f172a;
        --atrd-text: #334155;
        --atrd-muted: #64748b;
        --atrd-border: #e2e8f0;
        --atrd-soft: #f8fafc;
        --atrd-green: #059669;
        --atrd-amber: #d97706;
        --atrd-red: #dc2626;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: var(--atrd-text);
        display: grid;
        gap: 10px;
    }

    .atrd-filter-card {
        border: 1px solid var(--atrd-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        padding: 10px;
        display: grid;
        gap: 10px;
    }
    .atrd-filter-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .atrd-filter-pill {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: var(--atrd-primary);
        border-radius: 999px;
        min-height: 38px;
        padding: 0 16px;
        font-size: .92rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .atrd-filter-pill:hover {
        background: #f0f7fd;
        border-color: #b9c8dc;
        transform: translateY(-1px);
    }
    .atrd-filter-pill.is-active {
        background: var(--atrd-primary);
        border-color: var(--atrd-primary);
        color: #fff;
        box-shadow: 0 4px 12px rgba(15, 95, 168, .18);
    }
    .atrd-filter-range {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .atrd-filter-range[hidden] {
        display: none !important;
    }
    .atrd-filter-input {
        min-height: 40px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        padding: 0 .78rem;
        font-size: .92rem;
        color: var(--atrd-text);
        background: #fff;
    }
    .atrd-filter-input:focus {
        outline: none;
        border-color: var(--atrd-primary);
        box-shadow: 0 0 0 3px rgba(15, 95, 168, .14);
    }
    .atrd-filter-apply {
        border: 1px solid var(--atrd-primary);
        background: var(--atrd-primary);
        color: #fff;
        border-radius: 9px;
        min-height: 40px;
        padding: 0 1rem;
        font-size: .9rem;
        font-weight: 700;
        cursor: pointer;
    }
    .atrd-filter-summary {
        font-size: .92rem;
        color: var(--atrd-muted);
    }

    .atrd-kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }
    .atrd-kpi {
        border: 1px solid var(--atrd-border);
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        padding: .95rem 1rem;
        display: grid;
        gap: 6px;
        position: relative;
        overflow: hidden;
    }
    .atrd-kpi::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }
    .atrd-kpi-blue::before { background: #1d4ed8; }
    .atrd-kpi-green::before { background: #10b981; }
    .atrd-kpi-amber::before { background: #f59e0b; }
    .atrd-kpi-purple::before { background: var(--atrd-primary); }
    .atrd-kpi-red::before { background: #ef4444; }
    .atrd-kpi-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }
    .atrd-kpi-title {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--atrd-muted);
        font-weight: 800;
    }
    .atrd-kpi-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .96rem;
    }
    .atrd-kpi-icon.blue { background: #eff6ff; color: #1d4ed8; }
    .atrd-kpi-icon.green { background: #ecfdf5; color: #047857; }
    .atrd-kpi-icon.amber { background: #fffbeb; color: #b45309; }
    .atrd-kpi-icon.purple { background: #eaf3fb; color: var(--atrd-primary); }
    .atrd-kpi-icon.red { background: #fef2f2; color: #b91c1c; }
    .atrd-kpi-value {
        font-size: 1.42rem;
        line-height: 1.05;
        letter-spacing: -.02em;
        color: var(--atrd-head);
        font-weight: 800;
    }
    .atrd-kpi-sub {
        font-size: .79rem;
        color: var(--atrd-muted);
    }

    .atrd-twin {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
        gap: 10px;
    }
    .atrd-card {
        border: 1px solid var(--atrd-border);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        overflow: hidden;
    }
    .atrd-card-head {
        border-bottom: 1px solid var(--atrd-border);
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .atrd-card-head h3 {
        margin: 0;
        color: var(--atrd-head);
        font-size: 1rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .atrd-card-head span {
        color: var(--atrd-muted);
        font-size: .8rem;
        font-weight: 600;
    }
    .atrd-card-body {
        padding: 1rem;
    }
    .atrd-chart-wrap {
        position: relative;
        width: 100%;
    }
    .atrd-chart-wrap canvas {
        width: 100% !important;
        display: block;
    }

    .atrd-status-layout {
        display: grid;
        gap: 10px;
    }
    .atrd-status-top {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    .atrd-donut-wrap {
        width: 120px;
        height: 120px;
        flex-shrink: 0;
    }
    .atrd-status-list {
        flex: 1;
        min-width: 180px;
        display: grid;
        gap: 6px;
    }
    .atrd-stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: .86rem;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: .42rem;
    }
    .atrd-stat-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .atrd-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        display: inline-block;
        margin-right: 6px;
    }

    .atrd-progress-grid {
        display: grid;
        gap: 10px;
    }
    .atrd-progress-item {
        display: grid;
        gap: 4px;
    }
    .atrd-progress-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: .82rem;
        color: var(--atrd-muted);
        font-weight: 700;
    }
    .atrd-progress-bar {
        height: 8px;
        border-radius: 999px;
        overflow: hidden;
        background: #e2e8f0;
    }
    .atrd-progress-fill {
        height: 100%;
        border-radius: 999px;
        width: 0;
        transition: width .5s ease;
    }
    .atrd-progress-fill.green { background: #10b981; }
    .atrd-progress-fill.red { background: #ef4444; }

    .atrd-table-wrap {
        overflow: auto;
    }
    .atrd-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 680px;
    }
    .atrd-table th {
        background: #eef5fb;
        color: #103250;
        border-bottom: 1px solid var(--atrd-border);
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 800;
        padding: .74rem .9rem;
        text-align: left;
    }
    .atrd-table td {
        border-bottom: 1px solid #f1f5f9;
        padding: .74rem .9rem;
        font-size: .86rem;
        color: var(--atrd-text);
        vertical-align: middle;
    }
    .atrd-table tbody tr:hover td {
        background: #f8fafc;
    }

    .atrd-tag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid transparent;
        padding: .18rem .54rem;
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        font-weight: 800;
    }
    .atrd-tag-reserved { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .atrd-tag-confirmed { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .atrd-tag-completed { background: #eaf3fb; border-color: #bfdbfe; color: #0a4880; }
    .atrd-tag-cancelled { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }

    .atrd-empty {
        text-align: center;
        color: var(--atrd-muted);
        padding: 2rem 1rem;
        font-size: .9rem;
    }

    @media (max-width: 1200px) {
        .atrd-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (max-width: 1000px) {
        .atrd-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .atrd-twin {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 680px) {
        .atrd-kpi-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="atrd" data-server-rendered-page="dashboard" data-page-title="Atrium Dashboard" data-live-refresh-ms="120000">
    <section class="atrd-filter-card">
        <form id="atrdFilterForm" method="GET" action="{{ route('atrium.dashboard') }}">
            <input type="hidden" id="atrdPeriodInput" name="period" value="{{ $period }}">
            <div class="atrd-filter-row">
                <button type="button" class="atrd-filter-pill {{ $period === 'today' ? 'is-active' : '' }}" data-period="today"><i class="fa-solid fa-sun"></i>Today</button>
                <button type="button" class="atrd-filter-pill {{ $period === 'week' ? 'is-active' : '' }}" data-period="week"><i class="fa-regular fa-calendar"></i>This Week</button>
                <button type="button" class="atrd-filter-pill {{ $period === 'month' ? 'is-active' : '' }}" data-period="month"><i class="fa-solid fa-calendar-days"></i>This Month</button>
                <button type="button" class="atrd-filter-pill {{ $period === 'range' ? 'is-active' : '' }}" data-period="range"><i class="fa-solid fa-calendar-check"></i>Custom Range</button>
                <div id="atrdFilterRangeFields" class="atrd-filter-range" {{ $period === 'range' ? '' : 'hidden' }}>
                    <input class="atrd-filter-input" type="date" id="atrdDateFrom" name="date_from" value="{{ $dateFrom }}">
                    <span style="color:var(--atrd-muted);font-size:.85rem;">to</span>
                    <input class="atrd-filter-input" type="date" id="atrdDateTo" name="date_to" value="{{ $dateTo }}">
                    <button type="submit" class="atrd-filter-apply">Apply Range</button>
                </div>
            </div>
        </form>
        <div class="atrd-filter-summary">
            Showing data for <strong>{{ $filterLabel }}</strong>: {{ $displayRange }}
        </div>
    </section>

    <section class="atrd-kpi-grid">
        <article class="atrd-kpi atrd-kpi-purple">
            <div class="atrd-kpi-head">
                <span class="atrd-kpi-title">Total Events</span>
                <span class="atrd-kpi-icon purple"><i class="fa-solid fa-calendar-check"></i></span>
            </div>
            <div class="atrd-kpi-value">{{ number_format($totalEvents) }}</div>
            <div class="atrd-kpi-sub">{{ number_format($eventsThisMonth) }} in {{ strtolower($filterLabel) }}</div>
        </article>

        <article class="atrd-kpi atrd-kpi-blue">
            <div class="atrd-kpi-head">
                <span class="atrd-kpi-title">Upcoming</span>
                <span class="atrd-kpi-icon blue"><i class="fa-solid fa-calendar-day"></i></span>
            </div>
            <div class="atrd-kpi-value">{{ number_format($upcomingEvents) }}</div>
            <div class="atrd-kpi-sub">Active reservations</div>
        </article>

        <article class="atrd-kpi atrd-kpi-green">
            <div class="atrd-kpi-head">
                <span class="atrd-kpi-title">Collected</span>
                <span class="atrd-kpi-icon green"><i class="fa-solid fa-peso-sign"></i></span>
            </div>
            <div class="atrd-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($totalCollected, 2) }}</div>
            <div class="atrd-kpi-sub">PHP {{ number_format($collectedThisMonth, 2) }} in {{ strtolower($filterLabel) }}</div>
        </article>

        <article class="atrd-kpi atrd-kpi-amber">
            <div class="atrd-kpi-head">
                <span class="atrd-kpi-title">Outstanding</span>
                <span class="atrd-kpi-icon amber"><i class="fa-solid fa-sack-dollar"></i></span>
            </div>
            <div class="atrd-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($outstanding, 2) }}</div>
            <div class="atrd-kpi-sub">Total due PHP {{ number_format($totalDue, 2) }}</div>
        </article>

        <article class="atrd-kpi atrd-kpi-red">
            <div class="atrd-kpi-head">
                <span class="atrd-kpi-title">Cancelled Events</span>
                <span class="atrd-kpi-icon red"><i class="fa-solid fa-ban"></i></span>
            </div>
            <div class="atrd-kpi-value">{{ number_format($statusCounts['cancelled']) }}</div>
            <div class="atrd-kpi-sub">Within selected range</div>
        </article>
    </section>

    <section class="atrd-twin">
        <article class="atrd-card">
            <div class="atrd-card-head">
                <h3><i class="fa-solid fa-chart-column" style="color:var(--atrd-primary);"></i>Daily Bookings Trend</h3>
                <span>{{ $displayRange }}</span>
            </div>
            <div class="atrd-card-body">
                <div class="atrd-chart-wrap" style="height:240px;">
                    <canvas id="atrdDailyChart"></canvas>
                </div>
            </div>
        </article>

        <article class="atrd-card">
            <div class="atrd-card-head">
                <h3><i class="fa-solid fa-circle-half-stroke" style="color:var(--atrd-primary);"></i>Status and Collection Mix</h3>
                <span>{{ $filterLabel }}</span>
            </div>
            <div class="atrd-card-body atrd-status-layout">
                <div class="atrd-status-top">
                    <div class="atrd-donut-wrap">
                        <canvas id="atrdStatusDonut"></canvas>
                    </div>
                    <div class="atrd-status-list">
                        <div class="atrd-stat-row"><span><i class="atrd-dot" style="background:#3b82f6;"></i>Reserved</span><strong>{{ number_format($statusCounts['reserved']) }}</strong></div>
                        <div class="atrd-stat-row"><span><i class="atrd-dot" style="background:#10b981;"></i>Confirmed</span><strong>{{ number_format($statusCounts['confirmed']) }}</strong></div>
                        <div class="atrd-stat-row"><span><i class="atrd-dot" style="background:#0f5fa8;"></i>Completed</span><strong>{{ number_format($statusCounts['completed']) }}</strong></div>
                        <div class="atrd-stat-row"><span><i class="atrd-dot" style="background:#ef4444;"></i>Cancelled</span><strong>{{ number_format($statusCounts['cancelled']) }}</strong></div>
                    </div>
                </div>

                <div class="atrd-progress-grid">
                    <div class="atrd-progress-item">
                        <div class="atrd-progress-label"><span>Collected Progress</span><span>{{ $collectionProgressPercent }}%</span></div>
                        <div class="atrd-progress-bar"><div class="atrd-progress-fill green js-atrd-progress" data-width="{{ $collectionProgressPercent }}"></div></div>
                    </div>
                    <div class="atrd-progress-item">
                        <div class="atrd-progress-label"><span>Outstanding Share</span><span>{{ $outstandingPercent }}%</span></div>
                        <div class="atrd-progress-bar"><div class="atrd-progress-fill red js-atrd-progress" data-width="{{ $outstandingPercent }}"></div></div>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="atrd-twin">
        <article class="atrd-card">
            <div class="atrd-card-head">
                <h3><i class="fa-solid fa-chart-area" style="color:var(--atrd-primary);"></i>Collection Trend</h3>
                <span>{{ $displayRange }}</span>
            </div>
            <div class="atrd-card-body">
                <div class="atrd-chart-wrap" style="height:240px;">
                    <canvas id="atrdRevenueChart"></canvas>
                </div>
            </div>
        </article>

        <article class="atrd-card">
            <div class="atrd-card-head">
                <h3><i class="fa-solid fa-clock" style="color:var(--atrd-primary);"></i>Events in Range</h3>
                <span>{{ $nextEvents->count() }} event(s)</span>
            </div>
            @if ($nextEvents->isEmpty())
                <div class="atrd-empty">
                    <i class="fa-solid fa-calendar-xmark" style="font-size:1.4rem;color:#cbd5e1;display:block;margin-bottom:8px;"></i>
                    No events found in this selected range.
                </div>
            @else
                <div class="atrd-table-wrap">
                    <table class="atrd-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Date</th>
                                <th>Contact</th>
                                <th>Hall</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($nextEvents as $event)
                                @php
                                    $tagClass = match ($event->booking_status) {
                                        'confirmed' => 'atrd-tag-confirmed',
                                        'completed' => 'atrd-tag-completed',
                                        'cancelled' => 'atrd-tag-cancelled',
                                        default => 'atrd-tag-reserved',
                                    };
                                @endphp
                                <tr>
                                    <td><strong>{{ $event->event_code }}</strong></td>
                                    <td style="white-space:nowrap;">{{ $event->date_of_event?->format('M d, Y') }}</td>
                                    <td>{{ $event->name_contact_person }}</td>
                                    <td>{{ $event->functionHall?->name ?? '-' }}</td>
                                    <td><span class="atrd-tag {{ $tagClass }}">{{ ucfirst($event->booking_status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>
    </section>
</div>

<script id="atrdDailyStatsJson" type="application/json">@json($dailyBookingStats)</script>
<script id="atrdStatusCountsJson" type="application/json">@json($statusCounts)</script>
<script id="atrdMonthlyRevenueJson" type="application/json">@json($monthlyRevenue)</script>

<script>
(function () {
    const filterForm = document.getElementById('atrdFilterForm');
    const periodInput = document.getElementById('atrdPeriodInput');
    const rangeFields = document.getElementById('atrdFilterRangeFields');
    const dateFromInput = document.getElementById('atrdDateFrom');
    const dateToInput = document.getElementById('atrdDateTo');
    const filterPills = document.querySelectorAll('.atrd-filter-pill[data-period]');

    filterPills.forEach((pill) => {
        pill.addEventListener('click', () => {
            if (!periodInput || !filterForm) return;

            const selected = pill.dataset.period || 'month';
            periodInput.value = selected;
            filterPills.forEach((p) => p.classList.toggle('is-active', p === pill));

            if (rangeFields) {
                rangeFields.hidden = selected !== 'range';
            }

            if (selected !== 'range') {
                filterForm.submit();
            }
        });
    });

    if (filterForm) {
        filterForm.addEventListener('submit', (event) => {
            if (!periodInput || periodInput.value !== 'range') {
                return;
            }

            const from = dateFromInput?.value || '';
            const to = dateToInput?.value || '';
            if (from === '' || to === '' || from > to) {
                event.preventDefault();
                alert('Please select a valid custom date range.');
            }
        });
    }

    document.querySelectorAll('.js-atrd-progress').forEach((el) => {
        const width = Number.parseFloat(el.dataset.width || '0');
        const clamped = Number.isFinite(width) ? Math.min(100, Math.max(0, width)) : 0;
        el.style.width = `${clamped}%`;
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

    if (typeof Chart === 'undefined') {
        return;
    }

    const dailyStats = parseJsonScript('atrdDailyStatsJson', []);
    const statusCounts = parseJsonScript('atrdStatusCountsJson', {
        reserved: 0,
        confirmed: 0,
        completed: 0,
        cancelled: 0,
    });
    const monthlyRevenue = parseJsonScript('atrdMonthlyRevenueJson', []);

    const dailyCanvas = document.getElementById('atrdDailyChart');
    if (dailyCanvas) {
        new Chart(dailyCanvas, {
            type: 'bar',
            data: {
                labels: dailyStats.map((d) => d.label),
                datasets: [
                    {
                        label: 'Bookings',
                        data: dailyStats.map((d) => d.bookings),
                        backgroundColor: '#1a7fd4',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Completed',
                        data: dailyStats.map((d) => d.completed),
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 11 },
                            boxWidth: 12,
                        },
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 10 },
                        },
                        grid: { color: '#f1f5f9' },
                    },
                },
            },
        });
    }

    const statusCanvas = document.getElementById('atrdStatusDonut');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Reserved', 'Confirmed', 'Completed', 'Cancelled'],
                datasets: [{
                    data: [
                        Number(statusCounts.reserved || 0),
                        Number(statusCounts.confirmed || 0),
                        Number(statusCounts.completed || 0),
                        Number(statusCounts.cancelled || 0),
                    ],
                    backgroundColor: ['#3b82f6', '#10b981', '#0f5fa8', '#ef4444'],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                },
            },
        });
    }

    const revenueCanvas = document.getElementById('atrdRevenueChart');
    if (revenueCanvas) {
        new Chart(revenueCanvas, {
            type: 'line',
            data: {
                labels: monthlyRevenue.map((r) => r.label),
                datasets: [{
                    label: 'Collected (PHP)',
                    data: monthlyRevenue.map((r) => r.amount),
                    borderColor: '#0f5fa8',
                    backgroundColor: 'rgba(15,95,168,0.14)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#0f5fa8',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.34,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' PHP ' + Number(ctx.parsed.y).toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }),
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 10 },
                            callback: (v) => 'PHP ' + Number(v).toLocaleString('en-PH'),
                        },
                        grid: { color: '#f1f5f9' },
                    },
                },
            },
        });
    }
})();
</script>
@endsection
