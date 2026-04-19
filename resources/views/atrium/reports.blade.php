@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')

<div class="atr" data-server-rendered-page="atrium_reports" data-page-title="Atrium Reports">
    <section class="atr-hero">
        <div>
            <h2><i class="fa-solid fa-chart-pie" style="margin-right:8px;opacity:.88;"></i>Atrium Reports</h2>
            <p>{{ $rangeLabel }} — {{ ucfirst($report) }} report</p>
        </div>
        <div class="atr-hero-meta">
            <span style="font-size:.83rem;color:rgba(255,255,255,.84);font-weight:600;">{{ now()->format('M d, Y h:i A') }}</span>
        </div>
    </section>

    <section class="atr-card">
        <div class="atr-card-head">
            <h3><i class="fa-solid fa-filter" style="color:var(--atr-primary);"></i>Report Filters</h3>
        </div>
        <form method="GET" action="{{ route('atrium.reports') }}" class="atr-filter-bar">
            <input type="hidden" name="range" id="atrRangeInput" value="{{ $range }}">
            <select name="report" class="atr-input" onchange="this.form.submit()">
                <option value="booking" {{ $report === 'booking' ? 'selected' : '' }}>Booking Report</option>
                <option value="collection" {{ $report === 'collection' ? 'selected' : '' }}>Collection Report</option>
                <option value="supplies" {{ $report === 'supplies' ? 'selected' : '' }}>Supplies Report</option>
            </select>
            <div class="atr-range-bar">
                <button type="button" class="atr-pill {{ $range === 'all' ? 'is-active' : '' }}" data-range="all">All</button>
                <button type="button" class="atr-pill {{ $range === 'today' ? 'is-active' : '' }}" data-range="today">Today</button>
                <button type="button" class="atr-pill {{ $range === 'week' ? 'is-active' : '' }}" data-range="week">Week</button>
                <button type="button" class="atr-pill {{ $range === 'month' ? 'is-active' : '' }}" data-range="month">Month</button>
                <button type="button" class="atr-pill {{ $range === 'custom' ? 'is-active' : '' }}" data-range="custom">Custom</button>
                <div id="atrCustomRange" class="atr-range-fields" {{ $range === 'custom' ? '' : 'hidden' }}>
                    <input type="date" name="from" value="{{ $from }}" class="atr-input">
                    <span style="color:var(--atr-muted);font-size:.84rem;">to</span>
                    <input type="date" name="to" value="{{ $to }}" class="atr-input">
                    <button type="submit" class="atr-btn-primary">Apply</button>
                </div>
            </div>
            <button class="atr-btn-outline" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i>Print</button>
        </form>
    </section>

    @if ($report === 'booking')
        <section class="atr-kpi-grid">
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Events</span><span class="atr-kpi-icon purple"><i class="fa-solid fa-calendar"></i></span></div><div class="atr-kpi-value">{{ number_format($summary['total']) }}</div></article>
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Confirmed</span><span class="atr-kpi-icon green"><i class="fa-solid fa-circle-check"></i></span></div><div class="atr-kpi-value">{{ number_format($summary['confirmed']) }}</div></article>
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Completed</span><span class="atr-kpi-icon blue"><i class="fa-solid fa-flag-checkered"></i></span></div><div class="atr-kpi-value">{{ number_format($summary['completed']) }}</div></article>
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Total Billed</span><span class="atr-kpi-icon amber"><i class="fa-solid fa-peso-sign"></i></span></div><div class="atr-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($summary['total_due'], 2) }}</div></article>
        </section>
        <section class="atr-card">
            <div class="atr-card-head"><h3><i class="fa-solid fa-calendar-check" style="color:var(--atr-primary);"></i>Booking Report</h3><span>{{ $events->count() }} event(s)</span></div>
            @if ($events->isEmpty())
                <div class="atr-empty">No events in selected range.</div>
            @else
                <div class="atr-table-wrap"><table class="atr-table">
                    <thead><tr><th>Code</th><th>Date</th><th>Contact</th><th>Hall</th><th>Hours</th><th>Due</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach ($events as $e)
                        @php $t = 'atr-tag-' . ($e->booking_status === 'reserved' ? 'reserved' : $e->booking_status); @endphp
                        <tr>
                            <td><b>{{ $e->event_code }}</b></td>
                            <td>{{ optional($e->date_of_event)->format('M d, Y') }}</td>
                            <td>{{ $e->name_contact_person }}</td>
                            <td>{{ $e->functionHall?->name }}</td>
                            <td>{{ number_format((float) $e->no_of_hours, 2) }}</td>
                            <td><b>PHP {{ number_format((float) $e->actual_due, 2) }}</b></td>
                            <td><span class="atr-tag {{ $t }}">{{ ucfirst($e->booking_status) }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
            @endif
        </section>
    @elseif ($report === 'collection')
        <section class="atr-kpi-grid">
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Receipts</span><span class="atr-kpi-icon purple"><i class="fa-solid fa-receipt"></i></span></div><div class="atr-kpi-value">{{ number_format($summary['total']) }}</div></article>
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Collected</span><span class="atr-kpi-icon green"><i class="fa-solid fa-peso-sign"></i></span></div><div class="atr-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($summary['collected'], 2) }}</div></article>
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Paid</span><span class="atr-kpi-icon green"><i class="fa-solid fa-circle-check"></i></span></div><div class="atr-kpi-value">{{ number_format($summary['paid']) }}</div></article>
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Partial</span><span class="atr-kpi-icon amber"><i class="fa-solid fa-hourglass-half"></i></span></div><div class="atr-kpi-value">{{ number_format($summary['partial']) }}</div></article>
        </section>
        <section class="atr-card">
            <div class="atr-card-head"><h3><i class="fa-solid fa-money-check-dollar" style="color:var(--atr-primary);"></i>Collection Report</h3><span>{{ $payments->count() }} receipt(s)</span></div>
            @if ($payments->isEmpty())
                <div class="atr-empty">No receipts in selected range.</div>
            @else
                <div class="atr-table-wrap"><table class="atr-table">
                    <thead><tr><th>OR #</th><th>Date</th><th>Event</th><th>Amount</th><th>Status</th><th>Recorded by</th></tr></thead>
                    <tbody>
                    @foreach ($payments as $p)
                        @php $t = 'atr-tag-' . $p->payment_status; @endphp
                        <tr>
                            <td><b>{{ $p->or_number }}</b></td>
                            <td>{{ optional($p->date_of_payment)->format('M d, Y') }}</td>
                            <td>{{ $p->event?->event_code }} — {{ $p->event?->name_contact_person }}</td>
                            <td><b>PHP {{ number_format((float) $p->payment_amount, 2) }}</b></td>
                            <td><span class="atr-tag {{ $t }}">{{ ucfirst($p->payment_status) }}</span></td>
                            <td>{{ $p->recordedBy?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
            @endif
        </section>
    @else
        <section class="atr-kpi-grid">
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Requests</span><span class="atr-kpi-icon purple"><i class="fa-solid fa-boxes-stacked"></i></span></div><div class="atr-kpi-value">{{ number_format($summary['total']) }}</div></article>
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Pending</span><span class="atr-kpi-icon amber"><i class="fa-solid fa-hourglass-half"></i></span></div><div class="atr-kpi-value">{{ number_format($summary['pending']) }}</div></article>
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Approved</span><span class="atr-kpi-icon blue"><i class="fa-solid fa-circle-check"></i></span></div><div class="atr-kpi-value">{{ number_format($summary['approved']) }}</div></article>
            <article class="atr-kpi"><div class="atr-kpi-head"><span class="atr-kpi-title">Fulfilled</span><span class="atr-kpi-icon green"><i class="fa-solid fa-box-open"></i></span></div><div class="atr-kpi-value">{{ number_format($summary['fulfilled']) }}</div></article>
        </section>
        <section class="atr-card">
            <div class="atr-card-head"><h3><i class="fa-solid fa-clipboard-list" style="color:var(--atr-primary);"></i>Supplies Report</h3><span>{{ $orders->count() }} request(s)</span></div>
            @if ($orders->isEmpty())
                <div class="atr-empty">No supplies requests in selected range.</div>
            @else
                <div class="atr-table-wrap"><table class="atr-table">
                    <thead><tr><th>Date</th><th>Event</th><th>Time Needed</th><th>Supplies</th><th>Status</th><th>Requested By</th></tr></thead>
                    <tbody>
                    @foreach ($orders as $o)
                        @php $t = 'atr-tag-' . $o->request_status; @endphp
                        <tr>
                            <td>{{ optional($o->created_at)->format('M d, Y') }}</td>
                            <td>{{ $o->event?->event_code }} — {{ $o->event?->name_contact_person }}</td>
                            <td>{{ $o->time_needed ?? '—' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($o->requested_supplies, 80) }}</td>
                            <td><span class="atr-tag {{ $t }}">{{ ucfirst($o->request_status) }}</span></td>
                            <td>{{ $o->requestedBy?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
            @endif
        </section>
    @endif
</div>

<script>
(function () {
    const rangeInput = document.getElementById('atrRangeInput');
    const customWrap = document.getElementById('atrCustomRange');
    const pills = document.querySelectorAll('.atr-pill[data-range]');
    pills.forEach(btn => {
        btn.addEventListener('click', () => {
            const chosen = btn.dataset.range;
            rangeInput.value = chosen;
            pills.forEach(p => p.classList.toggle('is-active', p === btn));
            if (customWrap) customWrap.hidden = chosen !== 'custom';
            if (chosen !== 'custom') btn.closest('form').submit();
        });
    });
})();
</script>
@endsection
