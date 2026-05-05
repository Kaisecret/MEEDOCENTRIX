@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')

@php
    $isEdit = $mode === 'edit';
    $action = $isEdit ? route('atrium.bookings.update', $event) : route('atrium.bookings.store');
    $existingAddOns = $isEdit ? $event->addOns : collect();
@endphp

<div class="atr" data-server-rendered-page="atrium_bookings" data-page-title="{{ $isEdit ? 'Edit Booking' : 'New Booking' }}">
    <section class="atr-hero">
        <div>
            <h2><i class="fa-solid fa-{{ $isEdit ? 'pen' : 'plus' }}" style="margin-right:8px;opacity:.88;"></i>{{ $isEdit ? 'Edit Booking' : 'New Booking' }}</h2>
            <p>{{ $isEdit ? 'Update event details, hall, add-ons and rates.' : 'Register a new atrium event with hall, hours, and add-on rentals.' }}</p>
        </div>
        <div class="atr-hero-meta">
            <span style="font-size:.83rem;color:rgba(255,255,255,.84);font-weight:600;">Code: <b>{{ $nextCode }}</b></span>
        </div>
    </section>

    @if ($errors->any())
        <div class="atr-flash" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
            <strong>Please correct the following:</strong>
            <ul style="margin: .4rem 0 0 1rem;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="atr-card">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="atr-card-head">
            <h3><i class="fa-solid fa-calendar-plus" style="color:var(--atr-primary);"></i>Event Details</h3>
            <span>Fields marked * are required</span>
        </div>

        <div class="atr-form-grid">
            <div class="atr-field">
                <label>Event Code</label>
                <input type="text" name="event_code" class="atr-input" value="{{ old('event_code', $isEdit ? $event->event_code : $nextCode) }}" placeholder="Auto-generated if blank">
                <span class="atr-help">Leave blank to auto-generate.</span>
            </div>
            <div class="atr-field">
                <label>Date of Event *</label>
                <input type="date" name="date_of_event" class="atr-input" required value="{{ old('date_of_event', optional($event->date_of_event)->format('Y-m-d')) }}">
            </div>
            <div class="atr-field">
                <label>Start Time</label>
                <input type="time" name="start_time" class="atr-input" value="{{ old('start_time', $event->start_time) }}">
            </div>
            <div class="atr-field">
                <label>Number of Hours *</label>
                <input type="number" step="0.25" min="0.5" max="48" name="no_of_hours" class="atr-input" required value="{{ old('no_of_hours', $event->no_of_hours ?? 1) }}">
            </div>

            <div class="atr-field full">
                <label>Event Details *</label>
                <textarea name="event_details" class="atr-input" required maxlength="500" placeholder="Describe the event (e.g., Wedding Reception, Birthday, Seminar...)">{{ old('event_details', $event->event_details) }}</textarea>
            </div>

            <div class="atr-field">
                <label>Name of Contact Person *</label>
                <input type="text" name="name_contact_person" class="atr-input" required maxlength="160" value="{{ old('name_contact_person', $event->name_contact_person) }}">
            </div>
            <div class="atr-field">
                <label>Contact Number</label>
                <input type="text" name="contact_number" class="atr-input" maxlength="60" value="{{ old('contact_number', $event->contact_number) }}">
            </div>

            <div class="atr-field">
                <label>Function Hall *</label>
                <select name="atrium_function_hall_id" class="atr-input" required>
                    <option value="">— Choose a hall —</option>
                    @foreach ($halls as $hall)
                        <option value="{{ $hall->id }}" data-rate="{{ $hall->hourly_rate }}" {{ (int) old('atrium_function_hall_id', $event->atrium_function_hall_id) === $hall->id ? 'selected' : '' }}>
                            {{ $hall->name }} ({{ $hall->code }}) — PHP {{ number_format((float) $hall->hourly_rate, 2) }}/hr
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="atr-field">
                <label>Booking Status</label>
                <select name="booking_status" class="atr-input">
                    @foreach (['reserved','confirmed','cancelled'] as $st)
                        <option value="{{ $st }}" {{ old('booking_status', $event->booking_status ?? 'reserved') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="atr-card-head" style="border-top:1px solid var(--atr-border);">
            <h3><i class="fa-solid fa-peso-sign" style="color:var(--atr-primary);"></i>Rates & Charges</h3>
            <span>All amounts in PHP</span>
        </div>

        <div class="atr-form-grid atr-form-grid--wide">
            <div class="atr-field">
                <label>Hall Payment *</label>
                <input type="number" step="0.01" min="0" name="hall_payment" id="atrHallPayment" class="atr-input" required value="{{ old('hall_payment', $event->hall_payment ?? 0) }}">
                <span class="atr-help">Auto-computed from hourly rate × hours; override freely.</span>
            </div>
            <div class="atr-field">
                <label>Miscellaneous Payment</label>
                <input type="number" step="0.01" min="0" name="miscellaneous_payment" class="atr-input" value="{{ old('miscellaneous_payment', $event->miscellaneous_payment ?? 0) }}">
            </div>
            <div class="atr-field">
                <label>Accommodation Payment</label>
                <input type="number" step="0.01" min="0" name="accommodation_payment" class="atr-input" value="{{ old('accommodation_payment', $event->accommodation_payment ?? 0) }}">
            </div>
        </div>

        <div class="atr-card-head" style="border-top:1px solid var(--atr-border);">
            <h3><i class="fa-solid fa-boxes-stacked" style="color:var(--atr-primary);"></i>Add-Ons</h3>
            <button type="button" class="atr-btn-outline" id="atrAddOnAdd"><i class="fa-solid fa-plus"></i>Add Row</button>
        </div>
        <div id="atrAddOnList">
            @forelse ($existingAddOns as $idx => $row)
                <div class="atr-addon-row">
                    <input type="text" name="add_ons[{{ $idx }}][description]" class="atr-input" placeholder="Description (e.g., Sound System)" value="{{ old('add_ons.' . $idx . '.description', $row->description) }}">
                    <input type="number" step="0.01" min="0" name="add_ons[{{ $idx }}][amount]" class="atr-input" placeholder="Amount" value="{{ old('add_ons.' . $idx . '.amount', $row->amount) }}">
                    <button type="button" class="atr-btn-danger atr-addon-remove"><i class="fa-solid fa-trash"></i></button>
                </div>
            @empty
                <div class="atr-addon-row">
                    <input type="text" name="add_ons[0][description]" class="atr-input" placeholder="Description (optional)">
                    <input type="number" step="0.01" min="0" name="add_ons[0][amount]" class="atr-input" placeholder="Amount">
                    <button type="button" class="atr-btn-danger atr-addon-remove"><i class="fa-solid fa-trash"></i></button>
                </div>
            @endforelse
        </div>

        <div class="atr-addon-head">
            <a class="atr-btn-outline" href="{{ route('atrium.bookings') }}"><i class="fa-solid fa-xmark"></i>Cancel</a>
            <button type="submit" class="atr-btn-primary"><i class="fa-solid fa-floppy-disk"></i>{{ $isEdit ? 'Save Changes' : 'Create Booking' }}</button>
        </div>
    </form>
</div>

<script>
(function () {
    const list = document.getElementById('atrAddOnList');
    const addBtn = document.getElementById('atrAddOnAdd');
    const hallSelect = document.querySelector('select[name="atrium_function_hall_id"]');
    const hoursInput = document.querySelector('input[name="no_of_hours"]');
    const hallPayment = document.getElementById('atrHallPayment');

    const recomputeHallPayment = () => {
        if (!hallSelect || !hoursInput || !hallPayment) return;
        const opt = hallSelect.options[hallSelect.selectedIndex];
        const rate = Number.parseFloat(opt ? opt.dataset.rate || 0 : 0) || 0;
        const hours = Number.parseFloat(hoursInput.value || 0) || 0;
        if (rate > 0 && hours > 0) {
            hallPayment.value = (rate * hours).toFixed(2);
        }
    };

    hallSelect && hallSelect.addEventListener('change', recomputeHallPayment);
    hoursInput && hoursInput.addEventListener('input', recomputeHallPayment);

    addBtn && addBtn.addEventListener('click', () => {
        const idx = list.querySelectorAll('.atr-addon-row').length;
        const div = document.createElement('div');
        div.className = 'atr-addon-row';
        div.innerHTML = `
            <input type="text" name="add_ons[${idx}][description]" class="atr-input" placeholder="Description (optional)">
            <input type="number" step="0.01" min="0" name="add_ons[${idx}][amount]" class="atr-input" placeholder="Amount">
            <button type="button" class="atr-btn-danger atr-addon-remove"><i class="fa-solid fa-trash"></i></button>
        `;
        list.appendChild(div);
    });

    list && list.addEventListener('click', (e) => {
        const btn = e.target.closest('.atr-addon-remove');
        if (!btn) return;
        const row = btn.closest('.atr-addon-row');
        if (list.querySelectorAll('.atr-addon-row').length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input').forEach(i => i.value = '');
        }
    });
})();
</script>
@endsection
