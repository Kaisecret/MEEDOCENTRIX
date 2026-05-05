@extends('layouts.app')

@section('content')
@php
    /** @var array<string, array<string, string>> $departments */
    /** @var array<string, array<string, string>> $recordTabs */
    /** @var array<string, mixed> $filters */
    /** @var array<string, string> $statusOptions */
    /** @var \Illuminate\Pagination\LengthAwarePaginator $records */
    /** @var array<string, mixed> $summary */

    $startEntry = $records->total() > 0 ? (($records->currentPage() - 1) * $records->perPage()) + 1 : 0;
    $endEntry = $records->total() > 0 ? min($records->currentPage() * $records->perPage(), $records->total()) : 0;
    $tabQueryBase = [
        'q' => $filters['q'],
        'status' => $filters['status'],
        'from' => $filters['from_input'],
        'to' => $filters['to_input'],
        'record_tab' => '',
    ];
    $recordTabQueryBase = [
        'q' => $filters['q'],
        'status' => $filters['status'],
        'from' => $filters['from_input'],
        'to' => $filters['to_input'],
        'department' => $filters['department'],
    ];
    $exportQuery = [
        'q' => $filters['q'],
        'department' => $filters['department'],
        'record_tab' => $filters['record_tab'],
        'status' => $filters['status'],
        'from' => $filters['from_input'],
        'to' => $filters['to_input'],
    ];
@endphp

