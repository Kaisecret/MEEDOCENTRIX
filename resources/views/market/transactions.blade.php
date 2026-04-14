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
    
    .mkt-btn { border-radius:10px; border:1.5px solid #cbd5e1; background:#fff; color:#0f5fa8; padding:.5rem 1.1rem; font-size:.84rem; font-weight:800; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:all .2s; }
    .mkt-btn:hover { background:#f8fafc; border-color:#94a3b8; transform:translateY(-1px); }

    .mkt-table-wrap { overflow:auto; padding-bottom: 8px;}
    .mkt-table { width:100%; border-collapse:collapse; min-width:800px; }
    .mkt-table th { background:#f8fafc; color:#103250; border-bottom:1px solid #e2e8f0; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; font-weight:800; padding:1.1rem 1.6rem; text-align:left; }
    .mkt-table td { padding:1rem 1.6rem; border-bottom:1px solid #f1f5f9; color:#334155; font-size:.92rem; vertical-align:middle; }
    .mkt-table tbody tr:hover { background:#f8fafc; }
</style>

<div class="mkt-page">
    <section class="mkt-hero">
        <div>
            <h2><i class="fa-solid fa-file-invoice-dollar" style="margin-right:8px;opacity:.9;"></i>Records & Transactions</h2>
            <p>View all ledger entries, payment history, and financial activity.</p>
        </div>
        <div>
            <button class="mkt-hero-btn">
                <i class="fa-solid fa-plus"></i> Add New Record
            </button>
        </div>
    </section>

    <section class="mkt-card">
        <div class="mkt-head">
            <h3>Transaction Ledger</h3>
            <button class="mkt-btn"><i class="fa-solid fa-filter"></i> Filter Results</button>
        </div>
        
        <div class="mkt-table-wrap">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title/Details</th>
                        <th>Amount (₱)</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #64748b; padding: 2.5rem 1rem;">
                            <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                            No records found or populated yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

@endsection