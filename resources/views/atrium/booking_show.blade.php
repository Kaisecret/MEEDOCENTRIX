@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')

@php
    $paid = (float) $event->payments->sum('payment_amount');
    $due = (float) $event->actual_due;
    $balance = max(0.0, $due - $paid);
    $statusTag = match ($event->booking_status) {
        'confirmed' => 'atr-tag-confirmed',
        'completed' => 'atr-tag-completed',
        'cancelled' => 'atr-tag-cancelled',
        default => 'atr-tag-reserved',
    };
@endphp

<div class="atr" data-server-rendered-page="atrium_bookings" data-page-title="Booking {{ $event->event_code }}">
    <section class="atr-hero">
        <div>
            <h2><i class="fa-solid fa-calendar-check" style="margin-right:8px;opacity:.88;"></i>Booking {{ $event->event_code }}</h2>
            <p>{{ $event->name_contact_person }} — {{ \Illuminate\Support\Str::limit($event->event_details, 100) }}</p>
        </div>
        <div class="atr-hero-meta">
            <span class="atr-tag {{ $statusTag }}" style="font-size:.75rem;">{{ ucfirst($event->booking_status) }}</span>
            <span style="font-size:.83rem;color:rgba(255,255,255,.84);font-weight:600;">{{ optional($event->date_of_event)->format('l, M d, Y') }}</span>
        </div>
    </section>

    @if (session('status'))
        <div class="atr-flash">{{ session('status') }}</div>
    @endif

    <section style="display:flex;gap:8px;flex-wrap:wrap;">
        <a class="atr-btn-outline" href="{{ route('atrium.bookings') }}"><i class="fa-solid fa-arrow-left"></i>Back</a>
        <a class="atr-btn-primary" href="{{ route('atrium.bookings.edit', $event) }}"><i class="fa-solid fa-pen"></i>Edit</a>
        <a class="atr-btn-outline" href="{{ route('atrium.payments.create', ['event' => $event->id]) }}"><i class="fa-solid fa-peso-sign"></i>Record Payment</a>
        <a class="atr-btn-outline" href="{{ route('atrium.supplies.create', ['event' => $event->id]) }}"><i class="fa-solid fa-boxes-stacked"></i>Request Supplies</a>
        @if ($event->booking_status !== 'cancelled')
            <form method="POST" action="{{ route('atrium.bookings.cancel', $event) }}" onsubmit="return confirm('Cancel this booking?')">
                @csrf @method('PATCH')
                <button class="atr-btn-danger" type="submit"><i class="fa-solid fa-ban"></i>Cancel Booking</button>
            </form>
        @endif
        @if ($event->booking_status !== 'completed' && $event->booking_status !== 'cancelled')
            <form method="POST" action="{{ route('atrium.bookings.complete', $event) }}">
                @csrf @method('PATCH')
                <button class="atr-btn-outline" type="submit"><i class="fa-solid fa-flag-checkered"></i>Mark Completed</button>
            </form>
        @endif
    </section>

    <section class="atr-kpi-grid">
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Total Due</span><span class="atr-kpi-icon purple"><i class="fa-solid fa-file-invoice-dollar"></i></span></div>
            <div class="atr-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($due, 2) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Paid</span><span class="atr-kpi-icon green"><i class="fa-solid fa-peso-sign"></i></span></div>
            <div class="atr-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($paid, 2) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Balance</span><span class="atr-kpi-icon amber"><i class="fa-solid fa-sack-dollar"></i></span></div>
            <div class="atr-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($balance, 2) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Hours</span><span class="atr-kpi-icon blue"><i class="fa-solid fa-clock"></i></span></div>
            <div class="atr-kpi-value">{{ number_format((float) $event->no_of_hours, 2) }}</div>
        </article>
    </section>

    <section style="display:grid;grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap: 12px;">
        <article class="atr-card">
            <div class="atr-card-head"><h3><i class="fa-solid fa-info-circle" style="color:var(--atr-primary);"></i>Event Information</h3></div>
            <div class="atr-card-body">
                <table style="width:100%;font-size:.88rem;">
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Event code</td><td><b>{{ $event->event_code }}</b></td></tr>
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Date</td><td>{{ optional($event->date_of_event)->format('M d, Y') }}</td></tr>
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Start time</td><td>{{ $event->start_time ?? '—' }}</td></tr>
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Contact person</td><td>{{ $event->name_contact_person }}</td></tr>
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Contact number</td><td>{{ $event->contact_number ?? '—' }}</td></tr>
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Function hall</td><td>{{ $event->functionHall?->name }} ({{ $event->functionHall?->code }})</td></tr>
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);vertical-align:top;">Details</td><td>{{ $event->event_details }}</td></tr>
                </table>
            </div>
        </article>
        <article class="atr-card">
            <div class="atr-card-head"><h3><i class="fa-solid fa-receipt" style="color:var(--atr-primary);"></i>Billing Breakdown</h3></div>
            <div class="atr-card-body">
                <table style="width:100%;font-size:.88rem;">
                    <tr><td style="padding:.4rem 0;">Hall payment</td><td style="text-align:right;"><b>PHP {{ number_format((float) $event->hall_payment, 2) }}</b></td></tr>
                    <tr><td style="padding:.4rem 0;">Miscellaneous</td><td style="text-align:right;">PHP {{ number_format((float) $event->miscellaneous_payment, 2) }}</td></tr>
                    <tr><td style="padding:.4rem 0;">Accommodation</td><td style="text-align:right;">PHP {{ number_format((float) $event->accommodation_payment, 2) }}</td></tr>
                    @foreach ($event->addOns as $row)
                        <tr><td style="padding:.4rem 0;color:var(--atr-muted);">+ {{ $row->description }}</td><td style="text-align:right;color:var(--atr-muted);">PHP {{ number_format((float) $row->amount, 2) }}</td></tr>
                    @endforeach
                    <tr style="border-top:1px solid var(--atr-border);"><td style="padding:.5rem 0;font-weight:800;">Total due</td><td style="text-align:right;font-weight:800;">PHP {{ number_format($due, 2) }}</td></tr>
                </table>
            </div>
        </article>
    </section>

    <section class="atr-card">
        <div class="atr-card-head"><h3><i class="fa-solid fa-money-check-dollar" style="color:var(--atr-primary);"></i>Payments ({{ $event->payments->count() }})</h3></div>
        @if ($event->payments->isEmpty())
            <div class="atr-empty">No payments recorded yet.</div>
        @else
            <div class="atr-table-wrap">
                <table class="atr-table">
                    <thead><tr><th>OR #</th><th>Date</th><th>Amount</th><th>Status</th><th>Recorded by</th><th>Remarks</th></tr></thead>
                    <tbody>
                        @foreach ($event->payments as $p)
                            @php $tag = 'atr-tag-' . $p->payment_status; @endphp
                            <tr>
                                <td><strong>{{ $p->or_number }}</strong></td>
                                <td>{{ optional($p->date_of_payment)->format('M d, Y') }}</td>
                                <td><b>PHP {{ number_format((float) $p->payment_amount, 2) }}</b></td>
                                <td><span class="atr-tag {{ $tag }}">{{ ucfirst($p->payment_status) }}</span></td>
                                <td>{{ $p->recordedBy?->name ?? '—' }}</td>
                                <td style="font-size:.8rem;color:var(--atr-muted);">{{ $p->remarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="atr-card">
        <div class="atr-card-head"><h3><i class="fa-solid fa-boxes-stacked" style="color:var(--atr-primary);"></i>Supplies Requests ({{ $event->suppliesOrders->count() }})</h3></div>
        @if ($event->suppliesOrders->isEmpty())
            <div class="atr-empty">No supplies requests for this event.</div>
        @else
            <div class="atr-table-wrap">
                <table class="atr-table">
                    <thead><tr><th>Time Needed</th><th>Requested Supplies</th><th>Status</th><th>Remarks</th></tr></thead>
                    <tbody>
                        @foreach ($event->suppliesOrders as $o)
                            @php $tag = 'atr-tag-' . $o->request_status; @endphp
                            <tr>
                                <td>{{ $o->time_needed ?? '—' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($o->requested_supplies, 120) }}</td>
                                <td><span class="atr-tag {{ $tag }}">{{ ucfirst($o->request_status) }}</span></td>
                                <td style="font-size:.8rem;color:var(--atr-muted);">{{ $o->remarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
