@php
    $prefix = $prefix ?? 'new';
    $mode = $mode ?? 'create';
    $isEditMode = $mode === 'edit';
@endphp

<section class="vr-form-section">
    <h5><i class="fas fa-circle-dot"></i> 1. Vessel Information</h5>
    <div class="vr-form-grid">
        <label class="vr-form-field">
            <span>Vessel Name</span>
            <input id="{{ $prefix }}VesselName" name="name" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('name') }}" placeholder="e.g., MV San Jose I" required>
        </label>
        <label class="vr-form-field">
            <span>Vessel Type</span>
            <select id="{{ $prefix }}VesselType" name="vessel_type" class="vr-form-control" required>
                @foreach ($vesselTypes as $vesselType)
                    <option value="{{ $vesselType }}" @selected(old('vessel_type') === $vesselType)>{{ $vesselType }}</option>
                @endforeach
            </select>
        </label>
        <label class="vr-form-field">
            <span>Registration Number</span>
            <input id="{{ $prefix }}RegistrationNumber" name="registration_number" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('registration_number') }}" placeholder="e.g., REG-2026-00123" required>
        </label>

        <label class="vr-form-field">
            <span>Official Number</span>
            <input id="{{ $prefix }}OfficialNumber" name="official_number" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('official_number') }}" placeholder="e.g., OFC-77821" required>
        </label>
        <label class="vr-form-field">
            <span>Plate/Permit Number</span>
            <input id="{{ $prefix }}PlatePermitNumber" name="plate_permit_number" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('plate_permit_number') }}" placeholder="e.g., BP-2026-445" required>
        </label>
        <label class="vr-form-field">
            <span>Home Port</span>
            <input id="{{ $prefix }}HomePort" name="home_port" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('home_port') }}" placeholder="e.g., San Jose Fishport" required>
        </label>

        <label class="vr-form-field">
            <span>Gross Tonnage (GT)</span>
            <input id="{{ $prefix }}GrossTonnage" name="gross_tonnage" class="vr-form-control" type="number" min="0" step="0.01" value="{{ $isEditMode ? '' : old('gross_tonnage') }}" placeholder="e.g., 12.50" required>
        </label>
        <label class="vr-form-field">
            <span>Net Tonnage (NT)</span>
            <input id="{{ $prefix }}NetTonnage" name="net_tonnage" class="vr-form-control" type="number" min="0" step="0.01" value="{{ $isEditMode ? '' : old('net_tonnage') }}" placeholder="e.g., 9.75" required>
        </label>
        <label class="vr-form-field">
            <span>Length (m)</span>
            <input id="{{ $prefix }}VesselLength" name="vessel_length" class="vr-form-control" type="number" min="0" step="0.01" value="{{ $isEditMode ? '' : old('vessel_length') }}" placeholder="e.g., 14.20" required>
        </label>

        <label class="vr-form-field">
            <span>Width / Beam (m)</span>
            <input id="{{ $prefix }}BeamWidth" name="beam_width" class="vr-form-control" type="number" min="0" step="0.01" value="{{ $isEditMode ? '' : old('beam_width') }}" placeholder="e.g., 4.10" required>
        </label>
        <label class="vr-form-field">
            <span>Depth (m)</span>
            <input id="{{ $prefix }}VesselDepth" name="vessel_depth" class="vr-form-control" type="number" min="0" step="0.01" value="{{ $isEditMode ? '' : old('vessel_depth') }}" placeholder="e.g., 1.80" required>
        </label>
        <label class="vr-form-field">
            <span>Engine Type</span>
            <input id="{{ $prefix }}EngineType" name="engine_type" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('engine_type') }}" placeholder="e.g., Inboard Diesel" required>
        </label>

        <label class="vr-form-field">
            <span>Engine Horsepower (HP)</span>
            <input id="{{ $prefix }}EngineHorsepower" name="engine_horsepower" class="vr-form-control" type="number" min="0" step="0.01" value="{{ $isEditMode ? '' : old('engine_horsepower') }}" placeholder="e.g., 320" required>
        </label>
        <label class="vr-form-field">
            <span>Hull Material</span>
            <input id="{{ $prefix }}HullMaterial" name="hull_material" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('hull_material') }}" placeholder="e.g., Fiberglass" required>
        </label>
        <label class="vr-form-field">
            <span>Color / Markings</span>
            <input id="{{ $prefix }}ColorMarkings" name="color_markings" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('color_markings') }}" placeholder="e.g., White hull, Blue stripe" required>
        </label>

        <label class="vr-form-field">
            <span>Year Built</span>
            <input id="{{ $prefix }}YearBuilt" name="year_built" class="vr-form-control" type="number" min="1900" max="{{ date('Y') }}" step="1" value="{{ $isEditMode ? '' : old('year_built') }}" placeholder="e.g., 2021" required>
        </label>
    </div>
