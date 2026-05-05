@extends('layouts.app')

@section('content')
@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\MarketStallLease> $leases */
    /** @var \Illuminate\Support\Collection<int, array{user_id:int,name:string,department:string}> $collectors */
    /** @var \Illuminate\Support\Collection<int, \App\Models\CollectionDispatchItem> $awaitingConfirmationItems */
@endphp

<div data-server-rendered-page="send_payment" data-page-title="Send for Payment" class="sp-page">
    @if (session('status') || session('error') || $errors->any())
        <div class="sp-toast-stack">
            @if (session('status'))
                <div class="sp-alert sp-alert-success js-sp-toast"><i class="fas fa-check-circle"></i> {{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="sp-alert sp-alert-error js-sp-toast"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="sp-alert sp-alert-error js-sp-toast"><i class="fas fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
            @endif
        </div>
    @endif

    <section class="sp-hero">
        <div>
            <a class="sp-due-link" href="{{ route('market.send_payment.due_tracker') }}">
                <i class="fa-solid fa-calendar-check"></i> View Daily Due Tracker
            </a>
        </div>
        <div class="sp-kpis">
            <div><span>Billable</span><strong>{{ $leases->count() }}</strong></div>
            <div><span>Collectors</span><strong>{{ $collectors->count() }}</strong></div>
            <div><span>For Approval</span><strong>{{ $awaitingConfirmationItems->count() }}</strong></div>
        </div>
    </section>

    <section class="sp-card">
        <form method="GET" action="{{ route('market.send_payment') }}" class="sp-filter-row" id="spFilterForm">
            <select name="period" class="sp-input">
                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                <option value="all" {{ $period === 'all' ? 'selected' : '' }}>All Dates</option>
                <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>
            <input type="date" name="from" value="{{ $from }}" class="sp-input">
            <input type="date" name="to" value="{{ $to }}" class="sp-input">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search stall no, tenant, contract..." class="sp-input sp-input-search">
        </form>
    </section>

    <section class="sp-card">
        <form method="POST" action="{{ route('market.send_payment.store') }}" id="sendBatchForm">
            @csrf
            <input type="hidden" name="period_type" value="{{ $period }}">
            <input type="hidden" name="from_date" value="{{ $from }}">
            <input type="hidden" name="to_date" value="{{ $to }}">

            <div class="sp-batch-head">
                <h3><i class="fa-solid fa-list-check"></i>Market Lease Charges Ready for Collector</h3>
                <div class="sp-batch-controls">
                    <label>
                        Collector
                        <select name="collector_user_id" id="collectorSelect" class="sp-input" required {{ $collectors->isEmpty() ? 'disabled' : '' }}>
                            <option value="">Select assigned collector...</option>
                            @foreach ($collectors as $collector)
                                <option value="{{ $collector['user_id'] }}">{{ $collector['name'] }} ({{ $collector['department'] }})</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="sp-send-btn" {{ $collectors->isEmpty() ? 'disabled' : '' }}>
                        <i class="fa-solid fa-paper-plane"></i> Send Selected
                    </button>
                </div>
            </div>

            @if ($collectors->isEmpty())
                <p class="sp-hint">No collector is assigned to Public Market yet. Ask admin to assign one first.</p>
            @endif

            <div class="sp-table-wrap">
                <table class="sp-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllLeases"></th>
                            <th>Stall</th>
                            <th>Tenant</th>
                            <th>Business</th>
                            <th>Contract</th>
                            <th>Billing</th>
                            <th>Amount</th>
                            <th>Queue Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leases as $lease)
                            @php
                                $stall = $lease->stall;
                                $tenant = $lease->tenant;
                                $amount = round((float) ($lease->computed_rate_amount ?? 0), 2);
                            @endphp
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        name="lease_ids[]"
                                        value="{{ $lease->id }}"
                                        class="lease-checkbox"
                                        data-amount="{{ number_format($amount, 2, '.', '') }}"
                                    >
                                </td>
                                <td>
                                    <strong>{{ $stall?->stall_no ?? '-' }}</strong><br>
                                    <span class="sp-sub">{{ $stall?->location?->location_code ?? '-' }}</span>
                                </td>
                                <td>{{ $tenant?->fullName() ?: '-' }}</td>
                                <td>{{ $tenant?->business_name ?: '-' }}</td>
                                <td>{{ $lease->contract_number ?: '-' }}</td>
                                <td>{{ ucfirst((string) ($lease->billing_period ?? 'monthly')) }} x {{ (int) ($lease->billing_cycles ?? 1) }}</td>
                                <td>PHP {{ number_format($amount, 2) }}</td>
                                <td>
                                    <span class="sp-pill sp-pill-green">Ready</span>
                                </td>
                                <td><span class="sp-sub">-</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="sp-empty"><span class="sp-empty-icon"><i class="fa-solid fa-magnifying-glass"></i></span>No billable lease transactions for this filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="sp-footer-summary">
                <span>Selected: <strong id="selectedCount">0</strong></span>
                <span>Total: <strong id="selectedAmount">PHP 0.00</strong></span>
            </div>
        </form>
    </section>

    <section class="sp-card">
        <div class="sp-batch-head">
            <h3><i class="fa-solid fa-clock-rotate-left"></i>Collection Proofs Waiting for Market Approval</h3>
        </div>
        <div class="sp-table-wrap">
            <table class="sp-table">
                <thead>
                    <tr>
                        <th>Stall</th>
                        <th>Tenant</th>
                        <th>Collector</th>
                        <th>Payer</th>
                        <th>Total</th>
                        <th>Proof</th>
                        <th>Collected</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($awaitingConfirmationItems as $item)
                        @php
                            $lease = $item->marketStallLease;
                            $stall = $lease?->stall;
                            $tenant = $lease?->tenant;
                        @endphp
                        <tr>
                            <td>{{ $stall?->stall_no ?? '-' }}</td>
                            <td>{{ $tenant?->fullName() ?: '-' }}</td>
                            <td>{{ $item->dispatch?->collector?->name ?? '-' }}</td>
                            <td>{{ $item->payer_name ?: '-' }}</td>
                            <td>PHP {{ number_format((float) $item->amount_snapshot, 2) }}</td>
                            <td>
                                @if ($item->proof_image_path)
                                    <a href="{{ route('collection.proof', $item) }}" target="_blank" class="sp-btn-outline">
                                        <i class="fas fa-image"></i> View
                                    </a>
                                @else
                                    <span class="sp-sub">No image</span>
                                @endif
                            </td>
                            <td class="sp-sub">{{ optional($item->collected_at)->format('m/d/Y h:i A') ?: '-' }}</td>
                            <td>
                                <div class="sp-action-row">
                                    <form method="POST" action="{{ route('market.send_payment.items.approve', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="sp-btn-approve"><i class="fas fa-check"></i> Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('market.send_payment.items.reject', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="review_note" value="Proof needs correction. Please review and resubmit.">
                                        <button type="submit" class="sp-btn-reject"><i class="fas fa-rotate-left"></i> Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="sp-empty"><span class="sp-empty-icon"><i class="fa-solid fa-inbox"></i></span>No collection proofs waiting for approval.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="sp-modal-backdrop" id="confirmSendModal">
    <div class="sp-modal">
        <div class="sp-modal-head">
            <h3>Confirm Send to Collector</h3>
            <button type="button" class="sp-modal-close" data-close-modal="confirmSendModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="sp-modal-body">
            <div class="sp-modal-icon"><i class="fas fa-paper-plane"></i></div>
            <div>
                <p id="confirmSendText"></p>
                <div class="sp-modal-meta">
                    <span id="confirmSendCollector"></span>
                    <span id="confirmSendCount"></span>
                    <span id="confirmSendTotal"></span>
                </div>
            </div>
        </div>
        <div class="sp-modal-foot">
            <button type="button" class="btn btn-secondary" data-close-modal="confirmSendModal">Cancel</button>
            <button type="button" class="btn btn-success" id="confirmSendBtn"><i class="fas fa-check"></i> Yes, Send Now</button>
        </div>
    </div>
</div>

<style>
:root { --sp-primary:#0f5fa8; --sp-primary-dk:#0a4880; --sp-green:#059669; --sp-red:#dc2626; --sp-amber:#d97706; --sp-border:#e2e8f0; --sp-soft:#f8fafc; --sp-text:#334155; --sp-muted:#64748b; --sp-head:#0f172a; }
#contentArea{padding:10px !important}
.sp-page{max-width:1400px;margin:0 auto;display:grid;gap:10px;padding-bottom:10px;font-family:'Inter',system-ui,sans-serif}
.sp-toast-stack{position:fixed;top:1rem;right:1rem;z-index:2500;display:flex;flex-direction:column;align-items:flex-end;gap:.5rem;pointer-events:none}
.sp-alert{border-radius:10px;padding:.75rem 1rem;display:flex;gap:8px;align-items:center;font-size:.9rem;font-weight:600;width:min(400px,100%);animation:spToastIn .3s ease both;pointer-events:auto;box-shadow:0 8px 24px rgba(0,0,0,.12)}
.sp-alert-success{background:#059669;border:1px solid #047857;color:#fff}.sp-alert-error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}.sp-alert.sp-toast-exit{animation:spToastOut .3s ease forwards}
.sp-hero{border-radius:12px;padding:10px;color:#fff;background:var(--sidebar-bg);display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;box-shadow:0 3px 10px rgba(10,63,168,.18)}
.sp-hero h1{margin:0 0 6px;font-size:1.2rem;font-weight:800;letter-spacing:-.01em}.sp-hero p{margin:0;font-size:.84rem;opacity:.9}
.sp-due-link{margin-top:10px;display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.28);color:#fff;border-radius:9px;padding:.5rem .85rem;font-size:.82rem;font-weight:700;text-decoration:none;transition:all .15s}
.sp-due-link:hover{background:rgba(255,255,255,.24);color:#fff}
.sp-kpis{display:grid;grid-template-columns:repeat(3,minmax(90px,1fr));gap:10px}
.sp-kpis div{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:.45rem .6rem;text-align:center;min-width:80px}
.sp-kpis span{font-size:.64rem;text-transform:uppercase;letter-spacing:.05em;opacity:.85;display:block;margin-bottom:3px}.sp-kpis strong{font-size:1.1rem;font-weight:800;line-height:1}
.sp-card{background:#fff;border:1px solid var(--sp-border);border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);overflow:hidden}
.sp-filter-row{padding:10px;display:grid;gap:10px;grid-template-columns:180px 160px 160px minmax(220px,1fr);align-items:end}
.sp-input{width:100%;border:1.5px solid var(--sp-border);border-radius:9px;min-height:40px;background:var(--sp-soft);color:var(--sp-head);padding:.5rem .75rem;font-family:inherit;font-size:.88rem;outline:none;transition:border-color .2s,box-shadow .2s}
.sp-input:focus{border-color:var(--sp-primary);box-shadow:0 0 0 3px rgba(15,95,168,.1);background:#fff}.sp-input-search{min-width:200px}
.sp-batch-head{padding:10px;border-bottom:1px solid var(--sp-border);display:flex;gap:10px;justify-content:space-between;flex-wrap:wrap;align-items:flex-end;background:#fafcff}
.sp-batch-head h3{margin:0 0 2px;font-size:1rem;color:var(--sp-head);font-weight:800;display:flex;align-items:center;gap:8px}.sp-batch-head h3 i{color:var(--sp-primary);font-size:.9rem}
.sp-batch-controls{display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap}.sp-batch-controls label{display:grid;gap:4px;font-size:.73rem;font-weight:700;color:var(--sp-muted);text-transform:uppercase;letter-spacing:.04em;min-width:240px}
.sp-send-btn{display:inline-flex;align-items:center;gap:7px;background:var(--sp-primary);color:#fff;border:none;border-radius:9px;padding:.6rem 1.1rem;font-size:.88rem;font-weight:700;cursor:pointer;white-space:nowrap;transition:background .2s,transform .1s;font-family:inherit}
.sp-send-btn:hover:not(:disabled){background:var(--sp-primary-dk);transform:translateY(-1px)}.sp-send-btn:disabled{opacity:.5;cursor:not-allowed}
.sp-table-wrap{overflow-x:auto}.sp-table{width:100%;border-collapse:collapse}
.sp-table thead th{background:#eef5fb;color:#103250;text-transform:uppercase;letter-spacing:.04em;font-size:.73rem;font-weight:700;text-align:left;padding:10px;border-bottom:1px solid var(--sp-border);white-space:nowrap}
.sp-table tbody td{padding:10px;border-bottom:1px solid #f1f5f9;font-size:.88rem;color:var(--sp-text)}.sp-table tbody tr:hover td{background:#f8fafc}.sp-table tbody tr:last-child td{border-bottom:none}
.sp-table input[type=checkbox]{width:16px;height:16px;accent-color:var(--sp-primary);cursor:pointer}
.sp-pill{border-radius:999px;padding:.25rem .7rem;font-size:.72rem;font-weight:700;border:1px solid transparent;display:inline-flex;align-items:center;gap:5px}
.sp-pill-green{background:#ecfdf5;border-color:#a7f3d0;color:#065f46}.sp-pill-blue{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}.sp-pill-orange{background:#fffbeb;border-color:#fde68a;color:#92400e}
.sp-footer-summary{padding:10px;display:flex;gap:10px;justify-content:flex-end;font-size:.88rem;color:var(--sp-text);border-top:1px solid var(--sp-border);background:#fafcff}
.sp-footer-summary strong{color:var(--sp-primary)}.sp-action-row{display:inline-flex;gap:6px;flex-wrap:wrap}
.sp-hint{margin:0;padding:10px;color:#991b1b;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:6px}.sp-sub{color:var(--sp-muted);font-size:.83rem}
.sp-btn-outline{display:inline-flex;align-items:center;gap:5px;border:1.5px solid var(--sp-border);background:#fff;color:var(--sp-text);border-radius:7px;padding:.35rem .7rem;font-size:.8rem;font-weight:600;cursor:pointer;font-family:inherit;transition:all .15s}
.sp-btn-outline:hover{border-color:var(--sp-primary);color:var(--sp-primary);background:#f0f7ff}
.sp-btn-approve{display:inline-flex;align-items:center;gap:5px;border:none;background:#ecfdf5;color:#065f46;border:1.5px solid #a7f3d0;border-radius:7px;padding:.35rem .75rem;font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s}
.sp-btn-approve:hover{background:#059669;color:#fff;border-color:#059669}
.sp-btn-reject{display:inline-flex;align-items:center;gap:5px;border:1.5px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:7px;padding:.35rem .75rem;font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s}
.sp-btn-reject:hover{background:var(--sp-red);color:#fff;border-color:var(--sp-red)}
.sp-empty{text-align:center;color:var(--sp-muted);padding:2.5rem 1rem !important;font-size:.9rem}.sp-empty-icon{font-size:2rem;color:#cbd5e1;display:block;margin-bottom:8px}
.sp-modal-backdrop{position:fixed;inset:0;background:rgba(10,29,49,.55);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;padding:1rem;z-index:1400}
.sp-modal-backdrop.is-open{display:flex}.sp-modal{width:min(520px,100%);border-radius:16px;border:1px solid var(--sp-border);background:#fff;box-shadow:0 24px 60px rgba(9,36,62,.28);overflow:hidden;animation:spModalIn .2s ease}
.sp-modal-head{border-bottom:1px solid var(--sp-border);padding:10px;background:#eef5fb;display:flex;justify-content:space-between;align-items:center}
.sp-modal-head h3{margin:0;font-size:1rem;color:var(--sp-head);font-weight:800}
.sp-modal-close{width:32px;height:32px;border-radius:8px;border:1px solid var(--sp-border);background:#fff;color:var(--sp-text);cursor:pointer;font-size:1.1rem;display:flex;align-items:center;justify-content:center;transition:background .15s}
.sp-modal-close:hover{background:#f1f5f9}.sp-modal-body{padding:10px;display:flex;gap:10px;align-items:flex-start}
.sp-modal-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:#dbeafe;border:1px solid #bfdbfe;color:var(--sp-primary);flex-shrink:0;font-size:1.1rem}
.sp-modal-icon-warn{background:#fef3c7;border-color:#fde68a;color:var(--sp-amber)}.sp-modal-body p{margin:0;color:var(--sp-text);font-size:.9rem;line-height:1.55}
.sp-modal-meta{margin-top:8px;display:flex;gap:6px;flex-wrap:wrap}.sp-modal-meta span{border:1px solid var(--sp-border);background:var(--sp-soft);color:var(--sp-primary);border-radius:999px;font-size:.76rem;font-weight:700;padding:.2rem .6rem}
.sp-modal-foot{border-top:1px solid var(--sp-border);padding:10px;display:flex;justify-content:flex-end;gap:10px;background:var(--sp-soft)}
@keyframes spToastIn{from{opacity:0;transform:translateX(14px)}to{opacity:1;transform:translateX(0)}}@keyframes spToastOut{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(14px)}}@keyframes spModalIn{from{opacity:0;transform:translateY(10px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
@media (max-width:980px){.sp-hero{flex-direction:column;align-items:flex-start}.sp-kpis{width:100%;grid-template-columns:repeat(3,1fr)}.sp-filter-row{grid-template-columns:1fr 1fr}}
@media (max-width:640px){.sp-toast-stack{top:.75rem;right:.75rem;left:.75rem;align-items:stretch}.sp-alert{width:100%}.sp-filter-row{grid-template-columns:1fr}.sp-batch-controls{width:100%}.sp-batch-controls label{min-width:100%}.sp-footer-summary{justify-content:space-between}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('spFilterForm');
    const periodInput = filterForm ? filterForm.querySelector('select[name="period"]') : null;
    const fromInput = filterForm ? filterForm.querySelector('input[name="from"]') : null;
    const toInput = filterForm ? filterForm.querySelector('input[name="to"]') : null;
    const searchInput = filterForm ? filterForm.querySelector('input[name="q"]') : null;
    const selectAll = document.getElementById('selectAllLeases');
    const checkboxes = Array.from(document.querySelectorAll('.lease-checkbox'));
    const selectedCount = document.getElementById('selectedCount');
    const selectedAmount = document.getElementById('selectedAmount');
    const sendBatchForm = document.getElementById('sendBatchForm');
    const collectorSelect = document.getElementById('collectorSelect');
    const confirmSendModal = document.getElementById('confirmSendModal');
    const confirmSendBtn = document.getElementById('confirmSendBtn');
    let allowSendSubmit = false;

    function submitFilterForm() { if (filterForm) filterForm.submit(); }
    function debounce(callback, delay) { let timer = null; return function () { if (timer) clearTimeout(timer); timer = setTimeout(callback, delay); }; }
    if (periodInput) periodInput.addEventListener('change', submitFilterForm);
    if (fromInput) fromInput.addEventListener('change', submitFilterForm);
    if (toInput) toInput.addEventListener('change', submitFilterForm);
    if (searchInput) searchInput.addEventListener('input', debounce(submitFilterForm, 450));

    function openModal(modal) { if (!modal) return; modal.classList.add('is-open'); document.body.style.overflow = 'hidden'; }
    function closeModal(modal) { if (!modal) return; modal.classList.remove('is-open'); if (!document.querySelector('.sp-modal-backdrop.is-open')) document.body.style.overflow = ''; }
    document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', () => closeModal(document.getElementById(button.getAttribute('data-close-modal')))));
    [confirmSendModal].forEach((modal) => modal?.addEventListener('click', (event) => { if (event.target === modal) closeModal(modal); }));

    function refreshSelectionSummary() {
        const selected = checkboxes.filter((checkbox) => checkbox.checked);
        const total = selected.reduce((sum, checkbox) => sum + Number(checkbox.dataset.amount || 0), 0);
        if (selectedCount) selectedCount.textContent = String(selected.length);
        if (selectedAmount) selectedAmount.textContent = 'PHP ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach((checkbox) => { if (!checkbox.disabled) checkbox.checked = selectAll.checked; });
            refreshSelectionSummary();
        });
    }
    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', refreshSelectionSummary));
    refreshSelectionSummary();

    if (sendBatchForm) {
        sendBatchForm.addEventListener('submit', function (event) {
            if (allowSendSubmit) return;
            const selected = checkboxes.filter((checkbox) => checkbox.checked);
            if (selected.length === 0) { event.preventDefault(); alert('Select at least one transaction to send.'); return; }
            if (!collectorSelect || collectorSelect.value.trim() === '') { event.preventDefault(); alert('Select a collector first.'); return; }
            event.preventDefault();
            const total = selected.reduce((sum, checkbox) => sum + Number(checkbox.dataset.amount || 0), 0);
            const collectorText = collectorSelect.options[collectorSelect.selectedIndex] ? collectorSelect.options[collectorSelect.selectedIndex].textContent.trim() : 'Selected collector';
            const sendText = document.getElementById('confirmSendText');
            const sendCollector = document.getElementById('confirmSendCollector');
            const sendCount = document.getElementById('confirmSendCount');
            const sendTotal = document.getElementById('confirmSendTotal');
            if (sendText) sendText.textContent = 'You are about to send selected lease charges to collector queue. Continue?';
            if (sendCollector) sendCollector.textContent = collectorText;
            if (sendCount) sendCount.textContent = selected.length + ' transaction(s)';
            if (sendTotal) sendTotal.textContent = 'PHP ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            openModal(confirmSendModal);
        });
    }
    if (confirmSendBtn && sendBatchForm) {
        confirmSendBtn.addEventListener('click', function () {
            allowSendSubmit = true;
            closeModal(confirmSendModal);
            sendBatchForm.submit();
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal(confirmSendModal);
        }
    });

    document.querySelectorAll('.js-sp-toast').forEach(function (toast) {
        window.setTimeout(function () {
            toast.classList.add('sp-toast-exit');
            window.setTimeout(function () { toast.remove(); }, 360);
        }, 3000);
    });
});
</script>
@endsection
