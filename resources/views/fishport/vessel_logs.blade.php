@extends('layouts.app')

@section('content')
<style>
    #contentArea {
        padding-top: 10px;
    }

    .vl-page {
        --vl-text: #334155;
        --vl-muted: #64748b;
        --vl-border: #e2e8f0;
        --vl-soft: #f8fafc;
        --vl-primary: #155f8f;
        --vl-primary-dark: #0f4b73;
        --vl-line: #cbd5e1;
        display: grid;
        gap: 8px;
        color: var(--vl-text);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .vl-hero-cta {
        border: 1px solid #bfd0e2;
        background: #ffffff;
        color: var(--vl-primary);
        border-radius: 10px;
        min-height: 38px;
        padding: 0 0.95rem;
        font-size: 0.9rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .vl-hero-cta:hover {
        background: #f1f7fd;
        border-color: #9fbbd5;
        color: var(--vl-primary-dark);
    }

    .vl-hero-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .vl-hero-cta-primary {
        background: var(--vl-primary);
        border-color: var(--vl-primary);
        color: #fff;
    }

    .vl-hero-cta-primary:hover {
        background: var(--vl-primary-dark);
        border-color: var(--vl-primary-dark);
        color: #fff;
    }

    .vl-hero-cta-secondary {
        background: #ffffff;
    }

    .vl-card {
        border: 1px solid var(--vl-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .vl-card-head {
        border-bottom: 1px solid var(--vl-border);
        background: #fff;
        padding: 0.78rem 1rem;
        display: grid;
        gap: 8px;
    }

    .vl-card-head-top {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .vl-card-head-main {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }

    .vl-card-head-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .vl-card-head-title > i {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: var(--vl-soft);
        color: var(--vl-primary);
        border-radius: 10px;
        border: 1px solid var(--vl-border);
        font-size: 1.1rem;
    }

    .vl-card-head h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
    }

    .vl-card-head p {
        margin: 0.12rem 0 0;
        color: var(--vl-muted);
        font-size: 0.88rem;
    }

    .vl-tabs {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px;
        background: var(--vl-soft);
        border: 1px solid var(--vl-border);
        border-radius: 12px;
        flex-wrap: wrap;
    }

    .vl-tab-btn {
        border: 0;
        background: transparent;
        color: var(--vl-muted);
        border-radius: 8px;
        min-height: 34px;
        padding: 0 1rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all .2s ease;
    }

    .vl-tab-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .vl-tab-btn.is-active {
        background: #fff;
        color: #0f172a;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .vl-tab-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        min-height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        background: #e2e8f0;
        color: #475569;
    }

    .vl-tab-btn.is-active .vl-tab-pill {
        background: var(--vl-primary);
        color: #fff;
    }

    .vl-filter-grid {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) minmax(170px, 220px);
        gap: 10px;
        align-items: center;
    }

    .vl-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .vl-input-wrap i {
        position: absolute;
        left: 12px;
        color: #94a3b8;
        font-size: 0.9em;
        pointer-events: none;
        transition: color 0.2s;
    }

    .vl-input-wrap:focus-within i {
        color: var(--vl-primary);
    }

    .vl-input,
    .vl-date {
        width: 100%;
        min-height: 40px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #fff;
        padding: 0.5rem 0.75rem;
        font-size: 0.92rem;
        color: var(--vl-text);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .vl-input {
        padding-left: 2.25rem;
    }
    
    .vl-date {
        /* standard browser date does not need left-padding for custom icon */
    }

    .vl-input:focus,
    .vl-date:focus {
        border-color: var(--vl-primary);
        box-shadow: 0 0 0 3px rgba(21, 95, 143, 0.15);
        outline: none;
    }

    .vl-table-wrap {
        overflow: auto;
    }

    .vl-table {
        width: 100%;
        border-collapse: collapse;
    }

    .vl-table thead th {
        background: #eef5fb;
        color: #103250;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 700;
        padding: 1rem 1.25rem;
        text-align: left;
    }

    .vl-table tbody td {
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        color: #334155;
        vertical-align: middle;
    }
    
    .vl-table thead th:first-child, .vl-table thead th:last-child,
    .vl-table tbody td:first-child, .vl-table tbody td:last-child {
        white-space: nowrap;
    }

    .vl-table tbody tr:nth-child(even) {
        background: #fdfdfe;
    }

    .vl-table tbody tr:hover {
        background: #f1f5f9;
    }

    .vl-badge {
        border-radius: 999px;
        padding: 0.25rem 0.65rem;
        font-size: 0.77rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .vl-badge-arr {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .vl-badge-dep {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .vl-action-row {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
    }

    .vl-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: transparent;
        border: 1px solid #e2e8f0;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .vl-action-btn:hover {
        background: #f1f5f9;
        color: #155f8f;
        border-color: #cbd5e1;
    }

    .vl-action-btn-danger:hover {
        background: #fee2e2;
        color: #dc2626;
        border-color: #fca5a5;
    }

    .vl-pagination {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 12px 16px;
    }

    .vl-page-link {
        min-height: 34px;
        padding: 0 12px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #155f8f;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }

    .vl-page-link:hover {
        background: #f1f5f9;
    }

    .vl-page-link.is-disabled {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
        pointer-events: none;
    }

    .vl-modal {
        position: fixed;
        inset: 0;
        z-index: 1600;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.56);
        backdrop-filter: blur(3px);
        padding: 16px;
    }

    .vl-modal.is-open {
        display: flex;
    }

    .vl-modal-card {
        width: min(460px, 96vw);
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .vl-modal-head {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 16px;
    }

    .vl-modal-head h4 {
        margin: 0;
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 700;
    }

    .vl-modal-body {
        padding: 16px;
        color: #334155;
        display: grid;
        gap: 12px;
    }

    .vl-modal-preview {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        padding: 12px 14px;
        font-size: 0.9rem;
    }

    .vl-modal-foot {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 12px 16px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .vl-modal-foot .btn-primary {
        background: #155f8f !important;
        border-color: #155f8f !important;
        color: #fff !important;
        opacity: 1 !important;
        box-shadow: 0 6px 14px rgba(21, 95, 143, 0.2) !important;
    }

    .vl-modal-foot .btn-primary:hover,
    .vl-modal-foot .btn-primary:focus,
    .vl-modal-foot .btn-primary:active {
        background: #0f4b73 !important;
        border-color: #0f4b73 !important;
        color: #fff !important;
        opacity: 1 !important;
    }

    .vl-modal-foot .btn-primary:disabled,
    .vl-modal-foot .btn-primary.disabled {
        background: #7fa8c5 !important;
        border-color: #7fa8c5 !important;
        color: #fff !important;
        opacity: 1 !important;
    }

    .vl-modal-card-form {
        width: min(760px, 97vw);
    }

    .vl-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .vl-form-field {
        display: grid;
        gap: 6px;
    }

    .vl-form-field.full {
        grid-column: 1 / -1;
    }

    .vl-form-field label {
        margin: 0;
        color: #23405c;
        font-size: .84rem;
        font-weight: 700;
    }

    .vl-form-input,
    .vl-form-select {
        width: 100%;
        min-height: 40px;
        border-radius: 9px;
        border: 1px solid #c9d8ea;
        background: #fff;
        color: #173754;
        padding: 0.52rem 0.7rem;
        font-size: .9rem;
    }

    .vl-form-input:focus,
    .vl-form-select:focus {
        outline: none;
        border-color: #1e6a99;
        box-shadow: 0 0 0 3px rgba(30, 106, 153, .15);
    }

    .vl-form-input:disabled {
        background: #edf2f7;
        color: #6b7280;
        cursor: not-allowed;
    }

    .vl-form-note {
        border-radius: 10px;
        border: 1px solid #cfe3f5;
        background: #f6fbff;
        padding: 10px 12px;
        color: #4b6783;
        font-size: .88rem;
        line-height: 1.4;
    }

    body.vl-lock-scroll {
        overflow: hidden;
    }

    @media (max-width: 980px) {
        .vl-filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 700px) {
        .vl-card-head-main {
            flex-direction: column;
            align-items: flex-start;
        }

        .vl-hero-actions {
            width: 100%;
        }

        .vl-filter-grid {
            grid-template-columns: 1fr;
        }

        .vl-form-grid {
            grid-template-columns: 1fr;
        }
    }

    .vl-status-toast {
        position: fixed;
        top: 18px;
        right: 18px;
        z-index: 1700;
        min-width: min(460px, calc(100vw - 36px));
        border-radius: 12px;
        border: 1px solid transparent;
        padding: 12px 14px;
        box-shadow: 0 14px 24px rgba(15, 23, 42, 0.18);
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.92rem;
        transform: translateY(0);
        opacity: 1;
        transition: opacity .22s ease, transform .22s ease;
    }

    .vl-status-toast i {
        font-size: 1rem;
    }

    .vl-status-toast.is-success {
        background: #ecfdf5;
        border-color: #86efac;
        color: #065f46;
    }

    .vl-status-toast.is-error {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #9f1239;
    }

    .vl-status-toast.is-hiding {
        opacity: 0;
        transform: translateY(-10px);
    }
</style>

<div class="vl-page" data-server-rendered-page="vessels" data-page-title="Vessel Logs">
    @if (session('status'))
        <div id="vlStatusToast" class="vl-status-toast is-success" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('status') }}</span>
        </div>
    @elseif ($errors->any())
        <div id="vlStatusToast" class="vl-status-toast is-error" role="alert" aria-live="assertive">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <section class="vl-card" id="vesselLogsCard">
        <div class="vl-card-head">
            <div class="vl-card-head-top">
                <div class="vl-card-head-main">
                    <div class="vl-card-head-title">
                        <i class="fa-solid fa-list-check"></i>
                        <div>
                            <h3>Log Ledger</h3>
                            <p>Showing {{ $rangeLabel }} data. Edit/delete logs here, or open full transaction form.</p>
                        </div>
                    </div>
                    <div class="vl-hero-actions">
                        <button type="button" class="vl-hero-cta vl-hero-cta-primary" id="openQuickLogBtn">
                            <i class="fas fa-plus"></i> Log Vessel First
                        </button>
                        <a class="vl-hero-cta vl-hero-cta-secondary" href="{{ route('fishport.records') }}">
                            <i class="fas fa-file-invoice"></i> Go To Transaction
                        </a>
                    </div>
                </div>
                <div class="vl-tabs">
                    @php
                        $tabLabels = [
                            'all' => 'All',
                            'today' => 'Today',
                            'week' => 'This Week',
                            'month' => 'This Month',
                        ];
                    @endphp
                    @foreach ($tabLabels as $tabKey => $tabLabel)
                        <button
                            type="button"
                            class="vl-tab-btn {{ $period === $tabKey ? 'is-active' : '' }}"
                            data-period-tab="{{ $tabKey }}"
                        >
                            {{ $tabLabel }}
                            <span class="vl-tab-pill">{{ $counts[$tabKey] ?? 0 }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <form id="vesselLogsFilterForm" method="GET" action="{{ route('fishport.vessel_logs') }}" class="vl-filter-grid">
                <input type="hidden" id="vlPeriodInput" name="period" value="{{ $period }}">
                <div class="vl-input-wrap">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input class="vl-input" type="text" name="q" value="{{ $search }}" placeholder="Search log no., vessel, origin, ...">
                </div>
                <div class="vl-input-wrap">
                    <input class="vl-date" type="date" name="date" value="{{ $date }}" title="Filter by specific date">
                </div>
            </form>
        </div>

        <div class="vl-table-wrap">
            <table class="vl-table">
                <thead>
                    <tr>
                        <th>Log No.</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Vessel</th>
                        <th>ARR/DEP</th>
                        <th>Origin</th>
                        <th>Encoder</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php
                            $rowEncoderName = trim((string) ($log->user?->name ?? ''));
                            if ($rowEncoderName === '' || strcasecmp($rowEncoderName, 'System Admin') === 0) {
                                $rowEncoderName = trim((string) (auth()->user()?->name ?? 'Fishport Personnel'));
                            }
                        @endphp
                        <tr>
                            <td><strong>{{ $log->log_number }}</strong></td>
                            <td>{{ optional($log->log_date)->format('Y-m-d') }}</td>
                            <td>{{ substr((string) $log->log_time, 0, 5) }}</td>
                            <td>{{ $log->vessel?->name ?? '-' }}</td>
                            <td>
                                <span class="vl-badge {{ $log->arr_dep === 'ARR' ? 'vl-badge-arr' : 'vl-badge-dep' }}">
                                    {{ $log->arr_dep }}
                                </span>
                            </td>
                            <td>{{ $log->origin?->name ?? '-' }}</td>
                            <td>{{ $rowEncoderName }}</td>
                            <td>
                                <div class="vl-action-row">
                                    <button
                                        type="button"
                                        class="vl-action-btn js-vl-edit-btn"
                                        title="Edit Log"
                                        data-log-id="{{ $log->id }}"
                                        data-log-date="{{ optional($log->log_date)->format('Y-m-d') }}"
                                        data-log-time="{{ substr((string) $log->log_time, 0, 5) }}"
                                        data-arr-dep="{{ $log->arr_dep }}"
                                        data-vessel-id="{{ $log->fishport_vessel_id }}"
                                        data-origin-id="{{ $log->fishport_origin_id }}"
                                        data-remarks="{{ $log->remarks ?? '' }}"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form
                                        action="{{ route('fishport.records.destroy', $log) }}"
                                        method="POST"
                                        class="js-vl-delete-form"
                                        data-log-number="{{ $log->log_number }}"
                                        data-vessel-name="{{ $log->vessel?->name ?? '-' }}"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="vl-action-btn vl-action-btn-danger" type="submit" title="Delete Log">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:1.6rem;">No vessel logs found for this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="vl-pagination">
                @if ($logs->previousPageUrl())
                    <a class="vl-page-link" href="{{ $logs->previousPageUrl() }}">Previous</a>
                @else
                    <span class="vl-page-link is-disabled">Previous</span>
                @endif
                @if ($logs->nextPageUrl())
                    <a class="vl-page-link" href="{{ $logs->nextPageUrl() }}">Next</a>
                @else
                    <span class="vl-page-link is-disabled">Next</span>
                @endif
            </div>
        @endif
    </section>
</div>

<div id="vlQuickLogModal" class="vl-modal" aria-hidden="true">
    <div class="vl-modal-card vl-modal-card-form">
        <div class="vl-modal-head">
            <h4>Quick Vessel Log (No Payment Yet)</h4>
        </div>
        <form method="POST" action="{{ route('fishport.vessel_logs.store') }}">
            @csrf
            <div class="vl-modal-body">
                <div class="vl-form-grid">
                    <div class="vl-form-field">
                        <label for="quickLogDate">Log Date</label>
                        <input id="quickLogDate" name="log_date" type="date" class="vl-form-input" value="{{ old('log_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="vl-form-field">
                        <label for="quickLogTime">Log Time</label>
                        <input id="quickLogTime" name="log_time" type="time" class="vl-form-input" value="{{ old('log_time', now()->format('H:i')) }}" required>
                    </div>
                    <div class="vl-form-field">
                        <label for="quickArrDep">Movement</label>
                        <select id="quickArrDep" name="arr_dep" class="vl-form-select" required>
                            <option value="ARR" {{ old('arr_dep', 'ARR') === 'ARR' ? 'selected' : '' }}>Arrival</option>
                            <option value="DEP" {{ old('arr_dep') === 'DEP' ? 'selected' : '' }}>Departure</option>
                        </select>
                    </div>
                    <div class="vl-form-field">
                        <label for="quickVesselId">Vessel</label>
                        <select id="quickVesselId" name="vessel_id" class="vl-form-select" required>
                            <option value="">Select registered vessel</option>
                            @foreach ($vessels as $vessel)
                                <option value="{{ $vessel->id }}" {{ (string) old('vessel_id') === (string) $vessel->id ? 'selected' : '' }}>{{ $vessel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="vl-form-field full">
                        <label for="quickOriginId">Origin</label>
                        <select id="quickOriginId" name="origin_id" class="vl-form-select">
                            <option value="">Select origin</option>
                            @foreach ($origins as $origin)
                                <option value="{{ $origin->id }}" {{ (string) old('origin_id') === (string) $origin->id ? 'selected' : '' }}>{{ $origin->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="vl-form-field full">
                        <label for="quickOriginName">Or Type Custom Origin</label>
                        <input id="quickOriginName" name="origin_name" type="text" class="vl-form-input" value="{{ old('origin_name') }}" placeholder="Type origin if not in dropdown">
                    </div>
                    <div class="vl-form-field full">
                        <label for="quickRemarks">Remarks</label>
                        <input id="quickRemarks" name="remarks" type="text" class="vl-form-input" value="{{ old('remarks') }}" placeholder="Optional notes before transaction encoding">
                    </div>
                </div>
            </div>
            <div class="vl-modal-foot">
                <button type="button" class="btn btn-secondary" id="vlCancelQuickLogBtn">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Vessel Log</button>
            </div>
        </form>
    </div>
</div>

<div id="vlEditLogModal" class="vl-modal" aria-hidden="true">
    <div class="vl-modal-card vl-modal-card-form">
        <div class="vl-modal-head">
            <h4>Edit Vessel Log</h4>
        </div>
        <form method="POST" id="vlEditLogForm" action="" data-action-template="{{ route('fishport.vessel_logs.update', '__LOG_ID__') }}">
            @csrf
            @method('PATCH')
            <div class="vl-modal-body">
                <div class="vl-form-note">
                    Update vessel log details here. This stays in Vessel Logs and does not redirect to Transactions.
                </div>
                <div class="vl-form-grid">
                    <div class="vl-form-field">
                        <label for="editLogDate">Log Date</label>
                        <input id="editLogDate" name="log_date" type="date" class="vl-form-input" required>
                    </div>
                    <div class="vl-form-field">
                        <label for="editLogTime">Log Time</label>
                        <input id="editLogTime" name="log_time" type="time" class="vl-form-input" required>
                    </div>
                    <div class="vl-form-field">
                        <label for="editArrDep">Movement</label>
                        <select id="editArrDep" name="arr_dep" class="vl-form-select" required>
                            <option value="ARR">Arrival</option>
                            <option value="DEP">Departure</option>
                        </select>
                    </div>
                    <div class="vl-form-field">
                        <label for="editVesselId">Vessel</label>
                        <select id="editVesselId" name="vessel_id" class="vl-form-select" required>
                            <option value="">Select registered vessel</option>
                            @foreach ($vessels as $vessel)
                                <option value="{{ $vessel->id }}">{{ $vessel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="vl-form-field full">
                        <label for="editOriginId">Origin</label>
                        <select id="editOriginId" name="origin_id" class="vl-form-select">
                            <option value="">Select origin</option>
                            @foreach ($origins as $origin)
                                <option value="{{ $origin->id }}">{{ $origin->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="vl-form-field full">
                        <label for="editOriginName">Or Type Custom Origin</label>
                        <input id="editOriginName" name="origin_name" type="text" class="vl-form-input" placeholder="Type origin if not in dropdown">
                    </div>
                    <div class="vl-form-field full">
                        <label for="editRemarks">Remarks</label>
                        <input id="editRemarks" name="remarks" type="text" class="vl-form-input" placeholder="Optional notes">
                    </div>
                </div>
            </div>
            <div class="vl-modal-foot">
                <button type="button" class="btn btn-secondary" id="vlCancelEditLogBtn">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Vessel Log</button>
            </div>
        </form>
    </div>
</div>

<div id="vlDeleteModal" class="vl-modal" aria-hidden="true">
    <div class="vl-modal-card">
        <div class="vl-modal-head">
            <h4>Delete Vessel Log</h4>
        </div>
        <div class="vl-modal-body">
            <p style="margin:0;">Are you sure you want to delete this log entry?</p>
            <div class="vl-modal-preview">
                <div><strong id="vlDeleteLogNo">-</strong></div>
                <div>Vessel: <span id="vlDeleteVesselName">-</span></div>
            </div>
        </div>
        <div class="vl-modal-foot">
            <button type="button" class="btn btn-secondary" id="vlCancelDeleteBtn">Cancel</button>
            <button type="button" class="btn btn-danger" id="vlConfirmDeleteBtn">Yes, Delete</button>
        </div>
    </div>
</div>

@php
    $shouldOpenQuickLogModal = $errors->has('log_date')
        || $errors->has('log_time')
        || $errors->has('arr_dep')
        || $errors->has('vessel_id')
        || $errors->has('origin_id')
        || $errors->has('origin_name')
        || $errors->has('remarks');
    $vesselLogsBootstrap = [
        'todayLoggedMovementsByVessel' => collect($todayLogsByVesselMovement ?? [])->mapWithKeys(static function ($movements, $vesselId) {
            return [(string) $vesselId => collect($movements)->values()->all()];
        })->all(),
        'todayDate' => now()->format('Y-m-d'),
        'shouldOpenQuickLogModal' => $shouldOpenQuickLogModal,
        'filterFormAction' => route('fishport.vessel_logs'),
    ];
@endphp
<script id="vesselLogsBootstrap" type="application/json">@json($vesselLogsBootstrap)</script>
<script>
(() => {
    const breadcrumb = document.querySelector('.breadcrumb');
    if (breadcrumb) breadcrumb.hidden = false;
    const titleEl = document.getElementById('pageTitle');
    if (titleEl) titleEl.textContent = 'Vessel Logs';

    const statusToast = document.getElementById('vlStatusToast');
    const bootstrapNode = document.getElementById('vesselLogsBootstrap');
    const bootstrap = bootstrapNode ? JSON.parse(bootstrapNode.textContent || '{}') : {};
    const quickLogModal = document.getElementById('vlQuickLogModal');
    const editLogModal = document.getElementById('vlEditLogModal');
    const deleteModal = document.getElementById('vlDeleteModal');
    const cancelQuickLogBtn = document.getElementById('vlCancelQuickLogBtn');
    const editLogForm = document.getElementById('vlEditLogForm');
    const editLogActionTemplate = editLogForm ? (editLogForm.dataset.actionTemplate || '') : '';
    const cancelEditLogBtn = document.getElementById('vlCancelEditLogBtn');
    const editLogDate = document.getElementById('editLogDate');
    const editLogTime = document.getElementById('editLogTime');
    const editArrDep = document.getElementById('editArrDep');
    const editVesselId = document.getElementById('editVesselId');
    const editOriginId = document.getElementById('editOriginId');
    const editOriginName = document.getElementById('editOriginName');
    const editRemarks = document.getElementById('editRemarks');
    const quickLogDateInput = document.getElementById('quickLogDate');
    const quickArrDepSelect = document.getElementById('quickArrDep');
    const quickVesselSelect = document.getElementById('quickVesselId');
    const quickOriginId = document.getElementById('quickOriginId');
    const quickOriginName = document.getElementById('quickOriginName');
    const todayLoggedMovementsByVessel = bootstrap.todayLoggedMovementsByVessel
        && typeof bootstrap.todayLoggedMovementsByVessel === 'object'
        ? bootstrap.todayLoggedMovementsByVessel
        : {};
    const todayDate = String(bootstrap.todayDate || '');
    const deleteLogNo = document.getElementById('vlDeleteLogNo');
    const deleteVesselName = document.getElementById('vlDeleteVesselName');
    const cancelDeleteBtn = document.getElementById('vlCancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('vlConfirmDeleteBtn');
    const shouldOpenQuickLogModal = Boolean(bootstrap.shouldOpenQuickLogModal);
    const allModals = [quickLogModal, editLogModal, deleteModal].filter(Boolean);
    const filterFormAction = String(bootstrap.filterFormAction || '');
    let liveFilterTimer = null;
    let activeFilterRequestId = 0;
    let pendingDeleteForm = null;

    const syncBodyLock = () => {
        const hasOpenModal = allModals.some((modal) => modal.classList.contains('is-open'));
        document.body.classList.toggle('vl-lock-scroll', hasOpenModal);
    };

    const openModal = (modal) => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        syncBodyLock();
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        syncBodyLock();
    };

    const currentFilterNodes = () => {
        const form = document.getElementById('vesselLogsFilterForm');
        return {
            form,
            periodInput: document.getElementById('vlPeriodInput'),
            searchInput: form ? form.querySelector('input[name="q"]') : null,
            dateInput: form ? form.querySelector('input[name="date"]') : null,
            tabButtons: Array.from(document.querySelectorAll('[data-period-tab]')),
        };
    };

    const captureFilterInputState = () => {
        const { periodInput, searchInput, dateInput } = currentFilterNodes();
        const active = document.activeElement;
        const focusName = active === searchInput ? 'search' : (active === dateInput ? 'date' : null);
        return {
            period: periodInput ? periodInput.value : 'today',
            search: searchInput ? searchInput.value : '',
            date: dateInput ? dateInput.value : '',
            focusName,
            start: focusName && typeof active.selectionStart === 'number' ? active.selectionStart : null,
            end: focusName && typeof active.selectionEnd === 'number' ? active.selectionEnd : null,
        };
    };

    const restoreFilterInputState = (state) => {
        if (!state) return;
        const { periodInput, searchInput, dateInput } = currentFilterNodes();
        if (periodInput && typeof state.period === 'string') periodInput.value = state.period;
        if (searchInput && typeof state.search === 'string') searchInput.value = state.search;
        if (dateInput && typeof state.date === 'string') dateInput.value = state.date;

        let target = null;
        if (state.focusName === 'search' && searchInput) target = searchInput;
        if (state.focusName === 'date' && dateInput) target = dateInput;
        if (!target) return;

        target.focus({ preventScroll: true });
        if (
            typeof state.start === 'number' &&
            typeof state.end === 'number' &&
            typeof target.setSelectionRange === 'function'
        ) {
            const len = target.value.length;
            const start = Math.max(0, Math.min(state.start, len));
            const end = Math.max(0, Math.min(state.end, len));
            target.setSelectionRange(start, end);
        }
    };

    const syncTabState = () => {
        const { periodInput, tabButtons } = currentFilterNodes();
        const activePeriod = periodInput ? String(periodInput.value || 'today') : 'today';
        tabButtons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.periodTab === activePeriod);
        });
    };

    const replaceLogsCardFromHtml = (html) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const incomingCard = doc.getElementById('vesselLogsCard');
        const currentCard = document.getElementById('vesselLogsCard');
        if (!incomingCard || !currentCard) return false;
        currentCard.replaceWith(incomingCard);
        return true;
    };

    const requestLiveFilter = (query, delayMs = 0, state = null) => {
        if (liveFilterTimer) {
            window.clearTimeout(liveFilterTimer);
            liveFilterTimer = null;
        }

        const run = () => {
            const requestId = ++activeFilterRequestId;
            const url = `${filterFormAction}?${query.toString()}`;
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => response.text())
                .then((html) => {
                    if (requestId !== activeFilterRequestId) return;
                    if (!replaceLogsCardFromHtml(html)) {
                        window.location.assign(url);
                        return;
                    }
                    history.replaceState({}, '', url);
                    restoreFilterInputState(state);
                    syncTabState();
                })
                .catch(() => {
                    window.location.assign(url);
                });
        };

        if (delayMs > 0) {
            liveFilterTimer = window.setTimeout(run, delayMs);
            return;
        }

        run();
    };

    if (cancelQuickLogBtn) {
        cancelQuickLogBtn.addEventListener('click', () => {
            closeModal(quickLogModal);
        });
    }

    const refreshQuickVesselAvailability = () => {
        if (!quickVesselSelect || !quickLogDateInput || !quickArrDepSelect) return;
        const selectedDate = quickLogDateInput.value || '';
        const selectedMovement = String(quickArrDepSelect.value || '');
        const isToday = selectedDate === todayDate;

        Array.from(quickVesselSelect.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const vesselId = Number(option.value || 0);
            const movementList = Array.isArray(todayLoggedMovementsByVessel[String(vesselId)])
                ? todayLoggedMovementsByVessel[String(vesselId)]
                : [];
            const hasSelectedMovementToday = movementList.includes(selectedMovement);
            const shouldHide = isToday && hasSelectedMovementToday;
            option.hidden = shouldHide;
            option.disabled = shouldHide;
        });

        const selectedOption = quickVesselSelect.selectedOptions[0];
        if (selectedOption && selectedOption.disabled) {
            quickVesselSelect.value = '';
        }
    };

    const syncOriginCustomInputState = (originSelect, customOriginInput) => {
        if (!originSelect || !customOriginInput) return;
        const hasSelectedOrigin = String(originSelect.value || '').trim() !== '';
        customOriginInput.disabled = hasSelectedOrigin;
    };

    if (quickLogDateInput) {
        quickLogDateInput.addEventListener('change', refreshQuickVesselAvailability);
        quickLogDateInput.addEventListener('input', refreshQuickVesselAvailability);
    }
    if (quickArrDepSelect) {
        quickArrDepSelect.addEventListener('change', refreshQuickVesselAvailability);
    }
    if (quickOriginId) {
        quickOriginId.addEventListener('change', () => {
            syncOriginCustomInputState(quickOriginId, quickOriginName);
        });
    }
    if (editOriginId) {
        editOriginId.addEventListener('change', () => {
            syncOriginCustomInputState(editOriginId, editOriginName);
        });
    }

    const setFieldValue = (field, value) => {
        if (!field) return;
        field.value = value || '';
    };

    document.addEventListener('click', (event) => {
        const openQuickLogTrigger = event.target.closest('#openQuickLogBtn');
        if (openQuickLogTrigger) {
            openModal(quickLogModal);
            return;
        }

        const tabButton = event.target.closest('[data-period-tab]');
        if (tabButton) {
            const { form, periodInput } = currentFilterNodes();
            if (!form || !periodInput) return;
            periodInput.value = tabButton.dataset.periodTab || 'today';
            const query = new URLSearchParams(new FormData(form));
            const state = captureFilterInputState();
            requestLiveFilter(query, 0, state);
            return;
        }

        const editButton = event.target.closest('.js-vl-edit-btn');
        if (editButton) {
            if (!editLogForm) return;
            const logId = editButton.dataset.logId || '';
            if (!logId) return;
            editLogForm.action = editLogActionTemplate.replace('__LOG_ID__', logId);
            setFieldValue(editLogDate, editButton.dataset.logDate);
            setFieldValue(editLogTime, editButton.dataset.logTime);
            setFieldValue(editArrDep, editButton.dataset.arrDep);
            setFieldValue(editVesselId, editButton.dataset.vesselId);
            setFieldValue(editOriginId, editButton.dataset.originId);
            setFieldValue(editOriginName, '');
            setFieldValue(editRemarks, editButton.dataset.remarks);
            syncOriginCustomInputState(editOriginId, editOriginName);
            openModal(editLogModal);
            return;
        }

        const paginationLink = event.target.closest('.vl-pagination .vl-page-link[href]');
        if (paginationLink) {
            event.preventDefault();
            const href = paginationLink.getAttribute('href');
            if (!href) return;
            const state = captureFilterInputState();
            fetch(href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => response.text())
                .then((html) => {
                    if (!replaceLogsCardFromHtml(html)) {
                        window.location.assign(href);
                        return;
                    }
                    history.replaceState({}, '', href);
                    restoreFilterInputState(state);
                    syncTabState();
                })
                .catch(() => {
                    window.location.assign(href);
                });
        }
    });

    document.addEventListener('input', (event) => {
        const { form, periodInput, searchInput, dateInput } = currentFilterNodes();
        if (!form || !periodInput) return;

        if (searchInput && event.target === searchInput) {
            const query = new URLSearchParams(new FormData(form));
            const state = captureFilterInputState();
            requestLiveFilter(query, 260, state);
            return;
        }

        if (dateInput && event.target === dateInput) {
            periodInput.value = 'custom';
            const query = new URLSearchParams(new FormData(form));
            const state = captureFilterInputState();
            requestLiveFilter(query, 180, state);
        }
    });

    document.addEventListener('change', (event) => {
        const { form, periodInput, dateInput } = currentFilterNodes();
        if (!form || !periodInput || !dateInput) return;
        if (event.target !== dateInput) return;
        periodInput.value = 'custom';
        const query = new URLSearchParams(new FormData(form));
        const state = captureFilterInputState();
        requestLiveFilter(query, 0, state);
    });

    document.addEventListener('submit', (event) => {
        const targetForm = event.target;
        if (!(targetForm instanceof HTMLFormElement)) return;

        if (targetForm.id === 'vesselLogsFilterForm') {
            event.preventDefault();
            const query = new URLSearchParams(new FormData(targetForm));
            const state = captureFilterInputState();
            requestLiveFilter(query, 0, state);
            return;
        }

        if (!targetForm.classList.contains('js-vl-delete-form')) return;

        if (targetForm.dataset.confirmed === '1') {
            targetForm.dataset.confirmed = '0';
            return;
        }

        event.preventDefault();
        pendingDeleteForm = targetForm;
        if (deleteLogNo) deleteLogNo.textContent = targetForm.dataset.logNumber || '-';
        if (deleteVesselName) deleteVesselName.textContent = targetForm.dataset.vesselName || '-';
        openModal(deleteModal);
    });

    if (cancelEditLogBtn) {
        cancelEditLogBtn.addEventListener('click', () => {
            closeModal(editLogModal);
        });
    }

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', () => {
            pendingDeleteForm = null;
            closeModal(deleteModal);
        });
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', () => {
            if (!pendingDeleteForm) return;
            const targetForm = pendingDeleteForm;
            targetForm.dataset.confirmed = '1';
            pendingDeleteForm = null;
            closeModal(deleteModal);
            if (typeof targetForm.requestSubmit === 'function') {
                targetForm.requestSubmit();
                return;
            }
            targetForm.submit();
        });
    }

    if (quickLogModal) {
        quickLogModal.addEventListener('click', (event) => {
            if (event.target !== quickLogModal) return;
            closeModal(quickLogModal);
        });
    }

    if (editLogModal) {
        editLogModal.addEventListener('click', (event) => {
            if (event.target !== editLogModal) return;
            closeModal(editLogModal);
        });
    }

    if (deleteModal) {
        deleteModal.addEventListener('click', (event) => {
            if (event.target !== deleteModal) return;
            pendingDeleteForm = null;
            closeModal(deleteModal);
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        closeModal(quickLogModal);
        closeModal(editLogModal);
        pendingDeleteForm = null;
        closeModal(deleteModal);
    });

    if (shouldOpenQuickLogModal) {
        openModal(quickLogModal);
    } else {
        syncBodyLock();
    }

    refreshQuickVesselAvailability();
    syncOriginCustomInputState(quickOriginId, quickOriginName);
    syncOriginCustomInputState(editOriginId, editOriginName);
    syncTabState();

    if (statusToast) {
        window.setTimeout(() => {
            statusToast.classList.add('is-hiding');
            window.setTimeout(() => statusToast.remove(), 220);
        }, 2200);
    }
})();
</script>
@endsection
