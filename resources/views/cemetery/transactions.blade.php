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

    .ctx-flow-cta {
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
        text-decoration: none;
    }

    .ctx-flow-cta:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #fff;
    }

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
        min-width: 980px;
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
    .ctx-cell-main {
        font-weight: 700;
        color: #0f172a;
    }
    .ctx-cell-sub {
        font-size: 0.78rem;
        color: #64748b;
        margin-top: 2px;
    }

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
    .ctx-icon-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        pointer-events: none;
        background: #f8fafc;
        color: #94a3b8;
    }

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
    .ctx-modal-card-view { width: min(900px, 96vw); }
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
    .ctx-view-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px 12px;
    }
    .ctx-view-item {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        padding: 10px 12px;
    }
    .ctx-view-item strong {
        display: block;
        color: #334155;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
    }
    .ctx-view-item span {
        color: #0f172a;
        font-size: 0.9rem;
        word-break: break-word;
    }
    .ctx-view-item.ctx-view-item-wide {
        grid-column: 1 / -1;
    }

    body.ctx-lock-scroll { overflow: hidden; }

    @media (max-width: 1120px) {
        .ctx-hero { grid-template-columns: 1fr; }
        .ctx-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ctx-filter-grid { grid-template-columns: 1fr 1fr 1fr; }
        .ctx-filter-actions { grid-column: 1 / -1; }
    }

    @media (max-width: 680px) {
        .ctx-stats, .ctx-form-grid, .ctx-filter-grid { grid-template-columns: 1fr; }
        .ctx-view-grid { grid-template-columns: 1fr; }
    }
</style>

