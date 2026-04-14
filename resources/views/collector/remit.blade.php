@extends('layouts.app')
@section('content')
<style>
:root {
    --rem-primary:#0f5fa8;
    --rem-green:#059669;
    --rem-border:#e2e8f0;
    --rem-soft:#f8fafc;
    --rem-muted:#64748b;
    --rem-head:#0f172a;
}
.rem-page {
    max-width:1100px; margin:0 auto;
    display:grid; gap:18px; padding-bottom:2rem;
    font-family:'Inter',system-ui,sans-serif;
}

/* Hero */
.rem-hero {
    border-radius:14px; padding:1.3rem 1.6rem; color:#fff;
    background:linear-gradient(135deg,#0a3d6b 0%,var(--rem-primary) 55%,#1a7fd4 100%);
    box-shadow:0 4px 14px rgba(10,63,168,.22);
    display:flex; align-items:center; gap:14px;
}
.rem-hero-icon {
    width:52px; height:52px; border-radius:14px;
    background:rgba(255,255,255,.15);
    display:flex; align-items:center; justify-content:center;
    font-size:1.4rem; flex-shrink:0;
}
.rem-hero h1 { margin:0 0 3px; font-size:1.45rem; font-weight:800; letter-spacing:-.02em; }
.rem-hero p  { margin:0; font-size:.9rem; opacity:.88; }

/* Grid */
.rem-grid { display:grid; grid-template-columns:1fr 2fr; gap:18px; align-items:start; }

/* Cards */
.rem-card { border:1px solid var(--rem-border); border-radius:14px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.05); overflow:hidden; }
.rem-card-head { padding:.9rem 1.2rem; border-bottom:1px solid var(--rem-border); background:#eef5fb; display:flex; align-items:center; gap:8px; }
.rem-card-head h3 { margin:0; font-size:.95rem; font-weight:800; color:var(--rem-head); }
.rem-card-body { padding:1.4rem; }

/* Total box */
.rem-total-box {
    background:var(--rem-soft); border:1px dashed #cbd5e1;
    border-radius:12px; padding:1.3rem; text-align:center; margin-bottom:1.2rem;
}
.rem-total-label { font-size:.73rem; font-weight:700; color:var(--rem-muted); text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px; }
.rem-total-value { font-size:2.5rem; font-weight:800; color:var(--rem-head); letter-spacing:-.03em; line-height:1; }
.rem-total-sub { font-size:.83rem; color:var(--rem-muted); margin-top:6px; }

/* Submit button */
.rem-submit-btn {
    display:flex; align-items:center; justify-content:center; gap:8px;
    width:100%; background:var(--rem-primary); color:#fff;
    border:none; border-radius:9px; padding:.8rem 1rem;
    font-size:.95rem; font-weight:700; cursor:pointer;
    transition:background .2s;
}
.rem-submit-btn:hover { background:#0a4880; }
.rem-submit-note { font-size:.78rem; color:var(--rem-muted); text-align:center; margin-top:10px; line-height:1.5; }

/* Table */
.rem-table-wrap { overflow-x:auto; }
.rem-table { width:100%; border-collapse:collapse; }
.rem-table thead th {
    background:#eef5fb; color:#103250;
    text-transform:uppercase; letter-spacing:.04em; font-size:.75rem;
    font-weight:700; text-align:left; padding:.85rem 1rem;
    border-bottom:1px solid var(--rem-border); white-space:nowrap;
}
.rem-table tbody td {
    padding:.85rem 1rem; border-bottom:1px solid #f1f5f9;
    font-size:.88rem; color:var(--rem-head);
}
.rem-table tbody tr:hover { background:var(--rem-soft); }
.rem-pill { border-radius:999px; padding:.22rem .65rem; font-size:.72rem; font-weight:700; border:1px solid transparent; }
.rem-pill-green   { background:#ecfdf5; border-color:#a7f3d0; color:#065f46; }
.rem-pill-amber   { background:#fffbeb; border-color:#fde68a; color:#92400e; }
.rem-empty { text-align:center; color:var(--rem-muted); padding:2rem; font-size:.9rem; }

@media (max-width:860px) {
    .rem-grid { grid-template-columns:1fr; }
}
</style>

<div class="rem-page" data-server-rendered-page="remit" data-page-title="Remit to Cashier">

    {{-- Hero --}}
    <section class="rem-hero">
        <div class="rem-hero-icon"><i class="fa-solid fa-arrow-right-arrow-left"></i></div>
        <div>
            <h1>Remit to Cashier</h1>
            <p>Forward your accepted collections to the Main Cashier for verification and recording.</p>
        </div>
    </section>

    <div class="rem-grid">

        {{-- Submit Remittance Card --}}
        <div class="rem-card">
            <div class="rem-card-head">
                <i class="fa-solid fa-file-invoice" style="color:var(--rem-primary);font-size:.95rem;"></i>
                <h3>Submit Remittance</h3>
            </div>
            <div class="rem-card-body">
                <div class="rem-total-box">
                    <div class="rem-total-label">Total Ready to Remit</div>
                    <div class="rem-total-value">₱0.00</div>
                    <div class="rem-total-sub">0 accepted transactions pending remittance</div>
                </div>
                <button class="rem-submit-btn" disabled>
                    <i class="fa-solid fa-paper-plane"></i> Submit Remittance Report
                </button>
                <p class="rem-submit-note">
                    Submitting forwards your accepted collections to the Main Cashier for verification.
                </p>
            </div>
        </div>

        {{-- History Card --}}
        <div class="rem-card">
            <div class="rem-card-head">
                <i class="fa-solid fa-clock-rotate-left" style="color:var(--rem-primary);font-size:.95rem;"></i>
                <h3>Recent Remittances</h3>
            </div>
            <div class="rem-table-wrap">
                <table class="rem-table">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Total Amount</th>
                            <th>Date Remitted</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="rem-empty">
                                <i class="fa-solid fa-inbox" style="font-size:1.4rem;color:#cbd5e1;display:block;margin-bottom:8px;"></i>
                                No remittance history yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection