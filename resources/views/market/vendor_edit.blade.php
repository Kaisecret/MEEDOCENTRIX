@extends('layouts.app')

@section('content')
@php
    $tenantIdLabel = 'TNT-' . str_pad((string) $tenant->id, 4, '0', STR_PAD_LEFT);
    $activeStall = $activeLease?->stall;
    $activeLocation = $activeStall?->location;
@endphp

<style>
    .mve-page { display:grid; gap:16px; font-family:'Inter',system-ui,sans-serif; color:#334155; }
    .mve-head { border:1px solid #dbe5f2; border-radius:14px; background:#155f8f; color:#fff; padding:1.2rem 1.35rem; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-start; box-shadow:0 4px 12px rgba(0,0,0,.12); }
    .mve-head h2 { margin:.45rem 0 .3rem; font-size:1.4rem; font-weight:800; letter-spacing:-.01em; }
    .mve-head p { margin:0; color:rgba(255,255,255,.88); font-size:.9rem; max-width:780px; }
    .mve-back { color:#fff; text-decoration:none; font-size:.83rem; font-weight:700; display:inline-flex; align-items:center; gap:6px; border:1px solid rgba(255,255,255,.32); border-radius:8px; padding:.32rem .62rem; background:rgba(255,255,255,.1); }
    .mve-back:hover { background:rgba(255,255,255,.2); color:#fff; }
    .mve-pill-wrap { display:flex; gap:7px; flex-wrap:wrap; }
    .mve-pill { display:inline-flex; align-items:center; gap:6px; border:1px solid rgba(255,255,255,.32); border-radius:999px; padding:.28rem .72rem; background:rgba(255,255,255,.12); font-size:.78rem; font-weight:700; }

    .mve-alert { border-radius:10px; border:1px solid transparent; padding:.75rem .95rem; font-size:.9rem; font-weight:600; display:flex; align-items:flex-start; gap:8px; }
    .mve-alert-success { background:#ecfdf5; border-color:#a7f3d0; color:#065f46; }
    .mve-alert-error { background:#fef2f2; border-color:#fecaca; color:#991b1b; }

    .mve-grid { display:grid; grid-template-columns:360px minmax(0, 1fr); gap:16px; align-items:start; }
    .mve-card { border:1px solid #e2e8f0; border-radius:14px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.04); overflow:hidden; }
    .mve-card-head { border-bottom:1px solid #e2e8f0; background:#f8fafc; padding:.95rem 1.1rem; display:flex; justify-content:space-between; align-items:center; gap:10px; }
    .mve-card-head h3 { margin:0; font-size:1rem; font-weight:800; color:#0f172a; }
    .mve-card-body { padding:1rem 1.1rem; display:grid; gap:12px; }

    .mve-kv { display:grid; gap:7px; }
    .mve-kv-row { display:grid; grid-template-columns:140px minmax(0, 1fr); gap:8px; font-size:.86rem; align-items:start; }
    .mve-kv-row span { color:#64748b; font-weight:700; }
    .mve-kv-row strong { color:#0f172a; font-weight:700; }
    .mve-tag { display:inline-flex; align-items:center; padding:.2rem .62rem; border-radius:999px; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.02em; }
    .mve-tag-active { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
    .mve-tag-inactive { background:#fff1f2; color:#9f1239; border:1px solid #fecdd3; }
    .mve-link { color:#155f8f; font-weight:700; text-decoration:none; }
    .mve-link:hover { text-decoration:underline; }

    .mve-form-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px 14px; }
    .mve-field { display:grid; gap:5px; }
    .mve-field-full { grid-column:1 / -1; }
    .mve-field label { color:#334155; font-size:.82rem; font-weight:700; text-transform:uppercase; letter-spacing:.02em; }
    .mve-input { width:100%; min-height:40px; border:1px solid #cbd5e1; border-radius:9px; background:#fff; padding:.5rem .72rem; color:#0f172a; font-size:.9rem; outline:none; transition:border-color .2s, box-shadow .2s; }
    .mve-input:focus { border-color:#155f8f; box-shadow:0 0 0 3px rgba(21,95,143,.12); }
    .mve-input.is-invalid { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.1); }
    .mve-error { color:#b91c1c; font-size:.78rem; font-weight:600; }

    .mve-actions { border-top:1px solid #e2e8f0; background:#f8fafc; padding:.9rem 1.1rem; display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap; }
    .mve-btn { min-height:38px; border-radius:8px; border:1px solid transparent; padding:0 .9rem; font-size:.86rem; font-weight:700; display:inline-flex; align-items:center; gap:6px; text-decoration:none; cursor:pointer; }
    .mve-btn-secondary { background:#fff; color:#334155; border-color:#cbd5e1; }
    .mve-btn-secondary:hover { background:#f8fafc; color:#0f172a; }
    .mve-btn-primary { background:#155f8f; color:#fff; border-color:#155f8f; }
    .mve-btn-primary:hover { background:#0f4b72; border-color:#0f4b72; color:#fff; }

    .mve-table-wrap { overflow:auto; }
    .mve-table { width:100%; border-collapse:collapse; min-width:860px; }
    .mve-table th { background:#eef5fb; border-bottom:1px solid #e2e8f0; color:#103250; text-transform:uppercase; letter-spacing:.03em; font-size:.73rem; font-weight:800; text-align:left; padding:.72rem .9rem; }
    .mve-table td { border-bottom:1px solid #f1f5f9; padding:.72rem .9rem; font-size:.85rem; color:#334155; vertical-align:top; }
    .mve-table tbody tr:hover td { background:#f8fafc; }
    .mve-empty { text-align:center; color:#64748b; padding:1.6rem !important; }
    .mve-muted { color:#64748b; font-size:.8rem; }

    @media (max-width:1024px) { .mve-grid { grid-template-columns:1fr; } }
    @media (max-width:700px) { .mve-form-grid { grid-template-columns:1fr; } }
</style>

<div class="mve-page" data-server-rendered-page="vendors" data-page-title="Tenant Directory">
    <section class="mve-head">
        <div>
            <a href="{{ route('market.vendors') }}" class="mve-back"><i class="fa-solid fa-arrow-left"></i> Back to Tenant Directory</a>
            <h2>Tenant Record: {{ $tenant->fullName() ?: $tenantIdLabel }}</h2>
            <p>Edit this shared tenant profile. Updates here are reflected in connected market tabs that read from this tenant record.</p>
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

                @if ($activeStall)
                    <a class="mve-link" href="{{ route('market.stalls', ['q' => $activeStall->stall_no]) }}">
                        <i class="fa-solid fa-up-right-from-square"></i> Open linked stall in Stall Management
                    </a>
                @endif

                <div class="mve-muted">
                    Connected tabs that use this tenant record: Tenant Directory, Stall Management, Send for Payment, Market Transactions, and Collector Market Payment views.
                </div>
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
                    <button type="submit" class="mve-btn mve-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Tenant Changes</button>
                </div>
            </form>
        </article>
    </section>

    <section class="mve-card">
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
@endsection