</section>

<section class="vr-form-section">
    <h5><i class="fas fa-circle-dot"></i> 2. Owner Information</h5>
    <div class="vr-form-grid">
        <label class="vr-form-field">
            <span>Owner Full Name</span>
            <input id="{{ $prefix }}VesselOwner" name="owner_name" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('owner_name') }}" placeholder="e.g., Juan Dela Cruz" required>
        </label>
        <label class="vr-form-field">
            <span>Owner Address</span>
            <input id="{{ $prefix }}OwnerAddress" name="owner_address" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('owner_address') }}" placeholder="e.g., Brgy. San Pedro, San Jose, Antique" required>
        </label>
        <label class="vr-form-field">
            <span>Contact Number</span>
            <input id="{{ $prefix }}OwnerContactNumber" name="owner_contact_number" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('owner_contact_number') }}" placeholder="e.g., 09171234567" required>
        </label>

        <label class="vr-form-field">
            <span>Email Address</span>
            <input id="{{ $prefix }}OwnerEmail" name="owner_email" class="vr-form-control" type="email" value="{{ $isEditMode ? '' : old('owner_email') }}" placeholder="e.g., owner@email.com" required>
        </label>
        <label class="vr-form-field">
            <span>Government ID Number</span>
            <input id="{{ $prefix }}OwnerGovernmentIdNumber" name="owner_government_id_number" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('owner_government_id_number') }}" placeholder="e.g., DLN-1234-56789" required>
        </label>
        <label class="vr-form-field">
            <span>Business Name</span>
            <input id="{{ $prefix }}BusinessName" name="business_name" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('business_name') }}" placeholder="e.g., Dela Cruz Fishing Ventures" required>
        </label>
    </div>
</section>

<section class="vr-form-section">
    <h5><i class="fas fa-circle-dot"></i> 3. Operator / Captain Information</h5>
    <div class="vr-form-grid">
        <label class="vr-form-field">
            <span>Captain / Operator Name</span>
            <input id="{{ $prefix }}CaptainOperatorName" name="captain_operator_name" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('captain_operator_name') }}" placeholder="e.g., Pedro Santos" required>
        </label>
        <label class="vr-form-field">
            <span>License Number</span>
            <input id="{{ $prefix }}CaptainLicenseNumber" name="captain_license_number" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('captain_license_number') }}" placeholder="e.g., CAP-LIC-88921" required>
        </label>
        <label class="vr-form-field">
            <span>Contact Number</span>
            <input id="{{ $prefix }}CaptainContactNumber" name="captain_contact_number" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('captain_contact_number') }}" placeholder="e.g., 09179876543" required>
        </label>

        <label class="vr-form-field vr-form-field--full">
            <span>Address</span>
            <input id="{{ $prefix }}CaptainAddress" name="captain_address" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('captain_address') }}" placeholder="e.g., Brgy. Funda, San Jose, Antique" required>
        </label>
    </div>
</section>

<section class="vr-form-section">
    <h5><i class="fas fa-circle-dot"></i> 4. Registration Details</h5>
    <div class="vr-form-grid">
        <label class="vr-form-field">
            <span>Date of Registration</span>
            <input id="{{ $prefix }}RegistrationDate" name="registration_date" class="vr-form-control" type="date" value="{{ $isEditMode ? '' : old('registration_date') }}" required>
        </label>
        <label class="vr-form-field">
            <span>Expiration Date</span>
            <input id="{{ $prefix }}ExpirationDate" name="expiration_date" class="vr-form-control" type="date" value="{{ $isEditMode ? '' : old('expiration_date') }}" required>
        </label>
        <label class="vr-form-field">
            <span>Registration Status</span>
            <select id="{{ $prefix }}RegistrationStatus" name="registration_status" class="vr-form-control" required>
                @foreach ($registrationStatuses as $registrationStatus)
                    <option value="{{ $registrationStatus }}" @selected(old('registration_status', 'Active') === $registrationStatus)>{{ $registrationStatus }}</option>
                @endforeach
            </select>
        </label>

        <label class="vr-form-field">
            <span>Renewal Date</span>
            <input id="{{ $prefix }}RenewalDate" name="renewal_date" class="vr-form-control" type="date" value="{{ $isEditMode ? '' : old('renewal_date') }}" required>
        </label>
        <label class="vr-form-field">
            <span>Issued By</span>
            <input id="{{ $prefix }}IssuedBy" name="issued_by" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : old('issued_by') }}" placeholder="e.g., Fishport Management Office" required>
        </label>
        <label class="vr-form-field">
            <span>Active / Inactive Status</span>
            <select id="{{ $prefix }}VesselStatus" name="is_active" class="vr-form-control" required>
                <option value="1" @selected(old('is_active', '1') === '1')>Active</option>
                <option value="0" @selected(old('is_active') === '0')>Inactive</option>
            </select>
        </label>

        <label class="vr-form-field vr-form-field--full">
            <span>Remarks</span>
            <textarea id="{{ $prefix }}Remarks" name="remarks" class="vr-form-control" rows="3" placeholder="Enter vessel notes, renewal remarks, or compliance details." required>{{ $isEditMode ? '' : old('remarks') }}</textarea>
        </label>
    </div>
