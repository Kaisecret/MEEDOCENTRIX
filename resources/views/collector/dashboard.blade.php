@extends('layouts.app')

@section('content')
    @php
        /** @var \App\Models\CollectorDepartmentAssignment|null $assignment */
    @endphp

    <style>
        :root {
            --col-primary: #0f5fa8;
            --col-primary-dk: #0a4880;
            --col-teal: #0891b2;
            --col-green: #059669;
            --col-amber: #d97706;
            --col-red: #dc2626;
            --col-border: #e2e8f0;
            --col-soft: #f8fafc;
            --col-text: #334155;
            --col-muted: #64748b;
            --col-head: #0f172a;
        }

        .col-page {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding-bottom: 2rem;
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--col-text);
            width: 100%;
            max-width: 100vw;
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .col-page * {
            box-sizing: border-box;
        }

        /* ── Alerts ── */
        .col-alert {
            border-radius: 10px;
            padding: .8rem 1rem;
            display: flex;
            gap: 8px;
            align-items: center;
            font-size: .9rem;
            font-weight: 600;
        }

        .col-alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .col-alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* ── Hero Banner ── */
        .col-hero { display: none; }

        .col-hero-left h1 {
            margin: 0 0 4px;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .col-hero-left p {
            margin: 0 0 8px;
            font-size: .92rem;
            opacity: .88;
        }

        .col-range-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 999px;
            padding: .25rem .7rem;
        }

        .col-hero-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .col-hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 9px;
            padding: .6rem 1.1rem;
            font-size: .88rem;
            font-weight: 700;
            text-decoration: none;
            transition: background .2s, transform .15s;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .col-hero-btn:hover {
            transform: translateY(-1px);
        }

        .col-hero-btn-amber {
            background: #f59e0b;
            color: #fff;
        }

        .col-hero-btn-amber:hover {
            background: #d97706;
        }

        .col-hero-btn-white {
            background: #fff;
            color: var(--col-primary);
        }

        .col-hero-btn-white:hover {
            background: #f0f7ff;
        }

        /* ── Filter Bar ── */
        .col-filter-wrap {
            background: #fff;
            border: 1px solid var(--col-border);
            border-radius: 12px;
            padding: .85rem 1.1rem;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .col-filter-row {
            display: grid;
            grid-template-columns: 200px 1fr 1fr;
            gap: 10px;
            align-items: center;
        }

        .col-filter-label {
            font-size: .75rem;
            font-weight: 700;
            color: var(--col-muted);
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 5px;
        }

        .col-filter-input {
            width: 100%;
            border: 1.5px solid var(--col-border);
            border-radius: 9px;
            min-height: 40px;
            background: var(--col-soft);
            color: var(--col-head);
            padding: .5rem .75rem;
            font-size: .88rem;
            font-family: inherit;
            transition: border-color .2s;
            outline: none;
        }

        .col-filter-input:focus {
            border-color: var(--col-primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(15, 95, 168, .1);
        }

        /* ── KPI Grid ── */
        .col-kpi-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .col-kpi {
            border-radius: 18px;
            padding: 1.1rem;
            display: flex;
            flex-direction: column;
            gap: 18px;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .06);
        }

        .col-kpi-pending {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
        }

        .col-kpi-awaiting {
            background: linear-gradient(135deg, #0f172a, #111827);
        }

        .col-kpi-accepted {
            background: linear-gradient(135deg, #0f766e, #10b981);
        }

        .col-kpi-rejected {
            background: linear-gradient(135deg, #7c2d12, #ef4444);
        }

        .col-kpi-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .col-kpi-label {
            font-size: .9rem;
            font-weight: 600;
            opacity: .95;
        }

        .col-kpi-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
        }

        .col-kpi-bottom {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .col-kpi-value {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
        }

        .col-kpi-sub {
            font-size: .75rem;
            font-weight: 600;
            opacity: .9;
        }

        /* ── Chart Grid ── */
        .col-chart-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: 1fr;
        }

        .col-chart-card {
            border: 1px solid var(--col-border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .col-chart-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            padding: .9rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .col-chart-head h3 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--col-head);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .col-chart-head span {
            font-size: .78rem;
            color: var(--col-muted);
            font-weight: 600;
        }

        .col-chart-body {
            padding: 1rem;
            min-height: 280px;
        }

        .col-chart-body canvas {
            width: 100% !important;
            height: 250px !important;
        }

        .col-history-wrap {
            overflow-x: auto;
        }

        .col-history-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .col-history-table thead th {
            background: #eef5fb;
            color: #0c3a5b;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: .72rem;
            font-weight: 800;
            text-align: left;
            padding: .72rem .8rem;
            border-bottom: 1px solid #dbe5f0;
            white-space: nowrap;
        }

        .col-history-table tbody td {
            padding: .72rem .8rem;
            border-bottom: 1px solid #eef2f7;
            font-size: .86rem;
            color: var(--col-text);
            vertical-align: middle;
            white-space: nowrap;
        }

        .col-history-table tbody tr:hover td {
            background: #f8fafc;
        }

        .col-history-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .col-history-num {
            text-align: left;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .col-status-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .2rem .58rem;
            font-size: .72rem;
            font-weight: 700;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .col-status-badge.pending {
            color: #1d4ed8;
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .col-status-badge.awaiting {
            color: #92400e;
            background: #fffbeb;
            border-color: #fde68a;
        }

        .col-status-badge.accepted {
            color: #065f46;
            background: #ecfdf5;
            border-color: #a7f3d0;
        }

        .col-status-badge.rejected {
            color: #991b1b;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .col-history-empty {
            padding: 1rem;
            color: var(--col-muted);
            font-size: .86rem;
        }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .col-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .col-chart-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {
            .col-page {
                gap: 10px;
            }

            .col-hero {
                padding: 1rem 1.1rem;
            }

            .col-hero-left h1 {
                font-size: 1.2rem;
            }

            .col-hero-left p {
                font-size: .82rem;
            }

            .col-range-badge {
                font-size: .68rem;
            }

            .col-filter-row {
                grid-template-columns: 1.2fr 1fr 1fr;
                gap: 8px;
            }

            .col-filter-wrap {
                padding: .7rem .9rem;
            }

            .col-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .col-kpi {
                padding: .8rem .9rem;
            }

            .col-kpi-value {
                font-size: 1.2rem;
            }

            .col-kpi-sub {
                font-size: .75rem;
            }

            .col-kpi-icon {
                width: 32px;
                height: 32px;
                font-size: .9rem;
            }

            .col-chart-body {
                padding: .7rem .8rem;
                min-height: 200px;
            }

            .col-chart-body canvas {
                height: 190px !important;
            }
        }

        @media (max-width: 480px) {
            .col-page {
                gap: 10px;
                padding-bottom: 1.2rem;
                overflow-x: hidden;
            }

            .col-hero {
                display: none;
            }

            /* Ultra-Compact Filter (Forces 1 line) */
            .col-filter-wrap {
                padding: .4rem .3rem;
            }

            .col-filter-row {
                display: flex;
                gap: 4px;
                width: 100%;
            }

            .col-filter-row>div {
                flex: 1 1 0%;
                min-width: 0;
            }

            .col-filter-label {
                margin-bottom: 2px;
                font-size: .55rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: block;
            }

            .col-filter-input {
                min-height: 28px;
                padding: 0;
                font-size: .6rem;
                border-radius: 5px;
                min-width: 0;
                width: 100%;
                text-align: center;
            }
            .col-filter-input::-webkit-datetime-edit { padding: 0; }
            .col-filter-input::-webkit-datetime-edit-fields-wrapper { padding: 0; }

            /* Force 2x2 grid on mobile */
            .col-kpi-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 6px;
                width: 100%;
            }

            .col-kpi {
                padding: .75rem .5rem;
                gap: 8px;
                border-radius: 10px;
                min-width: 0;
            }

            .col-kpi-label {
                font-size: .65rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .col-kpi-icon {
                width: 20px;
                height: 20px;
                font-size: .55rem;
                flex-shrink: 0;
            }

            .col-kpi-value {
                font-size: 1.05rem;
                overflow-wrap: anywhere;
                word-break: break-all;
            }

            .col-kpi-sub {
                font-size: .55rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .col-chart-grid {
                display: flex;
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }

            .col-chart-card {
                width: 100%;
                overflow: hidden;
            }

            .col-chart-body {
                min-height: 180px;
                padding: .5rem;
                position: relative;
                width: 100%;
            }

            .col-chart-body canvas {
                height: 175px !important;
                width: 100% !important;
            }
        }
    </style>

    <div data-server-rendered-page="dashboard" data-page-title="Collector Dashboard" class="col-page">

        @if(session('status'))
            <div class="col-alert col-alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="col-alert col-alert-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
        @endif

        {{-- ── Hero ── --}}
        <section class="col-hero">
            <div class="col-hero-left">
                <h1><i class="fa-solid fa-wallet" style="opacity:.85;margin-right:8px;"></i>Collector Dashboard</h1>
                <p>
                    @if($assignment?->department?->name)
                        Assigned Department: <strong>{{ $assignment->department->name }}</strong>
                    @else
                        No department assignment — contact admin.
                    @endif
                </p>
                <span class="col-range-badge"><i class="fa-solid fa-calendar-days"></i> {{ $rangeLabel }}</span>
            </div>
            <div class="col-hero-actions">
                <a href="{{ route('collector.pending_collections') }}" class="col-hero-btn col-hero-btn-amber">
                    <i class="fa-solid fa-clock"></i> Pending Collections
                </a>
                <a href="{{ route('collector.payments') }}" class="col-hero-btn col-hero-btn-white">
                    <i class="fa-solid fa-paper-plane"></i> Payment Updates
                </a>
            </div>
        </section>

        {{-- ── Filter Card ── --}}
        <section class="col-filter-wrap">
            <form method="GET" action="{{ route('collector.dashboard') }}" class="col-filter-row"
                id="collectorDashboardFilterForm">
                <div>
                    <div class="col-filter-label">Period</div>
                    <select name="period" class="col-filter-input">
                        <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="all" {{ $period === 'all' ? 'selected' : '' }}>All Dates</option>
                        <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div>
                    <div class="col-filter-label">From</div>
                    <input type="date" name="from" value="{{ $from }}" class="col-filter-input">
                </div>
                <div>
                    <div class="col-filter-label">To</div>
                    <input type="date" name="to" value="{{ $to }}" class="col-filter-input">
                </div>
            </form>
        </section>

        {{-- ── KPI Cards ── --}}
        <section class="col-kpi-grid">
            <article class="col-kpi col-kpi-pending">
                <div class="col-kpi-top">
                    <div class="col-kpi-label">To Collect</div>
                    <div class="col-kpi-icon"><i class="fa-solid fa-arrow-trend-up"></i></div>
                </div>
                <div class="col-kpi-bottom">
                    <div class="col-kpi-value">{{ $pendingCount }}</div>
                    <div class="col-kpi-sub">+ PHP {{ number_format((float) $pendingTotal, 2) }}</div>
                </div>
            </article>
            <article class="col-kpi col-kpi-awaiting">
                <div class="col-kpi-top">
                    <div class="col-kpi-label">Waiting</div>
                    <div class="col-kpi-icon"><i class="fa-solid fa-arrow-trend-down"></i></div>
                </div>
                <div class="col-kpi-bottom">
                    <div class="col-kpi-value">{{ $awaitingCount }}</div>
                    <div class="col-kpi-sub">PHP {{ number_format((float) $awaitingTotal, 2) }}</div>
                </div>
            </article>
            <article class="col-kpi col-kpi-accepted">
                <div class="col-kpi-top">
                    <div class="col-kpi-label">Accepted</div>
                    <div class="col-kpi-icon"><i class="fa-solid fa-arrow-trend-up"></i></div>
                </div>
                <div class="col-kpi-bottom">
                    <div class="col-kpi-value">{{ $acceptedCount }}</div>
                    <div class="col-kpi-sub">+ PHP {{ number_format((float) $acceptedTotal, 2) }}</div>
                </div>
            </article>
            <article class="col-kpi col-kpi-rejected">
                <div class="col-kpi-top">
                    <div class="col-kpi-label">Rejected</div>
                    <div class="col-kpi-icon"><i class="fa-solid fa-arrow-trend-up"></i></div>
                </div>
                <div class="col-kpi-bottom">
                    <div class="col-kpi-value">{{ $rejectedCount }}</div>
                    <div class="col-kpi-sub">PHP {{ number_format((float) $rejectedTotal, 2) }}</div>
                </div>
            </article>
        </section>

        {{-- ── Charts ── --}}
        <section class="col-chart-card">
            <div class="col-chart-head">
                <h3><i class="fa-solid fa-clock-rotate-left" style="color:var(--col-primary);"></i>Recent Transactions</h3>
                <span>Latest 10 records</span>
            </div>
            <div class="col-history-wrap">
                <table class="col-history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th class="col-history-num">Amount</th>
                            <th>Payer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTransactions as $tx)
                            <tr>
                                <td>{{ $tx['date'] }}</td>
                                <td>{{ $tx['reference'] }}</td>
                                <td>{{ $tx['source'] }}</td>
                                <td><span class="col-status-badge {{ $tx['status_class'] }}">{{ $tx['status'] }}</span></td>
                                <td class="col-history-num">PHP {{ number_format((float) $tx['amount'], 2) }}</td>
                                <td>{{ $tx['payer'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="col-history-empty">No recent transactions found for this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <script>
        (() => {
            // Auto-submit filter form on change
            const filterForm = document.getElementById('collectorDashboardFilterForm');
            if (filterForm) {
                const periodInput = filterForm.querySelector('select[name="period"]');
                const fromInput = filterForm.querySelector('input[name="from"]');
                const toInput = filterForm.querySelector('input[name="to"]');

                if (periodInput) {
                    periodInput.addEventListener('change', () => filterForm.requestSubmit());
                }

                const submitCustomDateRange = () => {
                    if (periodInput) {
                        periodInput.value = 'custom';
                    }
                    filterForm.requestSubmit();
                };

                if (fromInput) {
                    fromInput.addEventListener('change', submitCustomDateRange);
                }

                if (toInput) {
                    toInput.addEventListener('change', submitCustomDateRange);
                }
            }
        })();
    </script>
@endsection

