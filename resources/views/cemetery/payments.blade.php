@extends('layouts.app')

@section('content')
<style>
    .cpc-page {
        display: grid;
        gap: 16px;
        color: #334155;
        font-family: 'Inter', system-ui, sans-serif;
    }

    .cpc-hero {
        border-radius: 12px;
        border: 1px solid #dbe6f0;
        background: linear-gradient(120deg, #0f5f8f, #1f86ba);
        color: #fff;
        padding: 1.1rem 1.3rem;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.14);
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: center;
    }

    .cpc-hero h2 {
        margin: 0 0 0.25rem;
        font-size: 1.4rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .cpc-hero p {
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.9rem;
    }

    .cpc-add-btn {
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

    .cpc-add-btn:hover { background: rgba(255, 255, 255, 0.28); }

    .cpc-stats {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(6, minmax(0, 1fr));
    }

    .cpc-stat {
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        padding: 0.7rem 0.8rem;
    }

    .cpc-stat span {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .cpc-stat strong {
        display: block;
        margin-top: 0.28rem;
        color: #0f172a;
        font-size: 1.02rem;
    }

    .cpc-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .cpc-card-head {
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.1rem;
        background: #f8fafc;
        display: grid;
        gap: 9px;
    }

    .cpc-card-head h3 {
        margin: 0;
        font-size: 1.05rem;
        color: #0f172a;
    }

    .cpc-filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 8px;
    }

    .cpc-control {
        min-height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #0f172a;
        padding: 0.45rem 0.65rem;
        font-size: 0.86rem;
        width: 100%;
    }

    .cpc-control:focus {
        outline: none;
        border-color: #155f8f;
        box-shadow: 0 0 0 3px rgba(21, 95, 143, 0.11);
    }

    .cpc-filter-actions {
        display: inline-flex;
        gap: 6px;
    }

    .cpc-btn {
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

    .cpc-btn-primary {
        border-color: #155f8f;
        background: #155f8f;
        color: #fff;
    }

    .cpc-btn-secondary {
        border-color: #cbd5e1;
        background: #fff;
        color: #334155;
        text-decoration: none;
    }

    .cpc-table-wrap { overflow: auto; }

    .cpc-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1500px;
    }

    .cpc-table th {
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

    .cpc-table td {
        border-bottom: 1px solid #eef2f7;
        padding: 0.7rem;
        color: #334155;
        font-size: 0.85rem;
        vertical-align: top;
    }

    .cpc-table tbody tr:hover { background: #f8fbff; }

    .cpc-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        border: 1px solid;
        padding: 0.2rem 0.52rem;
        font-size: 0.71rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .cpc-badge-paid { border-color: #86efac; background: #ecfdf5; color: #065f46; }
    .cpc-badge-unpaid { border-color: #fecaca; background: #fff1f2; color: #b91c1c; }
    .cpc-badge-partial { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
    .cpc-badge-overdue { border-color: #fde68a; background: #fffbeb; color: #92400e; }

    .cpc-actions { display: inline-flex; gap: 6px; }
    .cpc-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #d8e2ef;
        background: #fff;
        color: #42566d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .cpc-icon-btn:hover { background: #f1f5f9; color: #155f8f; }
    .cpc-icon-btn-danger:hover { border-color: #fecaca; background: #fff1f2; color: #b91c1c; }

    .cpc-pagination {
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .cpc-page-link {
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

    .cpc-page-link.disabled {
        border-color: #e2e8f0;
        color: #94a3b8;
        background: #f8fafc;
        pointer-events: none;
    }

    .cpc-modal {
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

    .cpc-modal.is-open { display: flex; }

    .cpc-modal-card {
        width: min(1080px, 97vw);
        max-height: 92vh;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        overflow: hidden;
        display: grid;
        grid-template-rows: auto minmax(0, 1fr);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, .1), 0 10px 10px -5px rgba(0, 0, 0, .04);
    }

    .cpc-modal-card-compact { width: min(460px, 96vw); }
    .cpc-modal-head {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cpc-modal-head h4 { margin: 0; color: #0f172a; font-size: 1.06rem; }
    .cpc-modal-close {
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
    .cpc-modal-close:hover { background: #e2e8f0; color: #0f172a; }
    .cpc-modal form { display: grid; grid-template-rows: minmax(0, 1fr) auto; min-height: 0; }
    .cpc-modal-body { padding: 16px 20px; overflow-y: auto; min-height: 0; background: #fff; }
    .cpc-modal-foot {
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 12px 16px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .cpc-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 14px; }
    .cpc-field { display: grid; gap: 6px; }
    .cpc-field-full { grid-column: 1 / -1; }
    .cpc-field label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .cpc-control-textarea { min-height: 84px; resize: vertical; }
    body.cpc-lock-scroll { overflow: hidden; }

    @media (max-width: 1120px) {
        .cpc-hero { grid-template-columns: 1fr; }
        .cpc-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .cpc-filter-grid { grid-template-columns: 1fr 1fr 1fr; }
        .cpc-filter-actions { grid-column: 1 / -1; }
    }

    @media (max-width: 680px) {
        .cpc-stats, .cpc-form-grid, .cpc-filter-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="cpc-page" data-server-rendered-page="cemetery_payments" data-page-title="Payment Collection">
    <section class="cpc-hero">
        <div>
            <h2>Payment Collection</h2>
            <p>Manual office payment recording with official receipt, coverage period, and payment status tracking.</p>
        </div>
        @if ($hasAvailableTransactions)
            <button type="button" id="openCreatePaymentBtn" class="cpc-add-btn">
                <i class="fa-solid fa-plus"></i> Add Payment Record
            </button>
        @else
            <a href="{{ route('cemetery.transactions') }}" class="cpc-add-btn" style="text-decoration:none;">
                <i class="fa-solid fa-receipt"></i> Create Transaction First
            </a>
        @endif
    </section>

    @if (! $hasTransactions)
        <div class="alert alert-warning" style="margin:0;">
            <i class="fa-solid fa-triangle-exclamation"></i> No cemetery transactions found yet. Start from <strong>Cemetery Transactions</strong>, then return here to collect payment.
        </div>
    @elseif ($allTransactionsAlreadyRecorded)
        <div class="alert alert-info" style="margin:0;">
            <i class="fa-solid fa-circle-info"></i> All cemetery transactions already have payment records. Edit an existing payment instead.
        </div>
    @endif

    <section class="cpc-stats">
        <article class="cpc-stat"><span>Total Records</span><strong>{{ number_format((int) $summary['total_records']) }}</strong></article>
        <article class="cpc-stat"><span>Collected Today</span><strong>PHP {{ number_format((float) $summary['collected_today'], 2) }}</strong></article>
        <article class="cpc-stat"><span>Total Collected</span><strong>PHP {{ number_format((float) $summary['total_collected'], 2) }}</strong></article>
        <article class="cpc-stat"><span>Paid</span><strong>{{ number_format((int) $summary['paid_records']) }}</strong></article>
        <article class="cpc-stat"><span>Unpaid / Overdue</span><strong>{{ number_format((int) ($summary['unpaid_records'] + $summary['overdue_records'])) }}</strong></article>
        <article class="cpc-stat"><span>Outstanding</span><strong>PHP {{ number_format((float) $summary['outstanding_total'], 2) }}</strong></article>
    </section>

    @if (session('status'))
        <div class="alert alert-success" style="margin:0;"><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" style="margin:0;">
            <i class="fa-solid fa-circle-exclamation"></i> Please check the form details:
            <ul style="margin:.45rem 0 0 1.2rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="cpc-card">
        <div class="cpc-card-head">
            <h3>Payment Collection List</h3>
            <form method="GET" action="{{ route('cemetery.payments') }}" class="cpc-filter-grid">
                <input type="search" name="q" class="cpc-control" placeholder="Search payment no, transaction, deceased, OR no..." value="{{ $search }}">
                <select name="cemetery_site_id" class="cpc-control">
                    <option value="">All Cemeteries</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" @selected((string) $selectedSiteId === (string) $site->id)>{{ $site->site_name }}</option>
                    @endforeach
                </select>
                <select name="payment_status" class="cpc-control">
                    <option value="">All Statuses</option>
                    @foreach($statusOptions as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" @selected($selectedStatus === $statusKey)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
                <div class="cpc-filter-actions">
                    <button type="submit" class="cpc-btn cpc-btn-primary"><i class="fa-solid fa-filter"></i> Apply</button>
                    <a href="{{ route('cemetery.payments') }}" class="cpc-btn cpc-btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="cpc-table-wrap">
            <table class="cpc-table">
                <thead>
                    <tr>
                        <th>Payment Ref.</th>
                        <th>Transaction Ref.</th>
                        <th>Cemetery</th>
                        <th>Deceased Name</th>
                        <th>Niche / Lot</th>
                        <th>Contact Person</th>
                        <th>Amount Due</th>
                        <th>Amount Paid</th>
                        <th>Official Receipt</th>
                        <th>Payment Date</th>
                        <th>Coverage Period</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paymentCollections as $paymentCollection)
                        @php
                            $transaction = $paymentCollection->transaction;
                        @endphp
                        <tr>
                            <td><strong>{{ $paymentCollection->payment_no }}</strong></td>
                            <td>{{ $transaction?->transaction_no ?: '-' }}</td>
                            <td>{{ $transaction?->site?->site_name ?: '-' }}</td>
                            <td>{{ $transaction?->deceased_name ?: '-' }}</td>
                            <td>{{ $transaction?->plot_reference ?: '-' }}</td>
                            <td>{{ $paymentCollection->contact?->contact_person ?: '-' }}</td>
                            <td><strong>PHP {{ number_format((float) ($transaction?->amount_due ?? 0), 2) }}</strong></td>
                            <td>PHP {{ number_format((float) $paymentCollection->amount_paid, 2) }}</td>
                            <td>{{ $paymentCollection->official_receipt_no ?: '-' }}</td>
                            <td>{{ optional($paymentCollection->payment_date)->format('Y-m-d') ?: '-' }}</td>
                            <td>
                                @if ($paymentCollection->coverage_start_date && $paymentCollection->coverage_end_date)
                                    {{ optional($paymentCollection->coverage_start_date)->format('Y-m-d') }} to {{ optional($paymentCollection->coverage_end_date)->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td><span class="cpc-badge cpc-badge-{{ $paymentCollection->payment_status }}">{{ $statusOptions[$paymentCollection->payment_status] ?? strtoupper($paymentCollection->payment_status) }}</span></td>
                            <td>{{ $paymentCollection->remarks ?: '-' }}</td>
                            <td>
                                <div class="cpc-actions">
                                    <button
                                        type="button"
                                        class="cpc-icon-btn js-open-edit-payment-btn"
                                        data-payment-id="{{ $paymentCollection->id }}"
                                        data-payment-no="{{ $paymentCollection->payment_no }}"
                                        data-transaction-id="{{ $paymentCollection->cemetery_transaction_id }}"
                                        data-contact-id="{{ $paymentCollection->cemetery_contact_id }}"
                                        data-amount-paid="{{ number_format((float) $paymentCollection->amount_paid, 2, '.', '') }}"
                                        data-or-no="{{ $paymentCollection->official_receipt_no }}"
                                        data-payment-date="{{ optional($paymentCollection->payment_date)->format('Y-m-d') }}"
                                        data-coverage-start="{{ optional($paymentCollection->coverage_start_date)->format('Y-m-d') }}"
                                        data-coverage-end="{{ optional($paymentCollection->coverage_end_date)->format('Y-m-d') }}"
                                        data-payment-status="{{ $paymentCollection->payment_status }}"
                                        data-remarks="{{ $paymentCollection->remarks }}"
                                        title="Edit payment record">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST" action="{{ route('cemetery.payments.destroy', $paymentCollection) }}" class="js-delete-payment-form" data-payment-no="{{ $paymentCollection->payment_no }}" data-transaction-no="{{ $transaction?->transaction_no ?: '-' }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cpc-icon-btn cpc-icon-btn-danger" title="Delete payment record"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="14" style="text-align:center; padding:1.4rem;">No payment records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($paymentCollections->hasPages())
            <div class="cpc-pagination">
                @if ($paymentCollections->previousPageUrl())
                    <a class="cpc-page-link" href="{{ $paymentCollections->previousPageUrl() }}">Previous</a>
                @else
                    <span class="cpc-page-link disabled">Previous</span>
                @endif
                @if ($paymentCollections->nextPageUrl())
                    <a class="cpc-page-link" href="{{ $paymentCollections->nextPageUrl() }}">Next</a>
                @else
                    <span class="cpc-page-link disabled">Next</span>
                @endif
            </div>
        @endif
    </section>
</div>

<div id="createPaymentModal" class="cpc-modal" aria-hidden="true">
    <div class="cpc-modal-card">
        <div class="cpc-modal-head">
            <h4>Add Payment Collection Record</h4>
            <button type="button" class="cpc-modal-close" data-close-modal="createPaymentModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="createPaymentForm" method="POST" action="{{ route('cemetery.payments.store') }}">
            @csrf
            <input type="hidden" name="form_mode" value="create">
            <div class="cpc-modal-body">
                @include('cemetery.partials.payment_collection_form_fields', [
                    'prefix' => 'newPay',
                    'transactions' => $availableTransactions,
                    'contacts' => $contacts,
                    'contactByTransactionId' => $contactByTransactionId,
                    'statusOptions' => $statusOptions,
                    'paymentNoValue' => $nextPaymentNo,
                ])
            </div>
            <div class="cpc-modal-foot">
                <button type="button" class="cpc-btn cpc-btn-secondary" data-close-modal="createPaymentModal">Cancel</button>
                <button type="submit" class="cpc-btn cpc-btn-primary">Save Payment Record</button>
            </div>
        </form>
    </div>
</div>

<div id="editPaymentModal" class="cpc-modal" aria-hidden="true">
    <div class="cpc-modal-card">
        <div class="cpc-modal-head">
            <h4>Edit Payment Collection Record</h4>
            <button type="button" class="cpc-modal-close" data-close-modal="editPaymentModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editPaymentForm" method="POST" action="" data-action-template="{{ route('cemetery.payments.update', '__ID__') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_mode" value="edit">
            <input type="hidden" name="form_payment_id" id="editFormPaymentId" value="{{ old('form_payment_id') }}">
            <div class="cpc-modal-body">
                @include('cemetery.partials.payment_collection_form_fields', [
                    'prefix' => 'editPay',
                    'transactions' => $transactions,
                    'contacts' => $contacts,
                    'contactByTransactionId' => $contactByTransactionId,
                    'statusOptions' => $statusOptions,
                    'paymentNoValue' => old('payment_no'),
                ])
            </div>
            <div class="cpc-modal-foot">
                <button type="button" class="cpc-btn cpc-btn-secondary" data-close-modal="editPaymentModal">Cancel</button>
                <button type="submit" class="cpc-btn cpc-btn-primary">Update Payment Record</button>
            </div>
        </form>
    </div>
</div>

<div id="deletePaymentModal" class="cpc-modal" aria-hidden="true">
    <div class="cpc-modal-card cpc-modal-card-compact">
        <div class="cpc-modal-head">
            <h4>Delete Payment Record</h4>
            <button type="button" class="cpc-modal-close" data-close-modal="deletePaymentModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="cpc-modal-body">
            <p style="margin:0;">Are you sure you want to delete this payment record?</p>
            <div style="margin-top:12px; border:1px solid #e2e8f0; border-radius:10px; padding:10px 12px; background:#f8fafc;">
                <div><strong id="deletePaymentNo">-</strong></div>
                <div>Transaction: <span id="deletePaymentTransactionNo">-</span></div>
            </div>
        </div>
        <div class="cpc-modal-foot">
            <button type="button" class="cpc-btn cpc-btn-secondary" data-close-modal="deletePaymentModal">Cancel</button>
            <button type="button" class="cpc-btn cpc-btn-primary" id="confirmDeletePaymentBtn">Yes, Delete</button>
        </div>
    </div>
</div>

<script>
(() => {
    const createModal = document.getElementById('createPaymentModal');
    const editModal = document.getElementById('editPaymentModal');
    const deleteModal = document.getElementById('deletePaymentModal');
    const openCreateButton = document.getElementById('openCreatePaymentBtn');
    const closeButtons = Array.from(document.querySelectorAll('[data-close-modal]'));
    const editForm = document.getElementById('editPaymentForm');
    const editActionTemplate = editForm ? (editForm.dataset.actionTemplate || '') : '';
    const confirmDeleteButton = document.getElementById('confirmDeletePaymentBtn');
    const deletePaymentNo = document.getElementById('deletePaymentNo');
    const deletePaymentTransactionNo = document.getElementById('deletePaymentTransactionNo');
    const oldFormMode = "{{ old('form_mode') }}";
    const oldFormPaymentId = "{{ old('form_payment_id') }}";
    const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
    let pendingDeleteForm = null;

    const allModals = [createModal, editModal, deleteModal].filter(Boolean);

    const lockBody = () => {
        const hasOpenModal = allModals.some((modal) => modal.classList.contains('is-open'));
        document.body.classList.toggle('cpc-lock-scroll', hasOpenModal);
    };

    const openModal = (modal) => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        const body = modal.querySelector('.cpc-modal-body');
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

    const selectedTransactionAmountDue = (prefix) => {
        const transactionSelect = document.getElementById(prefix + 'Transaction');
        if (!transactionSelect) return 0;
        const selectedOption = transactionSelect.options[transactionSelect.selectedIndex];
        const parsed = Number(selectedOption ? (selectedOption.dataset.amountDue || '0') : '0');
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const autoResolveStatus = (prefix) => {
        const statusField = document.getElementById(prefix + 'PaymentStatus');
        const amountPaidField = document.getElementById(prefix + 'AmountPaid');
        if (!statusField || !amountPaidField) return;

        const currentStatus = String(statusField.value || '').toLowerCase();
        const amountPaid = Math.max(Number(amountPaidField.value || 0), 0);
        const amountDue = Math.max(selectedTransactionAmountDue(prefix), 0);

        if (!Number.isFinite(amountPaid) || !Number.isFinite(amountDue)) return;

        if (amountDue <= 0) {
            statusField.value = 'paid';
            return;
        }

        if (currentStatus === 'overdue' && amountPaid < amountDue) {
            return;
        }

        if (amountPaid <= 0) {
            statusField.value = 'unpaid';
        } else if (amountPaid >= amountDue) {
            statusField.value = 'paid';
        } else {
            statusField.value = 'partial';
        }
    };

    const syncCoverageDates = (prefix) => {
        const coverageStartField = document.getElementById(prefix + 'CoverageStart');
        const coverageEndField = document.getElementById(prefix + 'CoverageEnd');
        if (!coverageStartField || !coverageEndField) return;

        const startValue = coverageStartField.value || '';
        if (startValue !== '') {
            coverageEndField.min = startValue;
            if (coverageEndField.value !== '' && coverageEndField.value < startValue) {
                coverageEndField.value = startValue;
            }
            return;
        }

        coverageEndField.removeAttribute('min');
    };

    const updateTransactionMeta = (prefix) => {
        const transactionSelect = document.getElementById(prefix + 'Transaction');
        if (!transactionSelect) return;

        const selectedOption = transactionSelect.options[transactionSelect.selectedIndex];
        const siteName = selectedOption ? (selectedOption.dataset.siteName || '') : '';
        const categoryName = selectedOption ? (selectedOption.dataset.categoryName || '') : '';
        const deceasedName = selectedOption ? (selectedOption.dataset.deceasedName || '') : '';
        const plotReference = selectedOption ? (selectedOption.dataset.plotReference || '') : '';
        const amountDue = selectedOption ? (selectedOption.dataset.amountDue || '') : '';
        const defaultContactId = selectedOption ? (selectedOption.dataset.defaultContactId || '') : '';

        setValue(prefix + 'SiteName', siteName);
        setValue(prefix + 'CategoryName', categoryName);
        setValue(prefix + 'DeceasedName', deceasedName);
        setValue(prefix + 'PlotReference', plotReference);
        setValue(prefix + 'AmountDue', amountDue ? ('PHP ' + Number(amountDue).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) : '');

        const contactSelect = document.getElementById(prefix + 'Contact');
        if (contactSelect && defaultContactId && !contactSelect.value) {
            contactSelect.value = defaultContactId;
        }

        autoResolveStatus(prefix);
    };

    const bindTransactionSelect = (prefix) => {
        const transactionSelect = document.getElementById(prefix + 'Transaction');
        if (!transactionSelect) return;
        transactionSelect.addEventListener('change', () => updateTransactionMeta(prefix));
        updateTransactionMeta(prefix);
    };

    const bindFormAssist = (prefix) => {
        const amountPaidField = document.getElementById(prefix + 'AmountPaid');
        const statusField = document.getElementById(prefix + 'PaymentStatus');
        const coverageStartField = document.getElementById(prefix + 'CoverageStart');
        const coverageEndField = document.getElementById(prefix + 'CoverageEnd');

        if (amountPaidField) {
            amountPaidField.addEventListener('input', () => autoResolveStatus(prefix));
            amountPaidField.addEventListener('change', () => autoResolveStatus(prefix));
        }

        if (statusField) {
            statusField.addEventListener('change', () => autoResolveStatus(prefix));
        }

        if (coverageStartField) {
            coverageStartField.addEventListener('change', () => syncCoverageDates(prefix));
        }

        if (coverageEndField) {
            coverageEndField.addEventListener('change', () => syncCoverageDates(prefix));
        }

        syncCoverageDates(prefix);
        autoResolveStatus(prefix);
    };

    bindTransactionSelect('newPay');
    bindTransactionSelect('editPay');
    bindFormAssist('newPay');
    bindFormAssist('editPay');

    const openEditFromButton = (button) => {
        if (!editForm) return;

        const paymentId = button.dataset.paymentId || '';
        editForm.action = editActionTemplate.replace('__ID__', paymentId);
        setValue('editFormPaymentId', paymentId);

        setValue('editPayPaymentNo', button.dataset.paymentNo);
        setValue('editPayTransaction', button.dataset.transactionId);
        setValue('editPayContact', button.dataset.contactId);
        setValue('editPayAmountPaid', button.dataset.amountPaid);
        setValue('editPayOrNo', button.dataset.orNo);
        setValue('editPayPaymentDate', button.dataset.paymentDate);
        setValue('editPayCoverageStart', button.dataset.coverageStart);
        setValue('editPayCoverageEnd', button.dataset.coverageEnd);
        setValue('editPayPaymentStatus', button.dataset.paymentStatus);
        setValue('editPayRemarks', button.dataset.remarks);
        updateTransactionMeta('editPay');
        syncCoverageDates('editPay');
        autoResolveStatus('editPay');

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
        const editButton = event.target.closest('.js-open-edit-payment-btn');
        if (editButton) {
            event.preventDefault();
            openEditFromButton(editButton);
            return;
        }

        const deleteForm = event.target.closest('.js-delete-payment-form');
        if (!deleteForm) return;
        if (deleteForm.dataset.confirmed === '1') {
            deleteForm.dataset.confirmed = '0';
            return;
        }

        event.preventDefault();
        pendingDeleteForm = deleteForm;
        if (deletePaymentNo) deletePaymentNo.textContent = deleteForm.dataset.paymentNo || '-';
        if (deletePaymentTransactionNo) deletePaymentTransactionNo.textContent = deleteForm.dataset.transactionNo || '-';
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
            const paymentId = String(oldFormPaymentId || '').trim();
            if (paymentId !== '') {
                editForm.action = editActionTemplate.replace('__ID__', paymentId);
                setValue('editFormPaymentId', paymentId);
            }
            updateTransactionMeta('editPay');
            syncCoverageDates('editPay');
            autoResolveStatus('editPay');
            openModal(editModal);
        } else {
            updateTransactionMeta('newPay');
            syncCoverageDates('newPay');
            autoResolveStatus('newPay');
            openModal(createModal);
        }
    }
})();
</script>
@endsection
