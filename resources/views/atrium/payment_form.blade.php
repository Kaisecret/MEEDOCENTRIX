@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')

@php
    $isEdit = $mode === 'edit';
    $action = $isEdit ? route('atrium.payments.update', $payment) : route('atrium.payments.store');
    $selectedEventId = (int) old('atrium_event_id', $payment->atrium_event_id ?? ($event->id ?? 0));
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
                <select name="atrium_event_id" id="atrEventSelect" class="atr-input" required>
                    <option value="">- Choose event -</option>
                    @foreach ($eventsForSelect as $e)
                        @php
                            $totalPaid = (float) ($e->total_paid ?? 0);
                            $due = (float) $e->actual_due;
                            $balance = max(0.0, $due - $totalPaid);
                        @endphp
                        <option
                            value="{{ $e->id }}"
                            data-due="{{ number_format($due, 2, '.', '') }}"
                            data-paid="{{ number_format($totalPaid, 2, '.', '') }}"
                            data-balance="{{ number_format($balance, 2, '.', '') }}"
                            {{ $selectedEventId === $e->id ? 'selected' : '' }}
                        >
                            {{ $e->event_code }} - {{ $e->name_contact_person }} ({{ optional($e->date_of_event)->format('M d, Y') }}) - Remaining PHP {{ number_format($balance, 2) }}
                        </option>
                    @endforeach
                </select>
                <div class="atr-help" style="margin-top:.45rem;display:flex;gap:1rem;flex-wrap:wrap;">
                    <span>Total Due: <b id="atrEventDue">PHP 0.00</b></span>
                    <span>Paid: <b id="atrEventPaid">PHP 0.00</b></span>
                    <span>Remaining Balance: <b id="atrEventBalance">PHP 0.00</b></span>
                </div>
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

<script>
(function () {
    const eventSelect = document.getElementById('atrEventSelect');
    const dueEl = document.getElementById('atrEventDue');
    const paidEl = document.getElementById('atrEventPaid');
    const balanceEl = document.getElementById('atrEventBalance');

    if (!eventSelect || !dueEl || !paidEl || !balanceEl) return;

    const formatPhp = (value) => {
        const amount = Number.isFinite(value) ? value : 0;
        return 'PHP ' + amount.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    const updateBalanceInfo = () => {
        const selected = eventSelect.options[eventSelect.selectedIndex];
        if (!selected || selected.value === '') {
            dueEl.textContent = 'PHP 0.00';
            paidEl.textContent = 'PHP 0.00';
            balanceEl.textContent = 'PHP 0.00';
            return;
        }

        const due = Number(selected.dataset.due || 0);
        const paid = Number(selected.dataset.paid || 0);
        const balance = Math.max(0, Number(selected.dataset.balance || (due - paid)));

        dueEl.textContent = formatPhp(due);
        paidEl.textContent = formatPhp(paid);
        balanceEl.textContent = formatPhp(balance);
    };

    eventSelect.addEventListener('change', updateBalanceInfo);
    updateBalanceInfo();
})();
</script>
@endsection
