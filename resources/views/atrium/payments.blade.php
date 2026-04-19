@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')

<div class="atr" data-server-rendered-page="atrium_payments" data-page-title="Atrium Payments">
    <section class="atr-hero">
        <div>
            <h2><i class="fa-solid fa-money-check-dollar" style="margin-right:8px;opacity:.88;"></i>Payment Module</h2>
            <p>Record receipts and track outstanding balances across atrium events.</p>
        </div>
        <div class="atr-hero-meta">
            <span style="font-size:.83rem;color:rgba(255,255,255,.84);font-weight:600;">Collected: <b>PHP {{ number_format($summary['total_collected'], 2) }}</b></span>
            <span style="font-size:.83rem;color:rgba(255,255,255,.84);font-weight:600;">{{ $rangeLabel }}</span>
        </div>
    </section>

    @if (session('status'))
        <div class="atr-flash">{{ session('status') }}</div>
    @endif

    <section class="atr-kpi-grid">
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Total Collected</span><span class="atr-kpi-icon green"><i class="fa-solid fa-peso-sign"></i></span></div>
            <div class="atr-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($summary['total_collected'], 2) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Paid in Full</span><span class="atr-kpi-icon green"><i class="fa-solid fa-circle-check"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['paid_count']) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Partial</span><span class="atr-kpi-icon amber"><i class="fa-solid fa-hourglass-half"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['partial_count']) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Unpaid Events</span><span class="atr-kpi-icon red"><i class="fa-solid fa-file-invoice"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['unpaid_count']) }}</div>
        </article>
    </section>

    <section class="atr-card">
        <div class="atr-card-head">
            <h3><i class="fa-solid fa-filter" style="color:var(--atr-primary);"></i>Filters</h3>
            <a class="atr-btn-primary" href="{{ route('atrium.payments.create') }}"><i class="fa-solid fa-plus"></i>Record Payment</a>
        </div>
        <form method="GET" action="{{ route('atrium.payments') }}" class="atr-filter-bar">
            <input type="hidden" name="range" id="atrRangeInput" value="{{ $range }}">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search OR #, event code, contact..." class="atr-input atr-input--grow">
            <select name="status" class="atr-input" onchange="this.form.submit()">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
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
            <button class="atr-btn-outline" type="submit"><i class="fa-solid fa-magnifying-glass"></i>Search</button>
        </form>
    </section>

    <section class="atr-card">
        <div class="atr-card-head">
            <h3><i class="fa-solid fa-list" style="color:var(--atr-primary);"></i>Payment Records</h3>
            <span>{{ $payments->total() }} record(s)</span>
        </div>
        @if ($payments->isEmpty())
            <div class="atr-empty">No payment records.</div>
        @else
            <div class="atr-table-wrap">
                <table class="atr-table">
                    <thead>
                        <tr>
                            <th>OR #</th>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Total Collected</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $p)
                            @php
                                $tag = 'atr-tag-' . $p->payment_status;
                                $event = $p->event;
                                $totalPaid = $event ? (float) $event->payments->sum('payment_amount') : (float) $p->payment_amount;
                                $balance = $event ? max(0.0, (float) $event->actual_due - $totalPaid) : 0.0;
                            @endphp
                            <tr>
                                <td><strong>{{ $p->or_number }}</strong></td>
                                <td>
                                    <strong>{{ $event?->event_code ?? '—' }}</strong><br>
                                    <span style="font-size:.78rem;color:var(--atr-muted);">{{ $event?->name_contact_person }}</span>
                                </td>
                                <td style="white-space:nowrap;">{{ optional($p->date_of_payment)->format('M d, Y') }}</td>
                                <td><b>PHP {{ number_format((float) $p->payment_amount, 2) }}</b></td>
                                <td>PHP {{ number_format($totalPaid, 2) }}</td>
                                <td>PHP {{ number_format($balance, 2) }}</td>
                                <td><span class="atr-tag {{ $tag }}">{{ ucfirst($p->payment_status) }}</span></td>
                                <td style="font-size:.8rem;color:var(--atr-muted);">{{ \Illuminate\Support\Str::limit($p->remarks, 40) }}</td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a class="atr-link" href="{{ route('atrium.payments.show', $p) }}">View</a>
                                    <span style="color:#cbd5e1;">|</span>
                                    <a class="atr-link" href="{{ route('atrium.payments.edit', $p) }}">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: .8rem 1rem; border-top:1px solid var(--atr-border);">{{ $payments->links() }}</div>
        @endif
    </section>
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
