@extends('layouts.app')

@section('content')
@php
    $tenantIdLabel = 'TNT-' . str_pad((string) $tenant->id, 4, '0', STR_PAD_LEFT);
    $activeStall = $activeLease?->stall;
    $activeLocation = $activeStall?->location;
@endphp

<style>
    #contentArea { padding: 10px; }
    .mve-page { display:grid; gap:10px; font-family:'Inter',system-ui,sans-serif; color:#334155; }
    .mve-head {
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        background:
            radial-gradient(580px 190px at 0% 0%, rgba(255, 255, 255, 0.12), transparent 58%),
            linear-gradient(150deg, color-mix(in srgb, var(--sidebar-bg, #155e8f) 84%, #0c3656) 0%, var(--sidebar-bg, #155e8f) 100%);
        color: #fff;
        padding: 12px;
        display: grid;
        gap: 10px;
        box-shadow: 0 12px 28px rgba(9, 34, 58, 0.22);
    }
    .mve-head-main {
        display: grid;
        gap: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.16);
    }
    .mve-head h2 {
        margin: 0;
        font-size: 1.55rem;
        line-height: 1.1;
        font-weight: 900;
        letter-spacing: -0.015em;
        text-wrap: balance;
    }
    .mve-back {
        color: #fff;
        text-decoration: none;
        font-size: .82rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid rgba(255, 255, 255, .34);
        border-radius: 10px;
        padding: 6px 10px;
        background: rgba(255, 255, 255, .12);
        width: fit-content;
        transition: background .18s ease, transform .12s ease, border-color .18s ease;
    }
    .mve-back:hover {
        background: rgba(255, 255, 255, .2);
        border-color: rgba(255, 255, 255, .5);
        color: #fff;
        transform: translateY(-1px);
    }
    .mve-pill-wrap {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        width: 100%;
        padding: 8px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 12px;
        background: rgba(6, 26, 44, 0.18);
    }
    .mve-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 999px;
        padding: 5px 11px;
        background: rgba(255,255,255,.11);
        font-size: .79rem;
        font-weight: 700;
        backdrop-filter: blur(3px);
        color: #e8f1fb;
    }

    .mve-alert { border-radius:10px; border:1px solid transparent; padding:10px; font-size:.9rem; font-weight:600; display:flex; align-items:flex-start; gap:10px; }
    .mve-alert-success { background:#ecfdf5; border-color:#a7f3d0; color:#065f46; }
    .mve-alert-error { background:#fef2f2; border-color:#fecaca; color:#991b1b; }

    .mve-grid { display:grid; grid-template-columns:minmax(300px, 34%) minmax(0, 1fr); gap:10px; align-items:start; }
    .mve-card { border:1px solid #e2e8f0; border-radius:14px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.04); overflow:hidden; }
    .mve-card-head { border-bottom:1px solid #e2e8f0; background:#f8fafc; padding:10px; display:flex; justify-content:space-between; align-items:center; gap:10px; }
    .mve-card-head h3 { margin:0; font-size:1rem; font-weight:800; color:#0f172a; }
    .mve-card-body { padding:10px; display:grid; gap:10px; }

    .mve-kv { display:grid; gap:10px; }
    .mve-kv-row { display:grid; grid-template-columns:140px minmax(0, 1fr); gap:10px; font-size:.86rem; align-items:start; }
    .mve-kv-row span { color:#64748b; font-weight:700; }
    .mve-kv-row strong { color:#0f172a; font-weight:700; }
    .mve-tag { display:inline-flex; align-items:center; padding:.2rem .62rem; border-radius:999px; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.02em; }
    .mve-tag-active { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
    .mve-tag-inactive { background:#fff1f2; color:#9f1239; border:1px solid #fecdd3; }
    .mve-link { color:#155f8f; font-weight:700; text-decoration:none; }
    .mve-link:hover { text-decoration:underline; }
    .mve-due {
        border: 1px solid #dbe7f3;
        border-radius: 12px;
        background: linear-gradient(180deg, #f8fbff 0%, #f3f8fd 100%);
        padding: 10px;
        display: grid;
        gap: 10px;
    }
    .mve-due-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .mve-due-head h4 {
        margin: 0;
        color: #0f3b5b;
        font-size: .9rem;
        font-weight: 900;
    }
    .mve-due-stats { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:8px; }
    .mve-due-stat {
        border: 1px solid #d5e4f2;
        border-radius: 10px;
        background: #fff;
        padding: 8px;
        display: grid;
        gap: 4px;
    }
    .mve-due-stat span {
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .mve-due-stat strong {
        color: #0f172a;
        font-size: .84rem;
        font-weight: 800;
    }
    .mve-due-list-title {
        color: #0f3b5b;
        font-size: .82rem;
        font-weight: 800;
        margin-bottom: 6px;
    }
    .mve-due-empty {
        color: #166534;
        font-size: .8rem;
        font-weight: 700;
        border: 1px dashed #86efac;
        background: #f0fdf4;
        border-radius: 10px;
        padding: 8px;
    }
    .mve-due-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 5px;
    }
    .mve-due-item {
        border: 1px solid #d5e4f2;
        border-radius: 10px;
        background: #fff;
        padding: 7px 8px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 8px;
    }
    .mve-due-item-name {
        color: #0f172a;
        font-size: .82rem;
        font-weight: 800;
    }
    .mve-due-item-meta {
        color: #64748b;
        font-size: .76rem;
        font-weight: 700;
    }
    .mve-due-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #0f172a;
        font-size: .72rem;
        font-weight: 800;
        padding: 2px 8px;
        white-space: nowrap;
    }
    .mve-due-note {
        margin-top: 2px;
        color: #64748b;
        font-size: .76rem;
        font-weight: 700;
    }
    .mve-due-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .mve-btn-ghost {
        min-height: 34px;
        border-radius: 8px;
        border: 1px solid #bfd2e6;
        background: #fff;
        color: #0f3b5b;
        padding: 0 10px;
        font-size: .8rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        cursor: pointer;
    }
    .mve-btn-ghost:hover {
        background: #eff6ff;
        color: #0b3052;
        border-color: #9ec1df;
    }
    .mve-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(2, 12, 27, 0.56);
        z-index: 1100;
        display: grid;
        place-items: center;
        padding: 16px;
    }
    .mve-modal-backdrop[hidden] { display: none !important; }
    .mve-modal {
        width: min(760px, 96vw);
        max-height: 84vh;
        overflow: hidden;
        border-radius: 14px;
        border: 1px solid #c7d8ea;
        background: #f8fbff;
        box-shadow: 0 20px 46px rgba(2, 12, 27, 0.36);
        display: grid;
        grid-template-rows: auto minmax(0, 1fr) auto;
    }
    .mve-modal-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-bottom: 1px solid #d7e3f0;
        background: #fff;
    }
    .mve-modal-head h4 {
        margin: 0;
        color: #0f3b5b;
        font-size: .95rem;
        font-weight: 900;
    }
    .mve-modal-body {
        overflow: auto;
        padding: 10px 12px;
    }
    .mve-modal-foot {
        border-top: 1px solid #d7e3f0;
        background: #fff;
        padding: 9px 12px;
        display: flex;
        justify-content: flex-end;
    }

    .mve-form-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px; }
    .mve-field { display:grid; gap:10px; }
    .mve-field-full { grid-column:1 / -1; }
    .mve-field label { color:#334155; font-size:.82rem; font-weight:700; text-transform:uppercase; letter-spacing:.02em; }
    .mve-input { width:100%; min-height:40px; border:1px solid #cbd5e1; border-radius:9px; background:#fff; padding:.5rem .72rem; color:#0f172a; font-size:.9rem; outline:none; transition:border-color .2s, box-shadow .2s; }
    .mve-input:focus { border-color:#155f8f; box-shadow:0 0 0 3px rgba(21,95,143,.12); }
    .mve-input.is-invalid { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.1); }
    .mve-error { color:#b91c1c; font-size:.78rem; font-weight:600; }

    .mve-actions { border-top:1px solid #e2e8f0; background:#f8fafc; padding:10px; display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
    .mve-btn { min-height:38px; border-radius:8px; border:1px solid transparent; padding:0 .9rem; font-size:.86rem; font-weight:700; display:inline-flex; align-items:center; gap:6px; text-decoration:none; cursor:pointer; }
    .mve-btn-secondary { background:#fff; color:#334155; border-color:#cbd5e1; }
    .mve-btn-secondary:hover { background:#f8fafc; color:#0f172a; }
    .mve-btn-primary { background:#155f8f; color:#fff; border-color:#155f8f; }
    .mve-btn-primary:hover { background:#0f4b72; border-color:#0f4b72; color:#fff; }

    .mve-table-wrap { overflow:auto; }
    .mve-table { width:100%; border-collapse:collapse; min-width:860px; }
    .mve-table th { background:#eef5fb; border-bottom:1px solid #e2e8f0; color:#103250; text-transform:uppercase; letter-spacing:.03em; font-size:.73rem; font-weight:800; text-align:left; padding:10px; }
    .mve-table td { border-bottom:1px solid #f1f5f9; padding:10px; font-size:.85rem; color:#334155; vertical-align:top; }
    .mve-table tbody tr:hover td { background:#f8fafc; }
    .mve-empty { text-align:center; color:#64748b; padding:10px !important; }
    .mve-muted { color:#64748b; font-size:.8rem; }

    @media (max-width:1024px) {
        .mve-grid { grid-template-columns:1fr; }
        .mve-head h2 { font-size: 1.55rem; }
    }
    @media (max-width:700px) {
        .mve-form-grid { grid-template-columns:1fr; }
        .mve-head {
            padding: 10px;
            gap: 10px;
        }
        .mve-head h2 { font-size: 1.25rem; }
        .mve-pill-wrap { justify-content: center; }
        .mve-due-stats { grid-template-columns: 1fr; }
        .mve-due-item { grid-template-columns: 1fr; }
        .mve-modal { width: 100%; max-height: 88vh; }
    }
</style>

<div class="mve-page" data-server-rendered-page="vendors" data-page-title="Tenant Directory">
    <section class="mve-head">
        <div class="mve-head-main">
            <a href="{{ route('market.vendors') }}" class="mve-back"><i class="fa-solid fa-arrow-left"></i> Back to Tenant Directory</a>
            <h2>Tenant Record: {{ $tenant->fullName() ?: $tenantIdLabel }}</h2>
        </div>
        <div class="mve-pill-wrap">
            <span class="mve-pill"><i class="fa-solid fa-id-card"></i> {{ $tenantIdLabel }}</span>
            <span class="mve-pill"><i class="fa-solid fa-file-signature"></i> Total Leases: {{ number_format((int) $leaseSummary['total']) }}</span>
            <span class="mve-pill"><i class="fa-solid fa-circle-check"></i> Active Leases: {{ number_format((int) $leaseSummary['active']) }}</span>
            <span class="mve-pill"><i class="fa-solid fa-clock-rotate-left"></i> Payments: {{ number_format((int) $paymentSummary['count']) }}</span>
            <span class="mve-pill"><i class="fa-solid fa-money-bill-wave"></i> Total Paid: PHP {{ number_format((float) $paymentSummary['total_paid'], 2) }}</span>
        </div>
    </section>

    @if (session('status'))
        <div class="mve-alert mve-alert-success">
            <i class="fa-solid fa-circle-check" style="margin-top:2px;"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mve-alert mve-alert-error">
            <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px;"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <section class="mve-grid">
        <article class="mve-card">
            <div class="mve-card-head">
                <h3>Connection Summary</h3>
            </div>
            <div class="mve-card-body">
                <div class="mve-kv">
                    <div class="mve-kv-row">
                        <span>Lease Status</span>
                        <strong>
                            @if ($activeLease)
                                <span class="mve-tag mve-tag-active">Active</span>
                            @else
                                <span class="mve-tag mve-tag-inactive">No Active Lease</span>
                            @endif
                        </strong>
                    </div>
                    <div class="mve-kv-row">
                        <span>Active Stall</span>
                        <strong>{{ $activeStall?->stall_no ?: '-' }}</strong>
                    </div>
                    <div class="mve-kv-row">
                        <span>Location</span>
                        <strong>
                            @if ($activeLocation)
                                {{ $activeLocation->location_code }} - {{ $activeLocation->location_name }}
                            @else
                                -
                            @endif
                        </strong>
                    </div>
                    <div class="mve-kv-row">
                        <span>Contract No.</span>
                        <strong>{{ $activeLease?->contract_number ?: '-' }}</strong>
                    </div>
                    <div class="mve-kv-row">
                        <span>Current Rate</span>
                        <strong>
                            @if ($activeLease)
                                PHP {{ number_format((float) ($activeLease->computed_rate_amount ?? $activeLease->rate?->rate_amount ?? 0), 2) }}
                            @else
                                -
                            @endif
                        </strong>
                    </div>
                    <div class="mve-kv-row">
                        <span>Last Updated</span>
                        <strong>{{ optional($tenant->updated_at)->format('Y-m-d h:i A') ?: '-' }}</strong>
                    </div>
                </div>

                <div class="mve-due">
                    <div class="mve-due-head">
                        <h4>Billing Due Timeline</h4>
                        @if (($billingTimeline['has_active_lease'] ?? false) === true)
                            <span class="mve-tag mve-tag-active">{{ $billingTimeline['period_label'] ?? 'Billing' }}</span>
                        @endif
                    </div>

                    @if (($billingTimeline['has_active_lease'] ?? false) === true)
                        <div class="mve-due-stats">
                            <div class="mve-due-stat">
                                <span>Starts</span>
                                <strong>{{ $billingTimeline['start_date_label'] ?? '-' }}</strong>
                            </div>
                            <div class="mve-due-stat">
                                <span>Schedule</span>
                                <strong>{{ $billingTimeline['interval_label'] ?? '-' }}</strong>
                            </div>
                            <div class="mve-due-stat">
                                <span>First Due</span>
                                <strong>{{ $billingTimeline['first_due_label'] ?? '-' }}</strong>
                            </div>
                            <div class="mve-due-stat">
                                <span>Next Due</span>
                                <strong>{{ $billingTimeline['next_due_label'] ?? '-' }}</strong>
                            </div>
                            <div class="mve-due-stat">
                                <span>Paid Cycles</span>
                                <strong>{{ number_format((int) ($billingTimeline['paid_cycles'] ?? 0)) }}</strong>
                            </div>
                            <div class="mve-due-stat">
                                <span>Unpaid Cycles</span>
                                <strong>{{ number_format((int) ($billingTimeline['unpaid_cycles'] ?? 0)) }}</strong>
                            </div>
                        </div>

                        <div>
                            <div class="mve-due-list-title">
                                Unpaid {{ strtolower((string) ($billingTimeline['period_label'] ?? 'billing')) }} due days
                            </div>

                            @if ((int) ($billingTimeline['unpaid_cycles'] ?? 0) === 0)
                                <div class="mve-due-empty">
                                    No unpaid due day found for this active lease timeline.
                                </div>
                            @else
                                <ul class="mve-due-list">
                                    @foreach (($billingTimeline['unpaid_preview'] ?? []) as $dueItem)
                                        <li class="mve-due-item">
                                            <div>
                                                <div class="mve-due-item-name">{{ $dueItem['date'] ?? '-' }}</div>
                                                <div class="mve-due-item-meta">{{ $dueItem['age'] ?? '-' }}</div>
                                            </div>
                                            <span class="mve-due-status">{{ $dueItem['status'] ?? 'Unpaid' }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if ((int) ($billingTimeline['unpaid_remaining'] ?? 0) > 0)
                                    <div class="mve-due-note">
                                        +{{ number_format((int) ($billingTimeline['unpaid_remaining'] ?? 0)) }} more unpaid due day(s) not shown in preview.
                                    </div>
                                @endif

                                <div class="mve-due-actions">
                                    @if ((int) ($billingTimeline['unpaid_cycles'] ?? 0) > 3)
                                        <button
                                            type="button"
                                            class="mve-btn-ghost"
                                            data-open-unpaid-modal
                                            aria-haspopup="dialog"
                                            aria-controls="unpaidDueModal"
                                        >
                                            <i class="fa-solid fa-list"></i> See More
                                        </button>
                                    @endif
                                    <a href="#paymentHistorySection" class="mve-btn-ghost">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Go to Payment History
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="mve-due-empty">
                            No active lease available. Due schedule appears here once the tenant has an active stall lease.
                        </div>
                    @endif
                </div>

                @if ($activeStall)
                    <a class="mve-link" href="{{ route('market.stalls', ['q' => $activeStall->stall_no]) }}">
                        <i class="fa-solid fa-up-right-from-square"></i> Open linked stall in Stall Management
                    </a>
                @endif

            </div>
        </article>

        <article class="mve-card">
            <div class="mve-card-head">
                <h3>Edit Tenant / Lessee Details</h3>
            </div>
            <form method="POST" action="{{ route('market.vendors.update', $tenant) }}">
                @csrf
                @method('PUT')

                <div class="mve-card-body">
                    <div class="mve-form-grid">
                        <div class="mve-field">
                            <label for="first_name">First Name</label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $tenant->first_name) }}" class="mve-input @error('first_name') is-invalid @enderror" required>
                            @error('first_name') <div class="mve-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="mve-field">
                            <label for="last_name">Last Name</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $tenant->last_name) }}" class="mve-input @error('last_name') is-invalid @enderror" required>
                            @error('last_name') <div class="mve-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="mve-field">
                            <label for="middle_name">Middle Name</label>
                            <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name', $tenant->middle_name) }}" class="mve-input @error('middle_name') is-invalid @enderror">
                            @error('middle_name') <div class="mve-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="mve-field">
                            <label for="contact_number">Contact Number</label>
                            <input id="contact_number" name="contact_number" type="text" value="{{ old('contact_number', $tenant->contact_number) }}" class="mve-input @error('contact_number') is-invalid @enderror">
                            @error('contact_number') <div class="mve-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="mve-field mve-field-full">
                            <label for="address">Address</label>
                            <input id="address" name="address" type="text" value="{{ old('address', $tenant->address) }}" class="mve-input @error('address') is-invalid @enderror">
                            @error('address') <div class="mve-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="mve-field">
                            <label for="business_name">Business Name</label>
                            <input id="business_name" name="business_name" type="text" value="{{ old('business_name', $tenant->business_name) }}" class="mve-input @error('business_name') is-invalid @enderror">
                            @error('business_name') <div class="mve-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="mve-field">
                            <label for="business_type">Business Type</label>
                            <input id="business_type" name="business_type" type="text" value="{{ old('business_type', $tenant->business_type) }}" class="mve-input @error('business_type') is-invalid @enderror">
                            @error('business_type') <div class="mve-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="mve-field">
                            <label for="mpo_control_no">MPO Control No.</label>
                            <input id="mpo_control_no" name="mpo_control_no" type="text" value="{{ old('mpo_control_no', $tenant->mpo_control_no) }}" class="mve-input @error('mpo_control_no') is-invalid @enderror">
                            @error('mpo_control_no') <div class="mve-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="mve-actions">
                    <a href="{{ route('market.vendors') }}" class="mve-btn mve-btn-secondary"><i class="fa-solid fa-list"></i> Tenant Directory</a>
                    <a href="{{ route('market.vendors.final_notice.pdf', $tenant) }}" class="mve-btn mve-btn-secondary">
                        <i class="fa-solid fa-file-pdf"></i> Generate Final Notice
                    </a>
                    <button type="submit" class="mve-btn mve-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Tenant Changes</button>
                </div>
            </form>
        </article>
    </section>

    <section class="mve-card" id="paymentHistorySection">
        <div class="mve-card-head">
            <h3>Payment History</h3>
        </div>
        <div class="mve-table-wrap">
            <table class="mve-table">
                <thead>
                    <tr>
                        <th>Payment No.</th>
                        <th>Payment Date</th>
                        <th>Stall</th>
                        <th>Contract</th>
                        <th>Payer</th>
                        <th>Amount Paid</th>
                        <th>Collector</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paymentHistory as $paymentItem)
                        @php
                            $leaseItem = $paymentItem->lease;
                            $stallItem = $leaseItem?->stall;
                            $locationItem = $stallItem?->location;
                            $collectorName = $paymentItem->dispatchItem?->dispatch?->collector?->name;
                            $recordedByName = $paymentItem->generatedBy?->name;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $paymentItem->payment_number ?: '-' }}</strong>
                            </td>
                            <td>{{ optional($paymentItem->payment_date)->format('Y-m-d h:i A') ?: '-' }}</td>
                            <td>
                                <strong>{{ $stallItem?->stall_no ?: '-' }}</strong><br>
                                <span class="mve-muted">{{ $locationItem?->location_code ?: '-' }}</span>
                            </td>
                            <td>{{ $leaseItem?->contract_number ?: '-' }}</td>
                            <td>{{ $paymentItem->payer_name ?: '-' }}</td>
                            <td><strong>PHP {{ number_format((float) ($paymentItem->amount_paid ?? 0), 2) }}</strong></td>
                            <td>{{ $collectorName ?: '-' }}</td>
                            <td>{{ $recordedByName ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="mve-empty">No payment history found for this tenant yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mve-card">
        <div class="mve-card-head">
            <h3>Recent Lease Connections</h3>
        </div>
        <div class="mve-table-wrap">
            <table class="mve-table">
                <thead>
                    <tr>
                        <th>Stall</th>
                        <th>Location</th>
                        <th>Contract</th>
                        <th>Status</th>
                        <th>Rate</th>
                        <th>Lease Period</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaseHistory as $leaseItem)
                        @php
                            $stallItem = $leaseItem->stall;
                            $locationItem = $stallItem?->location;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $stallItem?->stall_no ?: '-' }}</strong>
                            </td>
                            <td>{{ $locationItem?->location_code ?: '-' }} - {{ $locationItem?->location_name ?: '-' }}</td>
                            <td>{{ $leaseItem->contract_number ?: '-' }}</td>
                            <td>
                                @if ($leaseItem->lease_status === 'active')
                                    <span class="mve-tag mve-tag-active">Active</span>
                                @else
                                    <span class="mve-tag mve-tag-inactive">{{ ucfirst((string) $leaseItem->lease_status) }}</span>
                                @endif
                            </td>
                            <td>PHP {{ number_format((float) ($leaseItem->computed_rate_amount ?? 0), 2) }}</td>
                            <td>
                                {{ optional($leaseItem->start_date)->format('Y-m-d') ?: '-' }}
                                to
                                {{ optional($leaseItem->end_date)->format('Y-m-d') ?: 'Present' }}
                            </td>
                            <td>{{ optional($leaseItem->updated_at)->format('Y-m-d h:i A') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="mve-empty">No lease history found for this tenant yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

@if ((int) ($billingTimeline['unpaid_cycles'] ?? 0) > 3)
    <div class="mve-modal-backdrop" id="unpaidDueModal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="unpaidDueModalTitle">
        <div class="mve-modal">
            <div class="mve-modal-head">
                <h4 id="unpaidDueModalTitle">All Unpaid Due Days</h4>
                <button type="button" class="mve-btn-ghost" data-close-unpaid-modal>
                    <i class="fa-solid fa-xmark"></i> Close
                </button>
            </div>
            <div class="mve-modal-body">
                <ul class="mve-due-list">
                    @foreach (($billingTimeline['unpaid_all'] ?? []) as $dueItem)
                        <li class="mve-due-item">
                            <div>
                                <div class="mve-due-item-name">{{ $dueItem['date'] ?? '-' }}</div>
                                <div class="mve-due-item-meta">{{ $dueItem['age'] ?? '-' }}</div>
                            </div>
                            <span class="mve-due-status">{{ $dueItem['status'] ?? 'Unpaid' }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="mve-modal-foot">
                <button type="button" class="mve-btn-ghost" data-close-unpaid-modal>
                    Done
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('unpaidDueModal');
            if (!modal) {
                return;
            }

            const openButtons = document.querySelectorAll('[data-open-unpaid-modal]');
            const closeButtons = modal.querySelectorAll('[data-close-unpaid-modal]');

            const openModal = function () {
                modal.hidden = false;
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            };

            const closeModal = function () {
                modal.hidden = true;
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            };

            openButtons.forEach(function (button) {
                button.addEventListener('click', openModal);
            });

            closeButtons.forEach(function (button) {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.hidden === false) {
                    closeModal();
                }
            });
        })();
    </script>
@endif
@endsection