<div class="admin-report-ledger" data-server-rendered-page="reports" data-page-title="Reports & Analytics">
    <nav class="report-tabs" aria-label="Department tabs">
        @foreach ($departments as $code => $config)
            <a
                href="{{ route('admin.reports', array_merge($tabQueryBase, ['department' => $code])) }}"
                class="report-tab {{ $filters['department'] === $code ? 'is-active' : '' }}"
            >
                <span class="report-tab-icon" style="--tab-color: {{ $config['color'] }}">
                    <i class="{{ $config['icon'] }}"></i>
                </span>
                <span>{{ $config['name'] }}</span>
            </a>
        @endforeach
    </nav>

    <section class="report-panel">
        <form method="GET" action="{{ route('admin.reports') }}" class="report-filters">
            <input type="hidden" name="department" value="{{ $filters['department'] }}">

            <label class="report-search">
                <i class="fas fa-search"></i>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search">
            </label>

            <label>
                <span>Records</span>
                <select name="record_tab">
                    @foreach ($recordTabs as $typeKey => $tab)
                        <option value="{{ $typeKey }}" {{ $filters['record_tab'] === $typeKey ? 'selected' : '' }}>
                            {{ $tab['label'] }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Status</span>
                <select name="status">
                    @foreach ($statusOptions as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" {{ $filters['status'] === $statusKey ? 'selected' : '' }}>{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>From</span>
                <input type="date" name="from" value="{{ $filters['from_input'] }}">
            </label>

            <label>
                <span>To</span>
                <input type="date" name="to" value="{{ $filters['to_input'] }}">
            </label>

            <div class="report-filter-actions">
                <button type="submit" class="report-btn report-btn-primary">
                    <i class="fas fa-filter"></i>
                    Apply
                </button>
                <a href="{{ route('admin.reports') }}" class="report-btn report-btn-light">Reset</a>
            </div>
        </form>

        <div class="report-record-tabs-wrap">
            <div class="report-record-tabs-head">
                <span class="report-record-tabs-title">Records</span>
            </div>
            <div class="report-record-tabs-row">
                <nav class="report-record-tabs" aria-label="Records tabs">
                    @foreach ($recordTabs as $typeKey => $tab)
                        <a
                            href="{{ route('admin.reports', array_merge($recordTabQueryBase, ['record_tab' => $typeKey])) }}"
                            class="report-record-tab {{ $filters['record_tab'] === $typeKey ? 'is-active' : '' }}"
                        >
                            <i class="{{ $tab['icon'] }}"></i>
                            <span>{{ $tab['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
                <a href="{{ route('admin.reports.csv', $exportQuery) }}" class="report-btn report-btn-export">
                    <i class="fa-solid fa-file-excel"></i>
                    Export CSV
                </a>
            </div>
        </div>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Department</th>
                        <th>Record Type</th>
                        <th>Reference</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Details</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $row)
                        <tr>
                            <td class="is-nowrap">{{ $row['occurred_at']->format('M d, Y h:i A') }}</td>
                            <td>
                                <span class="report-dept-chip" style="--dept-color: {{ $row['department_color'] }}">
                                    <i class="{{ $row['department_icon'] }}"></i>
                                    {{ $row['department_name'] }}
                                </span>
                            </td>
                            <td>{{ $row['record_type'] }}</td>
                            <td><span class="report-code">{{ $row['reference'] }}</span></td>
                            <td>{{ $row['subject'] }}</td>
                            <td>
                                <span class="report-status is-{{ $row['status_key'] }}">{{ $row['status_label'] }}</span>
                            </td>
                            <td>{{ $row['details'] }}</td>
                            <td>
                                <button
                                    type="button"
                                    class="report-eye-btn"
                                    data-open-report-modal
                                    data-date="{{ $row['occurred_at']->format('M d, Y h:i A') }}"
                                    data-department="{{ $row['department_name'] }}"
                                    data-record-type="{{ $row['record_type'] }}"
                                    data-reference="{{ $row['reference'] }}"
                                    data-subject="{{ $row['subject'] }}"
                                    data-status="{{ $row['status_label'] }}"
                                    data-details="{{ $row['details'] }}"
                                    data-full="{{ base64_encode(json_encode($row['full'], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)) }}"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="report-empty">
                                <i class="fas fa-inbox"></i>
                                <strong>No records found</strong>
                                <span>Try changing your filters to see records.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-footer">
            <span>Showing {{ $startEntry }} to {{ $endEntry }} of {{ $records->total() }} entries</span>
            <div class="report-pagination">
                @if ($records->onFirstPage())
                    <span class="is-disabled">Previous</span>
                @else
                    <a href="{{ $records->previousPageUrl() }}">Previous</a>
                @endif

                <span class="report-page-indicator">Page {{ $records->currentPage() }} of {{ max($records->lastPage(), 1) }}</span>

                @if ($records->hasMorePages())
                    <a href="{{ $records->nextPageUrl() }}">Next</a>
                @else
                    <span class="is-disabled">Next</span>
                @endif
            </div>
        </div>
    </section>
</div>

<div class="report-modal-backdrop" data-report-modal-backdrop hidden>
    <div class="report-modal" role="dialog" aria-modal="true" aria-labelledby="reportModalTitle">
        <div class="report-modal-head">
            <h3 id="reportModalTitle">Record Details</h3>
            <button type="button" class="report-modal-close" data-close-report-modal aria-label="Close">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <div class="report-modal-body">
            <div class="report-modal-grid">
                <div><span>Date</span><strong data-modal-date>-</strong></div>
                <div><span>Department</span><strong data-modal-department>-</strong></div>
                <div><span>Record Type</span><strong data-modal-type>-</strong></div>
                <div><span>Reference</span><strong data-modal-reference>-</strong></div>
                <div><span>Subject</span><strong data-modal-subject>-</strong></div>
                <div><span>Status</span><strong data-modal-status>-</strong></div>
            </div>
            <div class="report-modal-details">
                <span>Details</span>
                <p data-modal-details>-</p>
            </div>
            <div class="report-modal-details" data-modal-full-section>
                <div class="report-modal-full-list" data-modal-full-list></div>
            </div>
        </div>
    </div>
</div>

<style>
    #contentArea {
        padding-top: 10px;
    }

    .admin-report-ledger {
        --ink: #0b1a2c;
        --muted: #6b7d93;
        --line: #e3eaf3;
        --line-strong: #cfdae6;
        --soft: #f6f9fd;
        --panel: #ffffff;
        --primary: #155e8f;
        --primary-dark: #124f78;
        --good: #0f8a5f;
        --warn: #c46a17;
        --danger: #b1342f;
        max-width: 1480px;
        margin: 0 auto;
        padding: 10px 0;
        color: var(--ink);
        display: grid;
        gap: 10px;
    }

    .report-panel {
        border: 1px solid var(--line);
        border-radius: 14px;
        background: var(--panel);
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 35, 60, 0.04);
    }

    .report-tabs {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }

    .report-tab {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: var(--ink);
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #f8fbff;
        padding: 10px 12px;
        font-weight: 800;
        min-height: 56px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .report-tab:hover {
        border-color: #b9cde2;
        background: #ffffff;
    }

    .report-tab.is-active {
        border-color: #9fbedb;
        box-shadow: 0 4px 12px rgba(21, 94, 143, 0.1);
        background: #ffffff;
    }

    .report-tab-icon {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        background: color-mix(in srgb, var(--tab-color) 16%, white);
        color: var(--tab-color);
        flex-shrink: 0;
    }

    .report-filters {
        display: grid;
        grid-template-columns: minmax(220px, 1.35fr) repeat(4, minmax(150px, 1fr)) auto;
        gap: 12px;
        padding: 12px;
        align-items: end;
        border-bottom: 1px solid var(--line);
        background: #ffffff;
    }

    .report-filters label {
        display: grid;
        gap: 6px;
    }

    .report-filters label span {
        color: var(--muted);
        font-size: 0.69rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        line-height: 1;
        padding-left: 2px;
    }

    .report-search {
        position: relative;
        display: block;
    }

    .report-search i {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: #8aa0b6;
        pointer-events: none;
    }

    .report-search input {
        padding-left: 36px !important;
    }

    .report-filters input,
    .report-filters select {
        min-height: 44px;
        border: 1px solid var(--line);
        border-radius: 11px;
        padding: 10px 12px;
        background: var(--soft);
        font-size: 0.96rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .report-filters input:focus,
    .report-filters select:focus {
        outline: none;
        border-color: var(--primary);
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(21, 94, 143, 0.14);
    }

    .report-filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    .report-btn {
        min-height: 38px;
        padding: 0 14px;
        border-radius: 9px;
        font-weight: 800;
        font-size: 0.85rem;
        text-decoration: none;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
    }

    .report-btn-primary {
        color: #ffffff;
        background: var(--primary);
        border-color: var(--primary);
    }

    .report-btn-primary:hover {
        background: var(--primary-dark);
    }

    .report-btn-light {
        color: var(--ink);
        background: #ffffff;
        border-color: var(--line-strong);
    }

    .report-btn-light:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .report-btn-export {
        color: var(--primary);
        background: #ffffff;
        border-color: #cbd9e8;
        box-shadow: 0 1px 2px rgba(15, 35, 60, 0.05);
    }

    .report-btn-export:hover {
        background: #f0f7fd;
        border-color: var(--primary);
        color: var(--primary);
    }

    .report-table-wrap {
        overflow-x: auto;
        padding: 10px;
    }

    .report-table {
        width: 100%;
        min-width: 1180px;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid var(--line);
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
    }

    .report-table th {
        text-align: left;
        background: linear-gradient(180deg, #f7fafd 0%, #eef3f9 100%);
        color: #4a5e76;
        padding: 8px 10px;
        border-bottom: 1px solid var(--line);
        font-size: 0.7rem;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        white-space: nowrap;
    }

    .report-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #eef2f7;
        color: #2a3e57;
        font-size: 0.86rem;
        vertical-align: middle;
    }

    .report-table tbody tr:hover td {
        background: #f9fcff;
    }

    .report-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .report-code {
        display: inline-flex;
        border-radius: 6px;
        padding: 3px 7px;
        background: rgba(21, 94, 143, 0.1);
        color: #124f78;
        font-size: 0.74rem;
        font-weight: 800;
        font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    }

    .report-dept-chip {
        --dept-color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 3px 9px;
        font-size: 0.74rem;
        font-weight: 800;
        background: color-mix(in srgb, var(--dept-color) 14%, white);
        color: var(--dept-color);
    }

    .report-status {
        display: inline-flex;
        border-radius: 999px;
        padding: 3px 9px;
        font-size: 0.72rem;
        font-weight: 800;
        border: 1px solid var(--line);
        color: #42526b;
        background: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .report-status.is-active {
        border-color: #b5e4cc;
        color: var(--good);
        background: #e6f7ee;
    }

    .report-status.is-pending {
        border-color: #f1d2ad;
        color: var(--warn);
        background: #fff4e8;
    }

    .report-status.is-completed {
        border-color: #c9dff2;
        color: #155e8f;
        background: #edf6fd;
    }

    .report-status.is-cancelled {
        border-color: #f3c0bf;
        color: var(--danger);
        background: #ffeceb;
    }

    .report-empty {
        text-align: center;
        padding: 24px 12px !important;
    }

    .report-empty i {
        font-size: 1.4rem;
        color: #8aa0b6;
    }

    .report-empty strong,
    .report-empty span {
        display: block;
        margin-top: 5px;
    }

    .report-empty span {
        color: var(--muted);
    }

    .report-record-tabs-wrap {
        padding: 8px 10px;
        border-bottom: 1px solid var(--line);
        background: #fbfdff;
        display: grid;
        gap: 6px;
    }

    .report-record-tabs-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
    }

    .report-record-tabs-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .report-record-tabs-title {
        color: var(--muted);
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .report-record-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .report-record-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border: 1px solid var(--line);
        border-radius: 9px;
        text-decoration: none;
        color: #2a3e57;
        font-size: 0.82rem;
        font-weight: 750;
        background: #ffffff;
    }

    .report-record-tab:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .report-record-tab.is-active {
        border-color: #9fbedb;
        background: #edf6fd;
        color: #124f78;
    }

    .report-eye-btn {
        width: 33px;
        height: 33px;
        border-radius: 8px;
        border: 1px solid #c6d7e8;
        background: #f3f8fd;
        color: #155e8f;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .report-eye-btn:hover {
        background: #e9f2fb;
        border-color: #9fbedb;
    }

    .report-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.52);
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
        z-index: 1500;
    }

    .report-modal-backdrop[hidden] {
        display: none !important;
    }

    .report-modal {
        width: min(980px, 100%);
        background: #ffffff;
        border: 1px solid var(--line);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 24px 34px rgba(15, 35, 60, 0.2);
    }

    .report-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        border-bottom: 1px solid var(--line);
        background: #f8fbff;
    }

    .report-modal-head h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 850;
        color: #0b1a2c;
    }

    .report-modal-close {
        border: 1px solid var(--line);
        background: #ffffff;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        color: #2a3e57;
    }

    .report-modal-body {
        padding: 14px;
        display: grid;
        gap: 12px;
        max-height: 76vh;
        overflow: auto;
    }

    .report-modal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .report-modal-grid div {
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 8px 10px;
        background: #fbfdff;
        display: grid;
        gap: 3px;
    }

    .report-modal-grid span,
    .report-modal-details span {
        color: var(--muted);
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .report-modal-grid strong {
        color: #0b1a2c;
        font-size: 0.9rem;
    }

    .report-modal-details {
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 10px;
        background: #ffffff;
        display: grid;
        gap: 5px;
    }

    .report-modal-details p {
        margin: 0;
        color: #2a3e57;
        line-height: 1.4;
    }

    .report-modal-full-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .report-modal-full-item {
        display: grid;
        grid-template-columns: 1fr;
        gap: 4px;
        align-items: start;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: #fbfdff;
        padding: 9px 10px;
        min-height: 62px;
    }

    .report-modal-full-item strong {
        color: #5b6f86;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .report-modal-full-item span {
        color: #0f2742;
        font-size: 0.9rem;
        font-weight: 700;
        word-break: break-word;
        line-height: 1.35;
    }

    .is-nowrap {
        white-space: nowrap;
    }

    .report-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border-top: 1px solid var(--line);
        background: #ffffff;
        color: var(--muted);
        font-size: 0.82rem;
    }

    .report-pagination {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .report-pagination a,
    .report-pagination span {
        border: 1px solid var(--line-strong);
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #2a3e57;
        text-decoration: none;
        background: #ffffff;
    }

    .report-pagination a:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .report-pagination .is-disabled {
        opacity: 0.55;
    }

    .report-pagination .report-page-indicator {
        border-color: transparent;
        background: transparent;
        padding: 0;
    }

    @media (max-width: 1280px) {
        .report-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .report-search {
            grid-column: 1 / -1;
        }

        .report-filter-actions {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }

        .report-tabs {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 860px) {
        .report-filters {
            grid-template-columns: 1fr;
        }

        .report-filter-actions {
            grid-column: auto;
            justify-content: flex-start;
        }

        .report-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .report-tabs {
            grid-template-columns: 1fr;
        }

        .report-modal-grid {
            grid-template-columns: 1fr;
        }

        .report-modal-full-list {
            grid-template-columns: 1fr;
        }

        .report-record-tabs-head {
            align-items: flex-start;
        }

        .report-record-tabs-row {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<script>
    (function () {
        const backdrop = document.querySelector('[data-report-modal-backdrop]');
        if (!backdrop) {
            return;
        }
        backdrop.hidden = true;

        const dateEl = backdrop.querySelector('[data-modal-date]');
        const departmentEl = backdrop.querySelector('[data-modal-department]');
        const typeEl = backdrop.querySelector('[data-modal-type]');
        const referenceEl = backdrop.querySelector('[data-modal-reference]');
        const subjectEl = backdrop.querySelector('[data-modal-subject]');
        const statusEl = backdrop.querySelector('[data-modal-status]');
        const detailsEl = backdrop.querySelector('[data-modal-details]');
        const fullSectionEl = backdrop.querySelector('[data-modal-full-section]');
        const fullListEl = backdrop.querySelector('[data-modal-full-list]');

        const closeModal = function () {
            backdrop.hidden = true;
        };

        document.querySelectorAll('[data-open-report-modal]').forEach((button) => {
            button.addEventListener('click', function () {
                dateEl.textContent = this.dataset.date || '-';
                departmentEl.textContent = this.dataset.department || '-';
                typeEl.textContent = this.dataset.recordType || '-';
                referenceEl.textContent = this.dataset.reference || '-';
                subjectEl.textContent = this.dataset.subject || '-';
                statusEl.textContent = this.dataset.status || '-';
                detailsEl.textContent = this.dataset.details || '-';

                fullListEl.innerHTML = '';
                let full = {};
                try {
                    const encoded = this.dataset.full || '';
                    full = encoded ? JSON.parse(atob(encoded)) : {};
                } catch (error) {
                    full = {};
                }

                const entries = Object.entries(full);
                if (entries.length === 0) {
                    fullSectionEl.hidden = true;
                } else {
                    fullSectionEl.hidden = false;
                    entries.forEach(([key, value]) => {
                        const item = document.createElement('div');
                        item.className = 'report-modal-full-item';

                        const label = document.createElement('strong');
                        label.textContent = key;
                        item.appendChild(label);

                        const val = document.createElement('span');
                        val.textContent = value === null || value === '' ? '-' : String(value);
                        item.appendChild(val);

                        fullListEl.appendChild(item);
                    });
                }

                backdrop.hidden = false;
            });
        });

        backdrop.querySelectorAll('[data-close-report-modal]').forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        backdrop.addEventListener('click', function (event) {
            if (event.target === backdrop) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !backdrop.hidden) {
                closeModal();
            }
        });
    })();
</script>
@endsection
