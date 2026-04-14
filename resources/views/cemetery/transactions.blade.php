@extends('layouts.app')

@section('content')
<style>
    .ctx-page {
        display: grid;
        gap: 16px;
        color: #334155;
        font-family: 'Inter', system-ui, sans-serif;
    }

    .ctx-hero {
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

    .ctx-hero h2 {
        margin: 0 0 0.25rem;
        font-size: 1.4rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .ctx-hero p {
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.9rem;
    }

    .ctx-add-btn {
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

    .ctx-add-btn:hover { background: rgba(255, 255, 255, 0.28); }

    .ctx-stats {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .ctx-stat {
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        padding: 0.7rem 0.8rem;
    }

    .ctx-stat span {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .ctx-stat strong {
        display: block;
        margin-top: 0.28rem;
        color: #0f172a;
        font-size: 1.02rem;
    }

    .ctx-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .ctx-card-head {
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.1rem;
        background: #f8fafc;
        display: grid;
        gap: 9px;
    }

    .ctx-card-head h3 {
        margin: 0;
        font-size: 1.05rem;
        color: #0f172a;
    }

    .ctx-filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
        gap: 8px;
    }

    .ctx-control {
        min-height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #0f172a;
        padding: 0.45rem 0.65rem;
        font-size: 0.86rem;
        width: 100%;
    }

    .ctx-control:focus {
        outline: none;
        border-color: #155f8f;
        box-shadow: 0 0 0 3px rgba(21, 95, 143, 0.11);
    }

    .ctx-filter-actions {
        display: inline-flex;
        gap: 6px;
    }

    .ctx-btn {
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

    .ctx-btn-primary {
        border-color: #155f8f;
        background: #155f8f;
        color: #fff;
    }

    .ctx-btn-secondary {
        border-color: #cbd5e1;
        background: #fff;
        color: #334155;
    }

    .ctx-table-wrap { overflow: auto; }

    .ctx-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1380px;
    }

    .ctx-table th {
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

    .ctx-table td {
        border-bottom: 1px solid #eef2f7;
        padding: 0.7rem;
        color: #334155;
        font-size: 0.85rem;
        vertical-align: top;
    }

    .ctx-table tbody tr:hover { background: #f8fbff; }

    .ctx-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        border: 1px solid;
        padding: 0.2rem 0.52rem;
        font-size: 0.71rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .ctx-badge-pending { border-color: #fde68a; background: #fffbeb; color: #92400e; }
    .ctx-badge-paid { border-color: #86efac; background: #ecfdf5; color: #065f46; }
    .ctx-badge-partial { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
    .ctx-badge-cancelled { border-color: #fecaca; background: #fff1f2; color: #9f1239; }

    .ctx-actions { display: inline-flex; gap: 6px; }
    .ctx-icon-btn {
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
    .ctx-icon-btn:hover { background: #f1f5f9; color: #155f8f; }
    .ctx-icon-btn-danger:hover { border-color: #fecaca; background: #fff1f2; color: #b91c1c; }

    .ctx-pagination {
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .ctx-page-link {
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

    .ctx-page-link.disabled {
        border-color: #e2e8f0;
        color: #94a3b8;
        background: #f8fafc;
        pointer-events: none;
    }

    .ctx-modal {
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
    .ctx-modal.is-open { display: flex; }

    .ctx-modal-card {
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

    .ctx-modal-card-compact { width: min(460px, 96vw); }
    .ctx-modal-head {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .ctx-modal-head h4 { margin: 0; color: #0f172a; font-size: 1.06rem; }
    .ctx-modal-close {
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
    .ctx-modal-close:hover { background: #e2e8f0; color: #0f172a; }
    .ctx-modal form { display: grid; grid-template-rows: minmax(0, 1fr) auto; min-height: 0; }
    .ctx-modal-body { padding: 16px 20px; overflow-y: auto; min-height: 0; background: #fff; }
    .ctx-modal-foot {
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 12px 16px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .ctx-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 14px; }
    .ctx-field { display: grid; gap: 6px; }
    .ctx-field-full { grid-column: 1 / -1; }
    .ctx-field label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .ctx-control-textarea { min-height: 84px; resize: vertical; }

    body.ctx-lock-scroll { overflow: hidden; }

    @media (max-width: 1120px) {
        .ctx-hero { grid-template-columns: 1fr; }
        .ctx-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ctx-filter-grid { grid-template-columns: 1fr 1fr 1fr; }
        .ctx-filter-actions { grid-column: 1 / -1; }
    }

    @media (max-width: 680px) {
        .ctx-stats, .ctx-form-grid, .ctx-filter-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="ctx-page" data-server-rendered-page="cemetery_transactions" data-page-title="Cemetery Transactions">
    <section class="ctx-hero">
        <div>
            <h2>Cemetery Transactions</h2>
            <p>Record official cemetery transaction entries including type, amount due, quantity, and status.</p>
        </div>
        <button type="button" id="openCreateTransactionBtn" class="ctx-add-btn">
            <i class="fa-solid fa-plus"></i> Add Transaction
        </button>
    </section>

    <section class="ctx-stats">
        <article class="ctx-stat"><span>Total Transactions</span><strong>{{ number_format((int) $summary['total_transactions']) }}</strong></article>
        <article class="ctx-stat"><span>Transactions Today</span><strong>{{ number_format((int) $summary['today_transactions']) }}</strong></article>
        <article class="ctx-stat"><span>Pending</span><strong>{{ number_format((int) $summary['pending_transactions']) }}</strong></article>
        <article class="ctx-stat"><span>Paid</span><strong>{{ number_format((int) $summary['paid_transactions']) }}</strong></article>
        <article class="ctx-stat"><span>Total Amount Due</span><strong>PHP {{ number_format((float) $summary['total_due'], 2) }}</strong></article>
    </section>

    @if (session('status'))
        <div class="alert alert-success" style="margin:0;"><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" style="margin:0;"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>
    @endif

    <section class="ctx-card">
        <div class="ctx-card-head">
            <h3>Transaction List</h3>
            <form method="GET" action="{{ route('cemetery.transactions') }}" class="ctx-filter-grid">
                <input type="search" name="q" class="ctx-control" placeholder="Search transaction no, deceased, niche/lot..." value="{{ $search }}">
                <select name="cemetery_site_id" class="ctx-control">
                    <option value="">All Cemeteries</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" @selected((string) $selectedSiteId === (string) $site->id)>{{ $site->site_name }}</option>
                    @endforeach
                </select>
                <select name="cemetery_category_id" class="ctx-control">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>{{ $category->category_name }}</option>
                    @endforeach
                </select>
                <select name="cemetery_transaction_type_id" class="ctx-control">
                    <option value="">All Transaction Types</option>
                    @foreach($transactionTypes as $transactionType)
                        <option value="{{ $transactionType->id }}" @selected((string) $selectedTransactionTypeId === (string) $transactionType->id)>{{ $transactionType->type_name }}</option>
                    @endforeach
                </select>
                <select name="status" class="ctx-control">
                    <option value="">All Statuses</option>
                    @foreach($statusOptions as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" @selected($selectedStatus === $statusKey)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
                <div class="ctx-filter-actions">
                    <button type="submit" class="ctx-btn ctx-btn-primary"><i class="fa-solid fa-filter"></i> Apply</button>
                    <a href="{{ route('cemetery.transactions') }}" class="ctx-btn ctx-btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="ctx-table-wrap">
            <table class="ctx-table">
                <thead>
                    <tr>
                        <th>Transaction No.</th>
                        <th>Date</th>
                        <th>Cemetery / Category</th>
                        <th>Deceased Name</th>
                        <th>Niche / Lot</th>
                        <th>Transaction Type</th>
                        <th>Source</th>
                        <th>Quantity</th>
                        <th>Amount Due</th>
                        <th>Fee Breakdown</th>
                        <th>Remarks</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td><strong>{{ $transaction->transaction_no }}</strong></td>
                            <td>{{ optional($transaction->transaction_date)->format('Y-m-d') }}</td>
                            <td>{{ $transaction->site?->site_name ?: '-' }}<br><span class="csl-muted">{{ $transaction->category?->category_name ?: '-' }}</span></td>
                            <td>{{ $transaction->deceased_name }}</td>
                            <td>{{ $transaction->plot_reference }}</td>
                            <td>{{ $transaction->transactionType?->type_name ?: '-' }}</td>
                            <td>
                                @if ($transaction->occupant_record_id)
                                    <span class="ctx-badge ctx-badge-paid">OCCUPANT</span>
                                    <div class="csl-muted">{{ $transaction->occupantRecord?->record_no ?: '' }}</div>
                                @elseif ($transaction->service_log_id)
                                    <span class="ctx-badge ctx-badge-partial">SERVICE</span>
                                    <div class="csl-muted">{{ $transaction->serviceLog?->log_no ?: '' }}</div>
                                @else
                                    <span class="csl-muted">MANUAL</span>
                                @endif
                            </td>
                            <td>{{ $transaction->quantity !== null ? number_format((float) $transaction->quantity, 2) : '-' }}</td>
                            <td><strong>PHP {{ number_format((float) $transaction->amount_due, 2) }}</strong></td>
                            <td>
                                <div style="font-size:0.78rem; line-height:1.45;">
                                    <div>Base: <strong>PHP {{ number_format((float) ($transaction->base_fee ?? 0), 2) }}</strong></div>
                                    <div>Maint: <strong>PHP {{ number_format((float) ($transaction->maintenance_fee ?? 0), 2) }}</strong></div>
                                    <div>Permit: <strong>PHP {{ number_format((float) ($transaction->burial_permit_fee ?? 0), 2) }}</strong></div>
                                    <div>Other: <strong>PHP {{ number_format((float) ($transaction->other_applicable_fee ?? 0), 2) }}</strong></div>
                                </div>
                            </td>
                            <td>{{ $transaction->remarks ?: '-' }}</td>
                            <td><span class="ctx-badge ctx-badge-{{ $transaction->status }}">{{ $statusOptions[$transaction->status] ?? strtoupper($transaction->status) }}</span></td>
                            <td>
                                <div class="ctx-actions">
                                    <a
                                        href="{{ route('cemetery.payments', ['q' => $transaction->transaction_no]) }}"
                                        class="ctx-icon-btn"
                                        title="Open payment collection">
                                        <i class="fa-solid fa-cash-register"></i>
                                    </a>
                                    <button
                                        type="button"
                                        class="ctx-icon-btn js-open-edit-transaction-btn"
                                        data-transaction-id="{{ $transaction->id }}"
                                        data-transaction-no="{{ $transaction->transaction_no }}"
                                        data-transaction-date="{{ optional($transaction->transaction_date)->format('Y-m-d') }}"
                                        data-site-id="{{ $transaction->cemetery_site_id }}"
                                        data-category-id="{{ $transaction->cemetery_category_id }}"
                                        data-transaction-type-id="{{ $transaction->cemetery_transaction_type_id }}"
                                        data-occupant-record-id="{{ $transaction->occupant_record_id }}"
                                        data-service-log-id="{{ $transaction->service_log_id }}"
                                        data-deceased-name="{{ $transaction->deceased_name }}"
                                        data-plot-reference="{{ $transaction->plot_reference }}"
                                        data-quantity="{{ $transaction->quantity !== null ? number_format((float) $transaction->quantity, 2, '.', '') : '' }}"
                                        data-amount-due="{{ number_format((float) $transaction->amount_due, 2, '.', '') }}"
                                        data-maintenance-type="{{ $transaction->maintenance_type ?? 'none' }}"
                                        data-maintenance-years="{{ $transaction->maintenance_years ?? '' }}"
                                        data-has-burial-permit="{{ (int) ($transaction->has_burial_permit ?? 0) }}"
                                        data-other-applicable-fee="{{ number_format((float) ($transaction->other_applicable_fee ?? 0), 2, '.', '') }}"
                                        data-base-fee="{{ number_format((float) ($transaction->base_fee ?? 0), 2, '.', '') }}"
                                        data-maintenance-fee="{{ number_format((float) ($transaction->maintenance_fee ?? 0), 2, '.', '') }}"
                                        data-burial-permit-fee="{{ number_format((float) ($transaction->burial_permit_fee ?? 0), 2, '.', '') }}"
                                        data-remarks="{{ $transaction->remarks }}"
                                        data-status="{{ $transaction->status }}"
                                        title="Edit transaction">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST" action="{{ route('cemetery.transactions.destroy', $transaction) }}" class="js-delete-transaction-form" data-transaction-no="{{ $transaction->transaction_no }}" data-deceased="{{ $transaction->deceased_name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ctx-icon-btn ctx-icon-btn-danger" title="Delete transaction"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="13" style="text-align:center; padding:1.4rem;">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transactions->hasPages())
            <div class="ctx-pagination">
                @if ($transactions->previousPageUrl())
                    <a class="ctx-page-link" href="{{ $transactions->previousPageUrl() }}">Previous</a>
                @else
                    <span class="ctx-page-link disabled">Previous</span>
                @endif
                @if ($transactions->nextPageUrl())
                    <a class="ctx-page-link" href="{{ $transactions->nextPageUrl() }}">Next</a>
                @else
                    <span class="ctx-page-link disabled">Next</span>
                @endif
            </div>
        @endif
    </section>
</div>

<div id="createTransactionModal" class="ctx-modal" aria-hidden="true">
    <div class="ctx-modal-card">
        <div class="ctx-modal-head">
            <h4>Add Cemetery Transaction</h4>
            <button type="button" class="ctx-modal-close" data-close-modal="createTransactionModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="createTransactionForm" method="POST" action="{{ route('cemetery.transactions.store') }}">
            @csrf
            <input type="hidden" name="form_mode" value="create">
            <div class="ctx-modal-body">
                @include('cemetery.partials.transaction_form_fields', [
                    'prefix' => 'newTxn',
                    'sites' => $sites,
                    'categories' => $categories,
                    'transactionTypes' => $transactionTypes,
                    'occupantRecords' => $occupantRecords,
                    'serviceLogs' => $serviceLogs,
                    'serviceLinkMap' => $serviceLinkMap,
                    'prefillData' => $prefillData,
                    'statusOptions' => $statusOptions,
                    'transactionNoValue' => $nextTransactionNo,
                ])
            </div>
            <div class="ctx-modal-foot">
                <button type="button" class="ctx-btn ctx-btn-secondary" data-close-modal="createTransactionModal">Cancel</button>
                <button type="submit" class="ctx-btn ctx-btn-primary">Save Transaction</button>
            </div>
        </form>
    </div>
</div>

<div id="editTransactionModal" class="ctx-modal" aria-hidden="true">
    <div class="ctx-modal-card">
        <div class="ctx-modal-head">
            <h4>Edit Cemetery Transaction</h4>
            <button type="button" class="ctx-modal-close" data-close-modal="editTransactionModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editTransactionForm" method="POST" action="" data-action-template="{{ route('cemetery.transactions.update', '__ID__') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_mode" value="edit">
            <input type="hidden" name="form_transaction_id" id="editFormTransactionId" value="{{ old('form_transaction_id') }}">
            <div class="ctx-modal-body">
                @include('cemetery.partials.transaction_form_fields', [
                    'prefix' => 'editTxn',
                    'sites' => $sites,
                    'categories' => $categories,
                    'transactionTypes' => $transactionTypes,
                    'occupantRecords' => $occupantRecords,
                    'serviceLogs' => $serviceLogs,
                    'serviceLinkMap' => $serviceLinkMap,
                    'prefillData' => null,
                    'statusOptions' => $statusOptions,
                    'transactionNoValue' => old('transaction_no'),
                ])
            </div>
            <div class="ctx-modal-foot">
                <button type="button" class="ctx-btn ctx-btn-secondary" data-close-modal="editTransactionModal">Cancel</button>
                <button type="submit" class="ctx-btn ctx-btn-primary">Update Transaction</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteTransactionModal" class="ctx-modal" aria-hidden="true">
    <div class="ctx-modal-card ctx-modal-card-compact">
        <div class="ctx-modal-head">
            <h4>Delete Transaction</h4>
            <button type="button" class="ctx-modal-close" data-close-modal="deleteTransactionModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="ctx-modal-body">
            <p style="margin:0;">Are you sure you want to delete this transaction?</p>
            <div style="margin-top:12px; border:1px solid #e2e8f0; border-radius:10px; padding:10px 12px; background:#f8fafc;">
                <div><strong id="deleteTransactionNo">-</strong></div>
                <div>Deceased: <span id="deleteTransactionDeceased">-</span></div>
            </div>
        </div>
        <div class="ctx-modal-foot">
            <button type="button" class="ctx-btn ctx-btn-secondary" data-close-modal="deleteTransactionModal">Cancel</button>
            <button type="button" class="ctx-btn ctx-btn-primary" id="confirmDeleteTransactionBtn">Yes, Delete</button>
        </div>
    </div>
</div>

<script>
(() => {
    const createModal = document.getElementById('createTransactionModal');
    const editModal = document.getElementById('editTransactionModal');
    const deleteModal = document.getElementById('deleteTransactionModal');
    const openCreateButton = document.getElementById('openCreateTransactionBtn');
    const closeButtons = Array.from(document.querySelectorAll('[data-close-modal]'));
    const editForm = document.getElementById('editTransactionForm');
    const editActionTemplate = editForm ? (editForm.dataset.actionTemplate || '') : '';
    const confirmDeleteButton = document.getElementById('confirmDeleteTransactionBtn');
    const deleteTransactionNo = document.getElementById('deleteTransactionNo');
    const deleteTransactionDeceased = document.getElementById('deleteTransactionDeceased');
    const oldFormMode = "{{ old('form_mode') }}";
    const oldFormTransactionId = "{{ old('form_transaction_id') }}";
    const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
    let pendingDeleteForm = null;

    const allModals = [createModal, editModal, deleteModal].filter(Boolean);

    const lockBody = () => {
        const hasOpenModal = allModals.some((modal) => modal.classList.contains('is-open'));
        document.body.classList.toggle('ctx-lock-scroll', hasOpenModal);
    };

    const openModal = (modal) => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        const body = modal.querySelector('.ctx-modal-body');
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
        return `PHP ${Number.isFinite(amount) ? amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00'}`;
    };

    const computeFees = (prefix) => {
        const siteSelect = document.getElementById(`${prefix}Site`);
        const categorySelect = document.getElementById(`${prefix}Category`);
        const typeSelect = document.getElementById(`${prefix}TransactionType`);
        const maintenanceTypeSelect = document.getElementById(`${prefix}MaintenanceType`);
        const maintenanceYearsInput = document.getElementById(`${prefix}MaintenanceYears`);
        const permitToggle = document.getElementById(`${prefix}BurialPermitToggle`);
        const otherFeeInput = document.getElementById(`${prefix}OtherFee`);
        const baseFeeOutput = document.getElementById(`${prefix}BaseFee`);
        const maintenanceFeeOutput = document.getElementById(`${prefix}MaintenanceFee`);
        const permitFeeOutput = document.getElementById(`${prefix}BurialPermitFee`);
        const amountDueInput = document.getElementById(`${prefix}AmountDue`);

        if (!siteSelect || !categorySelect || !typeSelect || !amountDueInput) return;

        const selectedSite = siteSelect.options[siteSelect.selectedIndex];
        const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
        const selectedType = typeSelect.options[typeSelect.selectedIndex];

        const siteCode = (selectedSite?.dataset.siteCode || '').toUpperCase();
        const categoryCode = (selectedCategory?.dataset.categoryCode || '').toUpperCase();
        const typeCode = (selectedType?.dataset.typeCode || '').toUpperCase();
        const maintenanceType = String(maintenanceTypeSelect?.value || 'none').toLowerCase();
        const maintenanceYears = Math.max(parseInt(String(maintenanceYearsInput?.value || '0'), 10) || 0, 0);
        const hasPermit = Boolean(permitToggle?.checked);
        const otherFee = Math.max(parseFloat(String(otherFeeInput?.value || '0')) || 0, 0);

        let baseFee = 0;
        let maintenanceFee = 0;
        let burialPermitFee = hasPermit ? 300 : 0;

        if (typeCode === 'SINGLE_NICHE_PURCHASE') {
            if (siteCode === 'SJM') {
                if (categoryCode === 'INFANT') {
                    baseFee = 5000;
                } else if (['REGULAR', 'REGULAR_LARGE'].includes(categoryCode)) {
                    baseFee = 10000;
                }
            } else if (siteCode === 'NMC' && ['COLUMBARIUM', 'INFANT'].includes(categoryCode)) {
                baseFee = 5000;
            }
        } else if (typeCode === 'ADDITIONAL_BURIAL') {
            if (['OMC', 'NMC', 'SPMC'].includes(siteCode)) {
                baseFee = 5000;
            }
        } else if (typeCode === 'LOT_PURCHASE') {
            if (siteCode === 'SPMC') {
                baseFee = 10000;
            }
        } else if (typeCode === 'BURIAL_PERMIT') {
            baseFee = 300;
            burialPermitFee = 0;
        }

        if (maintenanceType === 'five_year_fixed') {
            maintenanceFee = 1500;
        } else if (maintenanceType === 'yearly' && maintenanceYears > 0) {
            maintenanceFee = maintenanceYears * 300;
        }

        const total = Number((baseFee + maintenanceFee + burialPermitFee + otherFee).toFixed(2));
        amountDueInput.value = total.toFixed(2);

        if (baseFeeOutput) baseFeeOutput.value = toMoneyLabel(baseFee);
        if (maintenanceFeeOutput) maintenanceFeeOutput.value = toMoneyLabel(maintenanceFee);
        if (permitFeeOutput) permitFeeOutput.value = toMoneyLabel(burialPermitFee);
    };

    const applyLinkedSource = (prefix, source) => {
        const occupantSelect = document.getElementById(`${prefix}OccupantRecord`);
        const serviceSelect = document.getElementById(`${prefix}ServiceLog`);
        const siteSelect = document.getElementById(`${prefix}Site`);
        const categorySelect = document.getElementById(`${prefix}Category`);
        const deceasedField = document.getElementById(`${prefix}DeceasedName`);
        const plotField = document.getElementById(`${prefix}PlotReference`);

        if (!siteSelect || !categorySelect || !deceasedField || !plotField) return;

        if (source === 'occupant' && occupantSelect) {
            const selected = occupantSelect.options[occupantSelect.selectedIndex];
            if (!selected || !selected.value) return;
            if (serviceSelect && serviceSelect.value !== '') {
                serviceSelect.value = '';
            }
            siteSelect.value = selected.dataset.siteId || siteSelect.value;
            categorySelect.value = selected.dataset.categoryId || categorySelect.value;
            deceasedField.value = selected.dataset.deceasedName || deceasedField.value;
            plotField.value = selected.dataset.plotReference || plotField.value;
        }

        if (source === 'service' && serviceSelect) {
            const selected = serviceSelect.options[serviceSelect.selectedIndex];
            if (!selected || !selected.value) return;
            siteSelect.value = selected.dataset.siteId || siteSelect.value;
            if (selected.dataset.categoryId) {
                categorySelect.value = selected.dataset.categoryId;
            }
            deceasedField.value = selected.dataset.deceasedName || deceasedField.value;
            plotField.value = selected.dataset.plotReference || plotField.value;

            const linkedOccupantId = selected.dataset.occupantRecordId || '';
            if (occupantSelect) {
                occupantSelect.value = linkedOccupantId || '';
            }
        }

        computeFees(prefix);
    };

    const bindSourceLinks = (prefix) => {
        const occupantSelect = document.getElementById(`${prefix}OccupantRecord`);
        const serviceSelect = document.getElementById(`${prefix}ServiceLog`);

        if (occupantSelect) {
            occupantSelect.addEventListener('change', () => applyLinkedSource(prefix, 'occupant'));
        }

        if (serviceSelect) {
            serviceSelect.addEventListener('change', () => applyLinkedSource(prefix, 'service'));
        }
    };

    const bindFeeComputation = (prefix) => {
        const ids = [
            `${prefix}Site`,
            `${prefix}Category`,
            `${prefix}TransactionType`,
            `${prefix}MaintenanceType`,
            `${prefix}MaintenanceYears`,
            `${prefix}BurialPermitToggle`,
            `${prefix}OtherFee`,
        ];

        ids.forEach((id) => {
            const element = document.getElementById(id);
            if (!element) return;
            const eventName = id.endsWith('Toggle') ? 'change' : 'input';
            element.addEventListener(eventName, () => computeFees(prefix));
            if (!id.endsWith('Toggle')) {
                element.addEventListener('change', () => computeFees(prefix));
            }
        });

        computeFees(prefix);
    };

    const openEditFromButton = (button) => {
        if (!editForm) return;

        const transactionId = button.dataset.transactionId || '';
        editForm.action = editActionTemplate.replace('__ID__', transactionId);
        setValue('editFormTransactionId', transactionId);

        setValue('editTxnTransactionNo', button.dataset.transactionNo);
        setValue('editTxnTransactionDate', button.dataset.transactionDate);
        setValue('editTxnSite', button.dataset.siteId);
        setValue('editTxnCategory', button.dataset.categoryId);
        setValue('editTxnTransactionType', button.dataset.transactionTypeId);
        setValue('editTxnOccupantRecord', button.dataset.occupantRecordId || '');
        setValue('editTxnServiceLog', button.dataset.serviceLogId || '');
        setValue('editTxnDeceasedName', button.dataset.deceasedName);
        setValue('editTxnPlotReference', button.dataset.plotReference);
        setValue('editTxnQuantity', button.dataset.quantity);
        setValue('editTxnMaintenanceType', button.dataset.maintenanceType || 'none');
        setValue('editTxnMaintenanceYears', button.dataset.maintenanceYears || '');
        const editPermitToggle = document.getElementById('editTxnBurialPermitToggle');
        if (editPermitToggle) {
            editPermitToggle.checked = String(button.dataset.hasBurialPermit || '0') === '1';
        }
        setValue('editTxnOtherFee', button.dataset.otherApplicableFee || '0');
        setValue('editTxnAmountDue', button.dataset.amountDue);
        setValue('editTxnRemarks', button.dataset.remarks);
        setValue('editTxnStatus', button.dataset.status);
        computeFees('editTxn');

        openModal(editModal);
    };

    bindSourceLinks('newTxn');
    bindSourceLinks('editTxn');
    bindFeeComputation('newTxn');
    bindFeeComputation('editTxn');

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
        const editButton = event.target.closest('.js-open-edit-transaction-btn');
        if (editButton) {
            event.preventDefault();
            openEditFromButton(editButton);
            return;
        }

        const deleteForm = event.target.closest('.js-delete-transaction-form');
        if (!deleteForm) return;
        if (deleteForm.dataset.confirmed === '1') {
            deleteForm.dataset.confirmed = '0';
            return;
        }

        event.preventDefault();
        pendingDeleteForm = deleteForm;
        if (deleteTransactionNo) deleteTransactionNo.textContent = deleteForm.dataset.transactionNo || '-';
        if (deleteTransactionDeceased) deleteTransactionDeceased.textContent = deleteForm.dataset.deceased || '-';
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
            const transactionId = String(oldFormTransactionId || '').trim();
            if (transactionId !== '') {
                editForm.action = editActionTemplate.replace('__ID__', transactionId);
                setValue('editFormTransactionId', transactionId);
            }
            computeFees('editTxn');
            openModal(editModal);
        } else {
            @if($prefillData)
                applyLinkedSource('newTxn', "{{ !empty($prefillData['service_log_id']) ? 'service' : 'occupant' }}");
            @endif
            computeFees('newTxn');
            openModal(createModal);
        }
    } else if ({{ $openCreateModal ? 'true' : 'false' }}) {
        @if($prefillData)
            applyLinkedSource('newTxn', "{{ !empty($prefillData['service_log_id']) ? 'service' : 'occupant' }}");
        @endif
        computeFees('newTxn');
        openModal(createModal);
    }
})();
</script>
@endsection
