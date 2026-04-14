@extends('layouts.app')

@section('content')
<style>
    .mkt-page { display:grid; gap:16px; font-family:'Inter',system-ui,sans-serif; color:#334155; }
    .mkt-hero { background:linear-gradient(135deg, #0a3d6b 0%, #0f5fa8 55%, #1a7fd4 100%); color:#fff; border-radius:16px; padding:1.45rem 1.6rem; display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center; box-shadow:0 4px 14px rgba(10,63,168,.22); }
    .mkt-hero h2 { margin:0 0 .4rem; font-size:1.7rem; font-weight:800; letter-spacing:-.02em; }
    .mkt-hero p { margin:0; font-size:.95rem; color:rgba(255,255,255,.85); }
    
    .mkt-kpi-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
    .mkt-kpi { border-radius:16px; border:1px solid #e2e8f0; background:#fff; padding:1.25rem 1.5rem; display:flex; align-items:center; gap:16px; box-shadow:0 2px 8px rgba(0,0,0,.03); transition:all .2s; cursor:pointer;}
    .mkt-kpi:hover { transform:translateY(-2px); box-shadow:0 12px 24px rgba(0,0,0,.06); border-color:#cbd5e1; }
    .mkt-kpi-icon { width:58px; height:58px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.55rem; flex-shrink:0; }
    .mkt-kpi-green { background:#ecfdf5; color:#059669; }
    .mkt-kpi-blue { background:#eff6ff; color:#2563eb; }
    .mkt-kpi-amber { background:#fffbeb; color:#d97706; }
    
    .mkt-kpi-info h3 { margin:0; font-size:.82rem; color:#64748b; font-weight:800; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid transparent;}
    .mkt-kpi-info h2 { margin:.2rem 0; font-size:1.7rem; font-weight:800; color:#0f172a; }
    .mkt-kpi-info span { font-size:.82rem; font-weight:700; display:inline-flex; align-items:center; gap:4px; }
    
    .mkt-card { border:1px solid #e2e8f0; border-radius:16px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.03); overflow:hidden; }
    .mkt-head { border-bottom:1px solid #e2e8f0; background:#fff; padding:1.25rem 1.6rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
    .mkt-head h3 { margin:0; font-size:1.15rem; font-weight:800; color:#0f172a; }
    .mkt-btn { border-radius:10px; border:1.5px solid #cbd5e1; background:#fff; color:#0f5fa8; padding:.5rem 1.1rem; font-size:.84rem; font-weight:800; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:all .2s; }
    .mkt-btn:hover { background:#f8fafc; border-color:#94a3b8; transform:translateY(-1px); }
    
    .mkt-table-wrap { overflow:auto; }
    .mkt-table { width:100%; border-collapse:collapse; min-width:800px; }
    .mkt-table th { background:#f8fafc; color:#103250; border-bottom:1px solid #e2e8f0; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; font-weight:800; padding:1.1rem 1.6rem; text-align:left; }
    .mkt-table td { padding:1rem 1.6rem; border-bottom:1px solid #f1f5f9; color:#334155; font-size:.92rem; vertical-align:middle; }
    .mkt-table tbody tr:hover { background:#f8fafc; }
    .mkt-badge-green { background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; padding:.25rem .7rem; border-radius:999px; font-size:.72rem; font-weight:800; text-transform:uppercase; }
    .mkt-badge-amber { background:#fffbeb; border:1px solid #fde68a; color:#b45309; padding:.25rem .7rem; border-radius:999px; font-size:.72rem; font-weight:800; text-transform:uppercase; }
    .mkt-badge-blue { background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; padding:.25rem .7rem; border-radius:999px; font-size:.72rem; font-weight:800; text-transform:uppercase; }
    
    @media (max-width:960px) { .mkt-kpi-grid { grid-template-columns:1fr 1fr; } }
    @media (max-width:640px) { .mkt-kpi-grid { grid-template-columns:1fr; } .mkt-table-wrap { padding-bottom: 8px;} }
</style>

<div class="mkt-page">
    <section class="mkt-hero">
        <div>
            <h2><i class="fa-solid fa-chart-line" style="margin-right:8px;opacity:.9;"></i>Market Administrator Dashboard</h2>
            <p>Monitor high-level market activity, vendor statistics, and pending collections in real-time.</p>
        </div>
    </section>

    <div class="mkt-kpi-grid">
        <div class="mkt-kpi">
            <div class="mkt-kpi-icon mkt-kpi-green"><i class="fa-solid fa-store"></i></div>
            <div class="mkt-kpi-info">
                <h3>Active Stalls</h3>
                <h2>142</h2>
                <span style="color:#059669;"><i class="fa-solid fa-circle-check"></i> 95% Occupancy Rate</span>
            </div>
        </div>
        <div class="mkt-kpi">
            <div class="mkt-kpi-icon mkt-kpi-blue"><i class="fa-solid fa-users"></i></div>
            <div class="mkt-kpi-info">
                <h3>Registered Vendors</h3>
                <h2>168</h2>
                <span style="color:#2563eb;"><i class="fa-solid fa-arrow-trend-up"></i> +3 new this month</span>
            </div>
        </div>
        <div class="mkt-kpi">
            <div class="mkt-kpi-icon mkt-kpi-amber"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            <div class="mkt-kpi-info">
                <h3>Pending for Payment</h3>
                <h2>15</h2>
                <span style="color:#d97706;"><i class="fa-regular fa-clock"></i> Requires sending to collector</span>
            </div>
        </div>
    </div>

    <section class="mkt-card">
        <div class="mkt-head">
            <h3>Recent Market Activity</h3>
            <button class="mkt-btn" type="button" onclick="navigateTo('market_records')"><i class="fa-solid fa-list-check"></i> View Full Ledger</button>
        </div>
        <div class="mkt-table-wrap">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th>Ref ID</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>#MKT-2042</strong></td>
                        <td>Stall Rental - Sec A (Jose Rizal)</td>
                        <td><strong>₱5,000.00</strong></td>
                        <td><span class="mkt-badge-green">Collected</span></td>
                        <td>Today, 11:45 AM</td>
                    </tr>
                    <tr>
                        <td><strong>#MKT-2041</strong></td>
                        <td>Arcabala Ticket - Elena Marquez</td>
                        <td><strong>₱20.00</strong></td>
                        <td><span class="mkt-badge-amber">Pending</span></td>
                        <td>Today, 09:30 AM</td>
                    </tr>
                    <tr>
                        <td><strong>#MKT-2040</strong></td>
                        <td>Vendor Permit Fee - Maria Santos</td>
                        <td><strong>₱1,200.00</strong></td>
                        <td><span class="mkt-badge-blue">Sent</span></td>
                        <td>Today, 08:15 AM</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection