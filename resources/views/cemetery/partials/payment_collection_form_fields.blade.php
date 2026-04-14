@php
    $prefix = $prefix ?? 'cpc';
    $defaultPaymentNo = old('payment_no', $paymentNoValue ?? '');
    $defaultStatus = old('payment_status', 'unpaid');
@endphp

<div class="cpc-form-grid">
    <div class="cpc-field">
        <label for="{{ $prefix }}PaymentNo"><i class="fa-solid fa-hashtag"></i>Payment Reference</label>
        <input id="{{ $prefix }}PaymentNo" name="payment_no" type="text" class="cpc-control" value="{{ $defaultPaymentNo }}" required>
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}Transaction"><i class="fa-solid fa-link"></i>Transaction Reference</label>
        <select id="{{ $prefix }}Transaction" name="cemetery_transaction_id" class="cpc-control js-payment-transaction-select" required>
            <option value="">Select transaction...</option>
            @foreach($transactions as $transaction)
                <option
                    value="{{ $transaction->id }}"
                    data-site-name="{{ $transaction->site?->site_name }}"
                    data-category-name="{{ $transaction->category?->category_name }}"
                    data-deceased-name="{{ $transaction->deceased_name }}"
                    data-plot-reference="{{ $transaction->plot_reference }}"
                    data-amount-due="{{ number_format((float) $transaction->amount_due, 2, '.', '') }}"
                    data-default-contact-id="{{ $contactByTransactionId[$transaction->id] ?? '' }}"
                    @selected((string) old('cemetery_transaction_id') === (string) $transaction->id)
                >
                    {{ $transaction->transaction_no }} - {{ $transaction->deceased_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}SiteName"><i class="fa-solid fa-location-dot"></i>Cemetery Name</label>
        <input id="{{ $prefix }}SiteName" type="text" class="cpc-control" placeholder="Auto from transaction" readonly>
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}CategoryName"><i class="fa-solid fa-layer-group"></i>Cemetery Category</label>
        <input id="{{ $prefix }}CategoryName" type="text" class="cpc-control" placeholder="Auto from transaction" readonly>
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}DeceasedName"><i class="fa-solid fa-cross"></i>Deceased Name</label>
        <input id="{{ $prefix }}DeceasedName" type="text" class="cpc-control" placeholder="Auto from transaction" readonly>
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}PlotReference"><i class="fa-solid fa-vector-square"></i>Niche / Lot Number</label>
        <input id="{{ $prefix }}PlotReference" type="text" class="cpc-control" placeholder="Auto from transaction" readonly>
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}Contact"><i class="fa-solid fa-user"></i>Contact Person</label>
        <select id="{{ $prefix }}Contact" name="cemetery_contact_id" class="cpc-control" required>
            <option value="">Select contact person...</option>
            @foreach($contacts as $contact)
                <option value="{{ $contact->id }}" @selected((string) old('cemetery_contact_id') === (string) $contact->id)>
                    {{ $contact->contact_person }}{{ $contact->contact_number ? ' (' . $contact->contact_number . ')' : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}AmountDue"><i class="fa-solid fa-peso-sign"></i>Amount Due</label>
        <input id="{{ $prefix }}AmountDue" type="text" class="cpc-control" placeholder="Auto from transaction" readonly>
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}AmountPaid"><i class="fa-solid fa-money-bill-wave"></i>Amount Paid</label>
        <input id="{{ $prefix }}AmountPaid" name="amount_paid" type="number" step="0.01" min="0" class="cpc-control" value="{{ old('amount_paid') }}" required>
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}OrNo"><i class="fa-solid fa-receipt"></i>Official Receipt No.</label>
        <input id="{{ $prefix }}OrNo" name="official_receipt_no" type="text" class="cpc-control" value="{{ old('official_receipt_no') }}">
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}PaymentDate"><i class="fa-regular fa-calendar-days"></i>Payment Date</label>
        <input id="{{ $prefix }}PaymentDate" name="payment_date" type="date" class="cpc-control" value="{{ old('payment_date') }}">
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}CoverageStart"><i class="fa-regular fa-calendar-plus"></i>Coverage Start</label>
        <input id="{{ $prefix }}CoverageStart" name="coverage_start_date" type="date" class="cpc-control" value="{{ old('coverage_start_date') }}">
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}CoverageEnd"><i class="fa-regular fa-calendar-check"></i>Coverage End</label>
        <input id="{{ $prefix }}CoverageEnd" name="coverage_end_date" type="date" class="cpc-control" value="{{ old('coverage_end_date') }}">
    </div>

    <div class="cpc-field">
        <label for="{{ $prefix }}PaymentStatus"><i class="fa-solid fa-circle-info"></i>Payment Status</label>
        <select id="{{ $prefix }}PaymentStatus" name="payment_status" class="cpc-control" required>
            @foreach($statusOptions as $statusKey => $statusLabel)
                <option value="{{ $statusKey }}" @selected($defaultStatus === $statusKey)>{{ $statusLabel }}</option>
            @endforeach
        </select>
    </div>

    <div class="cpc-field cpc-field-full">
        <label for="{{ $prefix }}Remarks"><i class="fa-solid fa-comment-dots"></i>Remarks</label>
        <textarea id="{{ $prefix }}Remarks" name="remarks" class="cpc-control cpc-control-textarea">{{ old('remarks') }}</textarea>
    </div>
</div>
