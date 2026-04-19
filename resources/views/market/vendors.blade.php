@extends('layouts.app')

@section('content')
<style>
    .mkt-page { display:grid; gap:16px; font-family:'Inter',system-ui,sans-serif; color:#334155; }
    .mkt-hero { background:linear-gradient(135deg, #0a3d6b 0%, #0f5fa8 55%, #1a7fd4 100%); color:#fff; border-radius:16px; padding:1.45rem 1.6rem; display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center; box-shadow:0 4px 14px rgba(10,63,168,.22); }
    .mkt-hero h2 { margin:0 0 .4rem; font-size:1.7rem; font-weight:800; letter-spacing:-.02em; }
    .mkt-hero p { margin:0; font-size:.95rem; color:rgba(255,255,255,.85); }
    .mkt-hero-btn { border:1px solid rgba(255,255,255,.38); background:rgba(255,255,255,.15); color:#fff; border-radius:10px; min-height:42px; padding:0 1rem; font-size:.95rem; font-weight:700; display:inline-flex; align-items:center; gap:8px; text-decoration:none; cursor:pointer; transition:all .2s; }
    .mkt-hero-btn:hover { background:rgba(255,255,255,.25); transform:translateY(-1px); color:#fff; }
    .mkt-stats { margin-top:.75rem; display:flex; flex-wrap:wrap; gap:8px; }
    .mkt-pill { background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.38); border-radius:8px; padding:.25rem .75rem; font-size:.85rem; font-weight:700; display:inline-flex; align-items:center; gap:6px; }

    .mkt-card { border:1px solid #e2e8f0; border-radius:16px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.03); overflow:hidden; }
    .mkt-head { border-bottom:1px solid #e2e8f0; background:#fff; padding:1.25rem 1.6rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
    .mkt-head h3 { margin:0; font-size:1.15rem; font-weight:800; color:#0f172a; }

    .mkt-search { position:relative; width:min(100%, 360px); }
    .mkt-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:.9rem; }
    .mkt-search input { width:100%; height:40px; border:1px solid #cbd5e1; border-radius:10px; background:#f8fafc; padding:0 14px 0 38px; font-size:.9rem; color:#0f172a; transition:all .2s; outline:none; }
    .mkt-search input:focus { border-color:#0f5fa8; background:#fff; box-shadow:0 0 0 3px rgba(15,95,168,.1); }

    .mkt-table-wrap { overflow:auto; }
    .mkt-table { width:100%; border-collapse:collapse; min-width:1040px; }
    .mkt-table th { background:#f8fafc; color:#103250; border-bottom:1px solid #e2e8f0; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; font-weight:800; padding:1.1rem 1.2rem; text-align:left; }
    .mkt-table td { padding:1rem 1.2rem; border-bottom:1px solid #f1f5f9; color:#334155; font-size:.92rem; vertical-align:middle; }
    .mkt-table tbody tr:hover { background:#f8fafc; }
    .mkt-muted { color:#64748b; font-size:.83rem; }

    .mkt-badge { padding:.25rem .68rem; border-radius:999px; font-size:.72rem; font-weight:800; text-transform:uppercase; display:inline-flex; align-items:center; }
    .mkt-badge-active { background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; }
    .mkt-badge-inactive { background:#fff1f2; border:1px solid #fecdd3; color:#9f1239; }

    .mkt-icon-btn { width:32px; height:32px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; display:inline-flex; align-items:center; justify-content:center; color:#334155; cursor:pointer; transition:all .2s; text-decoration:none; }
    .mkt-icon-btn:hover { background:#f8fafc; border-color:#94a3b8; color:#0f5fa8; transform:translateY(-1px); }

    .mkt-pagination { border-top:1px solid #e2e8f0; background:#f8fafc; padding:12px 16px; display:flex; justify-content:flex-end; gap:8px; align-items:center; }
    .mkt-page-link { min-height:34px; padding:0 12px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; color:#0f5fa8; font-size:.85rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; transition:background .2s; }
    .mkt-page-link:hover { background:#f1f5f9; }
    .mkt-page-link.is-disabled { background:#f8fafc; color:#94a3b8; border-color:#e2e8f0; pointer-events:none; }

    @media (max-width:640px) { .mkt-head { align-items:stretch; } .mkt-search { width:100%; } }
</style>

<div class="mkt-page" data-server-rendered-page="vendors" data-page-title="Tenant Directory">
    <section class="mkt-hero">
        <div>
            <h2><i class="fa-solid fa-address-book" style="margin-right:8px;opacity:.9;"></i>Tenant / Lessee Directory</h2>
            <p>Automatically populated from Stall Management whenever an occupied stall with lease details is saved.</p>
            <div class="mkt-stats">
                <span class="mkt-pill"><i class="fa-solid fa-users"></i> Total: {{ number_format((int) $summary['total']) }}</span>
                <span class="mkt-pill"><i class="fa-solid fa-circle-check"></i> Active Lease: {{ number_format((int) $summary['active']) }}</span>
                <span class="mkt-pill"><i class="fa-solid fa-clock"></i> No Active Lease: {{ number_format((int) $summary['inactive']) }}</span>
            </div>
        </div>
        <a class="mkt-hero-btn" href="{{ route('market.stalls') }}">
            <i class="fa-solid fa-plus"></i> Register Stall / Lessee
        </a>
    </section>

    <section class="mkt-card">
        <div class="mkt-head">
            <h3>Registered Tenants / Lessees</h3>
            <form method="GET" action="{{ route('market.vendors') }}" class="mkt-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="q" value="{{ $search }}" placeholder="Search tenant, business, stall, or MPO control no...">
            </form>
        </div>

        <div class="mkt-table-wrap">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th>Tenant ID</th>
                        <th>Tenant / Lessee</th>
                        <th>Business</th>
                        <th>Contact</th>
                        <th>Active Stall</th>
                        <th>Lease Status</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenants as $tenant)
                        @php
                            $lease = $tenant->activeLease;
                            $stall = $lease?->stall;
                            $location = $stall?->location;
                            $hasActiveLease = $lease !== null;
                            $tenantIdLabel = 'TNT-' . str_pad((string) $tenant->id, 4, '0', STR_PAD_LEFT);
                        @endphp
                        <tr>
                            <td><strong>{{ $tenantIdLabel }}</strong></td>
                            <td>
                                <strong>{{ $tenant->fullName() ?: '-' }}</strong><br>
                                <span class="mkt-muted">{{ $tenant->mpo_control_no ?: 'No MPO control no.' }}</span>
                            </td>
                            <td>
                                <strong>{{ $tenant->business_name ?: '-' }}</strong><br>
                                <span class="mkt-muted">{{ $tenant->business_type ?: 'No business type' }}</span>
                            </td>
                            <td>
                                <strong>{{ $tenant->contact_number ?: '-' }}</strong><br>
                                <span class="mkt-muted">{{ $tenant->address ?: 'No address' }}</span>
                            </td>
                            <td>
                                @if ($stall)
                                    <strong>{{ $stall->stall_no }}</strong><br>
                                    <span class="mkt-muted">{{ $location?->location_code ?: '-' }} - {{ $location?->location_name ?: '-' }}</span>
                                @else
                                    <strong>-</strong><br>
                                    <span class="mkt-muted">No active stall</span>
                                @endif
                            </td>
                            <td>
                                <span class="mkt-badge {{ $hasActiveLease ? 'mkt-badge-active' : 'mkt-badge-inactive' }}">
                                    {{ $hasActiveLease ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ optional($tenant->updated_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <a
                                    class="mkt-icon-btn"
                                    href="{{ route('market.vendors.edit', $tenant) }}"
                                    title="View and edit tenant record"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 2rem;">
                                No tenant/lessee records found yet. Save an occupied stall with lease details to populate this directory.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tenants->hasPages())
            <div class="mkt-pagination">
                @if ($tenants->previousPageUrl())
                    <a class="mkt-page-link" href="{{ $tenants->previousPageUrl() }}">Previous</a>
                @else
                    <span class="mkt-page-link is-disabled">Previous</span>
                @endif
                @if ($tenants->nextPageUrl())
                    <a class="mkt-page-link" href="{{ $tenants->nextPageUrl() }}">Next</a>
                @else
                    <span class="mkt-page-link is-disabled">Next</span>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection
