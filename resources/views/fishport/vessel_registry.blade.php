@extends('layouts.app')

@section('content')
<style>
    #contentArea {
        padding-top: 16px;
    }

    .vr-page { display:grid; gap:12px; font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #334155; }
    .vr-hero-btn { border:1px solid #155f8f; background:#155f8f; color:#fff; border-radius:10px; min-height:40px; padding:0 .95rem; font-size: 0.92rem; font-weight:700; display:inline-flex; align-items:center; gap:8px; text-decoration: none; transition: all 0.2s; }
    .vr-hero-btn:hover { background:#0f4b73; border-color:#0f4b73; }
    .vr-card { border:1px solid #e2e8f0; border-radius:12px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.05); overflow:hidden; }
    .vr-head { border-bottom:1px solid #e2e8f0; background: #fff; padding:1rem 1.2rem; display:grid; gap:10px; align-items:center; }
    .vr-head-main { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .vr-head h3 { margin:0; font-size:1.25rem; font-weight: 700; color: #0f172a; }
    .vr-head p { margin:.25rem 0 0; color:#64748b; font-size:.9rem; }
    .vr-search-wrap { position:relative; display: flex; align-items: center; min-width: 320px; }
    .vr-search-wrap i { position:absolute; left:12px; color:#94a3b8; pointer-events:none; font-size: 0.9em; }
    .vr-search { width:100%; min-height:40px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; padding:0.5rem 0.75rem 0.5rem 2.25rem; font-size:.92rem; color: #334155; transition: border-color 0.2s, box-shadow 0.2s; }
    .vr-search:focus { border-color: #155f8f; box-shadow: 0 0 0 3px rgba(21, 95, 143, 0.15); outline: none; }
    .vr-table-wrap { overflow:auto; }
    .vr-table { width:100%; border-collapse:collapse; }
    .vr-table th { background:#eef5fb; color:#103250; border-bottom:1px solid #e2e8f0; font-size:.74rem; text-transform:uppercase; letter-spacing:.03em; font-weight:700; padding:.82rem 1rem; text-align: left; white-space: nowrap; }
    .vr-table td { padding:.74rem 1rem; border-bottom:1px solid #f1f5f9; color:#334155; font-size:.86rem; vertical-align: middle; line-height:1.32; }
    .vr-table td:first-child, .vr-table td:last-child { white-space: nowrap; }
    .vr-table tbody tr:nth-child(even) { background: #fdfdfe; }
    .vr-table tbody tr:hover { background: #f1f5f9; }
    .vr-badge { border-radius:999px; padding:.2rem .56rem; font-size:.7rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; }
    .vr-badge-active { background:#ecfdf5; color:#047857; border: 1px solid #a7f3d0; }
    .vr-badge-inactive { background:#fef2f2; color:#b91c1c; border: 1px solid #fecaca; }
    .vr-actions { display:inline-flex; gap:5px; flex-wrap: nowrap; }
    .vr-icon-btn { width:30px; height:30px; border-radius:8px; border:1px solid #e2e8f0; background:transparent; color:#475569; display:inline-flex; align-items:center; justify-content:center; cursor: pointer; transition: all 0.2s; flex-shrink: 0; }
    .vr-icon-btn:hover { background:#f1f5f9; color:#155f8f; border-color: #cbd5e1; }
    .vr-icon-btn-danger:hover { background:#fee2e2; border-color:#fca5a5; color:#dc2626; }
    .vr-pagination { border-top:1px solid #e2e8f0; background: #f8fafc; padding:12px 16px; display:flex; justify-content:flex-end; gap:8px; align-items: center; }
    .vr-page-link { min-height:34px; padding:0 12px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; color:#155f8f; font-size: 0.85rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; justify-content: center; transition: background 0.2s; }
    .vr-page-link:hover { background: #f1f5f9; }
    .vr-page-link.is-disabled { background:#f8fafc; color:#94a3b8; border-color: #e2e8f0; pointer-events:none; }
    .vr-modal { position:fixed; inset:0; z-index:1650; display:none; align-items:center; justify-content:center; background:rgba(15,23,42,.56); backdrop-filter: blur(3px); padding:16px; }
    .vr-modal.is-open { display:flex; }
    .vr-modal-card { width:min(1060px,97vw); max-height:92vh; border-radius:12px; border:1px solid #e2e8f0; background:#fff; overflow:hidden; display:grid; grid-template-rows:auto minmax(0,1fr); box-shadow: 0 20px 25px -5px rgba(0,0,0,.1), 0 10px 10px -5px rgba(0,0,0,.04); }
    .vr-modal-card--compact { width:min(460px,96vw); }
    .vr-modal-head { background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:14px 16px; display:flex; justify-content:space-between; align-items:center; }
    .vr-modal-head h4 { margin:0; color:#0f172a; font-size:1.1rem; font-weight:700; }
    .vr-modal-close { width:32px; height:32px; border-radius:8px; border:none; background:transparent; color:#64748b; font-size: 1.1rem; display: flex; align-items:center; justify-content:center; cursor: pointer; transition: all 0.2s; }
    .vr-modal-close:hover { background: #e2e8f0; color: #0f172a; }
    .vr-modal form { display:grid; grid-template-rows:minmax(0,1fr) auto; min-height:0; }
    .vr-modal-body { padding:16px 20px; overflow-y:auto; min-height:0; display:grid; gap:16px; background: #fff; }
    .vr-foot { border-top:1px solid #e2e8f0; background:#f8fafc; padding:12px 16px; display:flex; justify-content:flex-end; gap:10px; }
    .vr-preview { border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; padding:12px 14px; color:#334155; font-size:.9rem; }
    .vr-form-section { border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc; padding:16px; display:grid; gap:14px; }
    .vr-form-section h5 { margin:0; color:#0f172a; font-size:1.05rem; font-weight:700; display:flex; align-items:center; gap:8px; }
    .vr-form-note { margin:0; color:#64748b; font-size:.85rem; }
    .vr-form-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px 16px; }
    .vr-form-field { display:grid; gap:6px; }
    .vr-form-field span { color:#334155; font-size:.85rem; font-weight:600; }
    .vr-form-control { width:100%; min-height:40px; border:1px solid #cbd5e1; background:#fff; border-radius:8px; padding:.5rem .75rem; color:#0f172a; font-size:.92rem; outline:none; transition: all 0.2s; }
    .vr-form-control:focus { border-color:#155f8f; box-shadow:0 0 0 3px rgba(21,95,143,.15); }
    .vr-form-field--full { grid-column:1 / -1; }
    .vr-doc-grid { gap:14px 16px; }
    .vr-doc-field { border:1px solid #dbe7f3; background:#fff; border-radius:10px; padding:10px; gap:8px; }
    .vr-doc-field > span { display:flex; align-items:center; gap:8px; font-size:.92rem; color:#103250; }
    .vr-doc-field > span::before { content:''; width:8px; height:8px; border-radius:999px; background:#1d78b0; box-shadow:0 0 0 3px #e0f2fe; }
    .vr-doc-input { position:absolute !important; width:1px !important; height:1px !important; opacity:0 !important; pointer-events:none !important; }
    .vr-doc-actions { display:flex; flex-wrap:wrap; gap:8px; }
    .vr-doc-btn { min-height:34px; border-radius:8px; border:1px solid #c7d2e0; background:#f8fafc; color:#0f172a; font-size:.82rem; font-weight:700; display:inline-flex; align-items:center; gap:7px; padding:0 .78rem; transition:all .2s; }
    .vr-doc-btn:hover { border-color:#93c5fd; background:#eff6ff; color:#0f4b73; }
    .vr-doc-btn-camera { border-color:#bae6fd; background:#ecfeff; color:#075985; }
    .vr-doc-btn-camera:hover { border-color:#7dd3fc; background:#dff7ff; color:#0c4a6e; }
    .vr-doc-btn-phone { border-color:#c7d2fe; background:#eef2ff; color:#3730a3; }
    .vr-doc-btn-phone:hover { border-color:#a5b4fc; background:#e0e7ff; color:#312e81; }
    .vr-doc-btn-preview { border-color:#ddd6fe; background:#f5f3ff; color:#5b21b6; }
    .vr-doc-btn-preview:hover { border-color:#c4b5fd; background:#ede9fe; color:#4c1d95; }
    .vr-doc-meta { min-height:22px; border:1px dashed #cbd5e1; border-radius:8px; background:#f8fafc; padding:4px 8px; display:flex; align-items:center; }
    .vr-doc-meta span { color:#475569; font-size:.77rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .vr-camera-wrap { display:grid; gap:10px; }
    .vr-camera-top { display:flex; flex-wrap:wrap; gap:10px; align-items:end; }
    .vr-camera-field { display:grid; gap:6px; min-width:min(320px,100%); }
    .vr-camera-field label { font-size:.83rem; font-weight:700; color:#334155; }
    .vr-camera-video-wrap { position:relative; border:1px solid #cbd5e1; border-radius:10px; overflow:hidden; background:#0f172a; min-height:320px; }
    .vr-camera-video { width:100%; height:100%; min-height:320px; object-fit:cover; display:block; }
    .vr-camera-empty { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#cbd5e1; font-size:.9rem; padding:12px; text-align:center; }
    .vr-camera-note { margin:0; color:#64748b; font-size:.8rem; }
    .vr-camera-status { margin:0; font-size:.82rem; font-weight:600; color:#0f4b73; min-height:20px; }
    .vr-phone-wrap { display:grid; gap:12px; }
    .vr-phone-grid { display:grid; grid-template-columns:240px minmax(0,1fr); gap:14px; align-items:start; }
    .vr-qr-box { width:240px; height:240px; border:1px solid #dbe7f3; border-radius:10px; background:#fff; display:grid; place-items:center; padding:10px; }
    .vr-qr-placeholder { color:#64748b; font-size:.82rem; text-align:center; padding:8px; }
    .vr-phone-meta { display:grid; gap:10px; }
    .vr-phone-label { margin:0; padding:10px 12px; border:1px solid #dbe7f3; border-radius:10px; background:#f8fafc; font-size:.86rem; color:#103250; font-weight:700; }
    .vr-phone-link-wrap { display:grid; gap:6px; }
    .vr-phone-link-wrap label { font-size:.79rem; color:#475569; font-weight:700; }
    .vr-phone-link-row { display:flex; gap:8px; }
    .vr-phone-link-input { flex:1; min-height:38px; border:1px solid #cbd5e1; border-radius:8px; padding:0 .68rem; font-size:.8rem; color:#0f172a; background:#fff; }
    .vr-phone-host-help { margin:0; color:#64748b; font-size:.75rem; line-height:1.35; }
    .vr-phone-status { margin:0; min-height:20px; font-size:.82rem; font-weight:600; color:#0f4b73; }
    .vr-phone-note { margin:0; color:#64748b; font-size:.8rem; line-height:1.45; }
    @media (max-width:760px){
        .vr-phone-grid { grid-template-columns:1fr; }
        .vr-qr-box { width:100%; max-width:240px; margin:0 auto; }
    }
    .vr-file-preview-wrap { min-height:420px; border:1px solid #dbe7f3; border-radius:10px; background:#f8fafc; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .vr-file-preview-image { width:100%; max-height:70vh; object-fit:contain; display:block; }
    .vr-file-preview-pdf { width:100%; height:70vh; border:none; background:#fff; }
    .vr-file-preview-empty { color:#64748b; font-size:.9rem; text-align:center; padding:12px; }
    .vr-file-preview-note { margin:0; color:#64748b; font-size:.82rem; }
    body.vr-lock-scroll { overflow:hidden; }
    .vr-status-toast { position:fixed; top:18px; right:18px; z-index:1700; min-width:min(460px,calc(100vw - 36px)); border-radius:12px; border:1px solid transparent; padding:12px 14px; box-shadow:0 14px 24px rgba(15,23,42,.18); display:flex; align-items:center; gap:10px; font-size:.92rem; transform:translateY(0); opacity:1; transition:opacity .22s ease, transform .22s ease; }
    .vr-status-toast i { font-size:1rem; }
    .vr-status-toast.is-success { background:#ecfdf5; border-color:#86efac; color:#065f46; }
    .vr-status-toast.is-error { background:#fff1f2; border-color:#fecdd3; color:#9f1239; }
    .vr-status-toast.is-hiding { opacity:0; transform:translateY(-10px); }
    @media (max-width:920px){ .vr-form-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .vr-search-wrap{ min-width: 100%; } }
    @media (max-width:640px){ .vr-form-grid { grid-template-columns:1fr; } }
</style>

<div class="vr-page" data-server-rendered-page="vessel_registry" data-page-title="Vessel Registry">
    @if (session('status'))
        <div id="vrStatusToast" class="vr-status-toast is-success" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('status') }}</span>
        </div>
    @elseif ($errors->any())
        <div id="vrStatusToast" class="vr-status-toast is-error" role="alert" aria-live="assertive">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <section class="vr-card" id="vesselRegistryCard">
        <div class="vr-head">
            <div class="vr-head-main">
                <div>
                    <h3>Master Vessel Registry</h3>
                </div>
                <button type="button" id="openRegisterVesselBtn" class="vr-hero-btn"><i class="fas fa-plus"></i> Register Vessel</button>
            </div>
            <form id="vesselSearchForm" method="GET" action="{{ route('fishport.vessel_registry') }}">
                <div class="vr-search-wrap">
                    <i class="fas fa-search"></i>
                    <input id="vesselSearchInput" class="vr-search" type="search" name="search" value="{{ $search }}" placeholder="Search vessel name, registration, owner">
                </div>
            </form>
        </div>
        <div class="vr-table-wrap">
            <table class="vr-table">
                <thead>
                    <tr>
                        <th>Registry ID</th>
                        <th>Vessel Name</th>
                        <th>Registration No.</th>
                        <th>Owner / Operator</th>
                        <th>Classification</th>
                        <th>Home Port</th>
                        <th>Date Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vessels as $vessel)
                        @php
                            $owner = $vessel->ownerProfile;
                            $operator = $vessel->operatorProfile;
                            $registration = $vessel->registrationProfile;
                            $documents = $vessel->documentProfile;
                        @endphp
                        <tr>
                            <td><strong>#VR-{{ str_pad((string) $vessel->id, 3, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>{{ $vessel->name }}</td>
                            <td>{{ $registration?->registration_number ?: '-' }}</td>
                            <td>{{ $owner?->full_name ?: '-' }}</td>
                            <td>{{ $vessel->vessel_type ?: '-' }}</td>
                            <td>{{ $registration?->home_port ?: '-' }}</td>
                            <td>{{ optional($vessel->created_at)->format('Y-m-d') }}</td>
                            <td><span class="vr-badge {{ $vessel->is_active ? 'vr-badge-active' : 'vr-badge-inactive' }}">{{ $vessel->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="vr-actions">
                                    <button type="button" class="vr-icon-btn js-open-edit-vessel-btn"
                                        data-vessel-id="{{ $vessel->id }}" data-vessel-name="{{ $vessel->name }}" data-vessel-type="{{ $vessel->vessel_type }}"
                                        data-owner-name="{{ $owner?->full_name }}" data-owner-address="{{ $owner?->address }}" data-owner-contact-number="{{ $owner?->contact_number }}" data-owner-email="{{ $owner?->email }}"
                                        data-owner-government-id-number="{{ $owner?->government_id_number }}" data-business-name="{{ $owner?->business_name }}"
                                        data-captain-operator-name="{{ $operator?->name }}" data-captain-license-number="{{ $operator?->license_number }}" data-captain-contact-number="{{ $operator?->contact_number }}" data-captain-address="{{ $operator?->address }}"
                                        data-registration-number="{{ $registration?->registration_number }}" data-official-number="{{ $registration?->official_number }}" data-plate-permit-number="{{ $registration?->plate_permit_number }}"
                                        data-home-port="{{ $registration?->home_port }}" data-gross-tonnage="{{ $registration?->gross_tonnage }}" data-net-tonnage="{{ $registration?->net_tonnage }}" data-vessel-length="{{ $registration?->vessel_length }}"
                                        data-beam-width="{{ $registration?->beam_width }}" data-vessel-depth="{{ $registration?->vessel_depth }}" data-engine-type="{{ $registration?->engine_type }}" data-engine-horsepower="{{ $registration?->engine_horsepower }}"
                                        data-hull-material="{{ $registration?->hull_material }}" data-color-markings="{{ $registration?->color_markings }}" data-year-built="{{ $registration?->year_built }}"
                                        data-registration-date="{{ optional($registration?->registration_date)->format('Y-m-d') }}" data-expiration-date="{{ optional($registration?->expiration_date)->format('Y-m-d') }}" data-registration-status="{{ $registration?->registration_status }}"
                                        data-renewal-date="{{ optional($registration?->renewal_date)->format('Y-m-d') }}" data-issued-by="{{ $registration?->issued_by }}" data-remarks="{{ $registration?->remarks }}"
                                        data-created-by="{{ $registration?->creator?->name }}" data-updated-by="{{ $registration?->updater?->name }}"
                                        data-date-created="{{ optional($registration?->created_at)->format('Y-m-d H:i') }}" data-date-updated="{{ optional($registration?->updated_at)->format('Y-m-d H:i') }}"
                                        data-supporting-documents-uploaded="{{ $registration?->supporting_documents_uploaded ? 'Yes' : 'No' }}"
                                        data-is-active="{{ $vessel->is_active ? '1' : '0' }}"
                                        data-certificate-uploaded="{{ $documents?->certificate_of_ownership_path ? '1' : '0' }}" data-previous-registration-uploaded="{{ $documents?->previous_registration_path ? '1' : '0' }}"
                                        data-boat-permit-uploaded="{{ $documents?->boat_permit_license_path ? '1' : '0' }}" data-engine-receipt-uploaded="{{ $documents?->engine_receipt_proof_path ? '1' : '0' }}"
                                        data-valid-id-uploaded="{{ $documents?->valid_id_path ? '1' : '0' }}" data-inspection-uploaded="{{ $documents?->inspection_certificate_path ? '1' : '0' }}"
                                        title="Edit vessel"><i class="fas fa-pen"></i></button>

                                    <form action="{{ route('fishport.vessel_registry.toggle_active', $vessel) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="vr-icon-btn" type="submit" title="{{ $vessel->is_active ? 'Set inactive' : 'Set active' }}"><i class="fas {{ $vessel->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                                    </form>

                                    <form action="{{ route('fishport.vessel_registry.destroy', $vessel) }}" method="POST" class="js-delete-vessel-form" data-vessel-name="{{ $vessel->name }}" data-owner-name="{{ $owner?->full_name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="vr-icon-btn vr-icon-btn-danger" type="submit" title="Delete vessel permanently"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center; padding:1.6rem;">No vessel records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($vessels->hasPages())
            <div class="vr-pagination">
                @if ($vessels->previousPageUrl()) <a class="vr-page-link" href="{{ $vessels->previousPageUrl() }}">Previous</a> @else <span class="vr-page-link is-disabled">Previous</span> @endif
                @if ($vessels->nextPageUrl()) <a class="vr-page-link" href="{{ $vessels->nextPageUrl() }}">Next</a> @else <span class="vr-page-link is-disabled">Next</span> @endif
            </div>
        @endif
    </section>
</div>

<div id="registerVesselModal" class="vr-modal" aria-hidden="true">
    <div class="vr-modal-card">
        <div class="vr-modal-head"><h4>Register Vessel</h4><button type="button" class="vr-modal-close" data-close-modal="registerVesselModal"><i class="fas fa-xmark"></i></button></div>
        <form method="POST" action="{{ route('fishport.vessel_registry.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="vr-modal-body">@include('fishport.partials.vessel_registry_form_fields', ['prefix' => 'new', 'mode' => 'create', 'vesselTypes' => $vesselTypes, 'registrationStatuses' => $registrationStatuses])</div>
            <div class="vr-foot"><button type="button" class="btn btn-secondary" id="closeRegisterVesselBtn">Cancel</button><button class="btn btn-primary" type="submit">Save Vessel</button></div>
        </form>
    </div>
</div>

<div id="editVesselModal" class="vr-modal" aria-hidden="true">
    <div class="vr-modal-card">
        <div class="vr-modal-head"><h4>Edit Vessel</h4><button type="button" class="vr-modal-close" data-close-modal="editVesselModal"><i class="fas fa-xmark"></i></button></div>
        <form id="editVesselForm" method="POST" action="" data-action-template="{{ route('fishport.vessel_registry.update', '__VESSEL_ID__') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="vr-modal-body">@include('fishport.partials.vessel_registry_form_fields', ['prefix' => 'edit', 'mode' => 'edit', 'vesselTypes' => $vesselTypes, 'registrationStatuses' => $registrationStatuses])</div>
            <div class="vr-foot"><button type="button" class="btn btn-secondary" id="closeEditVesselBtn">Cancel</button><button class="btn btn-primary" type="submit">Update Vessel</button></div>
        </form>
    </div>
</div>

<div id="vrCameraModal" class="vr-modal" aria-hidden="true">
    <div class="vr-modal-card vr-modal-card--compact" style="width:min(760px,96vw);">
        <div class="vr-modal-head">
            <h4>Capture Document From Camera</h4>
            <button type="button" class="vr-modal-close" data-close-modal="vrCameraModal"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="vr-modal-body">
            <div class="vr-camera-wrap">
                <div class="vr-camera-top">
                    <div class="vr-camera-field">
                        <label for="vrCameraDeviceSelect">Camera Source</label>
                        <select id="vrCameraDeviceSelect" class="vr-form-control"></select>
                    </div>
                    <button type="button" class="vr-doc-btn" id="vrRefreshCameraSourcesBtn"><i class="fa-solid fa-rotate"></i>Refresh Cameras</button>
                </div>
                <div class="vr-camera-video-wrap">
                    <video id="vrCameraVideo" class="vr-camera-video" autoplay playsinline muted></video>
                    <div class="vr-camera-empty" id="vrCameraEmptyState">Enable camera access to capture this document.</div>
                </div>
                <p class="vr-camera-note">Tip: To use phone camera on PC, your phone must be exposed as a webcam device (e.g., Android USB Webcam mode, DroidCam/Iriun/Link to Windows). After connecting, click Refresh Cameras and select it above.</p>
                <p class="vr-camera-status" id="vrCameraStatus"></p>
            </div>
        </div>
        <div class="vr-foot">
            <button type="button" class="btn btn-secondary" id="vrCancelCameraBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="vrCaptureCameraBtn"><i class="fa-solid fa-camera"></i>Capture & Attach</button>
        </div>
    </div>
</div>

<div id="vrFilePreviewModal" class="vr-modal" aria-hidden="true">
    <div class="vr-modal-card" style="width:min(980px,97vw);">
        <div class="vr-modal-head">
            <h4 id="vrFilePreviewTitle">File Preview</h4>
            <button type="button" class="vr-modal-close" data-close-modal="vrFilePreviewModal"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="vr-modal-body">
            <div class="vr-file-preview-wrap" id="vrFilePreviewContainer">
                <p class="vr-file-preview-empty">No file selected yet.</p>
            </div>
            <p class="vr-file-preview-note" id="vrFilePreviewNote">Tip: Select or capture a document first, then click View File.</p>
        </div>
        <div class="vr-foot">
            <button type="button" class="btn btn-secondary" id="vrCloseFilePreviewBtn">Close</button>
        </div>
    </div>
</div>

<div id="vrPhoneUploadModal" class="vr-modal" aria-hidden="true">
    <div class="vr-modal-card" style="width:min(860px,97vw);">
        <div class="vr-modal-head">
            <h4>Phone Upload via QR</h4>
            <button type="button" class="vr-modal-close" data-close-modal="vrPhoneUploadModal"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="vr-modal-body">
            <div class="vr-phone-wrap">
                <div class="vr-phone-grid">
                    <div class="vr-qr-box" id="vrPhoneQrBox">
                        <p class="vr-qr-placeholder">Generate QR to start phone upload.</p>
                    </div>
                    <div class="vr-phone-meta">
                        <p class="vr-phone-label" id="vrPhoneUploadDocLabel">Document: -</p>
                        <div class="vr-phone-link-wrap">
                            <label for="vrPhoneReachableHostInput">Phone Reachable Host (Optional)</label>
                            <div class="vr-phone-link-row">
                                <input id="vrPhoneReachableHostInput" class="vr-phone-link-input" type="text" placeholder="http://192.168.1.10:8000" value="">
                            </div>
                            <p class="vr-phone-host-help" id="vrPhoneHostHelp">Use your computer LAN address if this page is running on localhost.</p>
                        </div>
                        <div class="vr-phone-link-wrap">
                            <label for="vrPhoneUploadLinkInput">Upload Link</label>
                            <div class="vr-phone-link-row">
                                <input id="vrPhoneUploadLinkInput" class="vr-phone-link-input" type="text" readonly value="">
                                <button type="button" class="vr-doc-btn" id="vrCopyPhoneUploadLinkBtn"><i class="fa-regular fa-copy"></i>Copy</button>
                            </div>
                        </div>
                        <p class="vr-phone-status" id="vrPhoneUploadStatus">Preparing secure upload session...</p>
                        <p class="vr-phone-note">Scan with your phone, upload/capture document, then keep this window open. The file auto-attaches once upload is complete.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="vr-foot">
            <button type="button" class="btn btn-secondary" id="vrClosePhoneUploadBtn">Close</button>
        </div>
    </div>
</div>

<div id="deleteVesselModal" class="vr-modal" aria-hidden="true">
    <div class="vr-modal-card vr-modal-card--compact">
        <div class="vr-modal-head"><h4>Delete Vessel</h4><button type="button" class="vr-modal-close" data-close-modal="deleteVesselModal"><i class="fas fa-xmark"></i></button></div>
        <div class="vr-modal-body"><p style="margin:0;">Are you sure you want to permanently delete this vessel and its linked records?</p><div class="vr-preview"><div><strong id="deleteVesselName">-</strong></div><div>Owner: <span id="deleteVesselOwner">-</span></div></div></div>
        <div class="vr-foot"><button type="button" class="btn btn-secondary" id="cancelDeleteVesselBtn">Cancel</button><button type="button" class="btn btn-danger" id="confirmDeleteVesselBtn">Yes, Delete</button></div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(() => {
    const breadcrumb = document.querySelector('.breadcrumb');
    if (breadcrumb) breadcrumb.hidden = false;
    const pageTitle = document.getElementById('pageTitle');
    if (pageTitle) pageTitle.textContent = 'Master Vessel Registry';

    const statusToast = document.getElementById('vrStatusToast');
    const registerModal = document.getElementById('registerVesselModal');
    const editModal = document.getElementById('editVesselModal');
    const cameraModal = document.getElementById('vrCameraModal');
    const filePreviewModal = document.getElementById('vrFilePreviewModal');
    const phoneUploadModal = document.getElementById('vrPhoneUploadModal');
    const deleteModal = document.getElementById('deleteVesselModal');
    const allModals = [registerModal, editModal, cameraModal, filePreviewModal, phoneUploadModal, deleteModal].filter(Boolean);
    const openRegisterButton = document.getElementById('openRegisterVesselBtn');
    const closeRegisterButton = document.getElementById('closeRegisterVesselBtn');
    const closeEditButton = document.getElementById('closeEditVesselBtn');
    const modalCloseButtons = Array.from(document.querySelectorAll('.vr-modal-close[data-close-modal]'));
    const editVesselForm = document.getElementById('editVesselForm');
    const editActionTemplate = editVesselForm ? (editVesselForm.dataset.actionTemplate || '') : '';
    const deleteVesselName = document.getElementById('deleteVesselName');
    const deleteVesselOwner = document.getElementById('deleteVesselOwner');
    const cancelDeleteButton = document.getElementById('cancelDeleteVesselBtn');
    const confirmDeleteButton = document.getElementById('confirmDeleteVesselBtn');
    const cameraDeviceSelect = document.getElementById('vrCameraDeviceSelect');
    const cameraVideo = document.getElementById('vrCameraVideo');
    const cameraEmptyState = document.getElementById('vrCameraEmptyState');
    const cameraStatus = document.getElementById('vrCameraStatus');
    const refreshCameraSourcesButton = document.getElementById('vrRefreshCameraSourcesBtn');
    const cancelCameraButton = document.getElementById('vrCancelCameraBtn');
    const captureCameraButton = document.getElementById('vrCaptureCameraBtn');
    const filePreviewTitle = document.getElementById('vrFilePreviewTitle');
    const filePreviewContainer = document.getElementById('vrFilePreviewContainer');
    const filePreviewNote = document.getElementById('vrFilePreviewNote');
    const closeFilePreviewButton = document.getElementById('vrCloseFilePreviewBtn');
    const phoneQrBox = document.getElementById('vrPhoneQrBox');
    const phoneUploadDocLabel = document.getElementById('vrPhoneUploadDocLabel');
    const phoneReachableHostInput = document.getElementById('vrPhoneReachableHostInput');
    const phoneHostHelp = document.getElementById('vrPhoneHostHelp');
    const phoneUploadLinkInput = document.getElementById('vrPhoneUploadLinkInput');
    const phoneUploadStatus = document.getElementById('vrPhoneUploadStatus');
    const copyPhoneUploadLinkButton = document.getElementById('vrCopyPhoneUploadLinkBtn');
    const closePhoneUploadButton = document.getElementById('vrClosePhoneUploadBtn');
    const registrySearchAction = @json(route('fishport.vessel_registry'));
    const phoneUploadStartUrl = @json(route('fishport.phone_upload.start'));
    const phoneUploadStatusTemplate = @json(route('fishport.phone_upload.status', ['token' => '__TOKEN__']));
    let searchTimer = null;
    let activeSearchRequestId = 0;
    let pendingDeleteForm = null;
    let activeCameraStream = null;
    let currentCameraInputId = '';
    let activePreviewObjectUrl = '';
    let activePhoneUploadTargetInputId = '';
    let activePhoneUploadToken = '';
    let activePhoneUploadServerUrl = '';
    let phoneUploadPollTimer = null;
    let activeQrInstance = null;
    const PHONE_HOST_STORAGE_KEY = 'vr_phone_upload_host_override';

    const lockBody = () => document.body.classList.toggle('vr-lock-scroll', allModals.some((m) => m.classList.contains('is-open')));
    const openModal = (m) => { if (!m) return; m.classList.add('is-open'); m.setAttribute('aria-hidden', 'false'); const b = m.querySelector('.vr-modal-body'); if (b) b.scrollTop = 0; lockBody(); };
    const closeModal = (m) => { if (!m) return; m.classList.remove('is-open'); m.setAttribute('aria-hidden', 'true'); lockBody(); };
    if (openRegisterButton) openRegisterButton.addEventListener('click', () => openModal(registerModal));
    if (closeRegisterButton) closeRegisterButton.addEventListener('click', () => { const f = registerModal ? registerModal.querySelector('form') : null; if (f) f.reset(); syncAllDocFileNames(); closeModal(registerModal); });
    if (closeEditButton) closeEditButton.addEventListener('click', () => { syncAllDocFileNames(); closeModal(editModal); });
    if (cancelCameraButton) cancelCameraButton.addEventListener('click', () => { stopCameraStream(); closeModal(cameraModal); });
    if (closeFilePreviewButton) closeFilePreviewButton.addEventListener('click', () => { clearFilePreview(); closeModal(filePreviewModal); });
    if (closePhoneUploadButton) closePhoneUploadButton.addEventListener('click', () => { clearPhoneUploadUi(); closeModal(phoneUploadModal); });
    if (copyPhoneUploadLinkButton) {
        copyPhoneUploadLinkButton.addEventListener('click', async () => {
            const uploadLink = phoneUploadLinkInput ? String(phoneUploadLinkInput.value || '') : '';
            if (!uploadLink) return;
            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(uploadLink);
                } else if (phoneUploadLinkInput) {
                    phoneUploadLinkInput.select();
                    document.execCommand('copy');
                }
                setPhoneUploadStatus('Upload link copied. Paste it on your phone browser if needed.');
            } catch (error) {
                setPhoneUploadStatus('Copy failed. You can manually copy the link text.', true);
            }
        });
    }
    modalCloseButtons.forEach((btn) => btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-close-modal');
        if (!id) return;
        const m = document.getElementById(id);
        if (m === registerModal) { const f = registerModal ? registerModal.querySelector('form') : null; if (f) f.reset(); syncAllDocFileNames(); }
        if (m === editModal) syncAllDocFileNames();
        if (m === cameraModal) stopCameraStream();
        if (m === filePreviewModal) clearFilePreview();
        if (m === phoneUploadModal) clearPhoneUploadUi();
        if (m === deleteModal) pendingDeleteForm = null;
        closeModal(m);
    }));

    const assign = (id, v) => { const i = document.getElementById(id); if (i) i.value = v || ''; };
    const syncDocFileName = (input) => {
        if (!input) return;
        const labelNode = document.querySelector(`.js-vr-doc-name[data-target-input="${input.id}"]`);
        if (!labelNode) return;
        const selectedFile = input.files && input.files[0] ? input.files[0] : null;
        labelNode.textContent = selectedFile ? selectedFile.name : 'No file selected';
    };
    const syncAllDocFileNames = () => {
        document.querySelectorAll('.js-vr-doc-input').forEach((input) => syncDocFileName(input));
    };
    const setDocReq = (id, uploaded) => {
        const i = document.getElementById(id);
        if (!i) return;
        i.value = '';
        i.required = uploaded !== '1';
        const labelNode = document.querySelector(`.js-vr-doc-name[data-target-input="${id}"]`);
        if (labelNode && uploaded === '1') {
            labelNode.textContent = 'Existing file on record. Upload or capture to replace.';
            return;
        }
        syncDocFileName(i);
    };

    const setCameraStatus = (message, isError = false) => {
        if (!cameraStatus) return;
        cameraStatus.textContent = message || '';
        cameraStatus.style.color = isError ? '#9f1239' : '#0f4b73';
    };

    const isCameraContextReady = () => {
        if (window.isSecureContext) return true;
        const host = window.location.hostname;
        return host === 'localhost' || host === '127.0.0.1';
    };

    const stopCameraStream = () => {
        if (activeCameraStream) {
            activeCameraStream.getTracks().forEach((track) => track.stop());
            activeCameraStream = null;
        }
        if (cameraVideo) cameraVideo.srcObject = null;
    };

    const clearFilePreview = () => {
        if (activePreviewObjectUrl) {
            URL.revokeObjectURL(activePreviewObjectUrl);
            activePreviewObjectUrl = '';
        }
        if (filePreviewContainer) {
            filePreviewContainer.innerHTML = '<p class="vr-file-preview-empty">No file selected yet.</p>';
        }
        if (filePreviewTitle) filePreviewTitle.textContent = 'File Preview';
        if (filePreviewNote) filePreviewNote.textContent = 'Tip: Select or capture a document first, then click View File.';
    };

    const stopPhoneUploadPolling = () => {
        if (phoneUploadPollTimer) {
            window.clearInterval(phoneUploadPollTimer);
            phoneUploadPollTimer = null;
        }
    };

    const clearPhoneUploadUi = () => {
        stopPhoneUploadPolling();
        activePhoneUploadToken = '';
        activePhoneUploadTargetInputId = '';
        activePhoneUploadServerUrl = '';
        if (phoneUploadDocLabel) phoneUploadDocLabel.textContent = 'Document: -';
        if (phoneUploadLinkInput) phoneUploadLinkInput.value = '';
        if (phoneUploadStatus) phoneUploadStatus.textContent = 'Preparing secure upload session...';
        if (phoneQrBox) {
            phoneQrBox.innerHTML = '<p class="vr-qr-placeholder">Generate QR to start phone upload.</p>';
            activeQrInstance = null;
        }
    };

    const isLocalLikeHost = (hostname) => {
        const value = String(hostname || '').toLowerCase();
        return value === 'localhost' || value === '127.0.0.1' || value === '::1' || value.endsWith('.local');
    };

    const normalizeHostOrigin = (rawValue) => {
        const text = String(rawValue || '').trim();
        if (!text) return '';
        const withProtocol = /^https?:\/\//i.test(text) ? text : `http://${text}`;
        try {
            const parsed = new URL(withProtocol);
            return `${parsed.protocol}//${parsed.host}`;
        } catch (error) {
            return '';
        }
    };

    const updatePhoneHostHelpText = (reachableUrl = '') => {
        if (!phoneHostHelp) return;
        if (reachableUrl) {
            phoneHostHelp.textContent = `Phone link host: ${reachableUrl}`;
            return;
        }
        if (isLocalLikeHost(window.location.hostname)) {
            phoneHostHelp.textContent = 'This page is on localhost. Enter LAN IP like http://192.168.1.10:8000 so phone can open it.';
            return;
        }
        phoneHostHelp.textContent = 'Using current site host for QR link.';
    };

    const buildPhoneReachableUploadUrl = (serverUrl) => {
        const parsedServerUrl = new URL(String(serverUrl || ''), window.location.href);
        const pathAndQuery = `${parsedServerUrl.pathname}${parsedServerUrl.search}${parsedServerUrl.hash}`;
        const storedOrigin = normalizeHostOrigin(window.localStorage.getItem(PHONE_HOST_STORAGE_KEY) || '');
        const typedOrigin = normalizeHostOrigin(phoneReachableHostInput ? phoneReachableHostInput.value : '');
        const currentOrigin = `${window.location.protocol}//${window.location.host}`;
        const currentIsReachable = !isLocalLikeHost(window.location.hostname);
        const selectedOrigin = typedOrigin || storedOrigin || (currentIsReachable ? currentOrigin : `${parsedServerUrl.protocol}//${parsedServerUrl.host}`);
        const selectedHostName = (() => {
            try { return new URL(selectedOrigin).hostname; } catch (error) { return ''; }
        })();
        const needsPhoneHost = isLocalLikeHost(selectedHostName);
        return {
            finalUrl: `${selectedOrigin}${pathAndQuery}`,
            selectedOrigin,
            needsPhoneHost,
        };
    };

    const refreshPhoneUploadLinkAndQr = () => {
        if (!activePhoneUploadServerUrl) return;
        const built = buildPhoneReachableUploadUrl(activePhoneUploadServerUrl);
        if (phoneUploadLinkInput) phoneUploadLinkInput.value = built.finalUrl;
        renderPhoneUploadQr(built.finalUrl);
        if (built.needsPhoneHost) {
            setPhoneUploadStatus('Phone cannot open localhost. Enter your PC LAN IP in Phone Reachable Host.', true);
        } else {
            setPhoneUploadStatus('Scan QR with phone, upload file, then wait for auto-attach.');
        }
        updatePhoneHostHelpText(built.selectedOrigin);
    };

    if (phoneReachableHostInput) {
        const rememberedHost = normalizeHostOrigin(window.localStorage.getItem(PHONE_HOST_STORAGE_KEY) || '');
        if (rememberedHost) phoneReachableHostInput.value = rememberedHost;
        phoneReachableHostInput.addEventListener('input', () => {
            const normalized = normalizeHostOrigin(phoneReachableHostInput.value);
            if (normalized) {
                window.localStorage.setItem(PHONE_HOST_STORAGE_KEY, normalized);
            } else {
                window.localStorage.removeItem(PHONE_HOST_STORAGE_KEY);
            }
            refreshPhoneUploadLinkAndQr();
        });
    }

    const setPhoneUploadStatus = (message, isError = false) => {
        if (!phoneUploadStatus) return;
        phoneUploadStatus.textContent = message || '';
        phoneUploadStatus.style.color = isError ? '#9f1239' : '#0f4b73';
    };

    const renderPhoneUploadQr = (url) => {
        if (!phoneQrBox) return;
        phoneQrBox.innerHTML = '';
        if (window.QRCode) {
            activeQrInstance = new window.QRCode(phoneQrBox, {
                text: url,
                width: 220,
                height: 220,
                colorDark: '#0f172a',
                colorLight: '#ffffff',
                correctLevel: window.QRCode.CorrectLevel.M,
            });
            return;
        }
        phoneQrBox.innerHTML = '<p class="vr-qr-placeholder">QR library unavailable. Use the copy link button instead.</p>';
    };

    const attachFetchedPhoneUploadToInput = async (statusPayload) => {
        if (!statusPayload || statusPayload.status !== 'uploaded') return false;
        const inputId = activePhoneUploadTargetInputId;
        const targetInput = document.getElementById(inputId);
        const fileUrl = String(statusPayload.file_url || '');
        if (!targetInput || !fileUrl) return false;

        const response = await fetch(fileUrl, { credentials: 'same-origin' });
        if (!response.ok) return false;
        const blob = await response.blob();
        const fileName = String(statusPayload.uploaded_name || `${targetInput.dataset.docLabel || 'phone-upload'}.jpg`);
        const file = new File([blob], fileName, { type: blob.type || String(statusPayload.uploaded_mime || 'application/octet-stream') });
        const transfer = new DataTransfer();
        transfer.items.add(file);
        targetInput.files = transfer.files;
        targetInput.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    };

    const pollPhoneUploadStatus = (token) => {
        stopPhoneUploadPolling();
        if (!token) return;
        const statusUrl = phoneUploadStatusTemplate.replace('__TOKEN__', encodeURIComponent(token));
        phoneUploadPollTimer = window.setInterval(async () => {
            try {
                const response = await fetch(statusUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!response.ok) {
                    if (response.status === 404) {
                        setPhoneUploadStatus('QR session expired. Generate a new one.', true);
                        stopPhoneUploadPolling();
                    }
                    return;
                }

                const statusPayload = await response.json();
                if (statusPayload.status !== 'uploaded') return;

                setPhoneUploadStatus('Upload received from phone. Attaching file...');
                const attached = await attachFetchedPhoneUploadToInput(statusPayload);
                if (!attached) {
                    setPhoneUploadStatus('Upload found but attach failed. Please retry.', true);
                    stopPhoneUploadPolling();
                    return;
                }

                setPhoneUploadStatus('File attached successfully.');
                stopPhoneUploadPolling();
                window.setTimeout(() => {
                    closeModal(phoneUploadModal);
                    clearPhoneUploadUi();
                }, 900);
            } catch (error) {
                setPhoneUploadStatus('Waiting for phone upload...');
            }
        }, 2200);
    };

    const openPhoneUploadBridge = async (inputId) => {
        const targetInput = document.getElementById(inputId);
        if (!targetInput || !phoneUploadModal) return;
        clearPhoneUploadUi();
        openModal(phoneUploadModal);

        const docLabel = String(targetInput.dataset.docLabel || 'Document');
        if (phoneUploadDocLabel) phoneUploadDocLabel.textContent = `Document: ${docLabel}`;
        setPhoneUploadStatus('Creating secure QR session...');

        try {
            const response = await fetch(phoneUploadStartUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    target_input_id: inputId,
                    doc_label: docLabel,
                }),
            });

            if (!response.ok) {
                setPhoneUploadStatus('Unable to start phone upload session.', true);
                return;
            }

            const payload = await response.json();
            if (!payload.ok || !payload.upload_url || !payload.token) {
                setPhoneUploadStatus('Invalid QR session response.', true);
                return;
            }

            activePhoneUploadTargetInputId = inputId;
            activePhoneUploadToken = String(payload.token);
            activePhoneUploadServerUrl = String(payload.upload_url);
            refreshPhoneUploadLinkAndQr();
            pollPhoneUploadStatus(activePhoneUploadToken);
        } catch (error) {
            setPhoneUploadStatus('Failed to create phone upload session.', true);
        }
    };

    const populateCameraSources = async () => {
        if (!cameraDeviceSelect || !navigator.mediaDevices?.enumerateDevices) return [];
        const devices = await navigator.mediaDevices.enumerateDevices();
        const cameras = devices.filter((device) => device.kind === 'videoinput');
        if (cameras.length === 0) {
            cameraDeviceSelect.innerHTML = '<option value="">No camera detected</option>';
            cameraDeviceSelect.disabled = true;
            return cameras;
        }
        cameraDeviceSelect.disabled = false;
        cameraDeviceSelect.innerHTML = cameras
            .map((camera, index) => `<option value="${camera.deviceId}">${camera.label || `Camera ${index + 1}`}</option>`)
            .join('');
        return cameras;
    };

    const startCameraStream = async (preferredDeviceId = '') => {
        if (!navigator.mediaDevices?.getUserMedia) throw new Error('Camera API not supported in this browser.');
        stopCameraStream();
        let stream = null;
        if (preferredDeviceId) {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { deviceId: { exact: preferredDeviceId } },
                    audio: false,
                });
            } catch (error) {
                // Fallback to generic request if specific device id fails.
            }
        }
        if (!stream) {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });
        }
        activeCameraStream = stream;
        if (cameraVideo) {
            cameraVideo.srcObject = activeCameraStream;
            await cameraVideo.play().catch(() => {});
        }
        if (cameraEmptyState) cameraEmptyState.style.display = 'none';
    };

    const openCameraCaptureModal = async (inputId) => {
        const input = document.getElementById(inputId);
        if (!input) return;
        currentCameraInputId = inputId;
        if (!cameraModal) {
            input.click();
            return;
        }
        if (!isCameraContextReady()) {
            setCameraStatus('Camera needs HTTPS or localhost. Open this app on secure context first.', true);
            openModal(cameraModal);
            if (cameraEmptyState) cameraEmptyState.style.display = 'flex';
            return;
        }
        openModal(cameraModal);
        setCameraStatus('Requesting camera access...');
        try {
            await startCameraStream();
            const cameras = await populateCameraSources();
            if (cameras.length === 0) {
                setCameraStatus('No camera device detected. Connect phone as webcam, then click Refresh Cameras.', true);
            } else {
                if (cameraDeviceSelect && cameraDeviceSelect.value !== cameras[0].deviceId) {
                    cameraDeviceSelect.value = cameras[0].deviceId;
                }
                setCameraStatus('Camera ready. Capture when document is visible.');
            }
        } catch (error) {
            setCameraStatus('Unable to access camera. You can still upload a file manually.', true);
            if (cameraEmptyState) cameraEmptyState.style.display = 'flex';
        }
    };

    const openFilePreviewForInput = (inputId) => {
        const input = document.getElementById(inputId);
        if (!input || !filePreviewModal || !filePreviewContainer) return;
        const selectedFile = input.files && input.files[0] ? input.files[0] : null;
        clearFilePreview();
        if (!selectedFile) {
            if (filePreviewNote) filePreviewNote.textContent = 'No file to preview yet for this document field.';
            openModal(filePreviewModal);
            return;
        }

        const objectUrl = URL.createObjectURL(selectedFile);
        activePreviewObjectUrl = objectUrl;
        if (filePreviewTitle) {
            const docLabel = input.dataset.docLabel || 'Document';
            filePreviewTitle.textContent = `${docLabel} Preview`;
        }
        if (filePreviewNote) filePreviewNote.textContent = selectedFile.name;

        if (selectedFile.type.startsWith('image/')) {
            filePreviewContainer.innerHTML = `<img src="${objectUrl}" alt="Document preview" class="vr-file-preview-image">`;
            openModal(filePreviewModal);
            return;
        }

        if (selectedFile.type === 'application/pdf' || /\.pdf$/i.test(selectedFile.name)) {
            filePreviewContainer.innerHTML = `<iframe src="${objectUrl}" class="vr-file-preview-pdf" title="PDF preview"></iframe>`;
            openModal(filePreviewModal);
            return;
        }

        filePreviewContainer.innerHTML = `<div class="vr-file-preview-empty">Preview unavailable for this file type.<br><a href="${objectUrl}" target="_blank" rel="noopener">Open file in new tab</a></div>`;
        openModal(filePreviewModal);
    };

    const openEditFromButton = (b) => {
        if (!editVesselForm) return;
        editVesselForm.action = editActionTemplate.replace('__VESSEL_ID__', b.dataset.vesselId || '');
        assign('editVesselName', b.dataset.vesselName); assign('editVesselType', b.dataset.vesselType); assign('editRegistrationNumber', b.dataset.registrationNumber); assign('editOfficialNumber', b.dataset.officialNumber);
        assign('editPlatePermitNumber', b.dataset.platePermitNumber); assign('editHomePort', b.dataset.homePort); assign('editGrossTonnage', b.dataset.grossTonnage); assign('editNetTonnage', b.dataset.netTonnage);
        assign('editVesselLength', b.dataset.vesselLength); assign('editBeamWidth', b.dataset.beamWidth); assign('editVesselDepth', b.dataset.vesselDepth); assign('editEngineType', b.dataset.engineType);
        assign('editEngineHorsepower', b.dataset.engineHorsepower); assign('editHullMaterial', b.dataset.hullMaterial); assign('editColorMarkings', b.dataset.colorMarkings); assign('editYearBuilt', b.dataset.yearBuilt);
        assign('editVesselOwner', b.dataset.ownerName); assign('editOwnerAddress', b.dataset.ownerAddress); assign('editOwnerContactNumber', b.dataset.ownerContactNumber); assign('editOwnerEmail', b.dataset.ownerEmail);
        assign('editOwnerGovernmentIdNumber', b.dataset.ownerGovernmentIdNumber); assign('editBusinessName', b.dataset.businessName); assign('editCaptainOperatorName', b.dataset.captainOperatorName); assign('editCaptainLicenseNumber', b.dataset.captainLicenseNumber);
        assign('editCaptainContactNumber', b.dataset.captainContactNumber); assign('editCaptainAddress', b.dataset.captainAddress); assign('editRegistrationDate', b.dataset.registrationDate); assign('editExpirationDate', b.dataset.expirationDate);
        assign('editRegistrationStatus', b.dataset.registrationStatus || 'Active'); assign('editRenewalDate', b.dataset.renewalDate); assign('editIssuedBy', b.dataset.issuedBy); assign('editRemarks', b.dataset.remarks); assign('editVesselStatus', b.dataset.isActive === '1' ? '1' : '0');
        assign('editCreatedBy', b.dataset.createdBy || 'N/A'); assign('editUpdatedBy', b.dataset.updatedBy || 'N/A'); assign('editDateCreated', b.dataset.dateCreated || 'N/A');
        assign('editDateUpdated', b.dataset.dateUpdated || 'N/A'); assign('editSupportingDocumentsUploaded', b.dataset.supportingDocumentsUploaded || 'No');
        setDocReq('editCertificateOfOwnershipFile', b.dataset.certificateUploaded || '0'); setDocReq('editPreviousRegistrationFile', b.dataset.previousRegistrationUploaded || '0'); setDocReq('editBoatPermitLicenseFile', b.dataset.boatPermitUploaded || '0');
        setDocReq('editEngineReceiptProofFile', b.dataset.engineReceiptUploaded || '0'); setDocReq('editValidIdFile', b.dataset.validIdUploaded || '0'); setDocReq('editInspectionCertificateFile', b.dataset.inspectionUploaded || '0');
        openModal(editModal);
    };

    if (cameraDeviceSelect) {
        cameraDeviceSelect.addEventListener('change', async () => {
            const selectedId = cameraDeviceSelect.value || '';
            if (!selectedId) return;
            try {
                await startCameraStream(selectedId);
                setCameraStatus('Switched camera source.');
            } catch (error) {
                setCameraStatus('Unable to switch to selected camera source.', true);
            }
        });
    }

    if (refreshCameraSourcesButton) {
        refreshCameraSourcesButton.addEventListener('click', async () => {
            try {
                const cameras = await populateCameraSources();
                setCameraStatus(cameras.length ? 'Camera list updated.' : 'No camera source found.', !cameras.length);
            } catch (error) {
                setCameraStatus('Unable to refresh camera devices.', true);
            }
        });
    }

    if (navigator.mediaDevices?.addEventListener) {
        navigator.mediaDevices.addEventListener('devicechange', async () => {
            try {
                const cameras = await populateCameraSources();
                if (cameraModal?.classList.contains('is-open')) {
                    setCameraStatus(cameras.length ? 'Camera devices changed. Select your device if needed.' : 'No camera source detected after device change.', !cameras.length);
                }
            } catch (error) {
                // Ignore silent refresh errors on device-change.
            }
        });
    }

    if (captureCameraButton) {
        captureCameraButton.addEventListener('click', async () => {
            const targetInput = document.getElementById(currentCameraInputId);
            if (!targetInput || !cameraVideo) return;
            if (!activeCameraStream) {
                setCameraStatus('Camera is not active. Please allow camera access first.', true);
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = cameraVideo.videoWidth || 1280;
            canvas.height = cameraVideo.videoHeight || 720;
            const context = canvas.getContext('2d');
            if (!context) {
                setCameraStatus('Capture failed. Please try again.', true);
                return;
            }
            context.drawImage(cameraVideo, 0, 0, canvas.width, canvas.height);

            canvas.toBlob((blob) => {
                if (!blob) {
                    setCameraStatus('Capture failed. Please try again.', true);
                    return;
                }
                const docName = (targetInput.dataset.docLabel || 'document').toLowerCase().replace(/[^a-z0-9]+/g, '-');
                const file = new File([blob], `${docName}-${Date.now()}.jpg`, { type: 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                targetInput.files = transfer.files;
                targetInput.dispatchEvent(new Event('change', { bubbles: true }));
                setCameraStatus('Photo captured and attached.');
                stopCameraStream();
                closeModal(cameraModal);
            }, 'image/jpeg', 0.92);
        });
    }

    const currentSearchNodes = () => {
        const form = document.getElementById('vesselSearchForm');
        return {
            form,
            input: form ? form.querySelector('input[name="search"]') : null,
        };
    };

    const captureSearchState = () => {
        const { input } = currentSearchNodes();
        if (!input) {
            return { value: '', focused: false, start: null, end: null };
        }
        return {
            value: input.value,
            focused: document.activeElement === input,
            start: typeof input.selectionStart === 'number' ? input.selectionStart : null,
            end: typeof input.selectionEnd === 'number' ? input.selectionEnd : null,
        };
    };

    const restoreSearchState = (state) => {
        if (!state) return;
        const { input } = currentSearchNodes();
        if (!input) return;
        if (typeof state.value === 'string' && input.value !== state.value) {
            input.value = state.value;
        }
        if (!state.focused) return;
        input.focus({ preventScroll: true });
        if (typeof state.start === 'number' && typeof state.end === 'number' && typeof input.setSelectionRange === 'function') {
            const len = input.value.length;
            const start = Math.max(0, Math.min(state.start, len));
            const end = Math.max(0, Math.min(state.end, len));
            input.setSelectionRange(start, end);
        }
    };

    const replaceRegistryCardFromHtml = (html) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const incomingCard = doc.getElementById('vesselRegistryCard');
        const currentCard = document.getElementById('vesselRegistryCard');
        if (!incomingCard || !currentCard) return false;
        currentCard.replaceWith(incomingCard);
        return true;
    };

    const requestRegistrySearch = (query, delayMs = 0, state = null) => {
        if (searchTimer) {
            window.clearTimeout(searchTimer);
            searchTimer = null;
        }

        const run = () => {
            const requestId = ++activeSearchRequestId;
            const url = `${registrySearchAction}?${query.toString()}`;
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => response.text())
                .then((html) => {
                    if (requestId !== activeSearchRequestId) return;
                    if (!replaceRegistryCardFromHtml(html)) {
                        window.location.assign(url);
                        return;
                    }
                    history.replaceState({}, '', url);
                    restoreSearchState(state);
                })
                .catch(() => {
                    window.location.assign(url);
                });
        };

        if (delayMs > 0) {
            searchTimer = window.setTimeout(run, delayMs);
            return;
        }

        run();
    };

    document.addEventListener('click', (event) => {
        const openFileBtn = event.target.closest('.js-vr-open-file');
        if (openFileBtn) {
            event.preventDefault();
            const inputId = openFileBtn.getAttribute('data-target-input') || '';
            const input = document.getElementById(inputId);
            if (input) input.click();
            return;
        }

        const openCameraBtn = event.target.closest('.js-vr-open-camera');
        if (openCameraBtn) {
            event.preventDefault();
            const inputId = openCameraBtn.getAttribute('data-target-input') || '';
            openCameraCaptureModal(inputId);
            return;
        }

        const openPhoneUploadBtn = event.target.closest('.js-vr-open-phone-upload');
        if (openPhoneUploadBtn) {
            event.preventDefault();
            const inputId = openPhoneUploadBtn.getAttribute('data-target-input') || '';
            openPhoneUploadBridge(inputId);
            return;
        }

        const previewFileBtn = event.target.closest('.js-vr-preview-file');
        if (previewFileBtn) {
            event.preventDefault();
            const inputId = previewFileBtn.getAttribute('data-target-input') || '';
            openFilePreviewForInput(inputId);
            return;
        }

        const editButton = event.target.closest('.js-open-edit-vessel-btn');
        if (editButton) {
            openEditFromButton(editButton);
            return;
        }

        const paginationLink = event.target.closest('#vesselRegistryCard .vr-pagination .vr-page-link[href]');
        if (!paginationLink) return;

        event.preventDefault();
        const href = paginationLink.getAttribute('href');
        if (!href) return;
        const state = captureSearchState();
        fetch(href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => response.text())
            .then((html) => {
                if (!replaceRegistryCardFromHtml(html)) {
                    window.location.assign(href);
                    return;
                }
                history.replaceState({}, '', href);
                restoreSearchState(state);
            })
            .catch(() => {
                window.location.assign(href);
            });
    });

    document.addEventListener('input', (event) => {
        const { form, input } = currentSearchNodes();
        if (!form || !input || event.target !== input) return;
        const query = new URLSearchParams(new FormData(form));
        const state = captureSearchState();
        requestRegistrySearch(query, 260, state);
    });

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLInputElement)) return;
        if (!target.classList.contains('js-vr-doc-input')) return;
        syncDocFileName(target);
    });

    document.addEventListener('submit', (event) => {
        const targetForm = event.target;
        if (!(targetForm instanceof HTMLFormElement)) return;

        if (targetForm.id === 'vesselSearchForm') {
            event.preventDefault();
            const query = new URLSearchParams(new FormData(targetForm));
            const state = captureSearchState();
            requestRegistrySearch(query, 0, state);
            return;
        }

        if (!targetForm.classList.contains('js-delete-vessel-form')) return;
        if (targetForm.dataset.confirmed === '1') {
            targetForm.dataset.confirmed = '0';
            return;
        }

        event.preventDefault();
        pendingDeleteForm = targetForm;
        if (deleteVesselName) deleteVesselName.textContent = targetForm.dataset.vesselName || '-';
        if (deleteVesselOwner) deleteVesselOwner.textContent = targetForm.dataset.ownerName || '-';
        openModal(deleteModal);
    });

    if (cancelDeleteButton) cancelDeleteButton.addEventListener('click', () => { pendingDeleteForm = null; closeModal(deleteModal); });
    if (confirmDeleteButton) confirmDeleteButton.addEventListener('click', () => { if (!pendingDeleteForm) return; const t = pendingDeleteForm; t.dataset.confirmed = '1'; pendingDeleteForm = null; closeModal(deleteModal); if (typeof t.requestSubmit === 'function') { t.requestSubmit(); return; } t.submit(); });
    allModals.forEach((m) => m.addEventListener('click', (e) => {
        if (e.target !== m) return;
        if (m === registerModal) { const f = registerModal ? registerModal.querySelector('form') : null; if (f) f.reset(); syncAllDocFileNames(); }
        if (m === editModal) syncAllDocFileNames();
        if (m === cameraModal) stopCameraStream();
        if (m === filePreviewModal) clearFilePreview();
        if (m === phoneUploadModal) clearPhoneUploadUi();
        if (m === deleteModal) pendingDeleteForm = null;
        closeModal(m);
    }));
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        const f = registerModal ? registerModal.querySelector('form') : null;
        if (f) f.reset();
        syncAllDocFileNames();
        pendingDeleteForm = null;
        stopCameraStream();
        clearFilePreview();
        clearPhoneUploadUi();
        closeModal(registerModal);
        closeModal(editModal);
        closeModal(cameraModal);
        closeModal(filePreviewModal);
        closeModal(phoneUploadModal);
        closeModal(deleteModal);
    });
    lockBody();
    syncAllDocFileNames();
    updatePhoneHostHelpText();

    if (statusToast) {
        window.setTimeout(() => {
            statusToast.classList.add('is-hiding');
            window.setTimeout(() => statusToast.remove(), 220);
        }, 2200);
    }
})();
</script>
@endsection
