@extends('layouts.app')

@section('content')
@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\CollectionDispatchItem> $items */
@endphp

<style>
    .mtr-page { display:grid; gap:16px; font-family:'Inter',system-ui,sans-serif; color:#334155; }
    .mtr-hero { background:linear-gradient(135deg,#0a3d6b 0%,#0f5fa8 55%,#1a7fd4 100%); color:#fff; border-radius:16px; padding:1.35rem 1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; box-shadow:0 4px 14px rgba(10,63,168,.22); }
    .mtr-hero h2 { margin:0 0 4px; font-size:1.45rem; font-weight:800; }
    .mtr-hero p { margin:0; opacity:.88; font-size:.9rem; }
    .mtr-stats { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
    .mtr-pill { background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); border-radius:999px; padding:.25rem .7rem; font-size:.77rem; font-weight:700; display:inline-flex; gap:6px; align-items:center; }

    .mtr-card { border:1px solid #e2e8f0; border-radius:14px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.05); overflow:hidden; }
    .mtr-head { border-bottom:1px solid #e2e8f0; padding:1rem 1.2rem; background:#fafcff; display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .mtr-head h3 { margin:0; color:#0f172a; font-weight:800; font-size:1rem; }
    .mtr-filter { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    .mtr-input { min-height:38px; border:1.5px solid #e2e8f0; border-radius:9px; background:#f8fafc; padding:.45rem .7rem; font-size:.86rem; color:#0f172a; font-family:inherit; }
    .mtr-input:focus { outline:none; border-color:#0f5fa8; box-shadow:0 0 0 3px rgba(15,95,168,.1); background:#fff; }
    .mtr-range-bar { display:flex; gap:6px; flex-wrap:wrap; align-items:center; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:11px; padding:4px; }
    .mtr-range-chip { display:inline-flex; align-items:center; gap:6px; text-decoration:none; font-size:.8rem; font-weight:700; color:#475569; padding:.4rem .8rem; border-radius:8px; border:1px solid transparent; cursor:pointer; background:transparent; font-family:inherit; transition:all .15s; }
    .mtr-range-chip:hover { color:#0f5fa8; background:#e2e8f0; }
    .mtr-range-chip.is-active { background:#fff; color:#0f5fa8; border-color:#bfdbfe; box-shadow:0 1px 3px rgba(15,95,168,.12); }
    .mtr-range-custom { display:flex; gap:6px; align-items:center; padding:0 6px; border-left:1px solid #cbd5e1; margin-left:2px; }
    .mtr-range-custom label { font-size:.72rem; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
    .mtr-range-custom input[type=date] { min-height:32px; border:1.5px solid #cbd5e1; border-radius:7px; background:#fff; padding:.25rem .5rem; font-size:.8rem; color:#0f172a; font-family:inherit; }
    .mtr-range-custom input[type=date]:focus { outline:none; border-color:#0f5fa8; box-shadow:0 0 0 2px rgba(15,95,168,.12); }
    .mtr-range-apply { background:#0f5fa8; color:#fff; border:none; border-radius:7px; padding:.38rem .75rem; font-size:.78rem; font-weight:700; cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:5px; }
    .mtr-range-apply:hover { background:#0a4880; }
    .mtr-range-label { font-size:.78rem; color:#64748b; font-weight:600; margin-left:6px; }
    .mtr-range-label strong { color:#0f172a; font-weight:800; }

    .mtr-table-wrap { overflow:auto; }
    .mtr-table { width:100%; border-collapse:collapse; min-width:1080px; }
    .mtr-table th { background:#eef5fb; color:#103250; text-transform:uppercase; letter-spacing:.04em; font-size:.73rem; font-weight:800; text-align:left; padding:.86rem 1rem; border-bottom:1px solid #e2e8f0; }
    .mtr-table td { padding:.86rem 1rem; border-bottom:1px solid #f1f5f9; font-size:.88rem; color:#334155; vertical-align:middle; }
    .mtr-table tbody tr:hover td { background:#f8fafc; }
    .mtr-sub { color:#64748b; font-size:.8rem; }
    .mtr-code { font-family:'Courier New',monospace; font-size:.8rem; }
    .mtr-pill-status { border-radius:999px; padding:.22rem .65rem; font-size:.7rem; font-weight:800; border:1px solid transparent; display:inline-flex; align-items:center; gap:4px; text-transform:uppercase; }
    .mtr-sent { background:#eff6ff; border-color:#bfdbfe; color:#1d4ed8; }
    .mtr-await { background:#fffbeb; border-color:#fde68a; color:#92400e; }
    .mtr-accepted { background:#ecfdf5; border-color:#a7f3d0; color:#065f46; }
    .mtr-rejected { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
    .mtr-cancelled { background:#f1f5f9; border-color:#cbd5e1; color:#475569; }
    .mtr-empty { text-align:center; color:#64748b; padding:2.2rem 1rem !important; }
    .mtr-proof-btn { display:inline-flex; align-items:center; gap:5px; border:1px solid #cbd5e1; border-radius:7px; background:#fff; color:#334155; padding:.26rem .58rem; font-size:.76rem; text-decoration:none; }
    .mtr-proof-btn:hover { border-color:#0f5fa8; color:#0f5fa8; background:#f0f7ff; }
</style>

<div class="mtr-page" data-server-rendered-page="market_records" data-page-title="Market Transactions">
    <section class="mtr-hero">
        <div>
            <h2><i class="fa-solid fa-file-invoice-dollar" style="margin-right:8px;opacity:.9;"></i>Market Payment Transactions</h2>
            <p>All dispatched, collected, approved, and rejected market payment transactions.</p>
            <div class="mtr-stats">
                <span class="mtr-pill"><i class="fa-solid fa-list"></i> All: {{ number_format((int) $summary['all_count']) }}</span>
                <span class="mtr-pill"><i class="fa-solid fa-check"></i> Accepted: {{ number_format((int) $summary['accepted_count']) }}</span>
                <span class="mtr-pill"><i class="fa-solid fa-clock"></i> Awaiting: {{ number_format((int) $summary['awaiting_count']) }}</span>
                <span class="mtr-pill"><i class="fa-solid fa-hourglass-half"></i> Pending: {{ number_format((int) $summary['pending_count']) }}</span>
            </div>
        </div>
    </section>

    <section class="mtr-card">
        <div class="mtr-head" style="flex-direction:column;align-items:stretch;gap:12px;">
            <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;">
                <h3>Transaction Ledger <span class="mtr-range-label">Showing: <strong>{{ $rangeLabel }}</strong></span></h3>
                <form method="GET" action="{{ route('market.records') }}" class="mtr-filter" id="mtrFilterForm">
                    <input type="hidden" name="range" value="{{ $range }}" id="mtrRangeInput">
                    <input type="hidden" name="from" value="{{ $from }}" id="mtrFromHidden">
                    <input type="hidden" name="to" value="{{ $to }}" id="mtrToHidden">
                    <select name="status" class="mtr-input" onchange="document.getElementById('mtrFilterForm').submit()">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="collected_pending_confirmation" {{ $status === 'collected_pending_confirmation' ? 'selected' : '' }}>Awaiting Approval</option>
                        <option value="accepted" {{ $status === 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <input type="search" name="q" value="{{ $search }}" class="mtr-input" placeholder="Search stall, tenant, payment no...">
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
                                    <a href="{{ route('collection.proof', $item) }}" target="_blank" class="mtr-proof-btn"><i class="fa-solid fa-image"></i> View</a>
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

    <div>{{ $items->links() }}</div>
</div>
@endsection

