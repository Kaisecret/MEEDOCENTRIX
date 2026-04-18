@php
    $prefix = $prefix ?? 'occ';
    $mode = $mode ?? 'create';
    $isEditMode = $mode === 'edit';
    $defaultRecordNo = old('record_no', $recordNoValue ?? '');
    $defaultStatus = old('status', 'active');
    $defaultMaintenanceStatus = old('maintenance_fee_status', 'unpaid');
    $defaultPlotType = old('plot_type', 'niche');
    $defaultTxNo = old('tx_transaction_no', $transactionNoValue ?? '');
    $defaultTxDate = old('tx_transaction_date', now()->format('Y-m-d\TH:i'));
    $defaultTxStatus = old('tx_status', 'pending');
    $defaultTxMaintenanceType = old('tx_maintenance_type', 'none');
@endphp

<div class="cor-form-grid">
    <div class="cor-field">
        <label for="{{ $prefix }}RecordNo"><i class="fa-regular fa-address-card"></i>Record No.</label>
        <input id="{{ $prefix }}RecordNo" name="record_no" type="text" class="cor-control" value="{{ $defaultRecordNo }}" required>
        @error('record_no')
            <span class="cor-field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}IntermentDate"><i class="fa-regular fa-calendar-days"></i>Date of Interment</label>
        <input id="{{ $prefix }}IntermentDate" name="date_of_interment" type="date" class="cor-control" value="{{ old('date_of_interment') }}" required>
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}Site"><i class="fa-solid fa-location-dot"></i>Cemetery Name</label>
        <select id="{{ $prefix }}Site" name="cemetery_site_id" class="cor-control" required>
            <option value="">Select cemetery...</option>
            @foreach($sites as $site)
                <option
                    value="{{ $site->id }}"
                    data-site-code="{{ strtoupper((string) ($site->site_code ?? '')) }}"
                    @selected((string) old('cemetery_site_id') === (string) $site->id)>
                    {{ $site->site_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}Category"><i class="fa-solid fa-layer-group"></i>Cemetery Category</label>
        <select id="{{ $prefix }}Category" name="cemetery_category_id" class="cor-control" required>
            <option value="">Select category...</option>
            @foreach($categories as $category)
                <option
                    value="{{ $category->id }}"
                    data-category-code="{{ strtoupper((string) ($category->category_code ?? '')) }}"
                    @selected((string) old('cemetery_category_id') === (string) $category->id)>
                    {{ $category->category_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}PlotType"><i class="fa-solid fa-vector-square"></i>Plot Type</label>
        <select id="{{ $prefix }}PlotType" name="plot_type" class="cor-control" required>
            @foreach($plotTypeOptions as $plotTypeKey => $plotTypeLabel)
                <option value="{{ $plotTypeKey }}" @selected($defaultPlotType === $plotTypeKey)>{{ $plotTypeLabel }}</option>
            @endforeach
        </select>
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}PlotReference"><i class="fa-solid fa-hashtag"></i>Niche / Lot Number</label>
        <input id="{{ $prefix }}PlotReference" name="plot_reference" type="text" class="cor-control" value="{{ old('plot_reference') }}" required>
        @error('plot_reference')
            <span class="cor-field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="cor-field cor-field-full">
        <label for="{{ $prefix }}DeceasedName"><i class="fa-solid fa-cross"></i>Name of Deceased</label>
        <input id="{{ $prefix }}DeceasedName" name="deceased_name" type="text" class="cor-control" value="{{ old('deceased_name') }}" required>
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}ContactPerson"><i class="fa-regular fa-user"></i>Contact Person</label>
        <input id="{{ $prefix }}ContactPerson" name="contact_person" type="text" class="cor-control" value="{{ old('contact_person') }}" required>
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}ContactNumber"><i class="fa-solid fa-phone"></i>Contact Number</label>
        <input id="{{ $prefix }}ContactNumber" name="contact_number" type="text" class="cor-control" value="{{ old('contact_number') }}" required>
    </div>

    <div class="cor-field cor-field-full">
        <label for="{{ $prefix }}Address"><i class="fa-solid fa-map-location-dot"></i>Address</label>
        <input id="{{ $prefix }}Address" name="address" type="text" class="cor-control" value="{{ old('address') }}" required>
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}Maintenance"><i class="fa-solid fa-wallet"></i>Maintenance Fee Status</label>
        <select id="{{ $prefix }}Maintenance" name="maintenance_fee_status" class="cor-control" required>
            @foreach($maintenanceStatusOptions as $maintenanceStatusKey => $maintenanceStatusLabel)
                <option value="{{ $maintenanceStatusKey }}" @selected($defaultMaintenanceStatus === $maintenanceStatusKey)>{{ $maintenanceStatusLabel }}</option>
            @endforeach
        </select>
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}Status"><i class="fa-solid fa-circle-info"></i>Record Status</label>
        <select id="{{ $prefix }}Status" name="status" class="cor-control" required>
            @foreach($statusOptions as $statusKey => $statusLabel)
                <option value="{{ $statusKey }}" @selected($defaultStatus === $statusKey)>{{ $statusLabel }}</option>
            @endforeach
        </select>
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}CoverageStart"><i class="fa-solid fa-calendar-check"></i>Coverage Start</label>
        <input id="{{ $prefix }}CoverageStart" name="coverage_start_date" type="date" class="cor-control" value="{{ old('coverage_start_date') }}">
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}CoverageEnd"><i class="fa-solid fa-calendar-xmark"></i>Coverage End</label>
        <input id="{{ $prefix }}CoverageEnd" name="coverage_end_date" type="date" class="cor-control" value="{{ old('coverage_end_date') }}">
    </div>

    <div class="cor-field cor-field-full">
        <label for="{{ $prefix }}Remarks"><i class="fa-solid fa-comment-dots"></i>Remarks</label>
        <textarea id="{{ $prefix }}Remarks" name="remarks" class="cor-control cor-control-textarea">{{ old('remarks') }}</textarea>
    </div>

    @if(! $isEditMode)
        <div class="cor-field cor-field-full" style="margin-top:6px;">
            <div style="border:1px solid #dbe6f0; border-radius:10px; background:#f8fbff; padding:10px 12px; color:#1f2937; font-size:0.8rem; line-height:1.5;">
                <strong>Initial Transaction (Optional)</strong>: if you set transaction type below, this form will save both Occupant Record and Transaction.
            </div>
        </div>

        <div class="cor-field">
            <label for="{{ $prefix }}TxNo"><i class="fa-solid fa-hashtag"></i>Transaction No.</label>
            <input id="{{ $prefix }}TxNo" name="tx_transaction_no" type="text" class="cor-control" value="{{ $defaultTxNo }}">
        </div>

        <div class="cor-field">
            <label for="{{ $prefix }}TxDate"><i class="fa-regular fa-calendar-days"></i>Transaction Date & Time</label>
            <input id="{{ $prefix }}TxDate" name="tx_transaction_date" type="datetime-local" class="cor-control" value="{{ $defaultTxDate }}" readonly data-auto-now="1">
        </div>

        <div class="cor-field">
            <label><i class="fa-solid fa-location-dot"></i>Cemetery Name</label>
            <input id="{{ $prefix }}TxSiteName" type="text" class="cor-control" placeholder="Auto from occupant record" readonly>
        </div>

        <div class="cor-field">
            <label><i class="fa-solid fa-layer-group"></i>Cemetery Category</label>
            <input id="{{ $prefix }}TxCategoryName" type="text" class="cor-control" placeholder="Auto from occupant record" readonly>
        </div>

        <div class="cor-field">
            <label for="{{ $prefix }}TxType"><i class="fa-solid fa-list-check"></i>Transaction Type</label>
            <select id="{{ $prefix }}TxType" name="tx_transaction_type_id" class="cor-control">
                <option value="">Select transaction type...</option>
                @foreach(($transactionTypes ?? collect()) as $transactionType)
                    <option value="{{ $transactionType->id }}" data-type-code="{{ $transactionType->type_code }}" @selected((string) old('tx_transaction_type_id') === (string) $transactionType->id)>
                        {{ $transactionType->type_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="cor-field">
            <label for="{{ $prefix }}TxStatus"><i class="fa-solid fa-circle-info"></i>Status</label>
            <select id="{{ $prefix }}TxStatus" name="tx_status" class="cor-control">
                @foreach(($transactionStatusOptions ?? []) as $txStatusKey => $txStatusLabel)
                    <option value="{{ $txStatusKey }}" @selected($defaultTxStatus === $txStatusKey)>{{ $txStatusLabel }}</option>
                @endforeach
            </select>
        </div>

        <div class="cor-field">
            <label><i class="fa-solid fa-cross"></i>Deceased Name</label>
            <input id="{{ $prefix }}TxDeceased" type="text" class="cor-control" placeholder="Auto from occupant record" readonly>
        </div>

        <div class="cor-field">
            <label><i class="fa-solid fa-vector-square"></i>Niche / Lot Number</label>
            <input id="{{ $prefix }}TxPlotReference" type="text" class="cor-control" placeholder="Auto from occupant record" readonly>
        </div>

        <div class="cor-field">
            <label for="{{ $prefix }}TxQuantity"><i class="fa-solid fa-calculator"></i>Quantity</label>
            <input id="{{ $prefix }}TxQuantity" name="tx_quantity" type="number" step="0.01" min="0.01" class="cor-control" value="{{ old('tx_quantity') }}" placeholder="Optional">
        </div>

        <div class="cor-field">
            <label for="{{ $prefix }}TxMaintenanceType"><i class="fa-solid fa-screwdriver-wrench"></i>Maintenance Type</label>
            <select id="{{ $prefix }}TxMaintenanceType" name="tx_maintenance_type" class="cor-control">
                <option value="none" @selected($defaultTxMaintenanceType === 'none')>None</option>
                <option value="yearly" @selected($defaultTxMaintenanceType === 'yearly')>Yearly (300 per year)</option>
                <option value="five_year_fixed" @selected($defaultTxMaintenanceType === 'five_year_fixed')>5-Year Fixed (1,500)</option>
            </select>
        </div>

        <div class="cor-field">
            <label for="{{ $prefix }}TxMaintenanceYears"><i class="fa-regular fa-calendar"></i>Years to Cover</label>
            <input id="{{ $prefix }}TxMaintenanceYears" name="tx_maintenance_years" type="number" min="1" max="50" step="1" class="cor-control" value="{{ old('tx_maintenance_years') }}" placeholder="Use for yearly only">
        </div>

        <div class="cor-field">
            <label for="{{ $prefix }}TxOtherFee"><i class="fa-solid fa-circle-plus"></i>Other Applicable Fee</label>
            <input id="{{ $prefix }}TxOtherFee" name="tx_other_applicable_fee" type="number" step="0.01" min="0" class="cor-control" value="{{ old('tx_other_applicable_fee', '0') }}">
        </div>

        <div class="cor-field" style="align-content:end;">
            <label for="{{ $prefix }}TxBurialPermit"><i class="fa-solid fa-file-circle-check"></i>Burial Permit</label>
            <label style="display:flex; align-items:center; gap:8px; min-height:38px; border:1px solid #cbd5e1; border-radius:9px; padding:0 10px; background:#fff;">
                <input id="{{ $prefix }}TxBurialPermit" name="tx_has_burial_permit" type="checkbox" value="1" @checked((string) old('tx_has_burial_permit', '0') === '1')>
                <span style="font-size:0.84rem; color:#334155; font-weight:600;">Add Burial Permit (300)</span>
            </label>
        </div>

        <div class="cor-field">
            <label><i class="fa-solid fa-coins"></i>Base Fee</label>
            <input id="{{ $prefix }}TxBaseFee" type="text" class="cor-control" value="PHP 0.00" readonly>
        </div>

        <div class="cor-field">
            <label><i class="fa-solid fa-wrench"></i>Maintenance Fee</label>
            <input id="{{ $prefix }}TxMaintenanceFee" type="text" class="cor-control" value="PHP 0.00" readonly>
        </div>

        <div class="cor-field">
            <label><i class="fa-solid fa-receipt"></i>Burial Permit Fee</label>
            <input id="{{ $prefix }}TxPermitFee" type="text" class="cor-control" value="PHP 0.00" readonly>
        </div>

        <div class="cor-field">
            <label><i class="fa-solid fa-peso-sign"></i>Amount Due</label>
            <input id="{{ $prefix }}TxAmountDue" type="text" class="cor-control" value="PHP 0.00" readonly>
        </div>

        <div class="cor-field cor-field-full">
            <label for="{{ $prefix }}TxRemarks"><i class="fa-solid fa-comment-dots"></i>Transaction Remarks</label>
            <textarea id="{{ $prefix }}TxRemarks" name="tx_remarks" class="cor-control cor-control-textarea">{{ old('tx_remarks') }}</textarea>
        </div>
    @endif
</div>
