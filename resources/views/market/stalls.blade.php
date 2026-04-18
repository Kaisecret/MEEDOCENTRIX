@extends('layouts.app')

@section('content')
    @php
        $inactiveCount = max(0, (int) $summary['total'] - ((int) $summary['occupied'] + (int) $summary['vacant'] + (int) $summary['maintenance']));
    @endphp

    <style>
        .msr-page {
            display: grid;
            gap: 16px;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #334155;
        }

        .msr-hero {
            background: #155f8f;
            color: #fff;
            border-radius: 12px;
            padding: 1.45rem 1.6rem;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1), 0 2px 4px -1px rgba(0, 0, 0, .06);
        }

        .msr-hero h2 {
            margin: 0 0 .4rem;
            font-size: 1.7rem;
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .msr-hero p {
            margin: 0;
            font-size: .95rem;
            color: rgba(255, 255, 255, .85);
        }

        .msr-stats {
            margin-top: .75rem;
            display: inline-flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .msr-pill {
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .38);
            border-radius: 8px;
            padding: .25rem .75rem;
            font-size: .85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .msr-hero-btn {
            border: 1px solid rgba(255, 255, 255, .38);
            background: rgba(255, 255, 255, .15);
            color: #fff;
            border-radius: 10px;
            min-height: 42px;
            padding: 0 1rem;
            font-size: .95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all .2s;
        }

        .msr-hero-btn:hover {
            background: rgba(255, 255, 255, .25);
            color: #fff;
        }

        .msr-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
            overflow: hidden;
        }

        .msr-head {
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
            padding: 1.25rem 1.5rem;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 16px;
            align-items: center;
        }

        .msr-head h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
        }

        .msr-head p {
            margin: .25rem 0 0;
            color: #64748b;
            font-size: .9rem;
        }

        .msr-search-wrap {
            position: relative;
            display: flex;
            align-items: center;
            min-width: 340px;
        }

        .msr-search-wrap i {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            pointer-events: none;
            font-size: .9em;
        }

        .msr-search {
            width: 100%;
            min-height: 40px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
            padding: .5rem .75rem .5rem 2.25rem;
            font-size: .92rem;
            color: #334155;
            transition: border-color .2s, box-shadow .2s;
        }

        .msr-search:focus {
            border-color: #155f8f;
            box-shadow: 0 0 0 3px rgba(21, 95, 143, .15);
            outline: none;
        }

        .msr-table-wrap {
            overflow: auto;
        }

        .msr-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1080px;
        }

        .msr-table th {
            background: #eef5fb;
            color: #103250;
            border-bottom: 1px solid #e2e8f0;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            font-weight: 700;
            padding: 1rem 1.25rem;
            text-align: left;
        }

        .msr-table td {
            padding: .88rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: .93rem;
            vertical-align: middle;
        }

        .msr-table tbody tr:nth-child(even) {
            background: #fdfdfe;
        }

        .msr-table tbody tr:hover {
            background: #f1f5f9;
        }

        .msr-table td:first-child,
        .msr-table td:last-child {
            white-space: nowrap;
        }

        .msr-muted {
            color: #64748b;
            font-size: .84rem;
        }

        .msr-badge {
            border-radius: 999px;
            padding: .25rem .65rem;
            font-size: .77rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .msr-badge-vacant {
            background: #edf4ff;
            border: 1px solid #b7d2f6;
            color: #1c4c86;
        }

        .msr-badge-occupied {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
        }

        .msr-badge-maintenance {
            background: #fff4dd;
            border: 1px solid #f2dea7;
            color: #a06a00;
        }

        .msr-badge-inactive {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .msr-actions {
            display: inline-flex;
            gap: 6px;
            flex-wrap: nowrap;
        }

        .msr-icon-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid #d8e2ef;
            background: #fff;
            color: #42566d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .2s;
            flex-shrink: 0;
        }

        .msr-icon-btn:hover {
            background: #f1f5f9;
            color: #155f8f;
            border-color: #cbd5e1;
        }

        .msr-icon-btn-danger:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #dc2626;
        }

        .msr-pagination {
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 12px 16px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            align-items: center;
        }

        .msr-page-link {
            min-height: 34px;
            padding: 0 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #155f8f;
            font-size: .85rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
        }

        .msr-page-link:hover {
            background: #f1f5f9;
        }

        .msr-page-link.is-disabled {
            background: #f8fafc;
            color: #94a3b8;
            border-color: #e2e8f0;
            pointer-events: none;
        }

        .msr-modal {
            position: fixed;
            inset: 0;
            z-index: 1650;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, .56);
            backdrop-filter: blur(3px);
            padding: 16px;
        }

        .msr-modal.is-open {
            display: flex;
        }

        .msr-modal-card {
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

        .msr-modal-card--compact {
            width: min(470px, 96vw);
        }

        .msr-modal-head {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .msr-modal-head h4 {
            margin: 0;
            color: #0f172a;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .msr-modal-close {
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
            cursor: pointer;
            transition: all .2s;
        }

        .msr-modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .msr-modal form {
            display: grid;
            grid-template-rows: minmax(0, 1fr) auto;
            min-height: 0;
        }

        .msr-modal-body {
            padding: 16px 20px;
            overflow-y: auto;
            min-height: 0;
            display: grid;
            gap: 16px;
            background: #fff;
        }

        .msr-foot {
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 12px 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .msr-preview {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            padding: 12px 14px;
            color: #334155;
            font-size: .9rem;
            display: grid;
            gap: 6px;
        }

        .msr-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .msr-summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            padding: 10px 12px;
        }

        .msr-summary-card span {
            display: block;
            color: #64748b;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .msr-summary-card strong {
            color: #0f172a;
            font-size: .96rem;
        }

        .msr-items {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .msr-items-head {
            background: #eef5fb;
            border-bottom: 1px solid #e2e8f0;
            padding: .62rem .85rem;
            font-weight: 700;
            color: #103250;
            font-size: .85rem;
        }

        .msr-items-body {
            padding: .75rem .85rem;
            color: #334155;
            font-size: .88rem;
            display: grid;
            gap: 5px;
        }

        .msr-form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px 16px;
        }

        .msr-form-field {
            display: grid;
            gap: 6px;
        }

        .msr-form-field label {
            color: #334155;
            font-size: .85rem;
            font-weight: 600;
        }

        .msr-control {
            width: 100%;
            min-height: 40px;
            border: 1px solid #cbd5e1;
            background: #fff;
            border-radius: 8px;
            padding: .5rem .75rem;
            color: #0f172a;
            font-size: .92rem;
            outline: none;
            transition: all .2s;
        }

        .msr-control:focus {
            border-color: #155f8f;
            box-shadow: 0 0 0 3px rgba(21, 95, 143, .15);
        }

        .msr-textarea {
            min-height: 92px;
            resize: vertical;
        }

        .msr-rate-type-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .msr-rate-type-item {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #fff;
            padding: .6rem .68rem;
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .msr-rate-type-item:has(input:checked) {
            border-color: #155f8f;
            box-shadow: 0 0 0 2px rgba(21, 95, 143, .12) inset;
            background: #f7fbff;
        }

        .msr-rate-type-item input {
            width: 16px;
            height: 16px;
            margin: 0;
        }

        .msr-rate-type-name {
            color: #0f172a;
            font-size: .86rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .msr-rate-type-rate {
            color: #0f5fa8;
            font-size: .79rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .msr-help {
            display: inline-block;
            color: #64748b;
            font-size: .78rem;
            margin-top: 2px;
        }

        .msr-form-field--full {
            grid-column: 1 / -1;
        }

        .msr-check {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #334155;
            font-size: .9rem;
            font-weight: 600;
        }

        .msr-tenant-box {
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
            padding: 14px;
            display: grid;
            gap: 12px;
        }

        .msr-tenant-box h6 {
            margin: 0;
            color: #0f172a;
            font-size: .98rem;
            font-weight: 700;
        }

        .msr-tenant-box.is-disabled {
            opacity: .58;
        }

        body.msr-lock-scroll {
            overflow: hidden;
        }

        .msr-status-toast {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 1700;
            min-width: min(420px, calc(100vw - 36px));
            border-radius: 12px;
            border: 1px solid transparent;
            padding: 12px 14px;
            box-shadow: 0 14px 24px rgba(15, 23, 42, .18);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .92rem;
            transform: translateY(0);
            opacity: 1;
            transition: opacity .22s ease, transform .22s ease;
        }

        .msr-status-toast i {
            font-size: 1rem;
        }

        .msr-status-toast.is-success {
            background: #ecfdf5;
            border-color: #86efac;
            color: #065f46;
        }

        .msr-status-toast.is-error {
            background: #fff1f2;
            border-color: #fecdd3;
            color: #9f1239;
        }

        .msr-status-toast.is-hiding {
            opacity: 0;
            transform: translateY(-10px);
        }

        @media (max-width:960px) {
            .msr-head {
                grid-template-columns: 1fr;
            }

            .msr-search-wrap {
                min-width: 100%;
            }

            .msr-form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .msr-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .msr-rate-type-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width:640px) {
            .msr-form-grid {
                grid-template-columns: 1fr;
            }

            .msr-summary-grid {
                grid-template-columns: 1fr;
            }

            .msr-rate-type-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="msr-page" data-server-rendered-page="stalls" data-page-title="Stall Management">
        @if (session('status'))
            <div id="msrStatusToast" class="msr-status-toast is-success" role="status" aria-live="polite">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('status') }}</span>
            </div>
        @elseif ($errors->any())
            <div id="msrStatusToast" class="msr-status-toast is-error" role="alert" aria-live="assertive">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <section class="msr-hero">
            <div>
                <h2>Master Stall Registry</h2>
                <p>Manage all market stalls with type-based rates and clean lease records.</p>
                <div class="msr-stats">
                    <span class="msr-pill"><i class="fa-solid fa-store"></i> Total: {{ $summary['total'] }}</span>
                    <span class="msr-pill"><i class="fa-solid fa-circle-check"></i> Occupied:
                        {{ $summary['occupied'] }}</span>
                    <span class="msr-pill"><i class="fa-solid fa-door-open"></i> Vacant: {{ $summary['vacant'] }}</span>
                    <span class="msr-pill"><i class="fa-solid fa-circle-xmark"></i> Inactive: {{ $inactiveCount }}</span>
                </div>
            </div>
            <button type="button" id="openRegisterStallBtn" class="msr-hero-btn"><i class="fas fa-plus"></i> Register
                Stall</button>
        </section>

        <section class="msr-card" id="stallRegistryCard">
            <div class="msr-head">
                <div>
                    <h3>Registered Stalls</h3>
                    <p>{{ number_format($stalls->total()) }} total record{{ $stalls->total() === 1 ? '' : 's' }}.</p>
                </div>
                <form id="stallSearchForm" method="GET" action="{{ route('market.stalls') }}">
                    <div class="msr-search-wrap">
                        <i class="fas fa-search"></i>
                        <input id="stallSearchInput" class="msr-search" type="search" name="q" value="{{ $search }}"
                            placeholder="Search stall no, tenant, location...">
                    </div>
                </form>
            </div>
            <div class="msr-table-wrap">
                <table class="msr-table">
                    <thead>
                        <tr>
                            <th>Stall No.</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Current Tenant</th>
                            <th>Dimension</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stalls as $stall)
                            @php
                                $lease = $stall->activeLease;
                                $tenant = $lease?->tenant;
                                $location = $stall->location;
                                $rateAmount = $lease?->computed_rate_amount ?? $lease?->rate?->rate_amount ?? $location?->activeRate?->rate_amount ?? 0;
                                $tenantName = $tenant ? $tenant->fullName() : 'Vacant';
                                $selectedTypeIds = collect($lease?->selected_type_rates ?? [])->pluck('id')->filter()->map(fn ($id) => (string) $id);
                                if ($selectedTypeIds->isEmpty() && $stall->market_stall_type_id) {
                                    $selectedTypeIds = collect([(string) $stall->market_stall_type_id]);
                                }
                                $selectedTypeIdsCsv = $selectedTypeIds->implode(',');
                            @endphp
                            <tr>
                                <td><strong>{{ $stall->stall_no }}</strong></td>
                                <td>{{ $location?->location_code ?: '-' }}<br><span
                                        class="msr-muted">{{ $location?->location_name ?: '-' }}</span></td>
                                <td>{{ $stall->stallType?->type_name ?: '-' }}</td>
                                <td>
                                    <strong>{{ $tenantName }}</strong><br>
                                    <span class="msr-muted">{{ $tenant?->business_name ?: '-' }}</span>
                                </td>
                                <td>{{ $stall->dimension_sq_m ? number_format((float) $stall->dimension_sq_m, 2) . ' sq.m' : '-' }}
                                </td>
                                <td><strong>PHP {{ number_format((float) $rateAmount, 2) }}</strong></td>
                                <td><span
                                        class="msr-badge msr-badge-{{ $stall->stall_status }}">{{ $statusOptions[$stall->stall_status] ?? strtoupper($stall->stall_status) }}</span>
                                </td>
                                <td>{{ optional($stall->updated_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="msr-actions">
                                        <button type="button" class="msr-icon-btn js-open-view-stall-btn"
                                            data-stall-id="{{ $stall->id }}" data-stall-no="{{ $stall->stall_no }}"
                                            data-location-label="{{ $location?->location_code ?: '-' }} - {{ $location?->location_name ?: '-' }}"
                                            data-type-name="{{ $stall->stallType?->type_name ?: '-' }}"
                                            data-tenant-name="{{ $tenantName }}"
                                            data-business-name="{{ $tenant?->business_name }}"
                                            data-tenant-contact="{{ $tenant?->contact_number }}"
                                            data-tenant-address="{{ $tenant?->address }}"
                                            data-dimension="{{ $stall->dimension_sq_m ? number_format((float) $stall->dimension_sq_m, 2) . ' sq.m' : '-' }}"
                                            data-rate="{{ number_format((float) $rateAmount, 2, '.', '') }}"
                                            data-status="{{ $statusOptions[$stall->stall_status] ?? strtoupper($stall->stall_status) }}"
                                            data-billing-period="{{ $lease?->billing_period ?: 'monthly' }}"
                                            data-billing-cycles="{{ $lease?->billing_cycles ?: 1 }}"
                                            data-rate-multiplier="{{ number_format((float) ($lease?->rate_multiplier ?? 1), 2, '.', '') }}"
                                            data-contract-number="{{ $lease?->contract_number }}"
                                            data-start-date="{{ optional($lease?->start_date)->format('Y-m-d') }}"
                                            data-end-date="{{ optional($lease?->end_date)->format('Y-m-d') }}"
                                            data-lease-remarks="{{ $lease?->remarks }}"
                                            data-description="{{ $stall->description }}" title="View details">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button type="button" class="msr-icon-btn js-open-edit-stall-btn"
                                            data-stall-id="{{ $stall->id }}" data-stall-no="{{ $stall->stall_no }}"
                                            data-location-id="{{ $stall->market_stall_location_id }}"
                                            data-type-id="{{ $stall->market_stall_type_id }}"
                                            data-dimension="{{ $stall->dimension_sq_m !== null ? number_format((float) $stall->dimension_sq_m, 2, '.', '') : '' }}"
                                            data-description="{{ $stall->description }}"
                                            data-status="{{ $stall->stall_status }}"
                                            data-billable="{{ $stall->is_billable ? '1' : '0' }}"
                                            data-rate="{{ number_format((float) $rateAmount, 2, '.', '') }}"
                                            data-rate-type-ids="{{ $selectedTypeIdsCsv }}"
                                            data-billing-period="{{ $lease?->billing_period ?: 'monthly' }}"
                                            data-billing-cycles="{{ $lease?->billing_cycles ?: 1 }}"
                                            data-rate-multiplier="{{ number_format((float) ($lease?->rate_multiplier ?? 1), 2, '.', '') }}"
                                            data-start-date="{{ optional($lease?->start_date)->format('Y-m-d') }}"
                                            data-end-date="{{ optional($lease?->end_date)->format('Y-m-d') }}"
                                            data-contract-number="{{ $lease?->contract_number }}"
                                            data-lease-remarks="{{ $lease?->remarks }}"
                                            data-tenant-first-name="{{ $tenant?->first_name }}"
                                            data-tenant-last-name="{{ $tenant?->last_name }}"
                                            data-tenant-middle-name="{{ $tenant?->middle_name }}"
                                            data-tenant-address="{{ $tenant?->address }}"
                                            data-tenant-contact="{{ $tenant?->contact_number }}"
                                            data-business-name="{{ $tenant?->business_name }}"
                                            data-business-type="{{ $tenant?->business_type }}"
                                            data-mpo-control-no="{{ $tenant?->mpo_control_no }}" title="Edit stall">
                                            <i class="fas fa-pen"></i>
                                        </button>

                                        <form action="{{ route('market.stalls.destroy', $stall) }}" method="POST"
                                            class="js-delete-stall-form" data-stall-no="{{ $stall->stall_no }}"
                                            data-location="{{ $location?->location_code ?: '-' }} - {{ $location?->location_name ?: '-' }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="msr-icon-btn msr-icon-btn-danger"
                                                title="Delete stall"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align:center; padding:1.65rem;">No stall records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($stalls->hasPages())
                <div class="msr-pagination">
                    @if ($stalls->previousPageUrl())
                        <a class="msr-page-link" href="{{ $stalls->previousPageUrl() }}">Previous</a>
                    @else
                        <span class="msr-page-link is-disabled">Previous</span>
                    @endif
                    @if ($stalls->nextPageUrl())
                        <a class="msr-page-link" href="{{ $stalls->nextPageUrl() }}">Next</a>
                    @else
                        <span class="msr-page-link is-disabled">Next</span>
                    @endif
                </div>
            @endif
        </section>
    </div>

    <div id="registerStallModal" class="msr-modal" aria-hidden="true">
        <div class="msr-modal-card">
            <div class="msr-modal-head">
                <h4>Register Stall</h4>
                <button type="button" class="msr-modal-close" data-close-modal="registerStallModal"><i
                        class="fas fa-xmark"></i></button>
            </div>
            <form id="registerStallForm" method="POST" action="{{ route('market.stalls.store') }}">
                @csrf
                <input type="hidden" name="form_mode" value="create">
                <div class="msr-modal-body">
                    @include('market.partials.stall_form_fields', [
                        'prefix' => 'new',
                        'locations' => $locations,
                        'stallTypes' => $stallTypes,
                        'statusOptions' => $statusOptions,
                        'rateValue' => null,
                    ])
                </div>
                <div class="msr-foot">
                    <button type="button" class="btn btn-secondary" id="closeRegisterStallBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Stall</button>
                </div>
            </form>
        </div>
    </div>


    <div id="editStallModal" class="msr-modal" aria-hidden="true">
        <div class="msr-modal-card">
            <div class="msr-modal-head">
                <h4>Edit Stall</h4>
                <button type="button" class="msr-modal-close" data-close-modal="editStallModal"><i class="fas fa-xmark"></i></button>
            </div>
            <form id="editStallForm" method="POST" action="" data-action-template="{{ route('market.stalls.update', '__STALL_ID__') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_mode" value="edit">
                <input type="hidden" name="form_stall_id" id="editFormStallId" value="{{ old('form_stall_id') }}">
                <div class="msr-modal-body">
                    @include('market.partials.stall_form_fields', [
                        'prefix' => 'edit',
                        'locations' => $locations,
                        'stallTypes' => $stallTypes,
                        'statusOptions' => $statusOptions,
                        'rateValue' => null,
                    ])
                </div>
                <div class="msr-foot">

                               <button type="button" class="btn btn-secondary" id="closeEditStallBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stall</button>
                </div>
            </form>
        </div>
    </div>

    <div id="viewStallModal" class="msr-modal" aria-hidden="true">
        <div class="msr-modal-card">
            <div class="msr-modal-head">
                <h4>Stall Details</h4>
                <button type="button" class="msr-modal-close" data-close-modal="viewStallModal"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="msr-modal-body">
                <div class="msr-summary-grid">
                    <div class="msr-summary-card"><span>Stall No.</span><strong id="viewStallNo">-</strong></div>
                    <div class="msr-summary-card"><span>Location</span><strong id="viewLocation">-</strong></div>
                    <div class="msr-summary-card"><span>Status</span><strong id="viewStatus">-</strong></div>
                    <div class="msr-summary-card"><span>Type</span><strong id="viewType">-</strong></div>
                    <div class="msr-summary-card"><span>Dimension</span><strong id="viewDimension">-</strong></div>
                    <div class="msr-summary-card"><span>Rate</span><strong id="viewRate">-</strong></div>
                    <div class="msr-summary-card"><span>Tenant</span><strong id="viewTenantName">-</strong></div>
                    <div class="msr-summary-card"><span>Business</span><strong id="viewBusinessName">-</strong></div>
                    <div class="msr-summary-card"><span>Contact</span><strong id="viewTenantContact">-</strong></div>
                </div>

                <div class="msr-items">
                    <div class="msr-items-head">Lease Information</div>
                    <div class="msr-items-body">
                        <div>Contract No.: <strong id="viewContractNo">-</strong></div>
                        <div>Lease Period: <strong id="viewLeasePeriod">-</strong></div>
                        <div>Address: <strong id="viewTenantAddress">-</strong></div>
                        <div>Lease Remarks: <strong id="viewLeaseRemarks">-</strong></div>
                    </div>
                </div>

                <div class="msr-items">
                    <div class="msr-items-head">Stall Description</div>
                    <div class="msr-items-body">
                        <div id="viewDescription">-</div>
                    </div>
                </div>

                            </div>
            <div class="msr-foot">
                <button type="button" class="btn btn-secondary" id="closeViewStallBtn">Close</button>
            </div>
        </div>
    </div>

    <div id="deleteStallModal" class="msr-modal" aria-hidden="true">
        <div class="msr-modal-card msr-modal-card--compact">
            <div class="msr-modal-head">
                <h4>Delete Stall</h4>
                <button type="button" class="msr-modal-close" data-close-modal="deleteStallModal"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="msr-modal-body">
                <p style="margin:0;">Are you sure you want to delete this stall record?</p>
                <div class="msr-preview">
                        <div><strong id="deleteStallNo">-</strong></div>
                        <div>Location: <span id="deleteStallLocation">-</span></div>
                    </div>
                </div>
                <div class="msr-foot">
                    <button type="button" class="btn btn-secondary" id="cancelDeleteStallBtn">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteStallBtn">Yes, Delete</button>
                </div>
            </div>
        </div>


<script>
(() => {
    const statusToast = document.getElementById('msrStatusToast');
    const registerModal = document.getElementById('registerStallModal');
    const editModal = document.getElementById('editStallModal');
    const viewModal = document.getElementById('viewStallModal');
    const deleteModal = document.getElementById('deleteStallModal');
    const allModals = [registerModal, editModal, viewModal, deleteModal].filter(Boolean);
    const openRegisterButton = document.getElementById('openRegisterStallBtn');
    const closeRegisterButton = document.getElementById('closeRegisterStallBtn');
    const closeEditButton = document.getElementById('closeEditStallBtn');
    const closeViewButton = document.getElementById('closeViewStallBtn');
    const modalCloseButtons = Array.from(document.querySelectorAll('.msr-modal-close[data-close-modal]'));
    const editStallForm = document.getElementById('editStallForm');
    const registerStallForm = document.getElementById('registerStallForm');
    const editActionTemplate = editStallForm ? (editStallForm.dataset.actionTemplate || '') : '';
    const deleteStallNo = document.getElementById('deleteStallNo');
    const deleteStallLocation = document.getElementById('deleteStallLocation');
    const cancelDeleteButton = document.getElementById('cancelDeleteStallBtn');
    const confirmDeleteButton = document.getElementById('confirmDeleteStallBtn');
    const searchAction = "{{ route('market.stalls') }}";
    const oldFormMode = "{{ old('form_mode') }}";
    const oldFormStallId = "{{ old('form_stall_id') }}";
    const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
    let pendingDeleteForm = null;
    let searchTimer = null;
    let activeSearchRequestId = 0;

    const lockBody = () => document.body.classList.toggle('msr-lock-scroll', allModals.some((modal) => modal.classList.contains('is-open')));
    const openModal = (modal) => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        const body = modal.querySelector('.msr-modal-body');
        if (body) body.scrollTop = 0;
        lockBody();
    };
    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        lockBody();
    };
    const resetForm = (form) => {
        if (!form) return;
        form.reset();
        form.querySelectorAll('[data-rate-input]').forEach((input) => {
            delete input.dataset.manual;
        });
    };
    const setValue = (id, value) => {
        const field = document.getElementById(id);
        if (!field) return;
        field.value = value || '';
    };
    const setChecked = (id, checked) => {
        const field = document.getElementById(id);
        if (!field) return;
        field.checked = checked;
    };
    const setText = (id, value, fallback = '-') => {
        const node = document.getElementById(id);
        if (!node) return;
        const text = String(value || '').trim();
        node.textContent = text === '' ? fallback : text;
    };
    const formatDate = (value) => {
        const text = String(value || '').trim();
        return text === '' ? '-' : text;
    };

    const initStallFormBehavior = (form) => {
        if (!form) {
            return {
                syncRate: () => {},
                syncTenant: () => {},
                syncRateTypeRequired: () => {},
            };
        }

        const PERIOD_MULTIPLIERS = {
            daily: 1,
            weekly: 7,
            monthly: 30,
        };
        const primaryTypeSelect = form.querySelector('select[name="market_stall_type_id"]');
        const rateTypeFields = Array.from(form.querySelectorAll('[data-rate-type]'));
        const billingPeriodField = form.querySelector('[data-billing-period]');
        const billingCyclesField = form.querySelector('[data-billing-cycles]');
        const rateMultiplierField = form.querySelector('[data-rate-multiplier]');
        const rateFormulaHint = form.querySelector('[data-rate-formula-hint]');
        const rateInput = form.querySelector('[data-rate-input]');
        const statusSelect = form.querySelector('[data-stall-status]');
        const tenantBox = form.querySelector('[data-tenant-box]');
        const tenantFields = Array.from(form.querySelectorAll('[data-tenant-field]'));

        const toNumber = (value, fallback = 0) => {
            const parsed = Number.parseFloat(String(value ?? '').trim());
            return Number.isFinite(parsed) ? parsed : fallback;
        };

        const selectedRateTypeCount = () => rateTypeFields.filter((field) => field.checked).length;

        const ensurePrimaryRateTypeChecked = () => {
            if (!primaryTypeSelect || rateTypeFields.length === 0 || selectedRateTypeCount() > 0) return;
            const primaryId = String(primaryTypeSelect.value || '').trim();
            if (primaryId === '') return;
            const primaryRateType = rateTypeFields.find((field) => field.value === primaryId);
            if (primaryRateType) {
                primaryRateType.checked = true;
            }
        };

        const syncRateTypeRequired = () => {
            const isOccupied = statusSelect && statusSelect.value === 'occupied';
            rateTypeFields.forEach((field) => {
                field.required = false;
            });
            if (!isOccupied || selectedRateTypeCount() > 0) return;
            if (rateTypeFields[0]) {
                rateTypeFields[0].required = true;
            }
        };

        const syncRate = (force = false) => {
            if (!rateInput) return;

            ensurePrimaryRateTypeChecked();
            const selectedRateTypes = rateTypeFields.filter((field) => field.checked);
            const baseTotal = selectedRateTypes.reduce((sum, field) => sum + toNumber(field.dataset.baseRate, 0), 0);

            const period = String((billingPeriodField && billingPeriodField.value) || 'monthly').toLowerCase();
            const periodMultiplier = Object.prototype.hasOwnProperty.call(PERIOD_MULTIPLIERS, period)
                ? PERIOD_MULTIPLIERS[period]
                : PERIOD_MULTIPLIERS.monthly;

            const billingCycles = Math.max(1, Math.trunc(toNumber(billingCyclesField && billingCyclesField.value, 1)));
            const rateMultiplier = Math.max(0.01, toNumber(rateMultiplierField && rateMultiplierField.value, 1));
            const computedRate = baseTotal * periodMultiplier * billingCycles * rateMultiplier;

            if (rateFormulaHint) {
                if (selectedRateTypes.length === 0) {
                    rateFormulaHint.textContent = 'Select one or more stall types to compute the lease rate.';
                } else {
                    rateFormulaHint.textContent = `Base ${baseTotal.toFixed(2)} x ${periodMultiplier} (${period}) x ${billingCycles} cycle(s) x ${rateMultiplier.toFixed(2)} = PHP ${computedRate.toFixed(2)}`;
                }
            }

            if (force || rateInput.dataset.manual !== '1') {
                rateInput.value = computedRate.toFixed(2);
            }
        };

        const syncTenant = () => {
            if (!statusSelect || !tenantBox) return;
            const occupied = statusSelect.value === 'occupied';
            tenantFields.forEach((field) => {
                if (field.name === 'tenant_first_name' || field.name === 'tenant_last_name') {
                    field.required = occupied;
                }
            });
            tenantBox.classList.toggle('is-disabled', !occupied);
            syncRateTypeRequired();
        };

        if (rateInput) {
            rateInput.addEventListener('input', () => {
                rateInput.dataset.manual = '1';
            });
        }

        if (primaryTypeSelect) {
            primaryTypeSelect.addEventListener('change', () => {
                if (rateInput) {
                    delete rateInput.dataset.manual;
                }
                syncRate();
            });
        }

        if (billingPeriodField) {
            billingPeriodField.addEventListener('change', () => {
                if (rateInput) {
                    delete rateInput.dataset.manual;
                }
                syncRate();
            });
        }

        if (billingCyclesField) {
            billingCyclesField.addEventListener('input', () => {
                if (rateInput) {
                    delete rateInput.dataset.manual;
                }
                syncRate();
            });
        }

        if (rateMultiplierField) {
            rateMultiplierField.addEventListener('input', () => {
                if (rateInput) {
                    delete rateInput.dataset.manual;
                }
                syncRate();
            });
        }

        rateTypeFields.forEach((field) => {
            field.addEventListener('change', () => {
                if (rateInput) {
                    delete rateInput.dataset.manual;
                }
                syncRate();
                syncRateTypeRequired();
            });
        });

        if (statusSelect) {
            statusSelect.addEventListener('change', () => {
                syncTenant();
                syncRate();
            });
        }

        syncRateTypeRequired();
        syncRate(true);
        syncTenant();
        return { syncRate, syncTenant, syncRateTypeRequired };
    };

    const registerHelpers = initStallFormBehavior(registerStallForm);
    const editHelpers = initStallFormBehavior(editStallForm);

    if (openRegisterButton) {
        openRegisterButton.addEventListener('click', () => {
            if (!hasErrors) {
                resetForm(registerStallForm);
                registerHelpers.syncRate();
                registerHelpers.syncTenant();
            }
            openModal(registerModal);
        });
    }

    if (closeRegisterButton) {
        closeRegisterButton.addEventListener('click', () => {
            resetForm(registerStallForm);
            registerHelpers.syncRate();
            registerHelpers.syncTenant();
            closeModal(registerModal);
        });
    }

    if (closeEditButton) {
        closeEditButton.addEventListener('click', () => closeModal(editModal));
    }
    if (closeViewButton) {
        closeViewButton.addEventListener('click', () => closeModal(viewModal));
    }

    modalCloseButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-close-modal');
            if (!modalId) return;
            const modal = document.getElementById(modalId);
            if (modal === registerModal) {
                resetForm(registerStallForm);
                registerHelpers.syncRate();
                registerHelpers.syncTenant();
            }
            if (modal === deleteModal) {
                pendingDeleteForm = null;
            }
            closeModal(modal);
        });
    });

    const openEditFromButton = (button) => {
        if (!editStallForm) return;
        const stallId = button.dataset.stallId || '';
        editStallForm.action = editActionTemplate.replace('__STALL_ID__', stallId);
        setValue('editFormStallId', stallId);
        setValue('editStallNo', button.dataset.stallNo);
        setValue('editLocationId', button.dataset.locationId);
        setValue('editTypeId', button.dataset.typeId);
        setValue('editDimension', button.dataset.dimension);
        setValue('editDescription', button.dataset.description);
        setValue('editStatus', button.dataset.status);
        setChecked('editBillable', button.dataset.billable === '1');
        setValue('editRate', button.dataset.rate);
        setValue('editBillingPeriod', button.dataset.billingPeriod || 'monthly');
        setValue('editBillingCycles', button.dataset.billingCycles || 1);
        setValue('editRateMultiplier', button.dataset.rateMultiplier || 1);
        setValue('editStartDate', button.dataset.startDate);
        setValue('editEndDate', button.dataset.endDate);
        setValue('editContractNo', button.dataset.contractNumber);
        setValue('editLeaseRemarks', button.dataset.leaseRemarks);
        setValue('editTenantFirstName', button.dataset.tenantFirstName);
        setValue('editTenantLastName', button.dataset.tenantLastName);
        setValue('editTenantMiddleName', button.dataset.tenantMiddleName);
        setValue('editTenantAddress', button.dataset.tenantAddress);
        setValue('editTenantContact', button.dataset.tenantContact);
        setValue('editBusinessName', button.dataset.businessName);
        setValue('editBusinessType', button.dataset.businessType);
        setValue('editMpoControlNo', button.dataset.mpoControlNo);
        const selectedRateTypeIds = String(button.dataset.rateTypeIds || '')
            .split(',')
            .map((value) => value.trim())
            .filter((value) => value !== '');
        const selectedRateTypeSet = new Set(selectedRateTypeIds);
        const editRateTypeFields = Array.from(editStallForm.querySelectorAll('[data-rate-type]'));
        editRateTypeFields.forEach((field) => {
            field.checked = selectedRateTypeSet.has(field.value);
        });
        if (selectedRateTypeSet.size === 0 && button.dataset.typeId) {
            const fallbackRateType = editRateTypeFields.find((field) => field.value === button.dataset.typeId);
            if (fallbackRateType) {
                fallbackRateType.checked = true;
            }
        }
        const editRateField = editStallForm.querySelector('[data-rate-input]');
        if (editRateField) {
            delete editRateField.dataset.manual;
        }
        editHelpers.syncRate(true);
        editHelpers.syncRateTypeRequired();
        editHelpers.syncTenant();
        openModal(editModal);
    };

    const openViewFromButton = (button) => {
        setText('viewStallNo', button.dataset.stallNo);
        setText('viewLocation', button.dataset.locationLabel);
        setText('viewStatus', button.dataset.status);
        setText('viewType', button.dataset.typeName);
        setText('viewDimension', button.dataset.dimension);
        setText('viewRate', `PHP ${Number(button.dataset.rate || 0).toFixed(2)}`);
        setText('viewTenantName', button.dataset.tenantName);
        setText('viewBusinessName', button.dataset.businessName);
        setText('viewTenantContact', button.dataset.tenantContact);
        setText('viewContractNo', button.dataset.contractNumber);
        const startDate = formatDate(button.dataset.startDate);
        const endDate = formatDate(button.dataset.endDate);
        setText('viewLeasePeriod', `${startDate} to ${endDate}`);
        setText('viewTenantAddress', button.dataset.tenantAddress);
        setText('viewLeaseRemarks', button.dataset.leaseRemarks);
        setText('viewDescription', button.dataset.description);
        openModal(viewModal);
    };

    if (cancelDeleteButton) {
        cancelDeleteButton.addEventListener('click', () => {
            pendingDeleteForm = null;
            closeModal(deleteModal);
        });
    }
    if (confirmDeleteButton) {
        confirmDeleteButton.addEventListener('click', () => {
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

    const currentSearchNodes = () => {
        const form = document.getElementById('stallSearchForm');
        return {
            form,
            input: form ? form.querySelector('input[name="q"]') : null,
        };
    };

    const captureSearchState = () => {
        const { input } = currentSearchNodes();
        if (!input) {
            return { value: '', focused: false, start: null, end: null };
        }
        return {
            value: input.value,
            focused: document.activeElement === input,
            start: typeof input.selectionStart === 'number' ? input.selectionStart : null,
            end: typeof input.selectionEnd === 'number' ? input.selectionEnd : null,
        };
    };

    const restoreSearchState = (state) => {
        if (!state) return;
        const { input } = currentSearchNodes();
        if (!input) return;
        if (typeof state.value === 'string' && input.value !== state.value) {
            input.value = state.value;
        }
        if (!state.focused) return;
        input.focus({ preventScroll: true });
        if (typeof state.start === 'number' && typeof state.end === 'number' && typeof input.setSelectionRange === 'function') {
            const length = input.value.length;
            const start = Math.max(0, Math.min(state.start, length));
            const end = Math.max(0, Math.min(state.end, length));
            input.setSelectionRange(start, end);
        }
    };

    const replaceRegistryCardFromHtml = (html) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const incomingCard = doc.getElementById('stallRegistryCard');
        const currentCard = document.getElementById('stallRegistryCard');
        if (!incomingCard || !currentCard) return false;
        currentCard.replaceWith(incomingCard);
        return true;
    };

    const requestSearch = (query, delayMs = 0, state = null) => {
        if (searchTimer) {
            window.clearTimeout(searchTimer);
            searchTimer = null;
        }

        const run = () => {
            const requestId = ++activeSearchRequestId;
            const url = `${searchAction}?${query.toString()}`;
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => response.text())
                .then((html) => {
                    if (requestId !== activeSearchRequestId) return;
                    if (!replaceRegistryCardFromHtml(html)) {
                        window.location.assign(url);
                        return;
                    }
                    history.replaceState({}, '', url);
                    restoreSearchState(state);
                })
                .catch(() => {
                    window.location.assign(url);
                });
        };

        if (delayMs > 0) {
            searchTimer = window.setTimeout(run, delayMs);
            return;
        }

        run();
    };

    document.addEventListener('click', (event) => {
        const editButton = event.target.closest('.js-open-edit-stall-btn');
        if (editButton) {
            openEditFromButton(editButton);
            return;
        }

        const viewButton = event.target.closest('.js-open-view-stall-btn');
        if (viewButton) {
            openViewFromButton(viewButton);
            return;
        }

        const paginationLink = event.target.closest('#stallRegistryCard .msr-pagination .msr-page-link[href]');
        if (!paginationLink) return;

        event.preventDefault();
        const href = paginationLink.getAttribute('href');
        if (!href) return;
        const state = captureSearchState();
        fetch(href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => response.text())
            .then((html) => {
                if (!replaceRegistryCardFromHtml(html)) {
                    window.location.assign(href);
                    return;
                }
                history.replaceState({}, '', href);
                restoreSearchState(state);
            })
            .catch(() => {
                window.location.assign(href);
            });
    });

    document.addEventListener('input', (event) => {
        const { form, input } = currentSearchNodes();
        if (!form || !input || event.target !== input) return;
        const query = new URLSearchParams(new FormData(form));
        const state = captureSearchState();
        requestSearch(query, 260, state);
    });

    document.addEventListener('submit', (event) => {
        const targetForm = event.target;
        if (!(targetForm instanceof HTMLFormElement)) return;

        if (targetForm.id === 'stallSearchForm') {
            event.preventDefault();
            const query = new URLSearchParams(new FormData(targetForm));
            const state = captureSearchState();
            requestSearch(query, 0, state);
            return;
        }

        if (!targetForm.classList.contains('js-delete-stall-form')) return;
        if (targetForm.dataset.confirmed === '1') {
            targetForm.dataset.confirmed = '0';
            return;
        }

        event.preventDefault();
        pendingDeleteForm = targetForm;
        if (deleteStallNo) deleteStallNo.textContent = targetForm.dataset.stallNo || '-';
        if (deleteStallLocation) deleteStallLocation.textContent = targetForm.dataset.location || '-';
        openModal(deleteModal);
    });

    allModals.forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target !== modal) return;
            if (modal === registerModal) {
                resetForm(registerStallForm);
                registerHelpers.syncRate();
                registerHelpers.syncTenant();
            }
            if (modal === deleteModal) {
                pendingDeleteForm = null;
            }
            closeModal(modal);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        closeModal(registerModal);
        closeModal(editModal);
        closeModal(viewModal);
        pendingDeleteForm = null;
        closeModal(deleteModal);
    });

    if (hasErrors) {
        if (oldFormMode === 'edit' && editStallForm) {
            const stallId = String(oldFormStallId || '').trim();
            if (stallId !== '') {
                editStallForm.action = editActionTemplate.replace('__STALL_ID__', stallId);
                setValue('editFormStallId', stallId);
            }
            openModal(editModal);
        } else {
            openModal(registerModal);
        }
    }

    if (statusToast) {
        window.setTimeout(() => {
            statusToast.classList.add('is-hiding');
            window.setTimeout(() => statusToast.remove(), 220);
        }, 2600);
    }

    lockBody();
})();
</script>
@endsection