<div
    class="ctx-page"
    data-server-rendered-page="cemetery_transactions"
    data-page-title="Cemetery Transactions"
    data-old-form-mode="{{ old('form_mode', '') }}"
    data-old-form-transaction-id="{{ old('form_transaction_id', '') }}"
    data-old-quick-transaction-id="{{ old('quick_transaction_id', '') }}"
    data-has-errors="{{ $errors->any() ? '1' : '0' }}"
    data-open-create-modal="{{ $openCreateModal ? '1' : '0' }}"
    data-quick-pay-template="{{ route('cemetery.payments.quick_pay', '__ID__') }}"
    data-last-payment-id="{{ session('last_payment_id', '') }}">
    <section class="ctx-hero">
        <div>
            <h2>Cemetery Transactions</h2>
            <p>Transactions are created from Occupant Records to keep all records connected and valid.</p>
        </div>
        <a href="{{ route('cemetery.records') }}" class="ctx-flow-cta">
            <i class="fa-solid fa-users"></i> Create from Occupant Records
        </a>
    </section>

    <section class="ctx-stats">
        <article class="ctx-stat"><span>Total Transactions</span><strong>{{ number_format((int) $summary['total_transactions']) }}</strong></article>
        <article class="ctx-stat"><span>Transactions Today</span><strong>{{ number_format((int) $summary['today_transactions']) }}</strong></article>
        <article class="ctx-stat"><span>Pending</span><strong>{{ number_format((int) $summary['pending_transactions']) }}</strong></article>
        <article class="ctx-stat"><span>Paid</span><strong>{{ number_format((int) $summary['paid_transactions']) }}</strong></article>
        <article class="ctx-stat"><span>Total Amount Due</span><strong>PHP {{ number_format((float) $summary['total_due'], 2) }}</strong></article>
    </section>

    @if (session('status'))
        <div class="alert alert-success" style="margin:0; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
            <span><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</span>
            @if (session('last_payment_id'))
                <a
                    href="{{ route('cemetery.payments.receipt', (int) session('last_payment_id')) }}"
                    target="_blank"
                    rel="noopener"
                    class="ctx-btn ctx-btn-secondary"
                    style="min-height:34px;">
                    <i class="fa-solid fa-receipt"></i> View Receipt
                </a>
            @endif
        </div>
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
                        <th>Deceased Name</th>
                        <th>Niche / Lot</th>
                        <th>Cemetery / Category</th>
                        <th>Transaction Type</th>
                        <th>Occupant Record</th>
                        <th>Amount Due</th>
                        <th>Paid To Date</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        @php
                            $totalPaid = round((float) ($transaction->total_paid ?? 0), 2);
                            $amountDue = round((float) ($transaction->amount_due ?? 0), 2);
                            $balance = round((float) ($transaction->remaining_balance ?? max($amountDue - $totalPaid, 0)), 2);
                            $hasPaymentRecord = ((int) ($transaction->payments_count ?? 0)) > 0;
                            $isQuickPayAllowed = $transaction->status !== 'cancelled' && $balance > 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="ctx-cell-main">{{ $transaction->transaction_no }}</div>
                            </td>
                            <td>
                                <div>{{ optional($transaction->transaction_date)->format('Y-m-d') }}</div>
                                <div class="ctx-cell-sub">{{ optional($transaction->transaction_date)->format('h:i A') }}</div>
                            </td>
                            <td class="ctx-cell-main">{{ $transaction->deceased_name }}</td>
                            <td class="ctx-cell-main">{{ $transaction->plot_reference }}</td>
                            <td>
                                <div>{{ $transaction->site?->site_name ?: '-' }}</div>
                                <div class="ctx-cell-sub">{{ $transaction->category?->category_name ?: '-' }}</div>
                            </td>
                            <td>{{ $transaction->transactionType?->type_name ?: '-' }}</td>
                            <td>
                                @if ($transaction->occupant_record_id)
                                    <span class="ctx-badge ctx-badge-paid">OCCUPANT</span>
                                    <div class="csl-muted">{{ $transaction->occupantRecord?->record_no ?: '-' }}</div>
                                @else
                                    <span class="csl-muted">-</span>
                                @endif
                            </td>
                            <td><strong>PHP {{ number_format($amountDue, 2) }}</strong></td>
                            <td>PHP {{ number_format($totalPaid, 2) }}</td>
                            <td><strong>PHP {{ number_format($balance, 2) }}</strong></td>
                            <td><span class="ctx-badge ctx-badge-{{ $transaction->status }}">{{ $statusOptions[$transaction->status] ?? strtoupper($transaction->status) }}</span></td>
                            <td>
                                <div class="ctx-actions">
                                    <button
                                        type="button"
                                        class="ctx-icon-btn js-open-view-transaction-btn"
                                        data-transaction-no="{{ $transaction->transaction_no }}"
                                        data-transaction-date="{{ optional($transaction->transaction_date)->format('Y-m-d h:i A') }}"
                                        data-cemetery-name="{{ $transaction->site?->site_name ?: '-' }}"
                                        data-category-name="{{ $transaction->category?->category_name ?: '-' }}"
                                        data-deceased-name="{{ $transaction->deceased_name }}"
                                        data-plot-reference="{{ $transaction->plot_reference }}"
                                        data-transaction-type-name="{{ $transaction->transactionType?->type_name ?: '-' }}"
                                        data-source-type="Occupant Record"
                                        data-source-reference="{{ $transaction->occupantRecord?->record_no ?: '-' }}"
                                        data-quantity="{{ $transaction->quantity !== null ? number_format((float) $transaction->quantity, 2) : '-' }}"
                                        data-status="{{ $statusOptions[$transaction->status] ?? strtoupper($transaction->status) }}"
                                        data-amount-due="{{ number_format((float) $transaction->amount_due, 2) }}"
                                        data-base-fee="{{ number_format((float) ($transaction->base_fee ?? 0), 2) }}"
                                        data-maintenance-fee="{{ number_format((float) ($transaction->maintenance_fee ?? 0), 2) }}"
                                        data-burial-permit-fee="{{ number_format((float) ($transaction->burial_permit_fee ?? 0), 2) }}"
                                        data-other-applicable-fee="{{ number_format((float) ($transaction->other_applicable_fee ?? 0), 2) }}"
                                        data-remarks="{{ $transaction->remarks ?: '-' }}"
                                        title="View full transaction">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="ctx-icon-btn js-open-quick-pay-btn"
                                        data-quick-pay-action="{{ route('cemetery.payments.quick_pay', $transaction) }}"
                                        data-quick-transaction-id="{{ $transaction->id }}"
                                        data-quick-transaction-no="{{ $transaction->transaction_no }}"
                                        data-quick-deceased-name="{{ $transaction->deceased_name }}"
                                        data-quick-amount-due="{{ number_format($amountDue, 2, '.', '') }}"
                                        data-quick-total-paid="{{ number_format($totalPaid, 2, '.', '') }}"
                                        data-quick-balance="{{ number_format($balance, 2, '.', '') }}"
                                        data-quick-pay-enabled="{{ $isQuickPayAllowed ? '1' : '0' }}"
                                        title="{{ $isQuickPayAllowed ? 'Collect payment' : 'Already fully paid or cancelled' }}"
                                        @disabled(! $isQuickPayAllowed)>
                                        <i class="fa-solid fa-cash-register"></i>
                                    </button>
                                    @if($hasPaymentRecord && ! empty($transaction->latest_payment_id))
                                        <a
                                            href="{{ route('cemetery.payments.receipt', (int) $transaction->latest_payment_id) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="ctx-icon-btn"
                                            title="Open latest receipt">
                                            <i class="fa-solid fa-receipt"></i>
                                        </a>
                                    @endif
                                    <button
                                        type="button"
                                        class="ctx-icon-btn js-open-edit-transaction-btn"
                                        data-transaction-id="{{ $transaction->id }}"
                                        data-transaction-no="{{ $transaction->transaction_no }}"
                                        data-transaction-date="{{ optional($transaction->transaction_date)->format('Y-m-d\\TH:i') }}"
                                        data-site-id="{{ $transaction->cemetery_site_id }}"
                                        data-category-id="{{ $transaction->cemetery_category_id }}"
                                        data-transaction-type-id="{{ $transaction->cemetery_transaction_type_id }}"
                                        data-occupant-record-id="{{ $transaction->occupant_record_id }}"
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
                                    @if($hasPaymentRecord)
                                        <button type="button" class="ctx-icon-btn ctx-icon-btn-danger" title="Cannot delete: transaction has payment collection record" disabled>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('cemetery.transactions.destroy', $transaction) }}" class="js-delete-transaction-form" data-transaction-no="{{ $transaction->transaction_no }}" data-deceased="{{ $transaction->deceased_name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ctx-icon-btn ctx-icon-btn-danger" title="Delete transaction"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="12" style="text-align:center; padding:1.4rem;">No transactions found.</td></tr>
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

<div id="viewTransactionModal" class="ctx-modal" aria-hidden="true">
    <div class="ctx-modal-card ctx-modal-card-view">
        <div class="ctx-modal-head">
            <h4>Transaction Details</h4>
            <button type="button" class="ctx-modal-close" data-close-modal="viewTransactionModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="ctx-modal-body">
            <div class="ctx-view-grid">
                <div class="ctx-view-item"><strong>Transaction No.</strong><span id="viewTxnNo">-</span></div>
                <div class="ctx-view-item"><strong>Date & Time</strong><span id="viewTxnDate">-</span></div>
                <div class="ctx-view-item"><strong>Status</strong><span id="viewTxnStatus">-</span></div>
                <div class="ctx-view-item"><strong>Deceased Name</strong><span id="viewTxnDeceased">-</span></div>
                <div class="ctx-view-item"><strong>Niche / Lot</strong><span id="viewTxnPlot">-</span></div>
                <div class="ctx-view-item"><strong>Transaction Type</strong><span id="viewTxnType">-</span></div>
                <div class="ctx-view-item"><strong>Cemetery Name</strong><span id="viewTxnCemetery">-</span></div>
                <div class="ctx-view-item"><strong>Cemetery Category</strong><span id="viewTxnCategory">-</span></div>
                <div class="ctx-view-item"><strong>Source</strong><span id="viewTxnSource">-</span></div>
                <div class="ctx-view-item"><strong>Quantity</strong><span id="viewTxnQuantity">-</span></div>
                <div class="ctx-view-item"><strong>Amount Due</strong><span id="viewTxnAmountDue">-</span></div>
                <div class="ctx-view-item"><strong>Base Fee</strong><span id="viewTxnBaseFee">-</span></div>
                <div class="ctx-view-item"><strong>Maintenance Fee</strong><span id="viewTxnMaintenanceFee">-</span></div>
                <div class="ctx-view-item"><strong>Burial Permit Fee</strong><span id="viewTxnPermitFee">-</span></div>
                <div class="ctx-view-item"><strong>Other Applicable Fee</strong><span id="viewTxnOtherFee">-</span></div>
                <div class="ctx-view-item ctx-view-item-wide"><strong>Remarks</strong><span id="viewTxnRemarks">-</span></div>
            </div>
        </div>
        <div class="ctx-modal-foot">
            <button type="button" class="ctx-btn ctx-btn-secondary" data-close-modal="viewTransactionModal">Close</button>
        </div>
    </div>
</div>

<div id="quickPayModal" class="ctx-modal" aria-hidden="true">
    <div class="ctx-modal-card" style="width:min(780px,96vw);">
        <div class="ctx-modal-head">
            <h4>Collect Payment</h4>
            <button type="button" class="ctx-modal-close" data-close-modal="quickPayModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="quickPayForm" method="POST" action="">
            @csrf
            <input type="hidden" name="form_mode" value="quick_pay">
            <input type="hidden" name="quick_transaction_id" id="quickPayTransactionId" value="{{ old('quick_transaction_id') }}">
            <div class="ctx-modal-body">
                <div class="ctx-form-grid">
                    <div class="ctx-field">
                        <label><i class="fa-solid fa-hashtag"></i>Transaction No.</label>
                        <input id="quickPayTransactionNo" type="text" class="ctx-control" readonly>
                    </div>
                    <div class="ctx-field">
                        <label><i class="fa-solid fa-cross"></i>Deceased Name</label>
                        <input id="quickPayDeceasedName" type="text" class="ctx-control" readonly>
                    </div>
                    <div class="ctx-field">
                        <label><i class="fa-solid fa-peso-sign"></i>Amount Due</label>
                        <input id="quickPayAmountDue" type="text" class="ctx-control" readonly>
                    </div>
                    <div class="ctx-field">
                        <label><i class="fa-solid fa-money-bills"></i>Paid To Date</label>
                        <input id="quickPayTotalPaid" type="text" class="ctx-control" readonly>
                    </div>
                    <div class="ctx-field">
                        <label><i class="fa-solid fa-scale-balanced"></i>Current Balance</label>
                        <input id="quickPayCurrentBalance" type="text" class="ctx-control" readonly>
                    </div>
                    <div class="ctx-field">
                        <label><i class="fa-solid fa-scale-unbalanced"></i>Balance After Payment</label>
                        <input id="quickPayBalanceAfter" type="text" class="ctx-control" readonly>
                    </div>
                    <div class="ctx-field">
                        <label for="quickPayAmountPaid"><i class="fa-solid fa-cash-register"></i>Amount To Pay (Deduct Today)</label>
                        <input id="quickPayAmountPaid" name="amount_paid" type="number" step="0.01" min="0.01" class="ctx-control" value="{{ old('amount_paid') }}" required>
                    </div>
                    <div class="ctx-field">
                        <label for="quickPayDate"><i class="fa-regular fa-calendar-days"></i>Payment Date</label>
                        <input id="quickPayDate" name="payment_date" type="date" class="ctx-control" value="{{ old('payment_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="ctx-field ctx-field-full">
                        <label for="quickPayRemarks"><i class="fa-solid fa-comment-dots"></i>Remarks</label>
                        <textarea id="quickPayRemarks" name="remarks" class="ctx-control ctx-control-textarea">{{ old('remarks') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="ctx-modal-foot">
                <button type="button" class="ctx-btn ctx-btn-secondary" data-close-modal="quickPayModal">Cancel</button>
                <button type="submit" class="ctx-btn ctx-btn-primary">Save Payment</button>
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
    const page = document.querySelector('.ctx-page[data-server-rendered-page="cemetery_transactions"]');
    const createModal = document.getElementById('createTransactionModal');
    const viewModal = document.getElementById('viewTransactionModal');
    const quickPayModal = document.getElementById('quickPayModal');
    const editModal = document.getElementById('editTransactionModal');
    const deleteModal = document.getElementById('deleteTransactionModal');
    const openCreateButton = document.getElementById('openCreateTransactionBtn');
    const closeButtons = Array.from(document.querySelectorAll('[data-close-modal]'));
    const editForm = document.getElementById('editTransactionForm');
    const quickPayForm = document.getElementById('quickPayForm');
    const editActionTemplate = editForm ? (editForm.dataset.actionTemplate || '') : '';
    const quickPayTransactionIdInput = document.getElementById('quickPayTransactionId');
    const quickPayTransactionNoField = document.getElementById('quickPayTransactionNo');
    const quickPayDeceasedNameField = document.getElementById('quickPayDeceasedName');
    const quickPayAmountDueField = document.getElementById('quickPayAmountDue');
    const quickPayTotalPaidField = document.getElementById('quickPayTotalPaid');
    const quickPayCurrentBalanceField = document.getElementById('quickPayCurrentBalance');
    const quickPayBalanceAfterField = document.getElementById('quickPayBalanceAfter');
    const quickPayAmountPaidInput = document.getElementById('quickPayAmountPaid');
    const confirmDeleteButton = document.getElementById('confirmDeleteTransactionBtn');
    const deleteTransactionNo = document.getElementById('deleteTransactionNo');
    const deleteTransactionDeceased = document.getElementById('deleteTransactionDeceased');
    const oldFormMode = page?.dataset.oldFormMode || '';
    const oldFormTransactionId = page?.dataset.oldFormTransactionId || '';
    const oldQuickTransactionId = page?.dataset.oldQuickTransactionId || '';
    const quickPayActionTemplate = page?.dataset.quickPayTemplate || '';
    const hasErrors = (page?.dataset.hasErrors || '0') === '1';
    const openCreateModal = (page?.dataset.openCreateModal || '0') === '1';
    let pendingDeleteForm = null;

    const allModals = [createModal, viewModal, quickPayModal, editModal, deleteModal].filter(Boolean);

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

    const setText = (id, value) => {
        const field = document.getElementById(id);
        if (!field) return;
        field.textContent = value || '-';
    };

    const currentDatetimeLocal = () => {
        const now = new Date();
        const pad = (value) => String(value).padStart(2, '0');
        return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
    };

    const setNowTransactionDateIfEmpty = (prefix) => {
        const field = document.getElementById(`${prefix}TransactionDate`);
        if (!field) return;
        if (String(field.value || '').trim() !== '') return;
        field.value = currentDatetimeLocal();
    };

    const toMoneyLabel = (value) => {
        const amount = Number(value || 0);
        return `PHP ${Number.isFinite(amount) ? amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00'}`;
    };

    const parseMoney = (value) => {
        const normalized = String(value ?? '').replace(/[^0-9.-]/g, '');
        const parsed = Number.parseFloat(normalized);
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const setQuickPaySummary = (amountDue, totalPaid, inputAmount) => {
        const due = Math.max(parseMoney(amountDue), 0);
        const paid = Math.max(parseMoney(totalPaid), 0);
        const currentBalance = Math.max(due - paid, 0);
        const entered = Math.max(parseMoney(inputAmount), 0);
        const balanceAfter = Math.max(currentBalance - entered, 0);

        if (quickPayAmountDueField) quickPayAmountDueField.value = toMoneyLabel(due);
        if (quickPayTotalPaidField) quickPayTotalPaidField.value = toMoneyLabel(paid);
        if (quickPayCurrentBalanceField) quickPayCurrentBalanceField.value = toMoneyLabel(currentBalance);
        if (quickPayBalanceAfterField) quickPayBalanceAfterField.value = toMoneyLabel(balanceAfter);
    };

    const openQuickPayFromButton = (button) => {
        if (!quickPayForm || !quickPayModal) return;

        const canPay = String(button.dataset.quickPayEnabled || '0') === '1';
        if (!canPay) {
            return;
        }

        const action = button.dataset.quickPayAction || '';
        if (action !== '') {
            quickPayForm.action = action;
        }

        const transactionId = String(button.dataset.quickTransactionId || '');
        const transactionNo = String(button.dataset.quickTransactionNo || '');
        const deceasedName = String(button.dataset.quickDeceasedName || '');
        const amountDue = String(button.dataset.quickAmountDue || '0');
        const totalPaid = String(button.dataset.quickTotalPaid || '0');
        const currentBalance = String(button.dataset.quickBalance || '0');

        if (quickPayTransactionIdInput) quickPayTransactionIdInput.value = transactionId;
        if (quickPayTransactionNoField) quickPayTransactionNoField.value = transactionNo;
        if (quickPayDeceasedNameField) quickPayDeceasedNameField.value = deceasedName;

        const suggestedPayment = parseMoney(currentBalance);
        if (quickPayAmountPaidInput) {
            quickPayAmountPaidInput.max = suggestedPayment.toFixed(2);
            if (!quickPayAmountPaidInput.value || parseMoney(quickPayAmountPaidInput.value) <= 0) {
                quickPayAmountPaidInput.value = suggestedPayment > 0 ? suggestedPayment.toFixed(2) : '';
            }
        }

        setQuickPaySummary(amountDue, totalPaid, quickPayAmountPaidInput?.value || '0');
        openModal(quickPayModal);
    };

    const computeFees = (prefix) => {
        const occupantSelect = document.getElementById(`${prefix}OccupantRecord`);
        const typeSelect = document.getElementById(`${prefix}TransactionType`);
        const maintenanceTypeSelect = document.getElementById(`${prefix}MaintenanceType`);
        const maintenanceYearsInput = document.getElementById(`${prefix}MaintenanceYears`);
        const permitToggle = document.getElementById(`${prefix}BurialPermitToggle`);
        const otherFeeInput = document.getElementById(`${prefix}OtherFee`);
        const baseFeeOutput = document.getElementById(`${prefix}BaseFee`);
        const maintenanceFeeOutput = document.getElementById(`${prefix}MaintenanceFee`);
        const permitFeeOutput = document.getElementById(`${prefix}BurialPermitFee`);
        const amountDueInput = document.getElementById(`${prefix}AmountDue`);

        if (!occupantSelect || !typeSelect || !amountDueInput) return;

        const selectedOccupant = occupantSelect.options[occupantSelect.selectedIndex];
        const selectedType = typeSelect.options[typeSelect.selectedIndex];

        const siteCode = (selectedOccupant?.dataset.siteCode || '').toUpperCase();
        const categoryCode = (selectedOccupant?.dataset.categoryCode || '').toUpperCase();
        const typeCode = (selectedType?.dataset.typeCode || '').toUpperCase();
        const maintenanceType = String(maintenanceTypeSelect?.value || 'none').toLowerCase();
        const maintenanceYears = Math.max(parseInt(String(maintenanceYearsInput?.value || '0'), 10) || 0, 0);
        const hasPermit = Boolean(permitToggle?.checked);
        let otherFee = Math.max(parseFloat(String(otherFeeInput?.value || '0')) || 0, 0);

        let baseFee = 0;
        let maintenanceFee = 0;
        let burialPermitFee = hasPermit ? 300 : 0;
        let forceBurialPermitFee = false;

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
                forceBurialPermitFee = true;
            }
        } else if (typeCode === 'LOT_PURCHASE') {
            if (siteCode === 'SPMC') {
                baseFee = 10000;
                forceBurialPermitFee = true;
            }
        } else if (typeCode === 'BURIAL_PERMIT') {
            baseFee = 300;
            burialPermitFee = 0;
        } else if (typeCode === 'EXHUMATION') {
            baseFee = 200;
            burialPermitFee = 0;
        } else if (['TRANSFER', 'OTHER'].includes(typeCode)) {
            baseFee = 300;
            maintenanceFee = 0;
            burialPermitFee = 0;
            if (otherFee > 200) {
                otherFee = 200;
                if (otherFeeInput) {
                    otherFeeInput.value = '200';
                }
            }
        }

        if (maintenanceType === 'five_year_fixed') {
            maintenanceFee = 1500;
        } else if (maintenanceType === 'yearly' && maintenanceYears > 0) {
            maintenanceFee = maintenanceYears * 300;
        }

        if (['TRANSFER', 'OTHER', 'EXHUMATION'].includes(typeCode)) {
            maintenanceFee = 0;
        }

        if (forceBurialPermitFee) {
            burialPermitFee = 300;
        }

        const total = Number((baseFee + maintenanceFee + burialPermitFee + otherFee).toFixed(2));
        amountDueInput.value = total.toFixed(2);

        if (baseFeeOutput) baseFeeOutput.value = toMoneyLabel(baseFee);
        if (maintenanceFeeOutput) maintenanceFeeOutput.value = toMoneyLabel(maintenanceFee);
        if (permitFeeOutput) permitFeeOutput.value = toMoneyLabel(burialPermitFee);
    };

    const syncSourceDetails = (prefix) => {
        const occupantSelect = document.getElementById(`${prefix}OccupantRecord`);
        if (!occupantSelect) return;

        const selected = occupantSelect.options[occupantSelect.selectedIndex];
        const siteId = selected?.dataset.siteId || '';
        const siteName = selected?.dataset.siteName || '';
        const categoryId = selected?.dataset.categoryId || '';
        const categoryName = selected?.dataset.categoryName || '';
        const deceasedName = selected?.dataset.deceasedName || '';
        const plotReference = selected?.dataset.plotReference || '';

        setValue(`${prefix}Site`, siteId);
        setValue(`${prefix}Category`, categoryId);
        setValue(`${prefix}DeceasedName`, deceasedName);
        setValue(`${prefix}PlotReference`, plotReference);
        setValue(`${prefix}SiteName`, siteName);
        setValue(`${prefix}CategoryName`, categoryName);
        setValue(`${prefix}DeceasedNameDisplay`, deceasedName);
        setValue(`${prefix}PlotReferenceDisplay`, plotReference);
    };

    const bindOccupantSource = (prefix) => {
        const occupantSelect = document.getElementById(`${prefix}OccupantRecord`);
        if (!occupantSelect) return;

        occupantSelect.addEventListener('change', () => {
            syncSourceDetails(prefix);
            computeFees(prefix);
        });
        syncSourceDetails(prefix);
    };

    const bindFeeComputation = (prefix) => {
        const ids = [
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
        setValue('editTxnTransactionType', button.dataset.transactionTypeId);
        setValue('editTxnOccupantRecord', button.dataset.occupantRecordId || '');
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
        syncSourceDetails('editTxn');
        computeFees('editTxn');

        openModal(editModal);
    };

    const openViewFromButton = (button) => {
        setText('viewTxnNo', button.dataset.transactionNo);
        setText('viewTxnDate', button.dataset.transactionDate);
        setText('viewTxnStatus', button.dataset.status);
        setText('viewTxnDeceased', button.dataset.deceasedName);
        setText('viewTxnPlot', button.dataset.plotReference);
        setText('viewTxnType', button.dataset.transactionTypeName);
        setText('viewTxnCemetery', button.dataset.cemeteryName);
        setText('viewTxnCategory', button.dataset.categoryName);
        setText('viewTxnSource', `${button.dataset.sourceType || '-'} (${button.dataset.sourceReference || '-'})`);
        setText('viewTxnQuantity', button.dataset.quantity);
        setText('viewTxnAmountDue', `PHP ${button.dataset.amountDue || '0.00'}`);
        setText('viewTxnBaseFee', `PHP ${button.dataset.baseFee || '0.00'}`);
        setText('viewTxnMaintenanceFee', `PHP ${button.dataset.maintenanceFee || '0.00'}`);
        setText('viewTxnPermitFee', `PHP ${button.dataset.burialPermitFee || '0.00'}`);
        setText('viewTxnOtherFee', `PHP ${button.dataset.otherApplicableFee || '0.00'}`);
        setText('viewTxnRemarks', button.dataset.remarks || '-');
        openModal(viewModal);
    };

    bindOccupantSource('newTxn');
    bindOccupantSource('editTxn');
    bindFeeComputation('newTxn');
    bindFeeComputation('editTxn');

    if (quickPayAmountPaidInput) {
        const recalcQuickPay = () => {
            const dueRaw = quickPayAmountDueField?.value || '0';
            const paidRaw = quickPayTotalPaidField?.value || '0';
            setQuickPaySummary(dueRaw, paidRaw, quickPayAmountPaidInput.value || '0');
        };
        quickPayAmountPaidInput.addEventListener('input', recalcQuickPay);
        quickPayAmountPaidInput.addEventListener('change', recalcQuickPay);
    }

    if (openCreateButton) {
        openCreateButton.addEventListener('click', () => {
            setNowTransactionDateIfEmpty('newTxn');
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
        const viewButton = event.target.closest('.js-open-view-transaction-btn');
        if (viewButton) {
            event.preventDefault();
            openViewFromButton(viewButton);
            return;
        }

        const editButton = event.target.closest('.js-open-edit-transaction-btn');
        if (editButton) {
            event.preventDefault();
            openEditFromButton(editButton);
            return;
        }

        const quickPayButton = event.target.closest('.js-open-quick-pay-btn');
        if (quickPayButton) {
            event.preventDefault();
            openQuickPayFromButton(quickPayButton);
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
        closeModal(viewModal);
        closeModal(quickPayModal);
        closeModal(editModal);
        closeModal(deleteModal);
    });

    if (hasErrors) {
        if (oldFormMode === 'quick_pay' && quickPayModal && quickPayForm) {
            const quickTransactionId = String(oldQuickTransactionId || '').trim();
            const quickPayButton = quickTransactionId !== ''
                ? document.querySelector(`.js-open-quick-pay-btn[data-quick-transaction-id="${quickTransactionId}"]`)
                : null;

            if (quickPayButton) {
                openQuickPayFromButton(quickPayButton);
            } else {
                if (quickPayActionTemplate !== '' && quickTransactionId !== '') {
                    quickPayForm.action = quickPayActionTemplate.replace('__ID__', quickTransactionId);
                    if (quickPayTransactionIdInput) quickPayTransactionIdInput.value = quickTransactionId;
                }
                openModal(quickPayModal);
            }
        } else if (oldFormMode === 'edit' && editForm) {
            const transactionId = String(oldFormTransactionId || '').trim();
            if (transactionId !== '') {
                editForm.action = editActionTemplate.replace('__ID__', transactionId);
                setValue('editFormTransactionId', transactionId);
            }
            computeFees('editTxn');
            openModal(editModal);
        } else {
            setNowTransactionDateIfEmpty('newTxn');
            syncSourceDetails('newTxn');
            computeFees('newTxn');
            openModal(createModal);
        }
    } else if (openCreateModal) {
        setNowTransactionDateIfEmpty('newTxn');
        syncSourceDetails('newTxn');
        computeFees('newTxn');
        openModal(createModal);
    }
})();
</script>
@endsection
