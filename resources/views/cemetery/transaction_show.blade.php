@extends('layouts.app')

@section('content')
@php
    /** @var \App\Models\CemeteryTransaction $transaction */
    /** @var float $amountDue */
    /** @var float $totalPaid */
    /** @var float $balance */
    /** @var string $statusLabel */
    /** @var array<string, string> $paymentStatusOptions */
@endphp

<style>
    :root {
        --ctd-primary: #0f5fa8;
        --ctd-primary-dk: #0a4880;
        --ctd-soft: #f8fafc;
        --ctd-soft-2: #f1f5f9;
        --ctd-border: #e2e8f0;
        --ctd-text: #334155;
        --ctd-head: #0f172a;
        --ctd-muted: #64748b;
        --ctd-good: #059669;
    }

    #contentArea { padding: 10px !important; }

    .ctd-page {
        max-width: 1380px;
        margin: 0 auto;
        display: grid;
        gap: 8px;
        color: var(--ctd-text);
        font-family: 'Inter', system-ui, sans-serif;
    }

    .ctd-header {
        border: 1px solid var(--ctd-border);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        color: var(--ctd-head);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        position: relative;
    }

    .ctd-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--ctd-primary);
    }

    .ctd-header-main {
        padding: 12px 14px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .ctd-title {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .ctd-sub {
        margin-top: 4px;
        font-size: 0.86rem;
        color: var(--ctd-muted);
        opacity: 1;
    }

    .ctd-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        background: #dcfce7;
        color: #065f46;
        border: 1px solid #86efac;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.25rem 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .ctd-header-date {
        margin-top: 6px;
        text-align: right;
        font-size: 0.82rem;
        color: #496a8b;
        font-weight: 700;
    }

    .ctd-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ctd-btn {
        min-height: 36px;
        border-radius: 9px;
        border: 1px solid #bfd3e7;
        background: #fff;
        color: var(--ctd-primary);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 12px;
        text-decoration: none;
        font-size: 0.86rem;
        font-weight: 700;
        transition: all 0.18s ease;
    }

    .ctd-btn:hover {
        background: #f0f7ff;
        color: var(--ctd-primary-dk);
        border-color: var(--ctd-primary);
    }

    .ctd-btn-primary {
        background: var(--ctd-primary);
        color: #fff;
        border-color: var(--ctd-primary);
    }

    .ctd-btn-primary:hover {
        background: var(--ctd-primary-dk);
        color: #fff;
        border-color: var(--ctd-primary-dk);
    }

    .ctd-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(150px, 1fr));
        gap: 8px;
    }

    .ctd-kpi {
        border: 1px solid var(--ctd-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
        padding: 12px;
    }

    .ctd-kpi label {
        display: block;
        margin: 0 0 7px;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--ctd-muted);
        font-weight: 800;
    }

    .ctd-kpi strong {
        display: block;
        color: #0f2d4e;
        font-size: 1.2rem;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .ctd-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .ctd-card {
        border: 1px solid var(--ctd-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .ctd-card h3 {
        margin: 0;
        padding: 10px 12px;
        border-bottom: 1px solid var(--ctd-border);
        color: var(--ctd-head);
        font-size: 0.9rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 7px;
        background: var(--ctd-soft-2);
    }

    .ctd-body {
        padding: 10px 12px;
    }

    .ctd-info-grid {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 10px;
    }

    .ctd-info-label {
        color: #5b7a9c;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .ctd-info-value {
        color: #1e3a5a;
        font-size: 0.89rem;
        font-weight: 700;
    }

    .ctd-billing-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 7px 0;
        border-bottom: 1px dashed #e6edf5;
        font-size: 0.89rem;
    }

    .ctd-billing-row:last-child { border-bottom: none; }
    .ctd-billing-row strong { color: #0f2d4e; }

    .ctd-payments-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .ctd-count {
        border-radius: 999px;
        padding: 0.2rem 0.7rem;
        border: 1px solid #bfd8ef;
        background: #f0f7ff;
        color: #175a90;
        font-size: 0.76rem;
        font-weight: 800;
    }

    .ctd-history {
        padding: 10px 12px;
        display: grid;
        gap: 8px;
    }

    .ctd-history-item {
        border: 1px solid #dce7f2;
        background: #fff;
        border-radius: 12px;
        padding: 10px;
        position: relative;
        overflow: hidden;
    }

    .ctd-history-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #8cb8de;
    }

    .ctd-history-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .ctd-history-id {
        font-size: 0.9rem;
        font-weight: 800;
        color: #0f2d4e;
    }

    .ctd-history-amount {
        font-size: 1.02rem;
        font-weight: 900;
        color: #0f2d4e;
        letter-spacing: -0.01em;
    }

    .ctd-history-meta {
        margin-top: 8px;
        display: grid;
        grid-template-columns: repeat(4, minmax(130px, 1fr));
        gap: 8px;
    }

    .ctd-meta-chip {
        border: 1px solid #e3edf7;
        background: #f8fbff;
        border-radius: 10px;
        padding: 7px 8px;
    }

    .ctd-meta-chip label {
        display: block;
        font-size: 0.68rem;
        color: #6b88a6;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .ctd-meta-chip div {
        font-size: 0.82rem;
        color: #1f3d5e;
        font-weight: 700;
        line-height: 1.3;
    }

    .ctd-history-foot {
        margin-top: 8px;
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 8px;
        align-items: center;
    }

    .ctd-run {
        border-radius: 10px;
        border: 1px dashed #c8dced;
        background: #f7fbff;
        padding: 6px 8px;
        font-size: 0.8rem;
        color: #244769;
        font-weight: 700;
    }

    .ctd-open-receipt {
        min-height: 32px;
        padding: 0 10px;
        font-size: 0.8rem;
    }

    .ctd-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.2rem 0.7rem;
        font-size: 0.68rem;
        font-weight: 800;
        border: 1px solid transparent;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .ctd-badge-paid { background: #ecfdf5; border-color: #86efac; color: #065f46; }
    .ctd-badge-partial { background: #fffbeb; border-color: #fde68a; color: #92400e; }
    .ctd-badge-overdue { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .ctd-badge-unpaid { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .ctd-muted { color: var(--ctd-muted); }

    .ctd-empty {
        text-align: center;
        color: var(--ctd-muted);
        padding: 1.4rem 1rem;
        font-size: 0.86rem;
    }

    @media (max-width: 1080px) {
        .ctd-kpis { grid-template-columns: repeat(2, minmax(140px, 1fr)); }
        .ctd-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 720px) {
        .ctd-kpis { grid-template-columns: 1fr; }
        .ctd-info-grid { grid-template-columns: 1fr; gap: 4px; }
        .ctd-title { font-size: 1.04rem; }
        .ctd-header-date { text-align: left; }
        .ctd-history-meta { grid-template-columns: 1fr 1fr; }
        .ctd-history-foot { grid-template-columns: 1fr; }
    }
</style>

<div class="ctd-page" data-server-rendered-page="cemetery_transaction_show" data-page-title="Transaction {{ $transaction->transaction_no }}">
    <section class="ctd-header">
        <div class="ctd-header-main">
            <div>
                <h1 class="ctd-title"><i class="fa-solid fa-file-invoice-dollar"></i> Transaction {{ $transaction->transaction_no }}</h1>
                <div class="ctd-sub">{{ $transaction->deceased_name ?: '-' }} &middot; {{ $transaction->transactionType?->type_name ?: '-' }}</div>
            </div>
            <div>
                <div class="ctd-status">{{ $statusLabel }}</div>
                <div class="ctd-header-date">{{ optional($transaction->transaction_date)->format('F d, Y') ?: '-' }}</div>
            </div>
        </div>
    </section>

    <section class="ctd-toolbar">
        <a href="{{ route('cemetery.transactions') }}" class="ctd-btn">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
        <a href="{{ route('cemetery.payments', ['q' => $transaction->transaction_no]) }}" class="ctd-btn ctd-btn-primary">
            <i class="fa-solid fa-cash-register"></i> Record / Manage Payments
        </a>
    </section>

    <section class="ctd-kpis">
        <article class="ctd-kpi">
            <label>Total Due</label>
            <strong>PHP {{ number_format($amountDue, 2) }}</strong>
        </article>
        <article class="ctd-kpi">
            <label>Paid</label>
            <strong>PHP {{ number_format($totalPaid, 2) }}</strong>
        </article>
        <article class="ctd-kpi">
            <label>Balance</label>
            <strong>PHP {{ number_format($balance, 2) }}</strong>
        </article>
        <article class="ctd-kpi">
            <label>Payment Records</label>
            <strong>{{ $transaction->payments->count() }}</strong>
        </article>
    </section>

    <section class="ctd-grid">
        <article class="ctd-card">
            <h3><i class="fa-solid fa-circle-info"></i> Transaction Information</h3>
            <div class="ctd-body ctd-info-grid">
                <div class="ctd-info-label">Transaction No.</div>
                <div class="ctd-info-value">{{ $transaction->transaction_no }}</div>

                <div class="ctd-info-label">Date</div>
                <div class="ctd-info-value">{{ optional($transaction->transaction_date)->format('F d, Y h:i A') ?: '-' }}</div>

                <div class="ctd-info-label">Cemetery / Category</div>
                <div class="ctd-info-value">{{ $transaction->site?->site_name ?: '-' }} / {{ $transaction->category?->category_name ?: '-' }}</div>

                <div class="ctd-info-label">Transaction Type</div>
                <div class="ctd-info-value">{{ $transaction->transactionType?->type_name ?: '-' }}</div>

                <div class="ctd-info-label">Occupant Record</div>
                <div class="ctd-info-value">{{ $transaction->occupantRecord?->record_no ?: '-' }}</div>

                <div class="ctd-info-label">Contact Person</div>
                <div class="ctd-info-value">{{ $transaction->occupantRecord?->contact?->contact_person ?: '-' }}</div>

                <div class="ctd-info-label">Contact Number</div>
                <div class="ctd-info-value">{{ $transaction->occupantRecord?->contact?->contact_number ?: '-' }}</div>

                <div class="ctd-info-label">Plot Reference</div>
                <div class="ctd-info-value">{{ $transaction->plot_reference ?: '-' }}</div>

                <div class="ctd-info-label">Remarks</div>
                <div class="ctd-info-value">{{ $transaction->remarks ?: '-' }}</div>
            </div>
        </article>

        <article class="ctd-card">
            <h3><i class="fa-solid fa-receipt"></i> Billing Breakdown</h3>
            <div class="ctd-body">
                <div class="ctd-billing-row">
                    <span>Base Fee</span>
                    <strong>PHP {{ number_format((float) ($transaction->base_fee ?? 0), 2) }}</strong>
                </div>
                <div class="ctd-billing-row">
                    <span>Maintenance Fee</span>
                    <strong>PHP {{ number_format((float) ($transaction->maintenance_fee ?? 0), 2) }}</strong>
                </div>
                <div class="ctd-billing-row">
                    <span>Burial Permit Fee</span>
                    <strong>PHP {{ number_format((float) ($transaction->burial_permit_fee ?? 0), 2) }}</strong>
                </div>
                <div class="ctd-billing-row">
                    <span>Other Applicable Fee</span>
                    <strong>PHP {{ number_format((float) ($transaction->other_applicable_fee ?? 0), 2) }}</strong>
                </div>
                <div class="ctd-billing-row">
                    <span><strong>Total Due</strong></span>
                    <strong>PHP {{ number_format($amountDue, 2) }}</strong>
                </div>
            </div>
        </article>
    </section>

    <section class="ctd-card">
        <h3 class="ctd-payments-head">
            <span><i class="fa-solid fa-money-check-dollar"></i> Payment History</span>
            <span class="ctd-count">{{ $transaction->payments->count() }} record(s)</span>
        </h3>
        <div class="ctd-history">
            @php $runningPaid = 0.0; @endphp
            @forelse($transaction->payments as $payment)
                @php
                    $statusKey = (string) ($payment->payment_status ?? '');
                    $statusClass = in_array($statusKey, ['paid', 'partial', 'overdue', 'unpaid'], true) ? $statusKey : 'unpaid';
                    $paymentNo = trim((string) ($payment->official_receipt_no ?: $payment->payment_no ?: '-'));
                    $paidAmount = round((float) $payment->amount_paid, 2);
                    $runningPaid += $paidAmount;
                    $remainingAfter = max($amountDue - $runningPaid, 0);
                @endphp
                <article class="ctd-history-item">
                    <div class="ctd-history-top">
                        <div class="ctd-history-id">{{ $paymentNo }}</div>
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span class="ctd-badge ctd-badge-{{ $statusClass }}">
                                {{ $paymentStatusOptions[$statusKey] ?? strtoupper($statusKey ?: 'unpaid') }}
                            </span>
                            <div class="ctd-history-amount">PHP {{ number_format($paidAmount, 2) }}</div>
                        </div>
                    </div>
                    <div class="ctd-history-meta">
                        <div class="ctd-meta-chip">
                            <label>Date</label>
                            <div>{{ optional($payment->payment_date)->format('M d, Y') ?: '-' }}</div>
                        </div>
                        <div class="ctd-meta-chip">
                            <label>Recorded By</label>
                            <div>{{ $payment->creator?->name ?: '-' }}</div>
                        </div>
                        <div class="ctd-meta-chip">
                            <label>Official Receipt</label>
                            <div>{{ $payment->official_receipt_no ?: '-' }}</div>
                        </div>
                        <div class="ctd-meta-chip">
                            <label>Remarks</label>
                            <div class="ctd-muted">{{ $payment->remarks ?: '-' }}</div>
                        </div>
                    </div>
                    <div class="ctd-history-foot">
                        <div class="ctd-run">Cumulative Paid: <strong>PHP {{ number_format($runningPaid, 2) }}</strong></div>
                        <div class="ctd-run">Remaining Balance: <strong>PHP {{ number_format($remainingAfter, 2) }}</strong></div>
                        <a
                            href="{{ route('cemetery.payments.receipt', $payment) }}"
                            class="ctd-btn ctd-open-receipt"
                            target="_blank"
                            rel="noopener"
                        >
                            <i class="fa-solid fa-file-invoice"></i> Open Receipt
                        </a>
                    </div>
                </article>
            @empty
                <div class="ctd-empty">No payment records yet for this transaction.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