</section>

<section class="vr-form-section">
    <h5><i class="fas fa-circle-dot"></i> 5. Supporting Documents</h5>
    <p class="vr-form-note">Accepted file types: PDF, JPG, JPEG, PNG. Maximum size: 5MB each. You can browse files or capture from camera.</p>
    <div class="vr-form-grid vr-doc-grid">
        <label class="vr-form-field vr-doc-field">
            <span>Certificate of Ownership</span>
            <input id="{{ $prefix }}CertificateOfOwnershipFile" name="certificate_of_ownership_file" class="vr-form-control vr-doc-input js-vr-doc-input" type="file" accept=".pdf,.jpg,.jpeg,.png,image/*" data-doc-label="Certificate of Ownership" @if (! $isEditMode) required @endif>
            <div class="vr-doc-actions">
                <button type="button" class="vr-doc-btn js-vr-open-file" data-target-input="{{ $prefix }}CertificateOfOwnershipFile"><i class="fa-regular fa-folder-open"></i>Browse File</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-camera js-vr-open-camera" data-target-input="{{ $prefix }}CertificateOfOwnershipFile"><i class="fa-solid fa-camera"></i>Use Camera</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-preview js-vr-preview-file" data-target-input="{{ $prefix }}CertificateOfOwnershipFile"><i class="fa-regular fa-eye"></i>View File</button>
            </div>
            <div class="vr-doc-meta"><span class="js-vr-doc-name" data-target-input="{{ $prefix }}CertificateOfOwnershipFile">No file selected</span></div>
        </label>
        <label class="vr-form-field vr-doc-field">
            <span>Previous Registration</span>
            <input id="{{ $prefix }}PreviousRegistrationFile" name="previous_registration_file" class="vr-form-control vr-doc-input js-vr-doc-input" type="file" accept=".pdf,.jpg,.jpeg,.png,image/*" data-doc-label="Previous Registration" @if (! $isEditMode) required @endif>
            <div class="vr-doc-actions">
                <button type="button" class="vr-doc-btn js-vr-open-file" data-target-input="{{ $prefix }}PreviousRegistrationFile"><i class="fa-regular fa-folder-open"></i>Browse File</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-camera js-vr-open-camera" data-target-input="{{ $prefix }}PreviousRegistrationFile"><i class="fa-solid fa-camera"></i>Use Camera</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-preview js-vr-preview-file" data-target-input="{{ $prefix }}PreviousRegistrationFile"><i class="fa-regular fa-eye"></i>View File</button>
            </div>
            <div class="vr-doc-meta"><span class="js-vr-doc-name" data-target-input="{{ $prefix }}PreviousRegistrationFile">No file selected</span></div>
        </label>
        <label class="vr-form-field vr-doc-field">
            <span>Boat Permit / License</span>
            <input id="{{ $prefix }}BoatPermitLicenseFile" name="boat_permit_license_file" class="vr-form-control vr-doc-input js-vr-doc-input" type="file" accept=".pdf,.jpg,.jpeg,.png,image/*" data-doc-label="Boat Permit / License" @if (! $isEditMode) required @endif>
            <div class="vr-doc-actions">
                <button type="button" class="vr-doc-btn js-vr-open-file" data-target-input="{{ $prefix }}BoatPermitLicenseFile"><i class="fa-regular fa-folder-open"></i>Browse File</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-camera js-vr-open-camera" data-target-input="{{ $prefix }}BoatPermitLicenseFile"><i class="fa-solid fa-camera"></i>Use Camera</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-preview js-vr-preview-file" data-target-input="{{ $prefix }}BoatPermitLicenseFile"><i class="fa-regular fa-eye"></i>View File</button>
            </div>
            <div class="vr-doc-meta"><span class="js-vr-doc-name" data-target-input="{{ $prefix }}BoatPermitLicenseFile">No file selected</span></div>
        </label>

        <label class="vr-form-field vr-doc-field">
            <span>Engine Receipt / Proof</span>
            <input id="{{ $prefix }}EngineReceiptProofFile" name="engine_receipt_proof_file" class="vr-form-control vr-doc-input js-vr-doc-input" type="file" accept=".pdf,.jpg,.jpeg,.png,image/*" data-doc-label="Engine Receipt / Proof" @if (! $isEditMode) required @endif>
            <div class="vr-doc-actions">
                <button type="button" class="vr-doc-btn js-vr-open-file" data-target-input="{{ $prefix }}EngineReceiptProofFile"><i class="fa-regular fa-folder-open"></i>Browse File</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-camera js-vr-open-camera" data-target-input="{{ $prefix }}EngineReceiptProofFile"><i class="fa-solid fa-camera"></i>Use Camera</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-preview js-vr-preview-file" data-target-input="{{ $prefix }}EngineReceiptProofFile"><i class="fa-regular fa-eye"></i>View File</button>
            </div>
            <div class="vr-doc-meta"><span class="js-vr-doc-name" data-target-input="{{ $prefix }}EngineReceiptProofFile">No file selected</span></div>
        </label>
        <label class="vr-form-field vr-doc-field">
            <span>Valid ID</span>
            <input id="{{ $prefix }}ValidIdFile" name="valid_id_file" class="vr-form-control vr-doc-input js-vr-doc-input" type="file" accept=".pdf,.jpg,.jpeg,.png,image/*" data-doc-label="Valid ID" @if (! $isEditMode) required @endif>
            <div class="vr-doc-actions">
                <button type="button" class="vr-doc-btn js-vr-open-file" data-target-input="{{ $prefix }}ValidIdFile"><i class="fa-regular fa-folder-open"></i>Browse File</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-camera js-vr-open-camera" data-target-input="{{ $prefix }}ValidIdFile"><i class="fa-solid fa-camera"></i>Use Camera</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-preview js-vr-preview-file" data-target-input="{{ $prefix }}ValidIdFile"><i class="fa-regular fa-eye"></i>View File</button>
            </div>
            <div class="vr-doc-meta"><span class="js-vr-doc-name" data-target-input="{{ $prefix }}ValidIdFile">No file selected</span></div>
        </label>
        <label class="vr-form-field vr-doc-field">
            <span>Inspection Certificate</span>
            <input id="{{ $prefix }}InspectionCertificateFile" name="inspection_certificate_file" class="vr-form-control vr-doc-input js-vr-doc-input" type="file" accept=".pdf,.jpg,.jpeg,.png,image/*" data-doc-label="Inspection Certificate" @if (! $isEditMode) required @endif>
            <div class="vr-doc-actions">
                <button type="button" class="vr-doc-btn js-vr-open-file" data-target-input="{{ $prefix }}InspectionCertificateFile"><i class="fa-regular fa-folder-open"></i>Browse File</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-camera js-vr-open-camera" data-target-input="{{ $prefix }}InspectionCertificateFile"><i class="fa-solid fa-camera"></i>Use Camera</button>
                <button type="button" class="vr-doc-btn vr-doc-btn-preview js-vr-preview-file" data-target-input="{{ $prefix }}InspectionCertificateFile"><i class="fa-regular fa-eye"></i>View File</button>
            </div>
            <div class="vr-doc-meta"><span class="js-vr-doc-name" data-target-input="{{ $prefix }}InspectionCertificateFile">No file selected</span></div>
        </label>
    </div>
