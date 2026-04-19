@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')

@php
    $event = $payment->event;
    $totalPaid = $event ? (float) $event->payments->sum('payment_amount') : (float) $payment->payment_amount;
    $balance = $event ? max(0.0, (float) $event->actual_due - $totalPaid) : 0.0;
    $tag = 'atr-tag-' . $payment->payment_status;
@endphp

<div class="atr" data-server-rendered-page="atrium_payments" data-page-title="Payment {{ $payment->or_number }}">
    <section class="atr-hero">
        <div>
            <h2><i class="fa-solid fa-receipt" style="margin-right:8px;opacity:.88;"></i>OR #{{ $payment->or_number }}</h2>
            <p>Payment applied to {{ $event?->event_code ?? '—' }} — {{ $event?->name_contact_person }}</p>
        </div>
        <div class="atr-hero-meta">
            <span class="atr-tag {{ $tag }}" style="font-size:.75rem;">{{ ucfirst($payment->payment_status) }}</span>
            <span style="font-size:.83rem;color:rgba(255,255,255,.84);font-weight:600;">{{ optional($payment->date_of_payment)->format('l, M d, Y') }}</span>
        </div>
    </section>

    @if (session('status'))
        <div class="atr-flash">{{ session('status') }}</div>
    @endif

    <section style="display:flex;gap:8px;flex-wrap:wrap;">
        <a class="atr-btn-outline" href="{{ route('atrium.payments') }}"><i class="fa-solid fa-arrow-left"></i>Back</a>
        <a class="atr-btn-primary" href="{{ route('atrium.payments.edit', $payment) }}"><i class="fa-solid fa-pen"></i>Edit</a>
        @if ($event)
            <a class="atr-btn-outline" href="{{ route('atrium.bookings.show', $event) }}"><i class="fa-solid fa-calendar-check"></i>View Booking</a>
        @endif
        <form method="POST" action="{{ route('atrium.payments.destroy', $payment) }}" onsubmit="return confirm('Delete this payment?')">
            @csrf @method('DELETE')
            <button class="atr-btn-danger" type="submit"><i class="fa-solid fa-trash"></i>Delete</button>
        </form>
    </section>

    <section style="display:grid;grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap:12px;">
        <article class="atr-card">
            <div class="atr-card-head"><h3><i class="fa-solid fa-info-circle" style="color:var(--atr-primary);"></i>Receipt</h3></div>
            <div class="atr-card-body">
                <table style="width:100%;font-size:.88rem;">
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);">OR #</td><td><b>{{ $payment->or_number }}</b></td></tr>
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Date</td><td>{{ optional($payment->date_of_payment)->format('M d, Y') }}</td></tr>
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Amount</td><td><b>PHP {{ number_format((float) $payment->payment_amount, 2) }}</b></td></tr>
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Status</td><td><span class="atr-tag {{ $tag }}">{{ ucfirst($payment->payment_status) }}</span></td></tr>
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Recorded by</td><td>{{ $payment->recordedBy?->name ?? '—' }}</td></tr>
                    <tr><td style="padding:.4rem 0;color:var(--atr-muted);vertical-align:top;">Remarks</td><td>{{ $payment->remarks ?? '—' }}</td></tr>
                </table>
            </div>
        </article>
        <article class="atr-card">
            <div class="atr-card-head"><h3><i class="fa-solid fa-calendar-check" style="color:var(--atr-primary);"></i>Event Summary</h3></div>
            <div class="atr-card-body">
                @if ($event)
                    <table style="width:100%;font-size:.88rem;">
                        <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Event code</td><td><b>{{ $event->event_code }}</b></td></tr>
                        <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Contact</td><td>{{ $event->name_contact_person }}</td></tr>
                        <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Hall</td><td>{{ $event->functionHall?->name }}</td></tr>
                        <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Total due</td><td><b>PHP {{ number_format((float) $event->actual_due, 2) }}</b></td></tr>
                        <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Total paid</td><td>PHP {{ number_format($totalPaid, 2) }}</td></tr>
                        <tr><td style="padding:.4rem 0;color:var(--atr-muted);">Balance</td><td><b>PHP {{ number_format($balance, 2) }}</b></td></tr>
                    </table>
                @else
                    <div class="atr-empty">Event record unavailable.</div>
                @endif
            </div>
        </article>
    </section>
</div>
@endsection
