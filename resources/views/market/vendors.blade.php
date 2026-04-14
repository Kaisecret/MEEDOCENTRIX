@extends('layouts.app')

@section('content')
<style>
    .mkt-page { display:grid; gap:16px; font-family:'Inter',system-ui,sans-serif; color:#334155; }
    .mkt-hero { background:linear-gradient(135deg, #0a3d6b 0%, #0f5fa8 55%, #1a7fd4 100%); color:#fff; border-radius:16px; padding:1.45rem 1.6rem; display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center; box-shadow:0 4px 14px rgba(10,63,168,.22); }
    .mkt-hero h2 { margin:0 0 .4rem; font-size:1.7rem; font-weight:800; letter-spacing:-.02em; }
    .mkt-hero p { margin:0; font-size:.95rem; color:rgba(255,255,255,.85); }
    
    .mkt-hero-btn { border:1px solid rgba(255,255,255,.38); background:rgba(255,255,255,.15); color:#fff; border-radius:10px; min-height:42px; padding:0 1rem; font-size:.95rem; font-weight:700; display:inline-flex; align-items:center; gap:8px; text-decoration:none; cursor:pointer; transition:all .2s; }
    .mkt-hero-btn:hover { background:rgba(255,255,255,.25); transform:translateY(-1px); }

    .mkt-card { border:1px solid #e2e8f0; border-radius:16px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.03); overflow:hidden; }
    .mkt-head { border-bottom:1px solid #e2e8f0; background:#fff; padding:1.25rem 1.6rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
    .mkt-head h3 { margin:0; font-size:1.15rem; font-weight:800; color:#0f172a; }
    
    .mkt-search { position:relative; width:min(100%, 320px); }
    .mkt-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:.9rem; }
    .mkt-search input { width:100%; height:40px; border:1px solid #cbd5e1; border-radius:10px; background:#f8fafc; padding:0 14px 0 38px; font-size:.9rem; color:#0f172a; transition:all .2s; outline:none; }
    .mkt-search input:focus { border-color:#0f5fa8; background:#fff; box-shadow:0 0 0 3px rgba(15,95,168,.1); }

    .mkt-table-wrap { overflow:auto; }
    .mkt-table { width:100%; border-collapse:collapse; min-width:800px; }
    .mkt-table th { background:#f8fafc; color:#103250; border-bottom:1px solid #e2e8f0; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; font-weight:800; padding:1.1rem 1.6rem; text-align:left; }
    .mkt-table td { padding:1rem 1.6rem; border-bottom:1px solid #f1f5f9; color:#334155; font-size:.92rem; vertical-align:middle; }
    .mkt-table tbody tr:hover { background:#f8fafc; }
    
    .mkt-btn-action { width:32px; height:32px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; display:inline-flex; align-items:center; justify-content:center; color:#334155; cursor:pointer; transition:all .2s; }
    .mkt-btn-action:hover { background:#f8fafc; border-color:#94a3b8; color:#0f5fa8; transform:translateY(-1px); }
</style>

<div class="mkt-page">
    <section class="mkt-hero">
        <div>
            <h2><i class="fa-solid fa-address-book" style="margin-right:8px;opacity:.9;"></i>Vendor Directory</h2>
            <p>Manage and edit the directory of all registered vendors operating in the Public Market.</p>
        </div>
        <div>
            <button class="mkt-hero-btn" onclick="openAddVendorModal()">
                <i class="fa-solid fa-user-plus"></i> Register Vendor
            </button>
        </div>
    </section>

    <section class="mkt-card">
        <div class="mkt-head">
            <h3>Registered Vendors</h3>
            <div class="mkt-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search vendor name or business..." onkeyup="filterVendors(this.value)">
            </div>
        </div>
        
        <div class="mkt-table-wrap">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th>Vendor ID</th>
                        <th>Vendor Name</th>
                        <th>Business Name</th>
                        <th>Category / Type</th>
                        <th>Contact No.</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="vendorTableBody">
                    <tr>
                        <td colspan="7" style="text-align: center; color: #64748b; padding: 2rem;">No vendor data available. Data binding pending.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

@endsection