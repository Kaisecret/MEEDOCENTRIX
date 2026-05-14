@extends('layouts.app')

@section('content')
@include('terminal.partials.terminal_shared_styles')
@php
    /** @var \Illuminate\Support\Collection<int, array{label:string,revenue:float,transactions:int}> $dailyRevenueTrend */
    /** @var \Illuminate\Support\Collection<int, array{label:string,amount:float}> $monthlyRevenue */

    $tmDailyTrend = $dailyRevenueTrend instanceof \Illuminate\Support\Collection
        ? $dailyRevenueTrend
        : collect($dailyRevenueTrend ?? []);
    $tmMonthlyTrend = $monthlyRevenue instanceof \Illuminate\Support\Collection
        ? $monthlyRevenue
        : collect($monthlyRevenue ?? []);

    $tmDailyLabels = $tmDailyTrend->pluck('label')->values()->all();
    $tmDailyRevenue = $tmDailyTrend->pluck('revenue')->values()->all();
    $tmDailyTransactions = $tmDailyTrend->pluck('transactions')->values()->all();
    $tmMonthLabels = $tmMonthlyTrend->pluck('label')->values()->all();
    $tmMonthAmounts = $tmMonthlyTrend->pluck('amount')->values()->all();
@endphp

<div class="tm" data-server-rendered-page="dashboard" data-page-title="Terminal Dashboard">
    <section class="tm-dash-filter-card">
        <div class="tm-dash-filter-row">
            <form id="tmDashFilterForm" method="GET" action="{{ route('terminal.dashboard') }}" class="tm-dash-filter-form">
                <input type="hidden" id="tmDashPeriodInput" name="period" value="{{ $period }}">
                <button type="button" class="tm-dash-filter-pill {{ $period === 'today' ? 'is-active' : '' }}" data-period="today"><i class="fa-solid fa-sun"></i>Today</button>
                <button type="button" class="tm-dash-filter-pill {{ $period === 'week' ? 'is-active' : '' }}" data-period="week"><i class="fa-regular fa-calendar"></i>This Week</button>
                <button type="button" class="tm-dash-filter-pill {{ $period === 'month' ? 'is-active' : '' }}" data-period="month"><i class="fa-solid fa-calendar-days"></i>This Month</button>
                <button type="button" class="tm-dash-filter-pill {{ $period === 'range' ? 'is-active' : '' }}" data-period="range"><i class="fa-solid fa-calendar-check"></i>Custom Range</button>
                <div id="tmDashFilterRangeFields" class="tm-dash-filter-range" {{ $period === 'range' ? '' : 'hidden' }}>
                    <input class="tm-dash-filter-input" type="date" id="tmDashDateFrom" name="date_from" value="{{ $dateFrom }}">
                    <span class="tm-dash-filter-to">to</span>
                    <input class="tm-dash-filter-input" type="date" id="tmDashDateTo" name="date_to" value="{{ $dateTo }}">
                    <button type="submit" class="tm-dash-filter-apply">Apply Range</button>
                </div>
            </form>
        </div>
        <div class="tm-dash-filter-summary">
            Showing data for <strong>{{ $filterLabel }}</strong>: {{ $displayRange }}
        </div>
    </section>

    @include('terminal.partials.toast_stack')

    <section class="tm-rev-kpi-grid">
        <article class="tm-kpi">
            <div class="tm-kpi-head">
                <span class="tm-kpi-title">Revenue ({{ $filterLabel }})</span>
                <span class="tm-kpi-icon green"><i class="fas fa-coins"></i></span>
            </div>
            <strong class="tm-kpi-value">PHP {{ number_format($filterRevenue, 2) }}</strong>
            <span class="tm-kpi-sub">{{ number_format($filterPaidCount) }} paid transaction{{ $filterPaidCount === 1 ? '' : 's' }}</span>
        </article>

        <article class="tm-kpi">
            <div class="tm-kpi-head">
                <span class="tm-kpi-title">Paid Transactions</span>
                <span class="tm-kpi-icon blue"><i class="fas fa-calendar-day"></i></span>
            </div>
            <strong class="tm-kpi-value">{{ number_format($filterPaidCount) }}</strong>
            <span class="tm-kpi-sub">{{ $filterLabel }} period paid count</span>
        </article>

        <article class="tm-kpi">
            <div class="tm-kpi-head">
                <span class="tm-kpi-title">Revenue This Year</span>
                <span class="tm-kpi-icon purple"><i class="fas fa-chart-line"></i></span>
            </div>
            <strong class="tm-kpi-value">PHP {{ number_format($yearRevenue, 2) }}</strong>
            <span class="tm-kpi-sub">Year-to-date collections</span>
        </article>

        <article class="tm-kpi">
            <div class="tm-kpi-head">
                <span class="tm-kpi-title">Pending Queue</span>
                <span class="tm-kpi-icon amber"><i class="fas fa-hourglass-half"></i></span>
            </div>
            <strong class="tm-kpi-value">{{ number_format($pendingCount) }}</strong>
            <span class="tm-kpi-sub">Potential PHP {{ number_format($pendingAmount, 2) }}</span>
        </article>

        <article class="tm-kpi">
            <div class="tm-kpi-head">
                <span class="tm-kpi-title">Avg Ticket ({{ $filterLabel }})</span>
                <span class="tm-kpi-icon red"><i class="fas fa-receipt"></i></span>
            </div>
            <strong class="tm-kpi-value">PHP {{ number_format($avgTicket, 2) }}</strong>
            <span class="tm-kpi-sub">Average paid fee per ticket</span>
        </article>
    </section>

    <section class="tm-twin">
        <article class="tm-card">
            <div class="tm-card-head">
                <h3><i class="fas fa-wave-square"></i> Revenue Trend</h3>
                <span>{{ $displayRange }}</span>
            </div>
            <div class="tm-card-body">
                <div class="tm-chart-wrap">
                    <canvas id="terminalRevenueTrendChart" height="118"></canvas>
                </div>
            </div>
        </article>

        <article class="tm-card">
            <div class="tm-card-head">
                <h3><i class="fas fa-chart-column"></i> Monthly Revenue Summary</h3>
                <span>{{ $displayRange }}</span>
            </div>
            <div class="tm-card-body">
                <div class="tm-chart-wrap">
                    <canvas id="terminalRevenueMonthlyChart" height="118"></canvas>
                </div>
            </div>
        </article>
    </section>

    <section class="tm-card">
        <div class="tm-card-head">
            <h3><i class="fas fa-route"></i> Top Revenue Routes ({{ $filterLabel }})</h3>
            <span>{{ number_format($routePerformance->count()) }} route{{ $routePerformance->count() === 1 ? '' : 's' }} listed</span>
        </div>
        <div class="tm-table-wrap">
            <table class="tm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Route / Operator</th>
                        <th>Transactions</th>
                        <th>Total Revenue</th>
                        <th>Avg Ticket</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($routePerformance as $index => $route)
                        @php
                            $txCount = (int) ($route->total_transactions ?? 0);
                            $totalRevenue = (float) ($route->total_revenue ?? 0);
                            $avgRouteFare = $txCount > 0 ? $totalRevenue / $txCount : 0;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $route->route_name ?: '-' }}</td>
                            <td>{{ number_format($txCount) }}</td>
                            <td>PHP {{ number_format($totalRevenue, 2) }}</td>
                            <td>PHP {{ number_format($avgRouteFare, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="tm-empty">No paid route data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="tm-card">
        <div class="tm-card-head">
            <h3><i class="fas fa-clock-rotate-left"></i> Recent Paid Transactions</h3>
            <span>{{ $filterLabel }} view (latest 10)</span>
        </div>
        <div class="tm-table-wrap">
            <table class="tm-table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Vehicle</th>
                        <th>Route / Operator</th>
                        <th>Amount</th>
                        <th>Paid At</th>
                        <th>Saved By</th>
                        <th>Paid By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPaid as $payment)
                        <tr>
                            <td>{{ $payment->ticket_number ?: '-' }}</td>
                            <td>{{ $payment->vehicle_kind ?: '-' }}</td>
                            <td>{{ $payment->route_name ?: '-' }}</td>
                            <td>PHP {{ number_format((float) $payment->total_payment, 2) }}</td>
                            <td>{{ optional($payment->paid_at)->format('m/d/Y h:i A') ?: '-' }}</td>
                            <td>{{ $payment->recordedBy?->name ?: '-' }}</td>
                            <td>{{ $payment->paidBy?->name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="tm-empty">No paid transactions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<style>
    .tm[data-server-rendered-page="dashboard"] {
        gap: 10px;
    }
    .tm-dash-filter-card {
        border: 1px solid #d6e0ea;
        border-radius: 12px;
        background: #fff;
        padding: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
    }
    .tm-dash-filter-row {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .tm-dash-filter-form {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-left: auto;
    }
    .tm-dash-filter-pill {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #155f8f;
        border-radius: 999px;
        min-height: 40px;
        padding: 0 16px;
        font-size: .92rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }
    .tm-dash-filter-pill:hover {
        background: #f0f7fd;
        border-color: #b9c8dc;
        transform: translateY(-1px);
    }
    .tm-dash-filter-pill.is-active {
        background: #155f8f;
        border-color: #155f8f;
        color: #fff;
        box-shadow: 0 4px 12px rgba(21, 95, 143, .18);
    }
    .tm-dash-filter-range {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .tm-dash-filter-range[hidden] {
        display: none !important;
    }
    .tm-dash-filter-input {
        min-height: 40px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        padding: 0 .78rem;
        font-size: .92rem;
        color: #334155;
        background: #fff;
    }
    .tm-dash-filter-input:focus {
        outline: none;
        border-color: #155f8f;
        box-shadow: 0 0 0 3px rgba(21,95,143,.14);
    }
    .tm-dash-filter-to {
        color: #64748b;
        font-size: .85rem;
    }
    .tm-dash-filter-apply {
        border: 1px solid #155f8f;
        background: #155f8f;
        color: #fff;
        border-radius: 9px;
        min-height: 40px;
        padding: 0 1rem;
        font-size: .9rem;
        font-weight: 700;
        cursor: pointer;
    }
    .tm-dash-filter-summary {
        font-size: .92rem;
        color: #64748b;
    }
    .tm-rev-kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }
    .tm[data-server-rendered-page="dashboard"] .tm-kpi,
    .tm[data-server-rendered-page="dashboard"] .tm-card-head,
    .tm[data-server-rendered-page="dashboard"] .tm-card-body {
        padding: 10px;
    }
    .tm[data-server-rendered-page="dashboard"] .tm-card-head {
        gap: 10px;
    }
    .tm[data-server-rendered-page="dashboard"] .tm-table th,
    .tm[data-server-rendered-page="dashboard"] .tm-table td {
        padding: 10px;
    }

    @media (max-width: 1200px) {
        .tm-rev-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (max-width: 860px) {
        .tm-rev-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 640px) {
        .tm-dash-filter-row {
            align-items: flex-start;
        }
        .tm-rev-kpi-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    (function () {
        const filterForm = document.getElementById('tmDashFilterForm');
        const periodInput = document.getElementById('tmDashPeriodInput');
        const rangeFields = document.getElementById('tmDashFilterRangeFields');
        const dateFromInput = document.getElementById('tmDashDateFrom');
        const dateToInput = document.getElementById('tmDashDateTo');
        const periodButtons = Array.from(document.querySelectorAll('.tm-dash-filter-pill[data-period]'));

        function syncFilterUI() {
            const selected = periodInput ? periodInput.value : 'month';
            periodButtons.forEach(function (button) {
                button.classList.toggle('is-active', button.dataset.period === selected);
            });
            if (rangeFields) {
                rangeFields.hidden = selected !== 'range';
            }
            const requireRange = selected === 'range';
            if (dateFromInput) dateFromInput.required = requireRange;
            if (dateToInput) dateToInput.required = requireRange;
        }

        periodButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                if (!periodInput || !filterForm) return;
                const selected = button.dataset.period || 'month';
                periodInput.value = selected;
                syncFilterUI();
                if (selected !== 'range') {
                    filterForm.requestSubmit();
                }
            });
        });

        if (filterForm) {
            filterForm.addEventListener('submit', function (event) {
                if (!periodInput || periodInput.value !== 'range') return;
                const from = dateFromInput ? dateFromInput.value : '';
                const to = dateToInput ? dateToInput.value : '';
                if (!from || !to || from > to) {
                    event.preventDefault();
                    alert('Please select a valid custom date range.');
                }
            });
        }

        syncFilterUI();

        const dailyLabels = <?php echo json_encode($tmDailyLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const dailyRevenue = <?php echo json_encode($tmDailyRevenue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const dailyTransactions = <?php echo json_encode($tmDailyTransactions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const monthLabels = <?php echo json_encode($tmMonthLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const monthAmounts = <?php echo json_encode($tmMonthAmounts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        const trendCanvas = document.getElementById('terminalRevenueTrendChart');
        if (trendCanvas && window.Chart) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [
                        {
                            label: 'Revenue (PHP)',
                            data: dailyRevenue,
                            borderColor: '#0f5fa8',
                            backgroundColor: 'rgba(15,95,168,.12)',
                            yAxisID: 'y',
                            tension: .28,
                            fill: true
                        },
                        {
                            label: 'Paid Transactions',
                            data: dailyTransactions,
                            type: 'bar',
                            backgroundColor: 'rgba(4,120,87,.25)',
                            borderColor: '#047857',
                            borderWidth: 1,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true },
                        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 } }
                    },
                    plugins: { legend: { position: 'top' } }
                }
            });
        }

        const monthlyCanvas = document.getElementById('terminalRevenueMonthlyChart');
        if (monthlyCanvas && window.Chart) {
            new Chart(monthlyCanvas, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Revenue (PHP)',
                        data: monthAmounts,
                        backgroundColor: 'rgba(26,127,212,.72)',
                        borderColor: '#1a7fd4',
                        borderWidth: 1.5,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { display: false } }
                }
            });
        }
    })();
</script>
@endsection
