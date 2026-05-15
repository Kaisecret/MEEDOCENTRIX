@extends('layouts.app')

@section('content')
@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\CollectionDispatchItem> $items */
@endphp

<style>
    #contentArea { padding:10px !important; }
    .mtr-page { display:grid; gap:10px; font-family:'Inter',system-ui,sans-serif; color:#334155; }
    .mtr-card { border:1px solid #e2e8f0; border-radius:12px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.05); overflow:hidden; }
    .mtr-head { border-bottom:1px solid #e2e8f0; padding:10px; background:#fafcff; display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .mtr-head h3 { margin:0; color:#0f172a; font-weight:800; font-size:1rem; }
    .mtr-filter {
        display: grid;
        grid-template-columns: 190px minmax(520px, 1fr);
        gap: 10px;
        align-items: center;
        width: min(100%, 920px);
        margin-left: auto;
    }
    .mtr-input { min-height:38px; border:1.5px solid #e2e8f0; border-radius:9px; background:#f8fafc; padding:.45rem .7rem; font-size:.86rem; color:#0f172a; font-family:inherit; }
    .mtr-input:focus { outline:none; border-color:#0f5fa8; box-shadow:0 0 0 3px rgba(15,95,168,.1); background:#fff; }
    .mtr-filter input[name="q"] {
        min-height: 42px;
        width: 100%;
        font-size: .95rem;
        padding: .55rem .8rem;
    }
    .mtr-filter select[name="status"] {
        min-height: 42px;
        font-size: .92rem;
    }
    .mtr-range-bar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:11px; padding:10px; }
    .mtr-range-chip { display:inline-flex; align-items:center; gap:6px; text-decoration:none; font-size:.8rem; font-weight:700; color:#475569; padding:.4rem .8rem; border-radius:8px; border:1px solid transparent; cursor:pointer; background:transparent; font-family:inherit; transition:all .15s; }
    .mtr-range-chip:hover { color:#0f5fa8; background:#e2e8f0; }
    .mtr-range-chip.is-active { background:#fff; color:#0f5fa8; border-color:#bfdbfe; box-shadow:0 1px 3px rgba(15,95,168,.12); }
    .mtr-range-custom { display:flex; gap:10px; align-items:center; padding:0 10px; border-left:1px solid #cbd5e1; margin-left:0; }
    .mtr-range-custom label { font-size:.72rem; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
    .mtr-range-custom input[type=date] { min-height:32px; border:1.5px solid #cbd5e1; border-radius:7px; background:#fff; padding:.25rem .5rem; font-size:.8rem; color:#0f172a; font-family:inherit; }
    .mtr-range-custom input[type=date]:focus { outline:none; border-color:#0f5fa8; box-shadow:0 0 0 2px rgba(15,95,168,.12); }
    .mtr-range-apply { background:#0f5fa8; color:#fff; border:none; border-radius:7px; padding:.38rem .75rem; font-size:.78rem; font-weight:700; cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:5px; }
    .mtr-range-apply:hover { background:#0a4880; }
    .mtr-range-label { font-size:.78rem; color:#64748b; font-weight:600; margin-left:10px; }
    .mtr-range-label strong { color:#0f172a; font-weight:800; }

    .mtr-table-wrap { overflow:auto; }
    .mtr-table { width:100%; border-collapse:collapse; min-width:1080px; }
    .mtr-table th { background:#eef5fb; color:#103250; text-transform:uppercase; letter-spacing:.04em; font-size:.73rem; font-weight:800; text-align:left; padding:10px; border-bottom:1px solid #e2e8f0; }
    .mtr-table td { padding:10px; border-bottom:1px solid #f1f5f9; font-size:.88rem; color:#334155; vertical-align:middle; }
    .mtr-table tbody tr:hover td { background:#f8fafc; }
    .mtr-sub { color:#64748b; font-size:.8rem; }
    .mtr-code { font-family:'Courier New',monospace; font-size:.8rem; }
    .mtr-pill-status { border-radius:999px; padding:.22rem .65rem; font-size:.7rem; font-weight:800; border:1px solid transparent; display:inline-flex; align-items:center; gap:4px; text-transform:uppercase; }
    .mtr-sent { background:#eff6ff; border-color:#bfdbfe; color:#1d4ed8; }
    .mtr-await { background:#fffbeb; border-color:#fde68a; color:#92400e; }
    .mtr-accepted { background:#ecfdf5; border-color:#a7f3d0; color:#065f46; }
    .mtr-rejected { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
    .mtr-cancelled { background:#f1f5f9; border-color:#cbd5e1; color:#475569; }
    .mtr-empty { text-align:center; color:#64748b; padding:10px !important; }
    .mtr-proof-btn { display:inline-flex; align-items:center; gap:5px; border:1px solid #cbd5e1; border-radius:7px; background:#fff; color:#334155; padding:.26rem .58rem; font-size:.76rem; text-decoration:none; }
    .mtr-proof-btn:hover { border-color:#0f5fa8; color:#0f5fa8; background:#f0f7ff; }
    .mtr-proof-modal-wrap { position:fixed; inset:0; background:rgba(15,23,42,.55); z-index:1800; padding:1rem; display:none; align-items:center; justify-content:center; }
    .mtr-proof-modal-wrap.is-open { display:flex; }
    .mtr-proof-modal { width:min(920px,96vw); max-height:calc(100vh - 2rem); display:flex; flex-direction:column; border-radius:14px; background:#fff; border:1px solid #e2e8f0; overflow:hidden; }
    .mtr-proof-head { border-bottom:1px solid #e2e8f0; padding:.9rem 1.1rem; display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .mtr-proof-head h4 { margin:0; color:#0f172a; font-size:.95rem; font-weight:800; }
    .mtr-proof-close { width:34px; height:34px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; color:#334155; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; }
    .mtr-proof-close:hover { border-color:#0f5fa8; color:#0f5fa8; background:#f0f7ff; }
    .mtr-proof-body { padding:1rem 1.1rem; display:grid; gap:10px; }
    .mtr-proof-meta { color:#64748b; font-size:.82rem; font-weight:700; }
    .mtr-proof-image { width:100%; max-height:72vh; object-fit:contain; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc; }
    .mtr-proof-image-wrap { position:relative; }
    .mtr-proof-watermark { position:absolute; inset:0; pointer-events:none; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .mtr-proof-watermark span { transform:rotate(-24deg); color:rgba(255,255,255,.34); font-size:clamp(14px,2.2vw,32px); font-weight:900; letter-spacing:.2em; text-align:center; text-shadow:0 1px 4px rgba(15,23,42,.35); padding:.5rem 1rem; border:1px dashed rgba(255,255,255,.28); border-radius:10px; background:rgba(15,23,42,.08); }
    .mtr-proof-foot { border-top:1px solid #e2e8f0; padding:.75rem 1.1rem; display:flex; justify-content:flex-end; background:#f8fafc; }
    .mtr-proof-foot-btn { border:1px solid #cbd5e1; border-radius:8px; background:#fff; color:#334155; font-size:.83rem; font-weight:700; padding:.45rem .85rem; cursor:pointer; }
    .mtr-proof-foot-btn:hover { border-color:#0f5fa8; color:#0f5fa8; background:#f0f7ff; }
    .mtr-pager {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .mtr-pager-meta { color:#64748b; font-size:.82rem; font-weight:700; }
    .mtr-pager-actions { display:flex; align-items:center; gap:10px; }
    .mtr-page-link {
        min-height: 34px;
        padding: 0 12px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #0f5fa8;
        font-size: .84rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .mtr-page-link:hover { background:#f1f5f9; }
    .mtr-page-link.is-disabled {
        background:#f8fafc;
        color:#94a3b8;
        border-color:#e2e8f0;
        pointer-events:none;
    }

    @media (max-width: 980px) {
        .mtr-filter {
            grid-template-columns: 1fr;
            width: 100%;
        }
    }
</style>

<div class="mtr-page" data-server-rendered-page="market_records" data-page-title="Market Transactions">
    <section class="mtr-card">
        <div class="mtr-head" style="flex-direction:column;align-items:stretch;gap:10px;">
            <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;">
                <h3>Transaction Ledger <span class="mtr-range-label">Showing: <strong>{{ $rangeLabel }}</strong></span></h3>
                <form method="GET" action="{{ route('market.records') }}" class="mtr-filter" id="mtrFilterForm">
                    <input type="hidden" name="range" value="{{ $range }}" id="mtrRangeInput">
                    <input type="hidden" name="from" value="{{ $from }}" id="mtrFromHidden">
                    <input type="hidden" name="to" value="{{ $to }}" id="mtrToHidden">
                    <select name="status" class="mtr-input" id="mtrStatusSelect" onchange="document.getElementById('mtrFilterForm').submit()">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="collected_pending_confirmation" {{ $status === 'collected_pending_confirmation' ? 'selected' : '' }}>Awaiting Approval</option>
                        <option value="accepted" {{ $status === 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <input type="search" name="q" value="{{ $search }}" class="mtr-input" id="mtrSearchInput" placeholder="Search stall, tenant, payment no..." autocomplete="off">
                </form>
            </div>

            <div class="mtr-range-bar" role="group" aria-label="Date range">
                <button type="button" class="mtr-range-chip {{ $range === 'all' ? 'is-active' : '' }}" data-range="all">
                    <i class="fa-solid fa-infinity"></i> All
                </button>
                <button type="button" class="mtr-range-chip {{ $range === 'today' ? 'is-active' : '' }}" data-range="today">
                    <i class="fa-solid fa-calendar-day"></i> Today
                </button>
                <button type="button" class="mtr-range-chip {{ $range === 'week' ? 'is-active' : '' }}" data-range="week">
                    <i class="fa-regular fa-calendar"></i> This Week
                </button>
                <button type="button" class="mtr-range-chip {{ $range === 'month' ? 'is-active' : '' }}" data-range="month">
                    <i class="fa-solid fa-calendar-days"></i> This Month
                </button>
                <div class="mtr-range-custom">
                    <label for="mtrFrom">From</label>
                    <input type="date" id="mtrFrom" value="{{ $from }}">
                    <label for="mtrTo">To</label>
                    <input type="date" id="mtrTo" value="{{ $to }}">
                    <button type="button" class="mtr-range-apply" id="mtrApplyCustom">
                        <i class="fa-solid fa-filter"></i> Apply
                    </button>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const form = document.getElementById('mtrFilterForm');
                const rangeInput = document.getElementById('mtrRangeInput');
                const fromHidden = document.getElementById('mtrFromHidden');
                const toHidden = document.getElementById('mtrToHidden');
                const fromInput = document.getElementById('mtrFrom');
                const toInput = document.getElementById('mtrTo');
                const applyBtn = document.getElementById('mtrApplyCustom');
                const searchInput = document.getElementById('mtrSearchInput');
                if (!form) return;

                document.querySelectorAll('.mtr-range-chip').forEach((chip) => {
                    chip.addEventListener('click', () => {
                        rangeInput.value = chip.dataset.range || 'all';
                        fromHidden.value = '';
                        toHidden.value = '';
                        form.submit();
                    });
                });

                applyBtn?.addEventListener('click', () => {
                    if (!fromInput.value && !toInput.value) return;
                    rangeInput.value = 'custom';
                    fromHidden.value = fromInput.value;
                    toHidden.value = toInput.value;
                    form.submit();
                });

                let searchDebounce = null;
                let lastSearch = searchInput ? searchInput.value : '';
                searchInput?.addEventListener('input', () => {
                    window.clearTimeout(searchDebounce);
                    searchDebounce = window.setTimeout(() => {
                        const nextValue = searchInput.value;
                        if (nextValue === lastSearch) return;
                        lastSearch = nextValue;
                        form.requestSubmit();
                    }, 350);
                });
            })();
        </script>

        <div class="mtr-table-wrap">
            <table class="mtr-table">
                <thead>
                    <tr>
                        <th>Payment No.</th>
                        <th>Stall</th>
                        <th>Tenant / Business</th>
                        <th>Payer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Proof</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $lease = $item->marketStallLease;
                            $stall = $lease?->stall;
                            $tenant = $lease?->tenant;
                            $payment = $item->marketPaymentCollection;
                            $statusKey = (string) $item->status;
                            $statusClass = match ($statusKey) {
                                'accepted' => 'mtr-accepted',
                                'collected_pending_confirmation' => 'mtr-await',
                                'rejected' => 'mtr-rejected',
                                'cancelled' => 'mtr-cancelled',
                                default => 'mtr-sent',
                            };
                            $statusLabel = match ($statusKey) {
                                'collected_pending_confirmation' => 'Awaiting',
                                default => ucfirst(str_replace('_', ' ', $statusKey)),
                            };
                        @endphp
                        <tr>
                            <td>
                                <strong class="mtr-code">{{ $payment?->payment_number ?? '-' }}</strong><br>
                                <span class="mtr-sub">{{ $lease?->contract_number ?: 'No contract no.' }}</span>
                            </td>
                            <td>
                                <strong>{{ $stall?->stall_no ?? '-' }}</strong><br>
                                <span class="mtr-sub">{{ $stall?->location?->location_code ?? '-' }} - {{ $stall?->location?->location_name ?? '-' }}</span>
                            </td>
                            <td>
                                <strong>{{ $tenant?->fullName() ?: '-' }}</strong><br>
                                <span class="mtr-sub">{{ $tenant?->business_name ?: '-' }}</span>
                            </td>
                            <td>{{ $item->payer_name ?: '-' }}</td>
                            <td><strong>PHP {{ number_format((float) $item->amount_snapshot, 2) }}</strong></td>
                            <td><span class="mtr-pill-status {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td>
                                @if ($item->proof_image_path)
                                    <button
                                        type="button"
                                        class="mtr-proof-btn js-mtr-proof-btn"
                                        data-proof-url="{{ route('collection.proof', $item) }}"
                                        data-proof-label="{{ $payment?->payment_number ?? '-' }} | {{ $stall?->stall_no ?? '-' }}"
                                    ><i class="fa-solid fa-image"></i> View</button>
                                @else
                                    <span class="mtr-sub">No image</span>
                                @endif
                            </td>
                            <td>{{ optional($item->updated_at)->format('m/d/Y h:i A') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="mtr-empty">
                                <i class="fa-solid fa-folder-open" style="font-size:1.6rem;color:#cbd5e1;display:block;margin-bottom:8px;"></i>
                                No market payment transactions found for this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($items->total() > 0)
        <div class="mtr-pager">
            <div class="mtr-pager-meta">
                Page {{ $items->currentPage() }} of {{ $items->lastPage() }} | Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} results
            </div>
            <div class="mtr-pager-actions">
                @if ($items->previousPageUrl())
                    <a class="mtr-page-link" href="{{ $items->previousPageUrl() }}">Previous</a>
                @else
                    <span class="mtr-page-link is-disabled">Previous</span>
                @endif
                @if ($items->nextPageUrl())
                    <a class="mtr-page-link" href="{{ $items->nextPageUrl() }}">Next</a>
                @else
                    <span class="mtr-page-link is-disabled">Next</span>
                @endif
            </div>
        </div>
    @endif
</div>

<div class="mtr-proof-modal-wrap" id="mtrProofModal" aria-hidden="true">
    <div class="mtr-proof-modal" role="dialog" aria-modal="true" aria-labelledby="mtrProofTitle">
        <div class="mtr-proof-head">
            <h4 id="mtrProofTitle"><i class="fa-solid fa-image"></i> Proof Preview</h4>
            <button type="button" class="mtr-proof-close" id="mtrProofCloseBtn" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="mtr-proof-body">
            <div class="mtr-proof-meta" id="mtrProofMeta">Record: -</div>
            <div class="mtr-proof-image-wrap">
                <img id="mtrProofImage" class="mtr-proof-image" src="" alt="Proof image preview">
                <div class="mtr-proof-watermark" aria-hidden="true">
                    <span>MARKET CONFIDENTIAL</span>
                </div>
            </div>
        </div>
        <div class="mtr-proof-foot">
            <button type="button" class="mtr-proof-foot-btn" id="mtrProofCloseFootBtn">Close</button>
        </div>
    </div>
</div>

<script>
    (function () {
        const proofModal = document.getElementById('mtrProofModal');
        const proofImage = document.getElementById('mtrProofImage');
        const proofMeta = document.getElementById('mtrProofMeta');
        const closeBtn = document.getElementById('mtrProofCloseBtn');
        const closeFootBtn = document.getElementById('mtrProofCloseFootBtn');

        if (!proofModal || !proofImage) return;

        const openProofModal = (url, label) => {
            if (!url) return;
            proofImage.src = url;
            if (proofMeta) proofMeta.textContent = 'Record: ' + (label || '-');
            proofModal.classList.add('is-open');
            proofModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        const closeProofModal = () => {
            proofModal.classList.remove('is-open');
            proofModal.setAttribute('aria-hidden', 'true');
            proofImage.src = '';
            document.body.style.overflow = '';
        };

        document.querySelectorAll('.js-mtr-proof-btn').forEach((button) => {
            button.addEventListener('click', () => {
                openProofModal(button.getAttribute('data-proof-url') || '', button.getAttribute('data-proof-label') || '-');
            });
        });

        closeBtn?.addEventListener('click', closeProofModal);
        closeFootBtn?.addEventListener('click', closeProofModal);
        proofModal.addEventListener('click', (event) => { if (event.target === proofModal) closeProofModal(); });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && proofModal.classList.contains('is-open')) closeProofModal();
        });
    })();
</script>
@endsection
