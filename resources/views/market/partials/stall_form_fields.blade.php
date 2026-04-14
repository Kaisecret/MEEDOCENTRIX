@php
    $prefix = $prefix ?? 'stall';
    $defaultStatus = old('stall_status', 'vacant');
    $defaultRate = old('rate_amount', $rateValue ?? '');
@endphp

<div class="msr-form-grid">
    <div class="msr-form-field">
        <label for="{{ $prefix }}StallNo"><i class="fa-solid fa-store" style="color:#0f5fa8;margin-right:6px;"></i>Stall No.</label>
        <input id="{{ $prefix }}StallNo" name="stall_no" type="text" class="msr-control" value="{{ old('stall_no') }}" required>
    </div>

    <div class="msr-form-field">
        <label for="{{ $prefix }}LocationId"><i class="fa-solid fa-location-dot" style="color:#0f5fa8;margin-right:6px;"></i>Location</label>
        <select id="{{ $prefix }}LocationId" name="market_stall_location_id" class="msr-control" required>
            <option value="">Select location...</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" data-rate="{{ number_format((float) ($location->activeRate?->rate_amount ?? 0), 2, '.', '') }}" @selected((string) old('market_stall_location_id') === (string) $location->id)>
                    {{ $location->location_code }} - {{ $location->location_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="msr-form-field">
        <label for="{{ $prefix }}TypeId"><i class="fa-solid fa-filter" style="color:#0f5fa8;margin-right:6px;"></i>Stall Type</label>
        <select id="{{ $prefix }}TypeId" name="market_stall_type_id" class="msr-control" required>
            <option value="">Select type...</option>
            @foreach($stallTypes as $type)
                <option value="{{ $type->id }}" @selected((string) old('market_stall_type_id') === (string) $type->id)>
                    {{ $type->type_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="msr-form-field">
        <label for="{{ $prefix }}Dimension"><i class="fa-solid fa-ruler-combined" style="color:#0f5fa8;margin-right:6px;"></i>Dimension (sq.m)</label>
        <input id="{{ $prefix }}Dimension" name="dimension_sq_m" type="number" step="0.01" min="0" class="msr-control" value="{{ old('dimension_sq_m') }}">
    </div>

    <div class="msr-form-field">
        <label for="{{ $prefix }}Status"><i class="fa-solid fa-circle-check" style="color:#0f5fa8;margin-right:6px;"></i>Stall Status</label>
        <select id="{{ $prefix }}Status" name="stall_status" class="msr-control" data-stall-status required>
            @foreach($statusOptions as $statusKey => $statusLabel)
                <option value="{{ $statusKey }}" @selected($defaultStatus === $statusKey)>{{ $statusLabel }}</option>
            @endforeach
        </select>
    </div>

    <div class="msr-form-field">
        <label for="{{ $prefix }}Rate"><i class="fa-solid fa-peso-sign" style="color:#0f5fa8;margin-right:6px;"></i>Rate (PHP)</label>
        <input id="{{ $prefix }}Rate" name="rate_amount" type="number" step="0.01" min="0" class="msr-control" value="{{ $defaultRate }}" data-rate-input required>
    </div>

    <div class="msr-form-field">
        <label for="{{ $prefix }}StartDate"><i class="fa-regular fa-calendar-check" style="color:#0f5fa8;margin-right:6px;"></i>Lease Start</label>
        <input id="{{ $prefix }}StartDate" name="start_date" type="date" class="msr-control" value="{{ old('start_date') }}">
    </div>

    <div class="msr-form-field">
        <label for="{{ $prefix }}EndDate"><i class="fa-regular fa-calendar-xmark" style="color:#0f5fa8;margin-right:6px;"></i>Lease End</label>
        <input id="{{ $prefix }}EndDate" name="end_date" type="date" class="msr-control" value="{{ old('end_date') }}">
    </div>

    <div class="msr-form-field">
        <label for="{{ $prefix }}ContractNo"><i class="fa-solid fa-file-contract" style="color:#0f5fa8;margin-right:6px;"></i>Contract No.</label>
        <input id="{{ $prefix }}ContractNo" name="contract_number" type="text" class="msr-control" value="{{ old('contract_number') }}">
    </div>

    <div class="msr-form-field msr-form-field--full">
        <label for="{{ $prefix }}Description"><i class="fa-solid fa-clipboard-list" style="color:#0f5fa8;margin-right:6px;"></i>Stall Description</label>
        <textarea id="{{ $prefix }}Description" name="description" class="msr-control msr-textarea">{{ old('description') }}</textarea>
    </div>

    <div class="msr-form-field msr-form-field--full">
        <input type="hidden" name="is_billable" value="0">
        <label class="msr-check">
            <input id="{{ $prefix }}Billable" type="checkbox" name="is_billable" value="1" @checked((string) old('is_billable', '1') === '1')>
            <span>Include in billing</span>
        </label>
    </div>
</div>

<div class="msr-tenant-box" data-tenant-box>
    <h6><i class="fa-solid fa-users" style="color:#0f5fa8;margin-right:8px;"></i>Tenant / Lessee Information</h6>
    <div class="msr-form-grid">
        <div class="msr-form-field">
            <label for="{{ $prefix }}TenantFirstName"><i class="fa-regular fa-user" style="color:#0f5fa8;margin-right:6px;"></i>First Name</label>
            <input id="{{ $prefix }}TenantFirstName" name="tenant_first_name" type="text" class="msr-control" value="{{ old('tenant_first_name') }}" data-tenant-field>
        </div>
        <div class="msr-form-field">
            <label for="{{ $prefix }}TenantLastName"><i class="fa-regular fa-user" style="color:#0f5fa8;margin-right:6px;"></i>Last Name</label>
            <input id="{{ $prefix }}TenantLastName" name="tenant_last_name" type="text" class="msr-control" value="{{ old('tenant_last_name') }}" data-tenant-field>
        </div>
        <div class="msr-form-field">
            <label for="{{ $prefix }}TenantMiddleName"><i class="fa-regular fa-user" style="color:#0f5fa8;margin-right:6px;"></i>Middle Name</label>
            <input id="{{ $prefix }}TenantMiddleName" name="tenant_middle_name" type="text" class="msr-control" value="{{ old('tenant_middle_name') }}" data-tenant-field>
        </div>

        <div class="msr-form-field">
            <label for="{{ $prefix }}TenantAddress"><i class="fa-solid fa-house" style="color:#0f5fa8;margin-right:6px;"></i>Address</label>
            <input id="{{ $prefix }}TenantAddress" name="tenant_address" type="text" class="msr-control" value="{{ old('tenant_address') }}" data-tenant-field>
        </div>
        <div class="msr-form-field">
            <label for="{{ $prefix }}TenantContact"><i class="fa-solid fa-phone" style="color:#0f5fa8;margin-right:6px;"></i>Contact Number</label>
            <input id="{{ $prefix }}TenantContact" name="tenant_contact_number" type="text" class="msr-control" value="{{ old('tenant_contact_number') }}" data-tenant-field>
        </div>
        <div class="msr-form-field">
            <label for="{{ $prefix }}BusinessName"><i class="fa-solid fa-briefcase" style="color:#0f5fa8;margin-right:6px;"></i>Business Name</label>
            <input id="{{ $prefix }}BusinessName" name="business_name" type="text" class="msr-control" value="{{ old('business_name') }}" data-tenant-field>
        </div>

        <div class="msr-form-field">
            <label for="{{ $prefix }}BusinessType"><i class="fa-solid fa-tags" style="color:#0f5fa8;margin-right:6px;"></i>Business Type</label>
            <input id="{{ $prefix }}BusinessType" name="business_type" type="text" class="msr-control" value="{{ old('business_type') }}" data-tenant-field>
        </div>
        <div class="msr-form-field">
            <label for="{{ $prefix }}MpoControlNo"><i class="fa-solid fa-fingerprint" style="color:#0f5fa8;margin-right:6px;"></i>MPO Control No.</label>
            <input id="{{ $prefix }}MpoControlNo" name="mpo_control_no" type="text" class="msr-control" value="{{ old('mpo_control_no') }}" data-tenant-field>
        </div>
        <div class="msr-form-field msr-form-field--full">
            <label for="{{ $prefix }}LeaseRemarks"><i class="fa-solid fa-comment-dots" style="color:#0f5fa8;margin-right:6px;"></i>Lease Remarks</label>
            <textarea id="{{ $prefix }}LeaseRemarks" name="lease_remarks" class="msr-control msr-textarea" data-tenant-field>{{ old('lease_remarks') }}</textarea>
        </div>
    </div>
</div>

