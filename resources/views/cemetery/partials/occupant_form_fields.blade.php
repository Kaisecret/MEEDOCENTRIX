@php
    $prefix = $prefix ?? 'occ';
    $defaultRecordNo = old('record_no', $recordNoValue ?? '');
    $defaultStatus = old('status', 'active');
    $defaultMaintenanceStatus = old('maintenance_fee_status', 'unpaid');
    $defaultPlotType = old('plot_type', 'niche');
@endphp

<div class="cor-form-grid">
    <div class="cor-field">
        <label for="{{ $prefix }}RecordNo"><i class="fa-regular fa-address-card"></i>Record No.</label>
        <input id="{{ $prefix }}RecordNo" name="record_no" type="text" class="cor-control" value="{{ $defaultRecordNo }}" required>
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
                <option value="{{ $site->id }}" @selected((string) old('cemetery_site_id') === (string) $site->id)>{{ $site->site_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="cor-field">
        <label for="{{ $prefix }}Category"><i class="fa-solid fa-layer-group"></i>Cemetery Category</label>
        <select id="{{ $prefix }}Category" name="cemetery_category_id" class="cor-control" required>
            <option value="">Select category...</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('cemetery_category_id') === (string) $category->id)>{{ $category->category_name }}</option>
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
</div>

