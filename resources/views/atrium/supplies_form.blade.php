@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')

@php
    $isEdit = $mode === 'edit';
    $action = $isEdit ? route('atrium.supplies.update', $order) : route('atrium.supplies.store');
@endphp

<div class="atr" data-server-rendered-page="atrium_supplies" data-page-title="{{ $isEdit ? 'Edit Supplies Request' : 'New Supplies Request' }}">
    <section class="atr-hero">
        <div>
            <h2><i class="fa-solid fa-{{ $isEdit ? 'pen' : 'box-archive' }}" style="margin-right:8px;opacity:.88;"></i>{{ $isEdit ? 'Edit Supplies Request' : 'New Supplies Request' }}</h2>
            <p>Supplies are tied to an atrium event booking record.</p>
        </div>
    </section>

    @if ($errors->any())
        <div class="atr-flash" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
            <ul style="margin: 0 0 0 1rem;">@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="atr-card">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="atr-card-head">
            <h3><i class="fa-solid fa-boxes-stacked" style="color:var(--atr-primary);"></i>Request Details</h3>
        </div>

        <div class="atr-form-grid">
            <div class="atr-field full">
                <label>Event Booking *</label>
                <select name="atrium_event_id" class="atr-input" required>
                    <option value="">— Choose event —</option>
                    @foreach ($eventsForSelect as $e)
                        <option value="{{ $e->id }}" {{ (int) old('atrium_event_id', $order->atrium_event_id ?? ($event->id ?? 0)) === $e->id ? 'selected' : '' }}>
                            {{ $e->event_code }} — {{ $e->name_contact_person }} ({{ optional($e->date_of_event)->format('M d, Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="atr-field">
                <label>Time Needed</label>
                <input type="text" name="time_needed" class="atr-input" placeholder="e.g., 09:00 AM or Before setup" value="{{ old('time_needed', $order->time_needed) }}">
            </div>
            <div class="atr-field">
                <label>Status</label>
                <select name="request_status" class="atr-input">
                    @foreach (['pending','approved','fulfilled','rejected'] as $st)
                        <option value="{{ $st }}" {{ old('request_status', $order->request_status ?? 'pending') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="atr-field full">
                <label>Requested Supplies *</label>
                <textarea name="requested_supplies" class="atr-input" required maxlength="2000" placeholder="List the supplies needed (e.g., 20 monobloc chairs, 4 round tables, 1 PA system...)">{{ old('requested_supplies', $order->requested_supplies) }}</textarea>
            </div>
            <div class="atr-field full">
                <label>Remarks</label>
                <textarea name="remarks" class="atr-input" maxlength="500">{{ old('remarks', $order->remarks) }}</textarea>
            </div>
        </div>

        <div class="atr-addon-head">
            <a class="atr-btn-outline" href="{{ route('atrium.supplies') }}"><i class="fa-solid fa-xmark"></i>Cancel</a>
            <button type="submit" class="atr-btn-primary"><i class="fa-solid fa-floppy-disk"></i>{{ $isEdit ? 'Save Changes' : 'Submit Request' }}</button>
        </div>
    </form>
</div>
@endsection
