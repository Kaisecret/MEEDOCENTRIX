@extends('layouts.app')

@section('content')
<style>
    #contentArea {
        padding-top: 10px;
    }

    .cpc-page {
        display: grid;
        gap: 10px;
        color: #334155;
        font-family: 'Inter', system-ui, sans-serif;
    }

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
        padding: 10px;
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
        margin-top: 10px;
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
        padding: 10px;
        background: #f8fafc;
        display: grid;
        gap: 10px;
    }

    .cpc-card-head h3 {
        margin: 0;
        font-size: 1.05rem;
        color: #0f172a;
    }

    .cpc-filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 10px;
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
        gap: 10px;
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

    .cpc-btn-export {
        border-color: #155f8f;
        background: #155f8f;
        color: #fff;
        text-decoration: none;
    }

    .cpc-btn-export:hover {
        background: #0f4b73;
        border-color: #0f4b73;
        color: #fff;
    }

    .cpc-status-toast {
        position: fixed;
        top: 10px;
        right: 10px;
        z-index: 1700;
        margin: 0;
        min-width: min(420px, calc(100vw - 32px));
        max-width: min(620px, calc(100vw - 32px));
        border-radius: 12px;
        border: 1px solid #a7f3d0;
        background: #ecfdf5;
        color: #065f46;
        box-shadow: 0 14px 28px rgba(15, 39, 64, 0.24);
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        transition: opacity .22s ease, transform .22s ease;
    }

    .cpc-status-toast.is-hiding {
        opacity: 0;
        transform: translateY(-8px);
        pointer-events: none;
    }

    .cpc-table-wrap { overflow: auto; }

    .cpc-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
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
        padding: 10px;
    }

    .cpc-table td {
        border-bottom: 1px solid #eef2f7;
        padding: 10px;
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

    .cpc-actions { display: inline-flex; gap: 10px; }
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
        padding: 10px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
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
        padding: 10px;
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
        padding: 10px;
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
    .cpc-modal-body { padding: 10px; overflow-y: auto; min-height: 0; background: #fff; }
    .cpc-modal-foot {
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 10px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .cpc-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .cpc-field { display: grid; gap: 10px; }
    .cpc-field-full { grid-column: 1 / -1; }
    .cpc-field label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .cpc-control-textarea { min-height: 84px; resize: vertical; }
    .cpc-view-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    .cpc-view-item {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        padding: 10px;
    }
    .cpc-view-item strong {
        display: block;
        color: #334155;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
    }
    .cpc-view-item span {
        color: #0f172a;
        font-size: 0.9rem;
        word-break: break-word;
    }
    .cpc-view-item.cpc-view-item-wide {
        grid-column: 1 / -1;
    }
    body.cpc-lock-scroll { overflow: hidden; }

    @media (max-width: 1120px) {
        .cpc-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .cpc-filter-grid { grid-template-columns: 1fr 1fr 1fr; }
    }

    @media (max-width: 680px) {
        .cpc-stats, .cpc-form-grid, .cpc-filter-grid { grid-template-columns: 1fr; }
        .cpc-view-grid { grid-template-columns: 1fr; }
    }
</style>

@php
    $exportQuery = [
        'q' => $search,
        'cemetery_site_id' => $selectedSiteId > 0 ? $selectedSiteId : '',
        'payment_status' => $selectedStatus,
    ];
@endphp

<div class="cpc-page" data-server-rendered-page="cemetery_payments" data-page-title="Payment Collection">
    @if (! $hasTransactions)
        <div class="alert alert-warning" style="margin:0;">
            <i class="fa-solid fa-triangle-exclamation"></i> No cemetery transactions found yet. Start from <strong>Cemetery Transactions</strong>, then return here to collect payment.
        </div>
    @elseif ($hasUnrecordedWithoutContact)
        <div class="alert alert-warning" style="margin:0;">
            <i class="fa-solid fa-triangle-exclamation"></i> Some transactions are missing linked occupant contact details. Update the occupant record contact first before collecting payment.
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
        <div id="cpcStatusToast" class="cpc-status-toast" role="status" aria-live="polite">
            <span><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</span>
            @if (session('last_payment_id'))
                <a
                    href="{{ route('cemetery.payments.receipt', (int) session('last_payment_id')) }}"
                    target="_blank"
                    rel="noopener"
                    class="cpc-btn cpc-btn-secondary"
                    style="min-height:34px;">
                    <i class="fa-solid fa-print"></i> Print Receipt
                </a>
            @endif
        </div>
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
            <form id="cpcAutoFilterForm" method="GET" action="{{ route('cemetery.payments') }}" class="cpc-filter-grid">
                <input id="cpcAutoSearch" type="search" name="q" class="cpc-control" placeholder="Search payment no, transaction, deceased..." value="{{ $search }}">
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
                <a href="{{ route('cemetery.payments.csv', $exportQuery) }}" class="cpc-btn cpc-btn-export"><i class="fa-solid fa-file-csv"></i> Export CSV</a>
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
                        <th>Amount Paid</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paymentCollections as $paymentCollection)
                        @php
                            $transaction = $paymentCollection->transaction;
                            $amountDue = (float) ($transaction?->amount_due ?? 0);
                            $totalPaid = (float) ($transaction?->total_paid ?? (float) $paymentCollection->amount_paid);
                            $currentBalance = max((float) ($transaction?->remaining_balance ?? ($amountDue - $totalPaid)), 0);
                        @endphp
                        <tr>
                            <td><strong>{{ $paymentCollection->payment_no }}</strong></td>
                            <td>{{ $transaction?->transaction_no ?: '-' }}</td>
                            <td>{{ $transaction?->site?->site_name ?: '-' }}</td>
                            <td>{{ $transaction?->deceased_name ?: '-' }}</td>
                            <td>PHP {{ number_format((float) $paymentCollection->amount_paid, 2) }}</td>
                            <td>{{ optional($paymentCollection->payment_date)->format('Y-m-d') ?: '-' }}</td>
                            <td><span class="cpc-badge cpc-badge-{{ $paymentCollection->payment_status }}">{{ $statusOptions[$paymentCollection->payment_status] ?? strtoupper($paymentCollection->payment_status) }}</span></td>
                            <td>
                                <div class="cpc-actions">
                                    <button
                                        type="button"
                                        class="cpc-icon-btn js-open-view-payment-btn"
                                        data-payment-no="{{ $paymentCollection->payment_no }}"
                                        data-transaction-no="{{ $transaction?->transaction_no ?: '-' }}"
                                        data-cemetery="{{ $transaction?->site?->site_name ?: '-' }}"
                                        data-category="{{ $transaction?->category?->category_name ?: '-' }}"
                                        data-deceased-name="{{ $transaction?->deceased_name ?: '-' }}"
                                        data-plot-reference="{{ $transaction?->plot_reference ?: '-' }}"
                                        data-contact-person="{{ $paymentCollection->contact?->contact_person ?: '-' }}"
                                        data-contact-number="{{ $paymentCollection->contact?->contact_number ?: '-' }}"
                                        data-amount-due="{{ number_format($amountDue, 2) }}"
                                        data-amount-paid="{{ number_format((float) $paymentCollection->amount_paid, 2) }}"
                                        data-current-balance="{{ number_format($currentBalance, 2) }}"
                                        data-payment-date="{{ optional($paymentCollection->payment_date)->format('Y-m-d') ?: '-' }}"
                                        data-coverage-start="{{ optional($paymentCollection->coverage_start_date)->format('Y-m-d') }}"
                                        data-coverage-end="{{ optional($paymentCollection->coverage_end_date)->format('Y-m-d') }}"
                                        data-payment-status="{{ $statusOptions[$paymentCollection->payment_status] ?? strtoupper($paymentCollection->payment_status) }}"
                                        data-remarks="{{ $paymentCollection->remarks ?: '-' }}"
                                        title="View full details">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="cpc-icon-btn js-open-edit-payment-btn"
                                        data-payment-id="{{ $paymentCollection->id }}"
                                        data-payment-no="{{ $paymentCollection->payment_no }}"
                                        data-transaction-id="{{ $paymentCollection->cemetery_transaction_id }}"
                                        data-contact-id="{{ $paymentCollection->cemetery_contact_id }}"
                                        data-amount-paid="{{ number_format((float) $paymentCollection->amount_paid, 2, '.', '') }}"
                                        data-payment-date="{{ optional($paymentCollection->payment_date)->format('Y-m-d') }}"
                                        data-coverage-start="{{ optional($paymentCollection->coverage_start_date)->format('Y-m-d') }}"
                                        data-coverage-end="{{ optional($paymentCollection->coverage_end_date)->format('Y-m-d') }}"
                                        data-payment-status="{{ $paymentCollection->payment_status }}"
                                        data-remarks="{{ $paymentCollection->remarks }}"
                                        title="Edit payment record">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a
                                        href="{{ route('cemetery.payments.receipt', $paymentCollection) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="cpc-icon-btn"
                                        title="Open receipt">
                                        <i class="fa-solid fa-receipt"></i>
                                    </a>
                                    <form method="POST" action="{{ route('cemetery.payments.destroy', $paymentCollection) }}" class="js-delete-payment-form" data-payment-no="{{ $paymentCollection->payment_no }}" data-transaction-no="{{ $transaction?->transaction_no ?: '-' }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cpc-icon-btn cpc-icon-btn-danger" title="Delete payment record"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center; padding:1.4rem;">No payment records found.</td></tr>
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

<div id="viewPaymentModal" class="cpc-modal" aria-hidden="true">
    <div class="cpc-modal-card">
        <div class="cpc-modal-head">
            <h4>Payment Details</h4>
            <button type="button" class="cpc-modal-close" data-close-modal="viewPaymentModal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="cpc-modal-body">
            <div class="cpc-view-grid">
                <div class="cpc-view-item"><strong>Payment Ref.</strong><span id="viewPayPaymentNo">-</span></div>
                <div class="cpc-view-item"><strong>Transaction Ref.</strong><span id="viewPayTransactionNo">-</span></div>
                <div class="cpc-view-item"><strong>Payment Date</strong><span id="viewPayPaymentDate">-</span></div>
                <div class="cpc-view-item"><strong>Cemetery</strong><span id="viewPayCemetery">-</span></div>
                <div class="cpc-view-item"><strong>Category</strong><span id="viewPayCategory">-</span></div>
                <div class="cpc-view-item"><strong>Status</strong><span id="viewPayStatus">-</span></div>
                <div class="cpc-view-item"><strong>Deceased Name</strong><span id="viewPayDeceasedName">-</span></div>
                <div class="cpc-view-item"><strong>Niche / Lot</strong><span id="viewPayPlotReference">-</span></div>
                <div class="cpc-view-item"><strong>Contact Person</strong><span id="viewPayContactPerson">-</span></div>
                <div class="cpc-view-item"><strong>Amount Due</strong><span id="viewPayAmountDue">-</span></div>
                <div class="cpc-view-item"><strong>Amount Paid</strong><span id="viewPayAmountPaid">-</span></div>
                <div class="cpc-view-item"><strong>Current Balance</strong><span id="viewPayCurrentBalance">-</span></div>
                <div class="cpc-view-item cpc-view-item-wide"><strong>Remarks</strong><span id="viewPayRemarks">-</span></div>
            </div>
        </div>
        <div class="cpc-modal-foot">
            <button type="button" class="cpc-btn cpc-btn-secondary" data-close-modal="viewPaymentModal">Close</button>
        </div>
    </div>
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

<div
    id="paymentPageState"
    data-old-form-mode="{{ old('form_mode', '') }}"
    data-old-form-payment-id="{{ old('form_payment_id', '') }}"
    data-has-errors="{{ $errors->any() ? '1' : '0' }}"
    hidden></div>

<script>
(() => {
    const createModal = document.getElementById('createPaymentModal');
    const editModal = document.getElementById('editPaymentModal');
    const viewModal = document.getElementById('viewPaymentModal');
    const deleteModal = document.getElementById('deletePaymentModal');
    const closeButtons = Array.from(document.querySelectorAll('[data-close-modal]'));
    const editForm = document.getElementById('editPaymentForm');
    const editActionTemplate = editForm ? (editForm.dataset.actionTemplate || '') : '';
    const confirmDeleteButton = document.getElementById('confirmDeletePaymentBtn');
    const deletePaymentNo = document.getElementById('deletePaymentNo');
    const deletePaymentTransactionNo = document.getElementById('deletePaymentTransactionNo');
    const autoFilterForm = document.getElementById('cpcAutoFilterForm');
    const autoSearchInput = document.getElementById('cpcAutoSearch');
    const statusToast = document.getElementById('cpcStatusToast');
    const pageState = document.getElementById('paymentPageState');
    const oldFormMode = pageState?.dataset.oldFormMode || '';
    const oldFormPaymentId = pageState?.dataset.oldFormPaymentId || '';
    const hasErrors = (pageState?.dataset.hasErrors || '0') === '1';
    let pendingDeleteForm = null;

    const allModals = [createModal, editModal, viewModal, deleteModal].filter(Boolean);

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

    const autoHideStatusToast = () => {
        if (!statusToast) return;
        window.setTimeout(() => {
            statusToast.classList.add('is-hiding');
            window.setTimeout(() => {
                statusToast.remove();
            }, 240);
        }, 2200);
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

    const selectedTransactionAmountDue = (prefix) => {
        const transactionSelect = document.getElementById(prefix + 'Transaction');
        if (!transactionSelect) return 0;
        const selectedOption = transactionSelect.options[transactionSelect.selectedIndex];
        const parsed = Number(selectedOption ? (selectedOption.dataset.amountDue || '0') : '0');
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const autoResolveStatus = (prefix) => {
        const statusField = document.getElementById(prefix + 'PaymentStatus');
        const statusLabelField = document.getElementById(prefix + 'PaymentStatusLabel');
        const amountPaidField = document.getElementById(prefix + 'AmountPaid');
        if (!statusField || !amountPaidField) return;

        const amountPaid = Math.max(Number(amountPaidField.value || 0), 0);
        const amountDue = Math.max(selectedTransactionAmountDue(prefix), 0);
        let resolvedStatus = 'unpaid';

        if (!Number.isFinite(amountPaid) || !Number.isFinite(amountDue)) return;

        if (amountDue <= 0) {
            resolvedStatus = 'paid';
        } else if (amountPaid <= 0) {
            resolvedStatus = 'unpaid';
        } else if (amountPaid >= amountDue) {
            resolvedStatus = 'paid';
        } else {
            resolvedStatus = 'partial';
        }

        statusField.value = resolvedStatus;
        if (statusLabelField) {
            const labels = { paid: 'Paid', unpaid: 'Unpaid', partial: 'Partial', overdue: 'Overdue' };
            statusLabelField.value = labels[resolvedStatus] || resolvedStatus.toUpperCase();
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
        const contactName = selectedOption ? (selectedOption.dataset.contactName || '') : '';
        const contactNumber = selectedOption ? (selectedOption.dataset.contactNumber || '') : '';

        setValue(prefix + 'SiteName', siteName);
        setValue(prefix + 'CategoryName', categoryName);
        setValue(prefix + 'DeceasedName', deceasedName);
        setValue(prefix + 'PlotReference', plotReference);
        setValue(prefix + 'AmountDue', amountDue ? ('PHP ' + Number(amountDue).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) : '');
        setValue(prefix + 'ContactId', defaultContactId);
        setValue(prefix + 'Contact', contactName ? `${contactName}${contactNumber ? ` (${contactNumber})` : ''}` : '');

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
        const coverageStartField = document.getElementById(prefix + 'CoverageStart');
        const coverageEndField = document.getElementById(prefix + 'CoverageEnd');

        if (amountPaidField) {
            amountPaidField.addEventListener('input', () => autoResolveStatus(prefix));
            amountPaidField.addEventListener('change', () => autoResolveStatus(prefix));
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
        setValue('editPayContactId', button.dataset.contactId);
        setValue('editPayAmountPaid', button.dataset.amountPaid);
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

    const openViewFromButton = (button) => {
        setText('viewPayPaymentNo', button.dataset.paymentNo);
        setText('viewPayTransactionNo', button.dataset.transactionNo);
        setText('viewPayPaymentDate', button.dataset.paymentDate);
        setText('viewPayCemetery', button.dataset.cemetery);
        setText('viewPayCategory', button.dataset.category);
        setText('viewPayStatus', button.dataset.paymentStatus);
        setText('viewPayDeceasedName', button.dataset.deceasedName);
        setText('viewPayPlotReference', button.dataset.plotReference);
        setText('viewPayContactPerson', button.dataset.contactPerson);
        setText('viewPayAmountDue', `PHP ${button.dataset.amountDue || '0.00'}`);
        setText('viewPayAmountPaid', `PHP ${button.dataset.amountPaid || '0.00'}`);
        setText('viewPayCurrentBalance', `PHP ${button.dataset.currentBalance || '0.00'}`);
        setText('viewPayRemarks', button.dataset.remarks);

        openModal(viewModal);
    };

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
        const viewButton = event.target.closest('.js-open-view-payment-btn');
        if (viewButton) {
            event.preventDefault();
            openViewFromButton(viewButton);
            return;
        }

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
        closeModal(viewModal);
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

    if (statusToast) {
        autoHideStatusToast();
    }
})();
</script>
@endsection
