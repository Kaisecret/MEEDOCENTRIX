@extends('layouts.app')

@section('content')
<style>
    #contentArea { padding-top: 10px; }
    .mkt-page { display:grid; gap:10px; font-family:'Inter',system-ui,sans-serif; color:#334155; }
    .mkt-card { border:1px solid #e2e8f0; border-radius:14px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.03); overflow:hidden; }
    .mkt-head { border-bottom:1px solid #e2e8f0; background:#fff; padding:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
    .mkt-head h3 { margin:0; font-size:1rem; font-weight:800; color:#0f172a; }
    .mkt-head-actions {
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:nowrap;
        justify-content:flex-end;
        width:100%;
        max-width:520px;
        margin-left:auto;
    }
    .mkt-export-btn {
        display:inline-flex;
        align-items:center;
        gap:7px;
        min-height:38px;
        border:1px solid #155f8f;
        background:#155f8f;
        color:#fff;
        border-radius:10px;
        padding:0 12px;
        font-size:.82rem;
        font-weight:800;
        text-decoration:none;
        transition:all .15s ease;
    }
    .mkt-export-btn:hover { background:#104f77; border-color:#104f77; color:#fff; }

    .mkt-search { position:relative; flex:1 1 auto; min-width:260px; max-width:380px; }
    .mkt-search i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:.85rem; }
    .mkt-search input { width:100%; height:38px; border:1px solid #cbd5e1; border-radius:10px; background:#f8fafc; padding:0 10px 0 32px; font-size:.86rem; color:#0f172a; transition:all .2s; outline:none; }
    .mkt-search input:focus { border-color:#0f5fa8; background:#fff; box-shadow:0 0 0 3px rgba(15,95,168,.1); }

    .mkt-table-wrap { overflow:auto; }
    .mkt-table { width:100%; border-collapse:collapse; min-width:1040px; }
    .mkt-table th { background:#f8fafc; color:#103250; border-bottom:1px solid #e2e8f0; font-size:.76rem; text-transform:uppercase; letter-spacing:.04em; font-weight:800; padding:10px; text-align:left; }
    .mkt-table td { padding:10px; border-bottom:1px solid #f1f5f9; color:#334155; font-size:.88rem; vertical-align:middle; }
    .mkt-table tbody tr:hover { background:#f8fafc; }
    .mkt-muted { color:#64748b; font-size:.83rem; }

    .mkt-badge { padding:.25rem .68rem; border-radius:999px; font-size:.72rem; font-weight:800; text-transform:uppercase; display:inline-flex; align-items:center; }
    .mkt-badge-active { background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; }
    .mkt-badge-inactive { background:#fff1f2; border:1px solid #fecdd3; color:#9f1239; }

    .mkt-icon-btn { width:32px; height:32px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; display:inline-flex; align-items:center; justify-content:center; color:#334155; cursor:pointer; transition:all .2s; text-decoration:none; }
    .mkt-icon-btn:hover { background:#f8fafc; border-color:#94a3b8; color:#0f5fa8; transform:translateY(-1px); }

    .mkt-pagination { border-top:1px solid #e2e8f0; background:#f8fafc; padding:10px; display:flex; justify-content:space-between; gap:10px; align-items:center; flex-wrap:wrap; }
    .mkt-page-meta { color:#64748b; font-size:.82rem; font-weight:700; }
    .mkt-page-actions { display:flex; gap:10px; align-items:center; }
    .mkt-page-link { min-height:34px; padding:0 12px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; color:#0f5fa8; font-size:.85rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; transition:background .2s; }
    .mkt-page-link:hover { background:#f1f5f9; }
    .mkt-page-link.is-disabled { background:#f8fafc; color:#94a3b8; border-color:#e2e8f0; pointer-events:none; }

    @media (max-width:640px) {
        .mkt-head { align-items:stretch; }
        .mkt-head-actions { max-width:100%; }
        .mkt-search { max-width:100%; min-width:0; }
    }
</style>

<div class="mkt-page" data-server-rendered-page="vendors" data-page-title="Tenant Directory">
    <section class="mkt-card">
        <div class="mkt-head">
            <h3>Registered Tenants / Lessees</h3>
            <div class="mkt-head-actions">
                <a class="mkt-export-btn" href="{{ route('market.vendors.csv', ['q' => $search]) }}">
                    <i class="fa-solid fa-file-excel"></i> Convert to CSV
                </a>
                <form method="GET" action="{{ route('market.vendors') }}" class="mkt-search" id="mktSearchForm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search" id="mktSearchInput" autocomplete="off">
                </form>
            </div>
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

        @if ($tenants->total() > 0)
            <div class="mkt-pagination">
                <div class="mkt-page-meta">
                    Page {{ $tenants->currentPage() }} of {{ $tenants->lastPage() }} | Total: {{ number_format($tenants->total()) }} record{{ $tenants->total() === 1 ? '' : 's' }}
                </div>
                <div class="mkt-page-actions">
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
            </div>
        @endif
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.getElementById('mktSearchForm');
    const searchInput = document.getElementById('mktSearchInput');
    if (!searchForm || !searchInput) return;

    let debounceTimer = null;
    let lastValue = searchInput.value;

    searchInput.addEventListener('input', function () {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(function () {
            const currentValue = searchInput.value;
            if (currentValue === lastValue) return;
            lastValue = currentValue;
            searchForm.requestSubmit();
        }, 350);
    });
});
</script>
@endsection
