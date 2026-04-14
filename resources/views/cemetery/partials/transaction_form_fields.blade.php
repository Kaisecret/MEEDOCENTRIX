@php
    $prefix = $prefix ?? 'txn';
    $defaultTransactionNo = old('transaction_no', $transactionNoValue ?? '');
    $defaultStatus = old('status', 'pending');
    $defaultMaintenanceType = old('maintenance_type', 'none');
    $prefillData = $prefillData ?? null;
    $occupantRecords = $occupantRecords ?? collect();
    $serviceLogs = $serviceLogs ?? collect();
    $serviceLinkMap = $serviceLinkMap ?? [];
    $defaultOccupantRecordId = old('occupant_record_id', $prefillData['occupant_record_id'] ?? '');
    $defaultServiceLogId = old('service_log_id', $prefillData['service_log_id'] ?? '');
    $defaultSiteId = old('cemetery_site_id', $prefillData['cemetery_site_id'] ?? '');
    $defaultCategoryId = old('cemetery_category_id', $prefillData['cemetery_category_id'] ?? '');
    $defaultDeceasedName = old('deceased_name', $prefillData['deceased_name'] ?? '');
    $defaultPlotReference = old('plot_reference', $prefillData['plot_reference'] ?? '');
@endphp

<div class="ctx-form-grid">
    <div class="ctx-field">
        <label for="{{ $prefix }}TransactionNo"><i class="fa-solid fa-hashtag"></i>Transaction No.</label>
        <input id="{{ $prefix }}TransactionNo" name="transaction_no" type="text" class="ctx-control" value="{{ $defaultTransactionNo }}" required>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}TransactionDate"><i class="fa-regular fa-calendar-days"></i>Transaction Date</label>
        <input id="{{ $prefix }}TransactionDate" name="transaction_date" type="date" class="ctx-control" value="{{ old('transaction_date') }}" required>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}OccupantRecord"><i class="fa-solid fa-users"></i>Link Occupant Record</label>
        <select id="{{ $prefix }}OccupantRecord" name="occupant_record_id" class="ctx-control js-txn-occupant-link">
            <option value="">None</option>
            @foreach($occupantRecords as $occupantRecord)
                @php $plotRef = $occupantRecord->plot?->plot_reference; @endphp
                <option
                    value="{{ $occupantRecord->id }}"
                    data-site-id="{{ $occupantRecord->cemetery_site_id }}"
                    data-category-id="{{ $occupantRecord->cemetery_category_id }}"
                    data-deceased-name="{{ $occupantRecord->deceased_name }}"
                    data-plot-reference="{{ $plotRef }}"
                    @selected((string) $defaultOccupantRecordId === (string) $occupantRecord->id)
                >
                    {{ $occupantRecord->record_no }} - {{ $occupantRecord->deceased_name }}{{ $plotRef ? ' (' . $plotRef . ')' : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}ServiceLog"><i class="fa-solid fa-book-journal-whills"></i>Link Service Log</label>
        <select id="{{ $prefix }}ServiceLog" name="service_log_id" class="ctx-control js-txn-service-link">
            <option value="">None</option>
            @foreach($serviceLogs as $serviceLog)
                @php
                    $meta = $serviceLinkMap[$serviceLog->id] ?? ['occupant_record_id' => null, 'category_id' => null];
                @endphp
                <option
                    value="{{ $serviceLog->id }}"
                    data-site-id="{{ $serviceLog->cemetery_site_id }}"
                    data-category-id="{{ $meta['category_id'] }}"
                    data-occupant-record-id="{{ $meta['occupant_record_id'] }}"
                    data-deceased-name="{{ $serviceLog->deceased_name }}"
                    data-plot-reference="{{ $serviceLog->plot_reference }}"
                    @selected((string) $defaultServiceLogId === (string) $serviceLog->id)
                >
                    {{ $serviceLog->log_no }} - {{ $serviceLog->deceased_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}Site"><i class="fa-solid fa-location-dot"></i>Cemetery Name</label>
        <select id="{{ $prefix }}Site" name="cemetery_site_id" class="ctx-control js-txn-site" required>
            <option value="">Select cemetery...</option>
            @foreach($sites as $site)
                <option value="{{ $site->id }}" data-site-code="{{ $site->site_code }}" @selected((string) $defaultSiteId === (string) $site->id)>{{ $site->site_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}Category"><i class="fa-solid fa-layer-group"></i>Cemetery Category</label>
        <select id="{{ $prefix }}Category" name="cemetery_category_id" class="ctx-control js-txn-category" required>
            <option value="">Select category...</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" data-category-code="{{ $category->category_code }}" @selected((string) $defaultCategoryId === (string) $category->id)>{{ $category->category_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}TransactionType"><i class="fa-solid fa-list-check"></i>Transaction Type</label>
        <select id="{{ $prefix }}TransactionType" name="cemetery_transaction_type_id" class="ctx-control js-txn-type" required>
            <option value="">Select transaction type...</option>
            @foreach($transactionTypes as $transactionType)
                <option value="{{ $transactionType->id }}" data-type-code="{{ $transactionType->type_code }}" @selected((string) old('cemetery_transaction_type_id') === (string) $transactionType->id)>{{ $transactionType->type_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}Status"><i class="fa-solid fa-circle-info"></i>Status</label>
        <select id="{{ $prefix }}Status" name="status" class="ctx-control" required>
            @foreach($statusOptions as $statusKey => $statusLabel)
                <option value="{{ $statusKey }}" @selected($defaultStatus === $statusKey)>{{ $statusLabel }}</option>
            @endforeach
        </select>
    </div>

    <div class="ctx-field ctx-field-full">
        <label for="{{ $prefix }}DeceasedName"><i class="fa-solid fa-cross"></i>Deceased Name</label>
        <input id="{{ $prefix }}DeceasedName" name="deceased_name" type="text" class="ctx-control" value="{{ $defaultDeceasedName }}" required>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}PlotReference"><i class="fa-solid fa-vector-square"></i>Niche / Lot Number</label>
        <input id="{{ $prefix }}PlotReference" name="plot_reference" type="text" class="ctx-control" value="{{ $defaultPlotReference }}" required>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}Quantity"><i class="fa-solid fa-calculator"></i>Quantity</label>
        <input id="{{ $prefix }}Quantity" name="quantity" type="number" step="0.01" min="0.01" class="ctx-control" value="{{ old('quantity') }}" placeholder="Optional">
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}MaintenanceType"><i class="fa-solid fa-screwdriver-wrench"></i>Maintenance Type</label>
        <select id="{{ $prefix }}MaintenanceType" name="maintenance_type" class="ctx-control js-txn-maintenance-type">
            <option value="none" @selected($defaultMaintenanceType === 'none')>None</option>
            <option value="yearly" @selected($defaultMaintenanceType === 'yearly')>Yearly (300 per year)</option>
            <option value="five_year_fixed" @selected($defaultMaintenanceType === 'five_year_fixed')>5-Year Fixed (1,500)</option>
        </select>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}MaintenanceYears"><i class="fa-regular fa-calendar"></i>Years to Cover</label>
        <input id="{{ $prefix }}MaintenanceYears" name="maintenance_years" type="number" min="1" max="50" step="1" class="ctx-control js-txn-maintenance-years" value="{{ old('maintenance_years') }}" placeholder="Use for yearly only">
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}OtherFee"><i class="fa-solid fa-circle-plus"></i>Other Applicable Fee</label>
        <input id="{{ $prefix }}OtherFee" name="other_applicable_fee" type="number" step="0.01" min="0" class="ctx-control js-txn-other-fee" value="{{ old('other_applicable_fee', '0') }}">
    </div>

    <div class="ctx-field" style="align-content:end;">
        <label for="{{ $prefix }}BurialPermitToggle"><i class="fa-solid fa-file-circle-check"></i>Burial Permit</label>
        <label style="display:flex; align-items:center; gap:8px; min-height:38px; border:1px solid #cbd5e1; border-radius:9px; padding:0 10px; background:#fff;">
            <input id="{{ $prefix }}BurialPermitToggle" type="checkbox" class="js-txn-has-permit" name="has_burial_permit" value="1" @checked((string) old('has_burial_permit', '0') === '1')>
            <span style="font-size:0.84rem; color:#334155; font-weight:600;">Add Burial Permit (300)</span>
        </label>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}BaseFee"><i class="fa-solid fa-coins"></i>Base Fee</label>
        <input id="{{ $prefix }}BaseFee" type="text" class="ctx-control js-txn-base-fee" value="PHP 0.00" readonly>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}MaintenanceFee"><i class="fa-solid fa-wrench"></i>Maintenance Fee</label>
        <input id="{{ $prefix }}MaintenanceFee" type="text" class="ctx-control js-txn-maintenance-fee" value="PHP 0.00" readonly>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}BurialPermitFee"><i class="fa-solid fa-receipt"></i>Burial Permit Fee</label>
        <input id="{{ $prefix }}BurialPermitFee" type="text" class="ctx-control js-txn-permit-fee" value="PHP 0.00" readonly>
    </div>

    <div class="ctx-field">
        <label for="{{ $prefix }}AmountDue"><i class="fa-solid fa-peso-sign"></i>Amount Due</label>
        <input id="{{ $prefix }}AmountDue" name="amount_due" type="number" step="0.01" min="0" class="ctx-control js-txn-amount-due" value="{{ old('amount_due', '0') }}" required readonly>
    </div>

    <div class="ctx-field ctx-field-full">
        <label for="{{ $prefix }}Remarks"><i class="fa-solid fa-comment-dots"></i>Remarks</label>
        <textarea id="{{ $prefix }}Remarks" name="remarks" class="ctx-control ctx-control-textarea">{{ old('remarks') }}</textarea>
    </div>

    <div class="ctx-field ctx-field-full">
        <div style="border:1px solid #dbe6f0; border-radius:10px; background:#f8fbff; padding:10px 12px; color:#1f2937; font-size:0.8rem; line-height:1.5;">
            <strong>Fee Rules Guide:</strong> SJM Regular Single Niche = 10,000; SJM Infant = 5,000; NMC Columbarium/Infant = 5,000; Additional Burial (OMC/NMC/SPMC) = 5,000; SPMC Lot Purchase = 10,000; Burial Permit add-on = 300; Maintenance yearly = years x 300; 5-year fixed = 1,500.
        </div>
    </div>
</div>
