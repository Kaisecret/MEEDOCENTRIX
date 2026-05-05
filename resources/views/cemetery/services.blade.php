@extends('layouts.app')

@section('content')
<style>
    #contentArea {
        padding-top: 10px;
    }

    .csl-page {
        display: grid;
        gap: 10px;
        color: #334155;
        font-family: 'Inter', system-ui, sans-serif;
    }

    .csl-hero {
        border-radius: 12px;
        border: 1px solid #dbe6f0;
        background: #155f8f;
        color: #fff;
        padding: 8px 10px;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.14);
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: center;
    }

    .csl-hero h2 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .csl-add-btn {
        border: 1px solid rgba(255, 255, 255, 0.42);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        min-height: 34px;
        padding: 0 0.95rem;
        font-size: 0.84rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .csl-add-btn:hover { background: rgba(255, 255, 255, 0.28); }

    .csl-stats {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .csl-stat {
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        padding: 10px;
    }

    .csl-stat span {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .csl-stat strong {
        display: block;
        margin-top: 10px;
        color: #0f172a;
        font-size: 1.02rem;
    }

    .csl-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .csl-card-head {
        border-bottom: 1px solid #e2e8f0;
        padding: 10px;
        background: #f8fafc;
        display: grid;
        gap: 10px;
    }

    .csl-card-head h3 {
        margin: 0;
        font-size: 1.05rem;
        color: #0f172a;
    }

    .csl-filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 10px;
    }

    .csl-control {
        min-height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #0f172a;
        padding: 0.45rem 0.65rem;
        font-size: 0.86rem;
        width: 100%;
    }

    .csl-control:focus {
        outline: none;
        border-color: #155f8f;
        box-shadow: 0 0 0 3px rgba(21, 95, 143, 0.11);
    }

    .csl-btn {
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

    .csl-btn-primary {
        border-color: #155f8f;
        background: #155f8f;
        color: #fff;
    }

    .csl-btn-secondary {
        border-color: #cbd5e1;
        background: #fff;
        color: #334155;
    }

    .csl-btn-export {
        border-color: #155f8f;
        background: #155f8f;
        color: #fff;
        text-decoration: none;
    }

    .csl-btn-export:hover {
        background: #0f4b73;
        border-color: #0f4b73;
        color: #fff;
    }

    .csl-table-wrap { overflow: auto; }

    .csl-table {
        width: 100%;
        border-collapse: collapse;
    }

    .csl-view-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .csl-view-grid .csl-view-full { grid-column: 1 / -1; }
    .csl-view-item { display: grid; gap: 10px; }
    .csl-view-item span {
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .csl-view-item strong {
        font-size: 0.9rem;
        color: #0f172a;
        font-weight: 600;
        word-break: break-word;
    }

    .csl-table th {
        background: #eef5fb;
        border-bottom: 1px solid #dce5ef;
        color: #12314d;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        text-align: left;
        padding: 10px;
    }

    .csl-table td {
        border-bottom: 1px solid #eef2f7;
        padding: 10px;
        color: #334155;
        font-size: 0.85rem;
        vertical-align: top;
    }

    .csl-table tbody tr:hover { background: #f8fbff; }

    .csl-muted { color: #64748b; font-size: 0.79rem; }

    .csl-actions { display: inline-flex; gap: 10px; }
    .csl-icon-btn {
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
    .csl-icon-btn:hover { background: #f1f5f9; color: #155f8f; }
    .csl-icon-btn-danger:hover { border-color: #fecaca; background: #fff1f2; color: #b91c1c; }

    .csl-pagination {
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 10px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .csl-page-link {
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

    .csl-page-link.disabled {
        border-color: #e2e8f0;
        color: #94a3b8;
        background: #f8fafc;
        pointer-events: none;
    }

    .csl-modal {
        position: fixed;
        inset: 0;
        z-index: 1650;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.56);
        backdrop-filter: blur(3px);
        padding: 10px;
    }
    .csl-modal.is-open { display: flex; }

    .csl-modal-card {
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

    .csl-modal-card-compact { width: min(460px, 96vw); }
    .csl-modal-head {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .csl-modal-head h4 { margin: 0; color: #0f172a; font-size: 1.06rem; }
    .csl-modal-close {
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
    .csl-modal-close:hover { background: #e2e8f0; color: #0f172a; }
    .csl-modal form { display: grid; grid-template-rows: minmax(0, 1fr) auto; min-height: 0; }
    .csl-modal-body { padding: 10px; overflow-y: auto; min-height: 0; background: #fff; }
    .csl-modal-foot {
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 10px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .csl-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .csl-field { display: grid; gap: 10px; }
    .csl-field-full { grid-column: 1 / -1; }
    .csl-field label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .csl-control-textarea { min-height: 84px; resize: vertical; }

    body.csl-lock-scroll { overflow: hidden; }

    @media (max-width: 980px) {
        .csl-hero { grid-template-columns: 1fr; }
        .csl-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .csl-filter-grid { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 680px) {
        .csl-stats, .csl-form-grid, .csl-filter-grid { grid-template-columns: 1fr; }
    }
</style>

@php
    $exportQuery = [
        'q' => $search,
        'cemetery_site_id' => $selectedSiteId > 0 ? $selectedSiteId : '',
        'cemetery_service_type_id' => $selectedServiceTypeId > 0 ? $selectedServiceTypeId : '',
    ];
@endphp

<div
    class="csl-page"
    data-server-rendered-page="cemetery_services"
    data-page-title="Service Logs"
    data-old-form-mode="{{ old('form_mode', '') }}"
    data-old-form-service-log-id="{{ old('form_service_log_id', '') }}"
    data-has-errors="{{ $errors->any() ? '1' : '0' }}">
    <section class="csl-hero">
        <div>
            <h2>Service Overview</h2>
        </div>
        <button type="button" id="openCreateServiceLogBtn" class="csl-add-btn">
            <i class="fa-solid fa-plus"></i> Add Service Log
        </button>
    </section>

    <section class="csl-stats">
        <article class="csl-stat"><span>Total Service Logs</span><strong>{{ number_format((int) $summary['total_logs']) }}</strong></article>
        <article class="csl-stat"><span>Services Today</span><strong>{{ number_format((int) $summary['today_logs']) }}</strong></article>
        <article class="csl-stat"><span>Interment Logs</span><strong>{{ number_format((int) $summary['interment_logs']) }}</strong></article>
        <article class="csl-stat"><span>Burial Logs</span><strong>{{ number_format((int) $summary['burial_logs']) }}</strong></article>
        <article class="csl-stat"><span>Exhumation Logs</span><strong>{{ number_format((int) $summary['exhumation_logs']) }}</strong></article>
    </section>

    @if (session('status'))
        <div class="alert alert-success" style="margin:0;"><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" style="margin:0;"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>
    @endif

    <section class="csl-card">
        <div class="csl-card-head">
            <h3>Service Log List</h3>
            <form id="cslAutoFilterForm" method="GET" action="{{ route('cemetery.services') }}" class="csl-filter-grid">
                <input id="cslAutoSearch" type="search" name="q" class="csl-control" placeholder="Search deceased, niche/lot, log no, processed by..." value="{{ $search }}">
                <select name="cemetery_site_id" class="csl-control">
                    <option value="">All Cemeteries</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" @selected((string) $selectedSiteId === (string) $site->id)>{{ $site->site_name }}</option>
                    @endforeach
                </select>
                <select name="cemetery_service_type_id" class="csl-control">
                    <option value="">All Service Types</option>
                    @foreach($serviceTypes as $serviceType)
                        <option value="{{ $serviceType->id }}" @selected((string) $selectedServiceTypeId === (string) $serviceType->id)>{{ $serviceType->type_name }}</option>
                    @endforeach
                </select>
                <a href="{{ route('cemetery.services.csv', $exportQuery) }}" class="csl-btn csl-btn-export"><i class="fa-solid fa-file-csv"></i> Export CSV</a>
            </form>
        </div>

        <div class="csl-table-wrap">
            <table class="csl-table">
                <thead>
                    <tr>
                        <th>Log No.</th>
                        <th>Service Date</th>
                        <th>Deceased Name</th>
                        <th>Cemetery</th>
                        <th>Service Type</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($serviceLogs as $serviceLog)
                        <tr>
                            <td><strong>{{ $serviceLog->log_no }}</strong></td>
                            <td>{{ optional($serviceLog->service_date)->format('Y-m-d') }}</td>
                            <td>{{ $serviceLog->deceased_name }}</td>
                            <td>{{ $serviceLog->site?->site_name ?: '-' }}</td>
                            <td>{{ $serviceLog->serviceType?->type_name ?: '-' }}</td>
                            <td style="text-align:right;">
                                <div class="csl-actions" style="justify-content:flex-end;">
                                    <button
                                        type="button"
                                        class="csl-icon-btn js-open-view-service-log-btn"
                                        data-log-no="{{ $serviceLog->log_no }}"
                                        data-service-date="{{ optional($serviceLog->service_date)->format('Y-m-d') }}"
                                        data-deceased-name="{{ $serviceLog->deceased_name }}"
                                        data-plot-reference="{{ $serviceLog->plot_reference }}"
                                        data-site-name="{{ $serviceLog->site?->site_name }}"
                                        data-service-type-name="{{ $serviceLog->serviceType?->type_name }}"
                                        data-details="{{ $serviceLog->details }}"
                                        data-processed-by="{{ $serviceLog->processed_by }}"
                                        data-remarks="{{ $serviceLog->remarks }}"
                                        title="View service log">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="csl-icon-btn js-open-edit-service-log-btn"
                                        data-service-log-id="{{ $serviceLog->id }}"
                                        data-log-no="{{ $serviceLog->log_no }}"
                                        data-service-date="{{ optional($serviceLog->service_date)->format('Y-m-d') }}"
                                        data-site-id="{{ $serviceLog->cemetery_site_id }}"
                                        data-deceased-name="{{ $serviceLog->deceased_name }}"
                                        data-plot-reference="{{ $serviceLog->plot_reference }}"
                                        data-service-type-id="{{ $serviceLog->cemetery_service_type_id }}"
                                        data-details="{{ $serviceLog->details }}"
                                        data-processed-by="{{ $serviceLog->processed_by }}"
                                        data-remarks="{{ $serviceLog->remarks }}"
                                        title="Edit service log">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST" action="{{ route('cemetery.services.destroy', $serviceLog) }}" class="js-delete-service-log-form" data-log-no="{{ $serviceLog->log_no }}" data-deceased="{{ $serviceLog->deceased_name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="csl-icon-btn csl-icon-btn-danger" title="Delete service log"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding:1.4rem;">No service logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($serviceLogs->hasPages())
            <div class="csl-pagination">
                @if ($serviceLogs->previousPageUrl())
                    <a class="csl-page-link" href="{{ $serviceLogs->previousPageUrl() }}">Previous</a>
                @else
                    <span class="csl-page-link disabled">Previous</span>
                @endif
                @if ($serviceLogs->nextPageUrl())
                    <a class="csl-page-link" href="{{ $serviceLogs->nextPageUrl() }}">Next</a>
                @else
                    <span class="csl-page-link disabled">Next</span>
                @endif
            </div>
        @endif
    </section>
</div>

<div id="createServiceLogModal" class="csl-modal" aria-hidden="true">
    <div class="csl-modal-card">
        <div class="csl-modal-head">
            <h4>Add Service Log</h4>
            <button type="button" class="csl-modal-close" data-close-modal="createServiceLogModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="createServiceLogForm" method="POST" action="{{ route('cemetery.services.store') }}">
            @csrf
            <input type="hidden" name="form_mode" value="create">
            <div class="csl-modal-body">
                @include('cemetery.partials.service_log_form_fields', [
                    'prefix' => 'newSvc',
                    'sites' => $sites,
                    'serviceTypes' => $serviceTypes,
                    'logNoValue' => $nextLogNo,
                    'processedByValue' => $defaultProcessedBy,
                ])
            </div>
            <div class="csl-modal-foot">
                <button type="button" class="csl-btn csl-btn-secondary" data-close-modal="createServiceLogModal">Cancel</button>
                <button type="submit" class="csl-btn csl-btn-primary">Save Service Log</button>
            </div>
        </form>
    </div>
</div>

<div id="editServiceLogModal" class="csl-modal" aria-hidden="true">
    <div class="csl-modal-card">
        <div class="csl-modal-head">
            <h4>Edit Service Log</h4>
            <button type="button" class="csl-modal-close" data-close-modal="editServiceLogModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editServiceLogForm" method="POST" action="" data-action-template="{{ route('cemetery.services.update', '__ID__') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_mode" value="edit">
            <input type="hidden" name="form_service_log_id" id="editFormServiceLogId" value="{{ old('form_service_log_id') }}">
            <div class="csl-modal-body">
                @include('cemetery.partials.service_log_form_fields', [
                    'prefix' => 'editSvc',
                    'sites' => $sites,
                    'serviceTypes' => $serviceTypes,
                    'logNoValue' => old('log_no'),
                    'processedByValue' => old('processed_by'),
                ])
            </div>
            <div class="csl-modal-foot">
                <button type="button" class="csl-btn csl-btn-secondary" data-close-modal="editServiceLogModal">Cancel</button>
                <button type="submit" class="csl-btn csl-btn-primary">Update Service Log</button>
            </div>
        </form>
    </div>
</div>

<div id="viewServiceLogModal" class="csl-modal" aria-hidden="true">
    <div class="csl-modal-card" style="width: min(720px, 96vw);">
        <div class="csl-modal-head">
            <h4>Service Log Details</h4>
            <button type="button" class="csl-modal-close" data-close-modal="viewServiceLogModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="csl-modal-body">
            <div class="csl-view-grid">
                <div class="csl-view-item"><span>Log No.</span><strong id="viewSvcLogNo">-</strong></div>
                <div class="csl-view-item"><span>Service Date</span><strong id="viewSvcServiceDate">-</strong></div>
                <div class="csl-view-item"><span>Deceased Name</span><strong id="viewSvcDeceasedName">-</strong></div>
                <div class="csl-view-item"><span>Niche / Lot No.</span><strong id="viewSvcPlotReference">-</strong></div>
                <div class="csl-view-item"><span>Cemetery</span><strong id="viewSvcSiteName">-</strong></div>
                <div class="csl-view-item"><span>Service Type</span><strong id="viewSvcServiceTypeName">-</strong></div>
                <div class="csl-view-item"><span>Processed By</span><strong id="viewSvcProcessedBy">-</strong></div>
                <div class="csl-view-item csl-view-full"><span>Details</span><strong id="viewSvcDetails">-</strong></div>
                <div class="csl-view-item csl-view-full"><span>Remarks</span><strong id="viewSvcRemarks">-</strong></div>
            </div>
        </div>
        <div class="csl-modal-foot">
            <button type="button" class="csl-btn csl-btn-secondary" data-close-modal="viewServiceLogModal">Close</button>
        </div>
    </div>
</div>

<div id="deleteServiceLogModal" class="csl-modal" aria-hidden="true">
    <div class="csl-modal-card csl-modal-card-compact">
        <div class="csl-modal-head">
            <h4>Delete Service Log</h4>
            <button type="button" class="csl-modal-close" data-close-modal="deleteServiceLogModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="csl-modal-body">
            <p style="margin:0;">Are you sure you want to delete this service log?</p>
            <div style="margin-top:12px; border:1px solid #e2e8f0; border-radius:10px; padding:10px 12px; background:#f8fafc;">
                <div><strong id="deleteServiceLogNo">-</strong></div>
                <div>Deceased: <span id="deleteServiceLogDeceased">-</span></div>
            </div>
        </div>
        <div class="csl-modal-foot">
            <button type="button" class="csl-btn csl-btn-secondary" data-close-modal="deleteServiceLogModal">Cancel</button>
            <button type="button" class="csl-btn csl-btn-primary" id="confirmDeleteServiceLogBtn">Yes, Delete</button>
        </div>
    </div>
</div>

<script>
(() => {
    const page = document.querySelector('.csl-page[data-server-rendered-page="cemetery_services"]');
    const createModal = document.getElementById('createServiceLogModal');
    const editModal = document.getElementById('editServiceLogModal');
    const deleteModal = document.getElementById('deleteServiceLogModal');
    const viewModal = document.getElementById('viewServiceLogModal');
    const openCreateButton = document.getElementById('openCreateServiceLogBtn');
    const closeButtons = Array.from(document.querySelectorAll('[data-close-modal]'));
    const editForm = document.getElementById('editServiceLogForm');
    const editActionTemplate = editForm ? (editForm.dataset.actionTemplate || '') : '';
    const confirmDeleteButton = document.getElementById('confirmDeleteServiceLogBtn');
    const deleteLogNo = document.getElementById('deleteServiceLogNo');
    const deleteDeceased = document.getElementById('deleteServiceLogDeceased');
    const oldFormMode = page?.dataset.oldFormMode || '';
    const oldFormServiceLogId = page?.dataset.oldFormServiceLogId || '';
    const hasErrors = (page?.dataset.hasErrors || '0') === '1';
    const autoFilterForm = document.getElementById('cslAutoFilterForm');
    const autoSearchInput = document.getElementById('cslAutoSearch');
    let pendingDeleteForm = null;

    const allModals = [createModal, editModal, deleteModal, viewModal].filter(Boolean);

    if (autoFilterForm) {
        const filterSelects = Array.from(autoFilterForm.querySelectorAll('select'));
        filterSelects.forEach((select) => {
            select.addEventListener('change', () => autoFilterForm.submit());
        });

        if (autoSearchInput) {
            let searchTimer = null;
            autoSearchInput.addEventListener('input', () => {
                if (searchTimer) clearTimeout(searchTimer);
                searchTimer = setTimeout(() => autoFilterForm.submit(), 350);
            });

            autoSearchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (searchTimer) clearTimeout(searchTimer);
                    autoFilterForm.submit();
                }
            });
        }
    }

    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (!el) return;
        const text = (value ?? '').toString().trim();
        el.textContent = text === '' ? '-' : text;
    };

    const openViewFromButton = (button) => {
        setText('viewSvcLogNo', button.dataset.logNo);
        setText('viewSvcServiceDate', button.dataset.serviceDate);
        setText('viewSvcDeceasedName', button.dataset.deceasedName);
        setText('viewSvcPlotReference', button.dataset.plotReference);
        setText('viewSvcSiteName', button.dataset.siteName);
        setText('viewSvcServiceTypeName', button.dataset.serviceTypeName);
        setText('viewSvcProcessedBy', button.dataset.processedBy);
        setText('viewSvcDetails', button.dataset.details);
        setText('viewSvcRemarks', button.dataset.remarks);
        openModal(viewModal);
    };

    const lockBody = () => {
        const hasOpenModal = allModals.some((modal) => modal.classList.contains('is-open'));
        document.body.classList.toggle('csl-lock-scroll', hasOpenModal);
    };

    const openModal = (modal) => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        const body = modal.querySelector('.csl-modal-body');
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

    const toMoneyLabel = (value) => {
        const amount = Number(value || 0);
        const safeAmount = Number.isFinite(amount) ? amount : 0;
        return `PHP ${safeAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    };

    const serviceTypeToSuggestion = (serviceTypeCode, siteCode) => {
        const code = String(serviceTypeCode || '').toUpperCase();
        const site = String(siteCode || '').toUpperCase();

        if (code === 'INTERMENT') {
            if (site === 'SJM') {
                return {
                    transactionTypeName: 'Single Niche Purchase (Regular)',
                    suggestedAmount: toMoneyLabel(10000),
                    breakdown: 'San Jose Memorial: regular single niche PHP 10,000.00 (infant single niche PHP 5,000.00).',
                };
            }
            if (site === 'NMC') {
                return {
                    transactionTypeName: 'Single Niche Purchase',
                    suggestedAmount: toMoneyLabel(5000),
                    breakdown: 'New Municipal Cemetery: single niche for Columbarium/Infant is PHP 5,000.00.',
                };
            }
            if (site === 'SPMC') {
                return {
                    transactionTypeName: 'Lot Purchase',
                    suggestedAmount: toMoneyLabel(10300),
                    breakdown: 'San Pedro Memorial: Lot PHP 10,000.00 + Burial Permit PHP 300.00.',
                };
            }
            if (site === 'OMC') {
                return {
                    transactionTypeName: 'Additional Burial',
                    suggestedAmount: toMoneyLabel(5300),
                    breakdown: 'Old Municipal Cemetery: Additional Burial PHP 5,000.00 + Burial Permit PHP 300.00.',
                };
            }
            return {
                transactionTypeName: 'Single Niche / Lot Purchase',
                suggestedAmount: toMoneyLabel(0),
                breakdown: 'Select a cemetery to show the exact interment fee.',
            };
        }

        if (code === 'BURIAL') {
            if (['OMC', 'NMC', 'SPMC'].includes(site)) {
                return {
                    transactionTypeName: 'Additional Burial',
                    suggestedAmount: toMoneyLabel(5300),
                    breakdown: 'Additional Burial PHP 5,000.00 + Burial Permit PHP 300.00.',
                };
            }
            if (site === 'SJM') {
                return {
                    transactionTypeName: 'Burial Permit',
                    suggestedAmount: toMoneyLabel(300),
                    breakdown: 'San Jose Memorial: Burial Permit standard fee PHP 300.00.',
                };
            }
            return {
                transactionTypeName: 'Burial Service',
                suggestedAmount: toMoneyLabel(300),
                breakdown: 'Burial Permit standard fee is PHP 300.00.',
            };
        }

        if (code === 'RENEWAL') {
            return {
                transactionTypeName: 'Maintenance Fee (5-year)',
                suggestedAmount: toMoneyLabel(1500),
                breakdown: '5-year maintenance fee is PHP 1,500.00.',
            };
        }

        if (code === 'MAINTENANCE_UPDATE') {
            return {
                transactionTypeName: 'Maintenance Fee (5-year)',
                suggestedAmount: toMoneyLabel(1500),
                breakdown: '5-year fixed maintenance PHP 1,500.00 (yearly option PHP 300.00/year).',
            };
        }

        if (code === 'EXHUMATION') {
            return {
                transactionTypeName: 'Exhumation',
                suggestedAmount: toMoneyLabel(200),
                breakdown: 'Exhumation (Kalkal) fee is PHP 200.00.',
            };
        }

        if (code === 'TRANSFER') {
            return {
                transactionTypeName: 'Transfer',
                suggestedAmount: toMoneyLabel(300),
                breakdown: 'Transfer service base fee PHP 300.00 (add up to PHP 200.00 extra if applicable).',
            };
        }

        if (code === 'RECORD_CORRECTION') {
            return {
                transactionTypeName: 'Other Cemetery Service',
                suggestedAmount: toMoneyLabel(300),
                breakdown: 'Record correction base fee PHP 300.00 (add up to PHP 200.00 extra if applicable).',
            };
        }

        return {
            transactionTypeName: '',
            suggestedAmount: 'PHP 0.00',
            breakdown: 'Choose Service Type to show suggested amount.',
        };
    };

    const computeSuggestedRate = (prefix) => {
        const siteField = document.getElementById(`${prefix}Site`);
        const serviceTypeField = document.getElementById(`${prefix}ServiceType`);
        const suggestedTypeField = document.getElementById(`${prefix}SuggestedTransactionType`);
        const suggestedAmountField = document.getElementById(`${prefix}SuggestedAmountDue`);
        const suggestedBreakdownField = document.getElementById(`${prefix}SuggestedBreakdown`);

        if (!siteField || !serviceTypeField || !suggestedTypeField || !suggestedAmountField || !suggestedBreakdownField) return;

        const selectedSite = siteField.options[siteField.selectedIndex];
        const selectedServiceType = serviceTypeField.options[serviceTypeField.selectedIndex];
        const siteCode = String(selectedSite?.dataset.siteCode || '').toUpperCase();
        const serviceTypeCode = String(selectedServiceType?.dataset.serviceTypeCode || '').toUpperCase();

        if (!selectedServiceType?.value) {
            setValue(`${prefix}SuggestedTransactionType`, '');
            setValue(`${prefix}SuggestedAmountDue`, 'PHP 0.00');
            setValue(`${prefix}SuggestedBreakdown`, 'Choose Service Type to show suggested amount.');
            return;
        }

        const suggestion = serviceTypeToSuggestion(serviceTypeCode, siteCode);
        setValue(`${prefix}SuggestedTransactionType`, suggestion.transactionTypeName);
        setValue(`${prefix}SuggestedAmountDue`, suggestion.suggestedAmount);
        setValue(`${prefix}SuggestedBreakdown`, suggestion.breakdown);
    };

    const bindSuggestedRate = (prefix) => {
        const siteField = document.getElementById(`${prefix}Site`);
        const serviceTypeField = document.getElementById(`${prefix}ServiceType`);

        if (siteField) {
            siteField.addEventListener('change', () => computeSuggestedRate(prefix));
        }
        if (serviceTypeField) {
            serviceTypeField.addEventListener('change', () => computeSuggestedRate(prefix));
        }

        computeSuggestedRate(prefix);
    };

    const openEditFromButton = (button) => {
        if (!editForm) return;

        const serviceLogId = button.dataset.serviceLogId || '';
        editForm.action = editActionTemplate.replace('__ID__', serviceLogId);
        setValue('editFormServiceLogId', serviceLogId);

        setValue('editSvcLogNo', button.dataset.logNo);
        setValue('editSvcServiceDate', button.dataset.serviceDate);
        setValue('editSvcSite', button.dataset.siteId || '');
        setValue('editSvcServiceType', button.dataset.serviceTypeId);
        setValue('editSvcDeceasedName', button.dataset.deceasedName || '');
        setValue('editSvcPlotReference', button.dataset.plotReference || '');
        setValue('editSvcDetails', button.dataset.details);
        setValue('editSvcProcessedBy', button.dataset.processedBy);
        setValue('editSvcRemarks', button.dataset.remarks);
        computeSuggestedRate('editSvc');

        openModal(editModal);
    };

    bindSuggestedRate('newSvc');
    bindSuggestedRate('editSvc');

    if (openCreateButton) {
        openCreateButton.addEventListener('click', () => {
            computeSuggestedRate('newSvc');
            openModal(createModal);
        });
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
        const viewButton = event.target.closest('.js-open-view-service-log-btn');
        if (viewButton) {
            event.preventDefault();
            openViewFromButton(viewButton);
            return;
        }

        const editButton = event.target.closest('.js-open-edit-service-log-btn');
        if (editButton) {
            event.preventDefault();
            openEditFromButton(editButton);
            return;
        }

        const deleteForm = event.target.closest('.js-delete-service-log-form');
        if (!deleteForm) return;
        if (deleteForm.dataset.confirmed === '1') {
            deleteForm.dataset.confirmed = '0';
            return;
        }

        event.preventDefault();
        pendingDeleteForm = deleteForm;
        if (deleteLogNo) deleteLogNo.textContent = deleteForm.dataset.logNo || '-';
        if (deleteDeceased) deleteDeceased.textContent = deleteForm.dataset.deceased || '-';
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
        closeModal(viewModal);
    });

    if (hasErrors) {
        if (oldFormMode === 'edit' && editForm) {
            const serviceLogId = String(oldFormServiceLogId || '').trim();
            if (serviceLogId !== '') {
                editForm.action = editActionTemplate.replace('__ID__', serviceLogId);
                setValue('editFormServiceLogId', serviceLogId);
            }
            computeSuggestedRate('editSvc');
            openModal(editModal);
        } else {
            computeSuggestedRate('newSvc');
            openModal(createModal);
        }
    }
})();
</script>
@endsection
