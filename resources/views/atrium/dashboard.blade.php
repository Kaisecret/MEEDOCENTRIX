@extends('layouts.app')

@section('content')
<style>
    .atr {
        --atr-primary: #0f5fa8;
        --atr-primary-deep: #0a4880;
        --atr-accent: #1a7fd4;
        --atr-surface: #ffffff;
        --atr-soft: #f8fafc;
        --atr-border: #e2e8f0;
        --atr-text: #334155;
        --atr-muted: #64748b;
        --atr-head: #0f172a;
        --atr-green: #047857;
        --atr-amber: #b45309;
        --atr-red: #b91c1c;
        --atr-blue: #1d4ed8;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: var(--atr-text);
        display: grid;
        gap: 16px;
    }

    .atr-hero {
        background:
            radial-gradient(circle at 86% 8%, rgba(255, 255, 255, .16) 0, transparent 42%),
            radial-gradient(circle at 12% 84%, rgba(255, 255, 255, .09) 0, transparent 36%),
            linear-gradient(135deg, #0a3d6b 0%, #0f5fa8 55%, #1a7fd4 100%);
        color: #fff;
        border-radius: 16px;
        padding: 1.35rem 1.45rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        box-shadow: 0 8px 26px rgba(10, 63, 168, .22);
    }

    .atr-hero h2 { margin: 0 0 .35rem; font-size: 1.55rem; font-weight: 800; letter-spacing: -.02em; }
    .atr-hero p { margin: 0; font-size: .92rem; color: rgba(255,255,255,.88); max-width: 680px; }
    .atr-hero-meta { display: grid; justify-items: end; gap: 2px; }
    .atr-hero-meta .atr-clock { font-size: 1.4rem; font-weight: 800; letter-spacing: -.01em; }

    .atr-kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 12px; }
    .atr-kpi { border: 1px solid var(--atr-border); border-radius: 13px; background: var(--atr-surface); box-shadow: 0 1px 3px rgba(0,0,0,.04); padding: .95rem 1rem; display: grid; gap: 6px; animation: atr-fade .34s ease both; }
    .atr-kpi:nth-child(1) { animation-delay: .02s; }
    .atr-kpi:nth-child(2) { animation-delay: .06s; }
    .atr-kpi:nth-child(3) { animation-delay: .1s; }
    .atr-kpi:nth-child(4) { animation-delay: .14s; }
    .atr-kpi-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .atr-kpi-title { font-size: .76rem; text-transform: uppercase; letter-spacing: .04em; color: var(--atr-muted); font-weight: 800; }
    .atr-kpi-icon { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: .98rem; }
    .atr-kpi-icon.purple { background: #eaf3fb; color: var(--atr-primary); }
    .atr-kpi-icon.blue { background: #eff6ff; color: var(--atr-blue); }
    .atr-kpi-icon.green { background: #ecfdf5; color: var(--atr-green); }
    .atr-kpi-icon.amber { background: #fffbeb; color: var(--atr-amber); }
    .atr-kpi-icon.red { background: #fef2f2; color: var(--atr-red); }
    .atr-kpi-value { font-size: 1.45rem; line-height: 1.05; letter-spacing: -.02em; color: var(--atr-head); font-weight: 800; }
    .atr-kpi-sub { font-size: .8rem; color: var(--atr-muted); }
    .atr-kpi-sub b { color: var(--atr-head); }

    .atr-grid-twin { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); gap: 12px; }
    .atr-card { border: 1px solid var(--atr-border); border-radius: 14px; background: var(--atr-surface); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; }
    .atr-card-head { border-bottom: 1px solid var(--atr-border); padding: .9rem 1rem; display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; }
    .atr-card-head h3 { margin: 0; color: var(--atr-head); font-size: 1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; }
    .atr-card-head span { color: var(--atr-muted); font-size: .8rem; font-weight: 600; }
    .atr-card-body { padding: 1rem; }

    .atr-table-wrap { overflow: auto; }
    .atr-table { width: 100%; border-collapse: collapse; min-width: 720px; }
    .atr-table th { background: #eef5fb; color: #103250; border-bottom: 1px solid var(--atr-border); font-size: .76rem; text-transform: uppercase; letter-spacing: .04em; font-weight: 800; padding: .78rem .95rem; text-align: left; }
    .atr-table td { border-bottom: 1px solid #f1f5f9; padding: .78rem .95rem; font-size: .87rem; color: var(--atr-text); vertical-align: middle; }
    .atr-table tbody tr:hover td { background: #f8fafc; }

    .atr-tag { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; border: 1px solid transparent; padding: .18rem .56rem; font-size: .68rem; text-transform: uppercase; letter-spacing: .03em; font-weight: 800; }
    .atr-tag-reserved { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .atr-tag-confirmed { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .atr-tag-completed { background: #eaf3fb; border-color: #bfdbfe; color: #0a4880; }
    .atr-tag-cancelled { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }

    .atr-link { color: var(--atr-primary); text-decoration: none; font-weight: 700; font-size: .82rem; }
    .atr-link:hover { text-decoration: underline; }
    .atr-empty { text-align: center; color: var(--atr-muted); padding: 2rem 1rem; font-size: .9rem; }

    .atr-action-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .atr-btn-primary { background: var(--atr-primary); border: 1px solid var(--atr-primary); color: #fff; border-radius: 9px; padding: .55rem .95rem; font-size: .85rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .atr-btn-primary:hover { background: var(--atr-primary-deep); }
    .atr-btn-outline { background: #fff; border: 1px solid var(--atr-primary); color: var(--atr-primary); border-radius: 9px; padding: .55rem .95rem; font-size: .85rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .atr-btn-outline:hover { background: #f0f7ff; }

    @keyframes atr-fade { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    @media (max-width: 1100px) {
        .atr-kpi-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .atr-grid-twin { grid-template-columns: 1fr; }
    }
    @media (max-width: 680px) {
        .atr-kpi-grid { grid-template-columns: 1fr; }
        .atr-hero h2 { font-size: 1.35rem; }
        .atr-hero-meta { justify-items: start; }
    }
</style>

<div class="atr" data-server-rendered-page="dashboard" data-page-title="Atrium Dashboard">
    <section class="atr-hero">
        <div>
            <h2><i class="fa-solid fa-building-columns" style="margin-right:8px;opacity:.88;"></i>Atrium Management Dashboard</h2>
            <p>Monitor event bookings, hall occupancy, collections, and supplies requests in one place.</p>
        </div>
        <div class="atr-hero-meta">
            <span class="atr-clock" id="atrClock">{{ now()->format('h:i A') }}</span>
            <span style="font-size:.83rem;color:rgba(255,255,255,.84);font-weight:600;">{{ now()->format('l, M d, Y') }}</span>
        </div>
    </section>

    <section class="atr-action-row">
        <a class="atr-btn-primary" href="{{ route('atrium.bookings', ['new_booking' => 1]) }}"><i class="fa-solid fa-plus"></i>New Booking</a>
        <a class="atr-btn-outline" href="{{ route('atrium.payments.create') }}"><i class="fa-solid fa-peso-sign"></i>Record Payment</a>
        <a class="atr-btn-outline" href="{{ route('atrium.supplies.create') }}"><i class="fa-solid fa-boxes-stacked"></i>Request Supplies</a>
        <a class="atr-btn-outline" href="{{ route('atrium.reports') }}"><i class="fa-solid fa-chart-pie"></i>Reports</a>
    </section>

    <section class="atr-kpi-grid">
        <article class="atr-kpi">
            <div class="atr-kpi-head">
                <span class="atr-kpi-title">Total Events</span>
                <span class="atr-kpi-icon purple"><i class="fa-solid fa-calendar-check"></i></span>
            </div>
            <div class="atr-kpi-value">{{ number_format($totalEvents) }}</div>
            <div class="atr-kpi-sub"><b>{{ number_format($eventsThisMonth) }}</b> this month</div>
        </article>

        <article class="atr-kpi">
            <div class="atr-kpi-head">
                <span class="atr-kpi-title">Upcoming</span>
                <span class="atr-kpi-icon blue"><i class="fa-solid fa-calendar-day"></i></span>
            </div>
            <div class="atr-kpi-value">{{ number_format($upcomingEvents) }}</div>
            <div class="atr-kpi-sub">Reserved / confirmed</div>
        </article>

        <article class="atr-kpi">
            <div class="atr-kpi-head">
                <span class="atr-kpi-title">Collections</span>
                <span class="atr-kpi-icon green"><i class="fa-solid fa-peso-sign"></i></span>
            </div>
            <div class="atr-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($totalCollected, 2) }}</div>
            <div class="atr-kpi-sub"><b>PHP {{ number_format($collectedThisMonth, 2) }}</b> this month</div>
        </article>

        <article class="atr-kpi">
            <div class="atr-kpi-head">
                <span class="atr-kpi-title">Outstanding</span>
                <span class="atr-kpi-icon amber"><i class="fa-solid fa-sack-dollar"></i></span>
            </div>
            <div class="atr-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($outstanding, 2) }}</div>
            <div class="atr-kpi-sub"><b>{{ number_format($pendingSupplies) }}</b> pending supplies</div>
        </article>
    </section>

    <section class="atr-grid-twin">
        <article class="atr-card">
            <div class="atr-card-head">
                <h3><i class="fa-solid fa-clock" style="color:var(--atr-primary);"></i>Upcoming Events</h3>
                <a class="atr-link" href="{{ route('atrium.bookings') }}">View bookings</a>
            </div>
            @if ($nextEvents->isEmpty())
                <div class="atr-empty">
                    <i class="fa-solid fa-calendar-xmark" style="font-size:1.45rem;color:#cbd5e1;display:block;margin-bottom:8px;"></i>
                    No upcoming events scheduled.
                </div>
            @else
                <div class="atr-table-wrap">
                    <table class="atr-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Date</th>
                                <th>Event</th>
                                <th>Hall</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($nextEvents as $event)
                                @php
                                    $tagClass = match ($event->booking_status) {
                                        'confirmed' => 'atr-tag-confirmed',
                                        'completed' => 'atr-tag-completed',
                                        'cancelled' => 'atr-tag-cancelled',
                                        default => 'atr-tag-reserved',
                                    };
                                @endphp
                                <tr>
                                    <td><strong>{{ $event->event_code }}</strong></td>
                                    <td style="white-space:nowrap;">{{ $event->date_of_event?->format('M d, Y') }}</td>
                                    <td>
                                        {{ $event->name_contact_person }}<br>
                                        <span style="font-size:.78rem;color:var(--atr-muted);">{{ \Illuminate\Support\Str::limit($event->event_details, 40) }}</span>
                                    </td>
                                    <td>{{ $event->functionHall?->name ?? '—' }}</td>
                                    <td><span class="atr-tag {{ $tagClass }}">{{ ucfirst($event->booking_status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>

        <article class="atr-card">
            <div class="atr-card-head">
                <h3><i class="fa-solid fa-chart-simple" style="color:var(--atr-primary);"></i>Status Overview</h3>
            </div>
            <div class="atr-card-body">
                <div style="display:grid;gap:10px;">
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding:.5rem 0;">
                        <span>Completed events</span>
                        <b>{{ number_format($completedEvents) }}</b>
                    </div>
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding:.5rem 0;">
                        <span>Events this month</span>
                        <b>{{ number_format($eventsThisMonth) }}</b>
                    </div>
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding:.5rem 0;">
                        <span>Total billed</span>
                        <b>PHP {{ number_format($totalDue, 2) }}</b>
                    </div>
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding:.5rem 0;">
                        <span>Collected</span>
                        <b>PHP {{ number_format($totalCollected, 2) }}</b>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;">
                        <span>Pending supplies</span>
                        <b>{{ number_format($pendingSupplies) }}</b>
                    </div>
                </div>
            </div>
        </article>
    </section>
</div>

<script>
(function () {
    const clockEl = document.getElementById('atrClock');
    if (clockEl) {
        const tick = () => {
            const now = new Date();
            let hour = now.getHours();
            const minute = now.getMinutes();
            const ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12 || 12;
            clockEl.textContent = `${String(hour).padStart(2,'0')}:${String(minute).padStart(2,'0')} ${ampm}`;
        };
        tick();
        setInterval(tick, 1000);
    }
})();
</script>
@endsection
