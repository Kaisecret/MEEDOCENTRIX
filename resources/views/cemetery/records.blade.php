@extends('layouts.app')

@section('content')
<style>
    .cor-page {
        display: grid;
        gap: 16px;
        color: #334155;
        font-family: 'Inter', system-ui, sans-serif;
    }

    .cor-hero {
        border-radius: 12px;
        border: 1px solid #dbe6f0;
        background: #155f8f;
        color: #fff;
        padding: 1.1rem 1.3rem;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.14);
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: center;
    }

    .cor-hero h2 {
        margin: 0 0 0.25rem;
        font-size: 1.4rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .cor-hero p {
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.9rem;
    }

    .cor-add-btn {
        border: 1px solid rgba(255, 255, 255, 0.42);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        min-height: 40px;
        padding: 0 0.95rem;
        font-size: 0.88rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .cor-add-btn:hover { background: rgba(255, 255, 255, 0.28); }

    .cor-stats {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .cor-stat {
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        padding: 0.7rem 0.8rem;
    }

    .cor-stat span {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .cor-stat strong {
        display: block;
        margin-top: 0.28rem;
        color: #0f172a;
        font-size: 1.02rem;
    }

    .cor-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .cor-card-head {
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.1rem;
        background: #f8fafc;
        display: grid;
        gap: 9px;
    }

    .cor-card-head h3 {
        margin: 0;
        font-size: 1.05rem;
        color: #0f172a;
    }

    .cor-filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
        gap: 8px;
    }

    .cor-control {
        min-height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #0f172a;
        padding: 0.45rem 0.65rem;
        font-size: 0.86rem;
        width: 100%;
    }

    .cor-control:focus {
        outline: none;
        border-color: #155f8f;
        box-shadow: 0 0 0 3px rgba(21, 95, 143, 0.11);
    }

    .cor-filter-actions {
        display: inline-flex;
        gap: 6px;
    }

    .cor-btn {
        border: 1px solid transparent;
        border-radius: 9px;
        min-height: 38px;
        padding: 0 0.8rem;
        font-size: 0.84rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .cor-btn-primary {
        border-color: #155f8f;
        background: #155f8f;
        color: #fff;
    }

    .cor-btn-secondary {
        border-color: #cbd5e1;
        background: #fff;
        color: #334155;
    }

    .cor-table-wrap { overflow: auto; }

    .cor-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1120px;
    }

    .cor-table th {
        background: #eef5fb;
        border-bottom: 1px solid #dce5ef;
        color: #12314d;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        text-align: left;
        padding: 0.74rem 0.7rem;
    }

    .cor-table td {
        border-bottom: 1px solid #eef2f7;
        padding: 0.7rem;
        color: #334155;
        font-size: 0.85rem;
        vertical-align: top;
    }

    .cor-table tbody tr:hover { background: #f8fbff; }

    .cor-muted { color: #64748b; font-size: 0.79rem; }
    .cor-stack { display: grid; gap: 2px; }
    .cor-title { font-weight: 700; color: #0f172a; }
    .cor-subtle { color: #64748b; font-size: 0.79rem; }

    .cor-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        border: 1px solid;
        padding: 0.2rem 0.52rem;
        font-size: 0.71rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .cor-badge-status-active,
    .cor-badge-maint-paid { border-color: #86efac; background: #ecfdf5; color: #065f46; }
    .cor-badge-status-inactive,
    .cor-badge-maint-unpaid { border-color: #fecaca; background: #fff1f2; color: #9f1239; }
    .cor-badge-status-exhumed,
    .cor-badge-maint-partial { border-color: #fde68a; background: #fffbeb; color: #92400e; }
    .cor-badge-status-transferred,
    .cor-badge-maint-overdue { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }

    .cor-actions { display: inline-flex; gap: 6px; }
    .cor-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #d8e2ef;
        background: #fff;
        color: #42566d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .cor-icon-btn:hover { background: #f1f5f9; color: #155f8f; }
    .cor-icon-btn-danger:hover { border-color: #fecaca; background: #fff1f2; color: #b91c1c; }

    .cor-pagination {
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .cor-page-link {
        min-height: 34px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #155f8f;
        font-size: 0.82rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 0.75rem;
        text-decoration: none;
    }

    .cor-page-link.disabled {
        border-color: #e2e8f0;
        color: #94a3b8;
        background: #f8fafc;
        pointer-events: none;
    }

    .cor-modal {
        position: fixed;
        inset: 0;
        z-index: 1650;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.56);
        backdrop-filter: blur(3px);
        padding: 16px;
    }
    .cor-modal.is-open { display: flex; }

    .cor-modal-card {
        width: min(1060px, 97vw);
        max-height: 92vh;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        overflow: hidden;
        display: grid;
        grid-template-rows: auto minmax(0, 1fr);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, .1), 0 10px 10px -5px rgba(0, 0, 0, .04);
    }

    .cor-modal-card-compact { width: min(460px, 96vw); }
    .cor-modal-head {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cor-modal-head h4 { margin: 0; color: #0f172a; font-size: 1.06rem; }
    .cor-modal-close {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cor-modal-close:hover { background: #e2e8f0; color: #0f172a; }
    .cor-modal form { display: grid; grid-template-rows: minmax(0, 1fr) auto; min-height: 0; }
    .cor-modal-body { padding: 16px 20px; overflow-y: auto; min-height: 0; background: #fff; }
    .cor-modal-foot {
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 12px 16px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .cor-form-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px 14px; }
    .cor-field { display: grid; gap: 6px; }
    .cor-field-full { grid-column: 1 / -1; }
    .cor-field label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .cor-control-textarea { min-height: 82px; resize: vertical; }

    body.cor-lock-scroll { overflow: hidden; }

    @media (max-width: 1180px) {
        .cor-filter-grid { grid-template-columns: 1fr 1fr 1fr; }
        .cor-filter-actions { grid-column: 1 / -1; }
    }

    @media (max-width: 900px) {
        .cor-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .cor-hero { grid-template-columns: 1fr; }
        .cor-form-grid { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 640px) {
        .cor-stats, .cor-form-grid { grid-template-columns: 1fr; }
        .cor-filter-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="cor-page" data-server-rendered-page="cemetery_records" data-page-title="Occupant Records">
    <section class="cor-hero">
        <div>
            <h2>Cemetery Occupant Records</h2>
            <p>Official office encoding and monitoring of cemetery occupant, niche, and lot records.</p>
        </div>
        <button type="button" id="openCreateRecordBtn" class="cor-add-btn">
            <i class="fa-solid fa-plus"></i> Add Occupant Record
        </button>
    </section>

    <section class="cor-stats">
        <article class="cor-stat"><span>Total Records</span><strong>{{ number_format((int) $summary['total_records']) }}</strong></article>
        <article class="cor-stat"><span>Occupied Niches/Lots</span><strong>{{ number_format((int) $summary['occupied_plots']) }}</strong></article>
        <article class="cor-stat"><span>Available Niches/Lots</span><strong>{{ number_format((int) $summary['available_plots']) }}</strong></article>
        <article class="cor-stat"><span>Unpaid Maintenance</span><strong>{{ number_format((int) $summary['unpaid_maintenance']) }}</strong></article>
        <article class="cor-stat"><span>Overdue Maintenance</span><strong>{{ number_format((int) $summary['overdue_maintenance']) }}</strong></article>
    </section>

    @if (session('status'))
        <div class="alert alert-success" style="margin:0;"><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" style="margin:0;"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>
    @endif

    <section class="cor-card">
        <div class="cor-card-head">
            <h3>Occupant Records List</h3>
            @if($hasActiveFilters)
                <div class="alert alert-info" style="margin:0; padding:.55rem .75rem; font-size:.84rem;">
                    <i class="fa-solid fa-filter"></i> Filters are active. Showing only matching records.
                </div>
            @endif
            <form method="GET" action="{{ route('cemetery.records') }}" class="cor-filter-grid">
                <input type="search" name="q" class="cor-control" placeholder="Search deceased, niche/lot, contact..." value="{{ $search }}">
                <select name="cemetery_site_id" class="cor-control">
                    <option value="">All Cemeteries</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" @selected((string) $selectedSiteId === (string) $site->id)>{{ $site->site_name }}</option>
                    @endforeach
                </select>
                <select name="cemetery_category_id" class="cor-control">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>{{ $category->category_name }}</option>
                    @endforeach
                </select>
                <select name="status" class="cor-control">
                    <option value="">All Statuses</option>
                    @foreach($statusOptions as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" @selected($selectedStatus === $statusKey)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
                <select name="maintenance_fee_status" class="cor-control">
                    <option value="">All Maintenance</option>
                    @foreach($maintenanceStatusOptions as $maintenanceStatusKey => $maintenanceStatusLabel)
                        <option value="{{ $maintenanceStatusKey }}" @selected($selectedMaintenanceStatus === $maintenanceStatusKey)>{{ $maintenanceStatusLabel }}</option>
                    @endforeach
                </select>
                <div class="cor-filter-actions">
                    <button type="submit" class="cor-btn cor-btn-primary"><i class="fa-solid fa-filter"></i> Apply</button>
                    <a href="{{ route('cemetery.records') }}" class="cor-btn cor-btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="cor-table-wrap">
            <table class="cor-table">
                <thead>
                    <tr>
                        <th>Record No.</th>
                        <th>Name of Deceased</th>
                        <th>Cemetery / Category</th>
                        <th>Niche / Lot</th>
                        <th>Interment Date</th>
                        <th>Contact Details</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        @php
                            $contact = $record->contact;
                            $plot = $record->plot;
                        @endphp
                        <tr>
                            <td><strong>{{ $record->record_no }}</strong></td>
                            <td><span class="cor-title">{{ $record->deceased_name }}</span></td>
                            <td>
                                <div class="cor-stack">
                                    <span class="cor-title">{{ $record->site?->site_name ?: '-' }}</span>
                                    <span class="cor-subtle">{{ $record->category?->category_name ?: '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="cor-stack">
                                    <span class="cor-title">{{ $plot?->plot_reference ?: '-' }}</span>
                                    <span class="cor-subtle">{{ strtoupper((string) ($plot?->plot_type ?? '-')) }}</span>
                                </div>
                            </td>
                            <td>{{ optional($record->date_of_interment)->format('Y-m-d') }}</td>
                            <td>
                                <div class="cor-stack">
                                    <span class="cor-title">{{ $contact?->contact_person ?: '-' }}</span>
                                    <span class="cor-subtle">{{ $contact?->contact_number ?: '-' }}</span>
                                    <span class="cor-subtle">{{ $contact?->address ?: '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="cor-badge cor-badge-status-{{ $record->status }}">{{ $statusOptions[$record->status] ?? strtoupper($record->status) }}</span>
                            </td>
                            <td>
                                <div class="cor-actions">
                                    <a
                                        href="{{ route('cemetery.transactions', ['occupant_record_id' => $record->id, 'open_create' => 1]) }}"
                                        class="cor-icon-btn"
                                        title="Create transaction from this occupant">
                                        <i class="fa-solid fa-receipt"></i>
                                    </a>
                                    <button
                                        type="button"
                                        class="cor-icon-btn js-open-edit-record-btn"
                                        data-record-id="{{ $record->id }}"
                                        data-record-no="{{ $record->record_no }}"
                                        data-site-id="{{ $record->cemetery_site_id }}"
                                        data-category-id="{{ $record->cemetery_category_id }}"
                                        data-plot-reference="{{ $plot?->plot_reference }}"
                                        data-plot-type="{{ $plot?->plot_type }}"
                                        data-deceased-name="{{ $record->deceased_name }}"
                                        data-interment-date="{{ optional($record->date_of_interment)->format('Y-m-d') }}"
                                        data-contact-person="{{ $contact?->contact_person }}"
                                        data-contact-number="{{ $contact?->contact_number }}"
                                        data-address="{{ $contact?->address }}"
                                        data-remarks="{{ $record->remarks }}"
                                        data-status="{{ $record->status }}"
                                        data-maintenance-status="{{ $record->maintenance_fee_status }}"
                                        data-coverage-start="{{ optional($record->coverage_start_date)->format('Y-m-d') }}"
                                        data-coverage-end="{{ optional($record->coverage_end_date)->format('Y-m-d') }}"
                                        title="Edit record">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST" action="{{ route('cemetery.records.destroy', $record) }}" class="js-delete-record-form" data-record-no="{{ $record->record_no }}" data-deceased="{{ $record->deceased_name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cor-icon-btn cor-icon-btn-danger" title="Delete record"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:1.4rem;">
                                @if($hasActiveFilters)
                                    No records match the current filters.
                                    <a href="{{ route('cemetery.records') }}" style="font-weight:700; margin-left:6px;">Clear filters</a>
                                @else
                                    No occupant records found.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div class="cor-pagination">
                @if ($records->previousPageUrl())
                    <a class="cor-page-link" href="{{ $records->previousPageUrl() }}">Previous</a>
                @else
                    <span class="cor-page-link disabled">Previous</span>
                @endif
                @if ($records->nextPageUrl())
                    <a class="cor-page-link" href="{{ $records->nextPageUrl() }}">Next</a>
                @else
                    <span class="cor-page-link disabled">Next</span>
                @endif
            </div>
        @endif
    </section>
</div>

<div id="createRecordModal" class="cor-modal" aria-hidden="true">
    <div class="cor-modal-card">
        <div class="cor-modal-head">
            <h4>Add Occupant Record</h4>
            <button type="button" class="cor-modal-close" data-close-modal="createRecordModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="createRecordForm" method="POST" action="{{ route('cemetery.records.store') }}">
            @csrf
            <input type="hidden" name="form_mode" value="create">
            <div class="cor-modal-body">
                @include('cemetery.partials.occupant_form_fields', [
                    'prefix' => 'newOcc',
                    'sites' => $sites,
                    'categories' => $categories,
                    'statusOptions' => $statusOptions,
                    'maintenanceStatusOptions' => $maintenanceStatusOptions,
                    'plotTypeOptions' => $plotTypeOptions,
                    'recordNoValue' => $nextRecordNo,
                ])
            </div>
            <div class="cor-modal-foot">
                <button type="button" class="cor-btn cor-btn-secondary" data-close-modal="createRecordModal">Cancel</button>
                <button type="submit" class="cor-btn cor-btn-primary">Save Record</button>
            </div>
        </form>
    </div>
</div>

<div id="editRecordModal" class="cor-modal" aria-hidden="true">
    <div class="cor-modal-card">
        <div class="cor-modal-head">
            <h4>Edit Occupant Record</h4>
            <button type="button" class="cor-modal-close" data-close-modal="editRecordModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editRecordForm" method="POST" action="" data-action-template="{{ route('cemetery.records.update', '__ID__') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_mode" value="edit">
            <input type="hidden" name="form_record_id" id="editFormRecordId" value="{{ old('form_record_id') }}">
            <div class="cor-modal-body">
                @include('cemetery.partials.occupant_form_fields', [
                    'prefix' => 'editOcc',
                    'sites' => $sites,
                    'categories' => $categories,
                    'statusOptions' => $statusOptions,
                    'maintenanceStatusOptions' => $maintenanceStatusOptions,
                    'plotTypeOptions' => $plotTypeOptions,
                    'recordNoValue' => old('record_no'),
                ])
            </div>
            <div class="cor-modal-foot">
                <button type="button" class="cor-btn cor-btn-secondary" data-close-modal="editRecordModal">Cancel</button>
                <button type="submit" class="cor-btn cor-btn-primary">Update Record</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteRecordModal" class="cor-modal" aria-hidden="true">
    <div class="cor-modal-card cor-modal-card-compact">
        <div class="cor-modal-head">
            <h4>Delete Occupant Record</h4>
            <button type="button" class="cor-modal-close" data-close-modal="deleteRecordModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="cor-modal-body">
            <p style="margin:0;">Are you sure you want to delete this occupant record?</p>
            <div style="margin-top:12px; border:1px solid #e2e8f0; border-radius:10px; padding:10px 12px; background:#f8fafc;">
                <div><strong id="deleteRecordNo">-</strong></div>
                <div>Deceased: <span id="deleteDeceasedName">-</span></div>
            </div>
        </div>
        <div class="cor-modal-foot">
            <button type="button" class="cor-btn cor-btn-secondary" data-close-modal="deleteRecordModal">Cancel</button>
            <button type="button" class="cor-btn cor-btn-primary" id="confirmDeleteRecordBtn">Yes, Delete</button>
        </div>
    </div>
</div>

<script>
(() => {
    const createModal = document.getElementById('createRecordModal');
    const editModal = document.getElementById('editRecordModal');
    const deleteModal = document.getElementById('deleteRecordModal');
    const openCreateButton = document.getElementById('openCreateRecordBtn');
    const closeButtons = Array.from(document.querySelectorAll('[data-close-modal]'));
    const editForm = document.getElementById('editRecordForm');
    const editActionTemplate = editForm ? (editForm.dataset.actionTemplate || '') : '';
    const confirmDeleteButton = document.getElementById('confirmDeleteRecordBtn');
    const deleteRecordNo = document.getElementById('deleteRecordNo');
    const deleteDeceasedName = document.getElementById('deleteDeceasedName');
    const oldFormMode = "{{ old('form_mode') }}";
    const oldFormRecordId = "{{ old('form_record_id') }}";
    const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
    let pendingDeleteForm = null;

    const allModals = [createModal, editModal, deleteModal].filter(Boolean);

    const lockBody = () => {
        const hasOpenModal = allModals.some((modal) => modal.classList.contains('is-open'));
        document.body.classList.toggle('cor-lock-scroll', hasOpenModal);
    };

    const openModal = (modal) => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        const body = modal.querySelector('.cor-modal-body');
        if (body) body.scrollTop = 0;
        lockBody();
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        lockBody();
    };

    const setValue = (id, value) => {
        const field = document.getElementById(id);
        if (!field) return;
        field.value = value || '';
    };

    const openEditFromButton = (button) => {
        if (!editForm) return;

        const recordId = button.dataset.recordId || '';
        editForm.action = editActionTemplate.replace('__ID__', recordId);
        setValue('editFormRecordId', recordId);

        setValue('editOccRecordNo', button.dataset.recordNo);
        setValue('editOccSite', button.dataset.siteId);
        setValue('editOccCategory', button.dataset.categoryId);
        setValue('editOccPlotReference', button.dataset.plotReference);
        setValue('editOccPlotType', button.dataset.plotType);
        setValue('editOccDeceasedName', button.dataset.deceasedName);
        setValue('editOccIntermentDate', button.dataset.intermentDate);
        setValue('editOccContactPerson', button.dataset.contactPerson);
        setValue('editOccContactNumber', button.dataset.contactNumber);
        setValue('editOccAddress', button.dataset.address);
        setValue('editOccRemarks', button.dataset.remarks);
        setValue('editOccStatus', button.dataset.status);
        setValue('editOccMaintenance', button.dataset.maintenanceStatus);
        setValue('editOccCoverageStart', button.dataset.coverageStart);
        setValue('editOccCoverageEnd', button.dataset.coverageEnd);

        openModal(editModal);
    };

    if (openCreateButton) {
        openCreateButton.addEventListener('click', () => openModal(createModal));
    }

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-close-modal');
            if (!modalId) return;
            const modal = document.getElementById(modalId);
            if (modal === deleteModal) pendingDeleteForm = null;
            closeModal(modal);
        });
    });

    document.addEventListener('click', (event) => {
        const editButton = event.target.closest('.js-open-edit-record-btn');
        if (editButton) {
            event.preventDefault();
            openEditFromButton(editButton);
            return;
        }

        const deleteForm = event.target.closest('.js-delete-record-form');
        if (!deleteForm) return;
        if (deleteForm.dataset.confirmed === '1') {
            deleteForm.dataset.confirmed = '0';
            return;
        }

        event.preventDefault();
        pendingDeleteForm = deleteForm;
        if (deleteRecordNo) deleteRecordNo.textContent = deleteForm.dataset.recordNo || '-';
        if (deleteDeceasedName) deleteDeceasedName.textContent = deleteForm.dataset.deceased || '-';
        openModal(deleteModal);
    });

    if (confirmDeleteButton) {
        confirmDeleteButton.addEventListener('click', () => {
            if (!pendingDeleteForm) return;
            pendingDeleteForm.dataset.confirmed = '1';
            pendingDeleteForm.submit();
            pendingDeleteForm = null;
        });
    }

    allModals.forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target !== modal) return;
            if (modal === deleteModal) pendingDeleteForm = null;
            closeModal(modal);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        pendingDeleteForm = null;
        closeModal(createModal);
        closeModal(editModal);
        closeModal(deleteModal);
    });

    if (hasErrors) {
        if (oldFormMode === 'edit' && editForm) {
            const recordId = String(oldFormRecordId || '').trim();
            if (recordId !== '') {
                editForm.action = editActionTemplate.replace('__ID__', recordId);
                setValue('editFormRecordId', recordId);
            }
            openModal(editModal);
        } else {
            openModal(createModal);
        }
    }
})();
</script>
@endsection
