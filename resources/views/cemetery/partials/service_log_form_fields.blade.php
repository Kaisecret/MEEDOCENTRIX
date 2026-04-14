@php
    $prefix = $prefix ?? 'svc';
    $defaultLogNo = old('log_no', $logNoValue ?? '');
    $defaultProcessedBy = old('processed_by', $processedByValue ?? '');
@endphp

<div class="csl-form-grid">
    <div class="csl-field">
        <label for="{{ $prefix }}LogNo"><i class="fa-solid fa-hashtag"></i>Log No.</label>
        <input id="{{ $prefix }}LogNo" name="log_no" type="text" class="csl-control" value="{{ $defaultLogNo }}" required>
    </div>

    <div class="csl-field">
        <label for="{{ $prefix }}ServiceDate"><i class="fa-regular fa-calendar-days"></i>Service Date</label>
        <input id="{{ $prefix }}ServiceDate" name="service_date" type="date" class="csl-control" value="{{ old('service_date') }}" required>
    </div>

    <div class="csl-field">
        <label for="{{ $prefix }}Site"><i class="fa-solid fa-location-dot"></i>Cemetery Name</label>
        <select id="{{ $prefix }}Site" name="cemetery_site_id" class="csl-control" required>
            <option value="">Select cemetery...</option>
            @foreach($sites as $site)
                <option value="{{ $site->id }}" @selected((string) old('cemetery_site_id') === (string) $site->id)>{{ $site->site_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="csl-field">
        <label for="{{ $prefix }}ServiceType"><i class="fa-solid fa-list-check"></i>Service Type</label>
        <select id="{{ $prefix }}ServiceType" name="cemetery_service_type_id" class="csl-control" required>
            <option value="">Select service type...</option>
            @foreach($serviceTypes as $serviceType)
                <option value="{{ $serviceType->id }}" @selected((string) old('cemetery_service_type_id') === (string) $serviceType->id)>{{ $serviceType->type_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="csl-field csl-field-full">
        <label for="{{ $prefix }}DeceasedName"><i class="fa-solid fa-cross"></i>Deceased Name</label>
        <input id="{{ $prefix }}DeceasedName" name="deceased_name" type="text" class="csl-control" value="{{ old('deceased_name') }}" required>
    </div>

    <div class="csl-field">
        <label for="{{ $prefix }}PlotReference"><i class="fa-solid fa-vector-square"></i>Niche / Lot Number</label>
        <input id="{{ $prefix }}PlotReference" name="plot_reference" type="text" class="csl-control" value="{{ old('plot_reference') }}" required>
    </div>

    <div class="csl-field">
        <label for="{{ $prefix }}ProcessedBy"><i class="fa-regular fa-user"></i>Processed By</label>
        <input id="{{ $prefix }}ProcessedBy" name="processed_by" type="text" class="csl-control" value="{{ $defaultProcessedBy }}" required>
    </div>

    <div class="csl-field csl-field-full">
        <label for="{{ $prefix }}Details"><i class="fa-solid fa-file-circle-plus"></i>Details</label>
        <textarea id="{{ $prefix }}Details" name="details" class="csl-control csl-control-textarea">{{ old('details') }}</textarea>
    </div>

    <div class="csl-field csl-field-full">
        <label for="{{ $prefix }}Remarks"><i class="fa-solid fa-comment-dots"></i>Remarks</label>
        <textarea id="{{ $prefix }}Remarks" name="remarks" class="csl-control csl-control-textarea">{{ old('remarks') }}</textarea>
    </div>
</div>

