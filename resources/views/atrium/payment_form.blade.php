@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')
<style>
    #contentArea {
        padding-top: 10px;
    }

    .atr {
        gap: 10px;
    }

    .atr-card-head {
        padding: 10px;
        gap: 10px;
    }

    .atr-card-head h3 {
        gap: 10px;
    }

    .atr-form-grid {
        gap: 10px;
        padding: 10px;
    }

    .atr-field {
        gap: 10px;
    }

    .atr-addon-head {
        padding: 10px;
        gap: 10px;
    }

    .atr-popup-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        padding: 12px;
    }

    .atr-popup-backdrop.is-open {
        display: flex;
    }

    .atr-popup {
        width: min(440px, 100%);
        border-radius: 14px;
        background: #fff;
        border: 1px solid #fecaca;
        box-shadow: 0 16px 40px rgba(2, 6, 23, .28);
        overflow: hidden;
    }

    .atr-popup-head {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px;
        background: #fef2f2;
        color: #b91c1c;
        font-weight: 800;
        font-size: .92rem;
        border-bottom: 1px solid #fecaca;
    }

    .atr-popup-body {
        padding: 12px;
        color: #7f1d1d;
        font-size: .88rem;
        line-height: 1.45;
    }

    .atr-popup-actions {
        display: flex;
        justify-content: flex-end;
        padding: 0 12px 12px;
    }

    .atr-btn-primary:disabled {
        opacity: .55;
        cursor: not-allowed;
        pointer-events: none;
    }
</style>

@php
    $isEdit = $mode === 'edit';
    $action = $isEdit ? route('atrium.payments.update', $payment) : route('atrium.payments.store');
    $selectedEventId = (int) old('atrium_event_id', $payment->atrium_event_id ?? ($event->id ?? 0));
@endphp

<div class="atr" data-server-rendered-page="atrium_payments" data-page-title="{{ $isEdit ? 'Edit Payment' : 'Record Payment' }}">
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
            <button type="submit" class="atr-btn-primary" id="atrPaymentSubmitBtn"><i class="fa-solid fa-floppy-disk"></i>{{ $isEdit ? 'Save Changes' : 'Record Payment' }}</button>
        </div>
    </form>

    <div class="atr-popup-backdrop" id="atrPayErrorPopup" aria-hidden="true">
        <div class="atr-popup" role="alertdialog" aria-modal="true" aria-labelledby="atrPayErrorTitle">
            <div class="atr-popup-head" id="atrPayErrorTitle">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Payment Validation Error
            </div>
            <div class="atr-popup-body" id="atrPayErrorMessage">
                Payment amount is invalid.
            </div>
            <div class="atr-popup-actions">
                <button type="button" class="atr-btn-primary" id="atrPayErrorOkBtn">Okay</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const eventSelect = document.getElementById('atrEventSelect');
    const form = eventSelect ? eventSelect.closest('form') : null;
    const amountInput = form ? form.querySelector('input[name="payment_amount"]') : null;
    const submitBtn = document.getElementById('atrPaymentSubmitBtn');
    const dueEl = document.getElementById('atrEventDue');
    const paidEl = document.getElementById('atrEventPaid');
    const balanceEl = document.getElementById('atrEventBalance');
    const popup = document.getElementById('atrPayErrorPopup');
    const popupMsg = document.getElementById('atrPayErrorMessage');
    const popupOk = document.getElementById('atrPayErrorOkBtn');

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
            if (submitBtn) submitBtn.disabled = true;
            return;
        }

        const due = Number(selected.dataset.due || 0);
        const paid = Number(selected.dataset.paid || 0);
        const balance = Math.max(0, Number(selected.dataset.balance || (due - paid)));

        dueEl.textContent = formatPhp(due);
        paidEl.textContent = formatPhp(paid);
        balanceEl.textContent = formatPhp(balance);
        if (submitBtn) submitBtn.disabled = balance <= 0;
    };

    const showPopup = (message) => {
        if (!popup || !popupMsg) return;
        popupMsg.textContent = message;
        popup.classList.add('is-open');
        popup.setAttribute('aria-hidden', 'false');
    };

    const closePopup = () => {
        if (!popup) return;
        popup.classList.remove('is-open');
        popup.setAttribute('aria-hidden', 'true');
    };

    if (popupOk) {
        popupOk.addEventListener('click', closePopup);
    }

    if (popup) {
        popup.addEventListener('click', (event) => {
            if (event.target === popup) closePopup();
        });
    }

    if (form && amountInput) {
        form.addEventListener('submit', (event) => {
            const selected = eventSelect.options[eventSelect.selectedIndex];
            if (!selected || selected.value === '') {
                event.preventDefault();
                showPopup('Please select a booking first.');
                return;
            }

            const enteredAmount = Number(amountInput.value || 0);
            const remaining = Math.max(0, Number(selected.dataset.balance || 0));

            if (!Number.isFinite(enteredAmount) || enteredAmount <= 0) {
                event.preventDefault();
                showPopup('Please enter a valid payment amount greater than zero.');
                return;
            }

            if (remaining <= 0) {
                event.preventDefault();
                showPopup('This booking has zero remaining balance and cannot accept new payments.');
                return;
            }

            if (enteredAmount > remaining + 0.009) {
                event.preventDefault();
                showPopup(`Payment amount cannot be greater than remaining balance (${formatPhp(remaining)}).`);
                amountInput.focus();
            }
        });
    }

    eventSelect.addEventListener('change', updateBalanceInfo);
    updateBalanceInfo();
})();
</script>
@endsection
