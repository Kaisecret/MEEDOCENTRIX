@extends('layouts.app')

@section('content')
@include('terminal.partials.terminal_shared_styles')

<div class="tm" data-server-rendered-page="dashboard" data-page-title="Terminal Dashboard">
    <section class="tm-hero">
        <div>
            <h2>TFCO Terminal Dashboard</h2>
            <p>Live view of parking sessions, collections, and terminal traffic trends powered by database records.</p>
        </div>
        <div class="tm-hero-meta">
            <span class="tm-help">Updated {{ now()->format('M d, Y h:i A') }}</span>
            <span class="tm-hero-clock">{{ now()->format('h:i A') }}</span>
        </div>
    </section>

    @if (session('status'))
        <div class="tm-flash">{{ session('status') }}</div>
    @endif

    <section class="tm-kpi-grid">
        <article class="tm-kpi">
            <div class="tm-kpi-head">
                <span class="tm-kpi-title">Entries Today</span>
                <span class="tm-kpi-icon blue"><i class="fas fa-right-to-bracket"></i></span>
            </div>
            <strong class="tm-kpi-value">{{ number_format($todayEntries) }}</strong>
            <span class="tm-kpi-sub">Vehicles checked in today</span>
        </article>
        <article class="tm-kpi">
            <div class="tm-kpi-head">
                <span class="tm-kpi-title">Currently Parked</span>
                <span class="tm-kpi-icon amber"><i class="fas fa-car-side"></i></span>
            </div>
            <strong class="tm-kpi-value">{{ number_format($currentlyParked) }}</strong>
            <span class="tm-kpi-sub">Open sessions without checkout</span>
        </article>
        <article class="tm-kpi">
            <div class="tm-kpi-head">
                <span class="tm-kpi-title">Ready for Payment</span>
                <span class="tm-kpi-icon purple"><i class="fas fa-receipt"></i></span>
            </div>
            <strong class="tm-kpi-value">{{ number_format($readyForPayment) }}</strong>
            <span class="tm-kpi-sub">Checked out and awaiting payment</span>
        </article>
        <article class="tm-kpi">
            <div class="tm-kpi-head">
                <span class="tm-kpi-title">Revenue Today</span>
                <span class="tm-kpi-icon green"><i class="fas fa-money-bill-wave"></i></span>
            </div>
            <strong class="tm-kpi-value">PHP {{ number_format($todayRevenue, 2) }}</strong>
            <span class="tm-kpi-sub">{{ number_format($activeVehicles) }} active registered vehicles</span>
        </article>
    </section>

    <section class="tm-twin">
        <article class="tm-card">
            <div class="tm-card-head">
                <h3><i class="fas fa-chart-line"></i> 14-Day Volume and Revenue</h3>
                <span>Entries vs collection trend</span>
            </div>
            <div class="tm-card-body">
                <div class="tm-chart-wrap">
                    <canvas id="terminalTrendChart" height="115"></canvas>
                </div>
            </div>
        </article>

        <article class="tm-card">
            <div class="tm-card-head">
                <h3><i class="fas fa-chart-pie"></i> 30-Day Vehicle Mix</h3>
                <span>By vehicle type</span>
            </div>
            <div class="tm-card-body">
                <div class="tm-chart-wrap">
                    <canvas id="terminalMixChart" height="210"></canvas>
                </div>
            </div>
        </article>
    </section>

    <section class="tm-card">
        <div class="tm-card-head">
            <h3><i class="fas fa-calendar-alt"></i> 6-Month Revenue</h3>
            <div class="tm-action-row">
                <a href="{{ route('terminal.records') }}" class="tm-btn-outline"><i class="fas fa-list"></i> View Records</a>
                <a href="{{ route('terminal.send_payment') }}" class="tm-btn-primary"><i class="fas fa-file-invoice-dollar"></i> Record Payment</a>
            </div>
        </div>
        <div class="tm-card-body">
            <div class="tm-chart-wrap">
                <canvas id="terminalMonthlyChart" height="96"></canvas>
            </div>
        </div>
    </section>

    <section class="tm-card">
        <div class="tm-card-head">
            <h3><i class="fas fa-clock-rotate-left"></i> Recent Parking Activity</h3>
            <span>Latest 10 records</span>
        </div>
        <div class="tm-table-wrap">
            <table class="tm-table">
                <thead>
                    <tr>
                        <th>Log #</th>
                        <th>Vehicle</th>
                        <th>Type</th>
                        <th>Entry</th>
                        <th>Exit</th>
                        <th>Billed</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentLogs as $log)
                        @php
                            $isParked = $log->exit_at === null;
                            $isPaid = $log->payment !== null;
                        @endphp
                        <tr>
                            <td>{{ $log->log_number }}</td>
                            <td>{{ $log->vehicle?->plate_number ?? '-' }}</td>
                            <td>{{ $log->vehicle?->type?->name ?? '-' }}</td>
                            <td>{{ optional($log->entry_at)->format('m/d/Y h:i A') }}</td>
                            <td>{{ optional($log->exit_at)->format('m/d/Y h:i A') ?: '-' }}</td>
                            <td>PHP {{ number_format($isPaid ? (float) $log->payment->paid_amount : $log->billedAmount(), 2) }}</td>
                            <td>
                                @if ($isPaid)
                                    <span class="tm-tag tm-tag-paid">Paid</span>
                                @elseif ($isParked)
                                    <span class="tm-tag tm-tag-parked">Parked</span>
                                @else
                                    <span class="tm-tag tm-tag-ready">Ready</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="tm-empty">No parking activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
    (function () {
        const labels = @json($dailyTrend->pluck('label')->values());
        const dailyEntries = @json($dailyTrend->pluck('entries')->values());
        const dailyRevenue = @json($dailyTrend->pluck('revenue')->values());
        const monthLabels = @json($monthlyRevenue->pluck('label')->values());
        const monthAmounts = @json($monthlyRevenue->pluck('amount')->values());
        const mixLabels = @json($typeLabels);
        const mixValues = @json($typeValues);

        const trendCanvas = document.getElementById('terminalTrendChart');
        if (trendCanvas && window.Chart) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Entries',
                            data: dailyEntries,
                            borderColor: '#0f5fa8',
                            backgroundColor: 'rgba(15,95,168,.15)',
                            yAxisID: 'y',
                            tension: .28,
                            fill: true
                        },
                        {
                            label: 'Revenue (PHP)',
                            data: dailyRevenue,
                            borderColor: '#047857',
                            backgroundColor: 'rgba(4,120,87,.10)',
                            yAxisID: 'y1',
                            tension: .28
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
                    },
                    plugins: { legend: { position: 'top' } }
                }
            });
        }

        const monthlyCanvas = document.getElementById('terminalMonthlyChart');
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

        const mixCanvas = document.getElementById('terminalMixChart');
        if (mixCanvas && window.Chart) {
            new Chart(mixCanvas, {
                type: 'doughnut',
                data: {
                    labels: mixLabels,
                    datasets: [{
                        data: mixValues,
                        backgroundColor: ['#0f5fa8', '#1a7fd4', '#047857', '#d97706', '#dc2626'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    })();
</script>
@endsection