</section>

<section class="vr-form-section">
    <h5><i class="fas fa-circle-dot"></i> 6. System Fields</h5>
    <div class="vr-form-grid">
        <label class="vr-form-field">
            <span>Created By</span>
            <input id="{{ $prefix }}CreatedBy" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : auth()->user()?->name }}" readonly>
        </label>
        <label class="vr-form-field">
            <span>Updated By</span>
            <input id="{{ $prefix }}UpdatedBy" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : auth()->user()?->name }}" readonly>
        </label>
        <label class="vr-form-field">
            <span>Date Created</span>
            <input id="{{ $prefix }}DateCreated" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : now()->format('Y-m-d H:i') }}" readonly>
        </label>

        <label class="vr-form-field">
            <span>Date Updated</span>
            <input id="{{ $prefix }}DateUpdated" class="vr-form-control" type="text" value="{{ $isEditMode ? '' : now()->format('Y-m-d H:i') }}" readonly>
        </label>
        <label class="vr-form-field vr-form-field--full">
            <span>Supporting Documents Uploaded</span>
            <input id="{{ $prefix }}SupportingDocumentsUploaded" class="vr-form-control" type="text" value="{{ $isEditMode ? 'Will auto-update based on uploaded files.' : 'Will be computed when you save this vessel.' }}" readonly>
        </label>
    </div>
</section>
