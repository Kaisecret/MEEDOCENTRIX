@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')

@php
    $isEdit = $mode === 'edit';
    $action = $isEdit ? route('atrium.payments.update', $payment) : route('atrium.payments.store');
@endphp

<div class="atr" data-server-rendered-page="atrium_payments" data-page-title="{{ $isEdit ? 'Edit Payment' : 'Record Payment' }}">
    <section class="atr-hero">
        <div>
            <h2><i class="fa-solid fa-{{ $isEdit ? 'pen' : 'peso-sign' }}" style="margin-right:8px;opacity:.88;"></i>{{ $isEdit ? 'Edit Payment' : 'Record Payment' }}</h2>
            <p>Attach receipts to an atrium event booking.</p>
        </div>
    </section>

    @if ($errors->any())
        <div class="atr-flash" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
            <ul style="margin: 0 0 0 1rem;">
                @foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="atr-card">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="atr-card-head">
            <h3><i class="fa-solid fa-file-invoice" style="color:var(--atr-primary);"></i>Payment Details</h3>
        </div>

        <div class="atr-form-grid">
            <div class="atr-field full">
                <label>Event Booking *</label>
                <select name="atrium_event_id" class="atr-input" required>
                    <option value="">— Choose event —</option>
                    @foreach ($eventsForSelect as $e)
                        <option value="{{ $e->id }}" {{ (int) old('atrium_event_id', $payment->atrium_event_id ?? ($event->id ?? 0)) === $e->id ? 'selected' : '' }}>
                            {{ $e->event_code }} — {{ $e->name_contact_person }} ({{ optional($e->date_of_event)->format('M d, Y') }}) — PHP {{ number_format((float) $e->actual_due, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="atr-field">
                <label>OR Number</label>
                <input type="text" name="or_number" class="atr-input" value="{{ old('or_number', $payment->or_number) }}" placeholder="Leave blank to auto-generate">
            </div>
            <div class="atr-field">
                <label>Date of Payment *</label>
                <input type="date" name="date_of_payment" class="atr-input" required value="{{ old('date_of_payment', optional($payment->date_of_payment)->format('Y-m-d') ?: now()->format('Y-m-d')) }}">
            </div>
            <div class="atr-field">
                <label>Payment Amount *</label>
                <input type="number" step="0.01" min="0.01" name="payment_amount" class="atr-input" required value="{{ old('payment_amount', $payment->payment_amount) }}">
            </div>
            <div class="atr-field full">
                <label>Remarks</label>
                <textarea name="remarks" class="atr-input" maxlength="300">{{ old('remarks', $payment->remarks) }}</textarea>
            </div>
        </div>

        <div class="atr-addon-head">
            <a class="atr-btn-outline" href="{{ route('atrium.payments') }}"><i class="fa-solid fa-xmark"></i>Cancel</a>
            <button type="submit" class="atr-btn-primary"><i class="fa-solid fa-floppy-disk"></i>{{ $isEdit ? 'Save Changes' : 'Record Payment' }}</button>
        </div>
    </form>
</div>
@endsection
