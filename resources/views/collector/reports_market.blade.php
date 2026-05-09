@extends('layouts.app')

@section('content')
<style>
.cr, .cr-modal {--pri:var(--sidebar-bg);--pri2:#104f77;--bd:#e2e8f0;--soft:#f8fafc;--text:#334155;--mut:#64748b;--head:#0f172a;}
.cr{display:grid;gap:14px;font-family:'Inter',system-ui,sans-serif;color:var(--text);position:relative;z-index:1}
.cr-hero{border-radius:14px;padding:1.25rem 1.4rem;color:#fff;background:var(--pri);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;position:relative;z-index:2;pointer-events:auto;box-shadow:0 6px 18px rgba(21,95,143,.18)}
.cr-hero h2{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;display:flex;align-items:center}
.cr-hero p{margin:0;font-size:.9rem;opacity:.9}
.cr-hero-actions{display:flex;gap:8px;flex-wrap:wrap}
.cr-btn{display:inline-flex;align-items:center;gap:7px;border-radius:10px;min-height:40px;padding:0 1rem;border:1px solid transparent;background:#fff;color:var(--pri);font-size:.84rem;font-weight:800;text-decoration:none;cursor:pointer;position:relative;z-index:2;transition:all .15s ease}
.cr-btn:hover{background:#f0f7fd;transform:translateY(-1px);box-shadow:0 4px 10px rgba(0,0,0,.08)}
.cr-card{border:1px solid var(--bd);border-radius:12px;background:#fff;overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,.04)}
.cr-head{border-bottom:1px solid var(--bd);padding:.85rem 1.15rem;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;background:linear-gradient(180deg,#fbfdff 0%,#ffffff 100%)}
.cr-head h3{margin:0;font-size:1rem;font-weight:800;color:var(--head);display:flex;align-items:center;gap:8px}
.cr-head h3::before{content:"";display:inline-block;width:4px;height:18px;border-radius:3px;background:var(--pri)}
.cr-head-meta{font-size:.78rem;color:#64748b;font-weight:600}
.cr-body{padding:1rem 1.15rem}
.cr-filter{display:grid;grid-template-columns:180px 1fr 1fr auto auto;gap:10px;align-items:end}
.cr-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:var(--mut);font-weight:700;margin-bottom:5px;display:block}
.cr-input{width:100%;min-height:40px;border:1.5px solid #cbd5e1;border-radius:9px;background:#fff;padding:.5rem .72rem;color:#0f172a;transition:border-color .15s ease,box-shadow .15s ease}
.cr-input:focus{outline:none;border-color:var(--pri);box-shadow:0 0 0 3px rgba(21,95,143,.12)}
.cr-btn-apply{border:1px solid var(--pri);background:var(--pri);color:#fff;border-radius:9px;min-height:40px;padding:0 1rem;font-size:.84rem;font-weight:800;cursor:pointer;transition:background .15s ease}
.cr-btn-apply:hover{background:var(--pri2)}
.cr-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.cr-kpi{position:relative;border:1px solid var(--bd);border-radius:12px;background:#fff;padding:1rem 1.15rem;display:grid;gap:6px;overflow:hidden;transition:transform .15s ease,box-shadow .15s ease}
.cr-kpi::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--pri)}
.cr-kpi:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(15,23,42,.06)}
.cr-kpi span{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--mut);font-weight:700}
.cr-kpi strong{font-size:1.55rem;color:var(--head);font-weight:800;letter-spacing:-.01em;line-height:1.1}
.cr-kpi b{font-size:.78rem;color:var(--mut);font-weight:600}
.cr-kpi.cr-kpi-accepted::before{background:#047857}
.cr-kpi.cr-kpi-amount::before{background:#b45309}
.cr-tables{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.cr-tables > .cr-card:only-child{grid-column:1 / -1}
.cr-table-wrap{overflow:auto}
.cr-table{width:100%;border-collapse:collapse;min-width:620px}
.cr-table th{background:#eef5fb;color:#103250;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;font-weight:800;text-align:left;padding:.78rem .9rem;border-bottom:2px solid #d6e6f3;white-space:nowrap}
.cr-table td{padding:.72rem .9rem;border-bottom:1px solid #f1f5f9;font-size:.86rem;vertical-align:middle}
.cr-table tbody tr{transition:background .12s ease}
.cr-table tbody tr:nth-child(even) td{background:#fbfdff}
.cr-table tbody tr:hover td{background:#f0f7fd}
.cr-table tbody tr:last-child td{border-bottom:none}
.cr-table .cr-num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
.cr-sub{color:var(--mut);font-size:.78rem}
.cr-tag{display:inline-flex;padding:.18rem .55rem;border-radius:999px;font-size:.68rem;font-weight:800;border:1px solid transparent;letter-spacing:.02em}
.cr-tag-accepted{border-color:#a7f3d0;background:#ecfdf5;color:#047857}
.cr-tag-await{border-color:#a5f3fc;background:#ecfeff;color:#0e7490}
.cr-tag-rejected{border-color:#fecaca;background:#fef2f2;color:#b91c1c}
.cr-tag-pending{border-color:#fde68a;background:#fffbeb;color:#b45309}
.cr-empty{padding:1.5rem 1rem;color:#94a3b8;font-size:.86rem;text-align:center;font-style:italic}
.cr-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(10,25,45,.65);z-index:2400;backdrop-filter:blur(6px);font-family:'Inter',system-ui,sans-serif;color:var(--text)}
.cr-modal.is-open{display:flex;animation: cr-fade-in .2s ease-out forwards}
.cr-modal-card{width:min(1000px,100%);max-height:calc(100vh - 36px);background:#fff;border-radius:20px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,.3);animation: cr-slide-up .3s ease-out forwards}
.cr-modal-head{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.4rem;background:#fff;border-bottom:1px solid var(--bd);position:sticky;top:0;z-index:2}
.cr-modal-head h4{margin:0;font-size:1.15rem;font-weight:800;color:var(--head);display:flex;align-items:center;gap:10px}
.cr-modal-body{flex:1;overflow:auto;background:var(--soft);padding:1rem}
.cr-modal-actions{display:flex;gap:10px}
.cr-modal-btn{border-radius:10px;border:1.5px solid #cbd5e1;background:#fff;color:var(--text);padding:.5rem 1.1rem;font-size:.85rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s}
.cr-modal-btn:hover{background:#f1f5f9;border-color:#94a3b8;transform:translateY(-1px)}
.cr-modal-btn-primary{background:var(--pri);border-color:var(--pri);color:#fff}
.cr-modal-btn-primary:hover{background:var(--pri2);border-color:var(--pri2);color:#fff}
.cr-preview-frame{width:100%;height:75vh;border:1px solid var(--bd);border-radius:12px;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.03);display:block}
@keyframes cr-fade-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes cr-slide-up { from { opacity: 0; transform: translateY(20px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
@media (max-width:1080px){.cr-filter{grid-template-columns:1fr 1fr}.cr-tables{grid-template-columns:1fr}}
@media (max-width:640px){.cr-grid{grid-template-columns:1fr}.cr-filter{grid-template-columns:1fr}.cr-modal-head{flex-wrap:wrap;gap:8px}.cr-modal-actions{width:100%;justify-content:flex-end}.cr-preview-frame{height:68vh}}
</style>

<div data-server-rendered-page="collector_reports_market" data-page-title="Market Collector Reports" class="cr">
    <section class="cr-hero">
        <div>
            <h2><i class="fa-solid fa-store" style="margin-right:8px;opacity:.88;"></i>Market Collector Reports</h2>
        </div>
        <div class="cr-hero-actions">
            <a class="cr-btn" id="crExportCsv" href="{{ route('collector.reports.csv', ['period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
            <button class="cr-btn" type="button" id="crOpenPreview" onclick="if(window.__openCollectorReportPreview){window.__openCollectorReportPreview();}">
                <i class="fa-solid fa-file-pdf"></i> Preview & Save
            </button>
        </div>
    </section>

    <section class="cr-card">
        <div class="cr-head">
            <h3>Filter Report Range</h3>
            <div style="font-size:.82rem;color:#64748b;">{{ $rangeLabel }} &middot; {{ $rangeStart->format('M d, Y') }} to {{ $rangeEnd->format('M d, Y') }}</div>
        </div>
        <div class="cr-body">
            <form method="GET" action="{{ route('collector.reports') }}" class="cr-filter" id="crFilterForm">
                <div>
                    <label class="cr-label">Period</label>
                    <select class="cr-input" name="period" id="crPeriod">
                        <option value="day" {{ $period === 'day' ? 'selected' : '' }}>This Day</option>
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="range" {{ $period === 'range' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div>
                    <label class="cr-label">From</label>
                    <input class="cr-input" type="date" name="date_from" id="crFrom" value="{{ $dateFrom }}">
                </div>
                <div>
                    <label class="cr-label">To</label>
                    <input class="cr-input" type="date" name="date_to" id="crTo" value="{{ $dateTo }}">
                </div>
                <button type="submit" class="cr-btn-apply"><i class="fa-solid fa-filter"></i> Apply</button>
                <a class="cr-btn" href="{{ route('collector.reports') }}"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            </form>
        </div>
    </section>

    <section class="cr-grid">
        <article class="cr-kpi"><span>Total Transactions</span><strong>{{ number_format($totalTransactions) }}</strong><b>Within selected range</b></article>
        <article class="cr-kpi cr-kpi-accepted"><span>Accepted / Pending</span><strong>{{ number_format($acceptedTransactions) }} / {{ number_format($pendingTransactions) }}</strong><b>Awaiting: {{ number_format($awaitingTransactions) }}</b></article>
        <article class="cr-kpi cr-kpi-amount"><span>Total / Accepted / Pending Amount</span><strong>PHP {{ number_format($totalAmount, 2) }}</strong><b>Accepted: PHP {{ number_format($acceptedAmount, 2) }} &bull; Pending: PHP {{ number_format($pendingAmount, 2) }}</b></article>
    </section>

    @if (in_array($period, ['week', 'month'], true))
        <section class="cr-tables">
            @if ($period === 'week')
                <article class="cr-card">
                    <div class="cr-head"><h3>Weekly Summary</h3></div>
                    <div class="cr-table-wrap">
                        <table class="cr-table">
                            <thead><tr><th>Week</th><th class="cr-num">Transactions</th><th class="cr-num">Accepted</th><th class="cr-num">Pending</th><th class="cr-num">Total</th></tr></thead>
                            <tbody>
                                @forelse($weeklySummary as $row)
                                    <tr>
                                        <td><strong>{{ $row['label'] }}</strong></td>
                                        <td class="cr-num">{{ number_format($row['transactions']) }}</td>
                                        <td class="cr-num">{{ number_format($row['accepted']) }}</td>
                                        <td class="cr-num">{{ number_format($row['pending']) }}</td>
                                        <td class="cr-num">PHP {{ number_format($row['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="cr-empty">No weekly data in selected range.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            @endif

            @if ($period === 'month')
                <article class="cr-card">
                    <div class="cr-head"><h3>Monthly Summary</h3></div>
                    <div class="cr-table-wrap">
                        <table class="cr-table">
                            <thead><tr><th>Month</th><th class="cr-num">Transactions</th><th class="cr-num">Accepted</th><th class="cr-num">Pending</th><th class="cr-num">Total</th></tr></thead>
                            <tbody>
                                @forelse($monthlySummary as $row)
                                    <tr>
                                        <td><strong>{{ $row['label'] }}</strong></td>
                                        <td class="cr-num">{{ number_format($row['transactions']) }}</td>
                                        <td class="cr-num">{{ number_format($row['accepted']) }}</td>
                                        <td class="cr-num">{{ number_format($row['pending']) }}</td>
                                        <td class="cr-num">PHP {{ number_format($row['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="cr-empty">No monthly data in selected range.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            @endif
        </section>
    @endif

    <section class="cr-card">
        <div class="cr-head"><h3>Detailed Collection Report</h3><div class="cr-head-meta">Market stall payments (tenant, stall, payer, total)</div></div>
        <div class="cr-table-wrap">
            <table class="cr-table">
                <thead>
                    <tr>
                        <th>Stall</th><th>Tenant</th><th>Business</th><th>Payment No.</th><th>Date</th><th>Time</th><th>Status</th><th class="cr-num">Total</th><th>Payer</th><th>Collector</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['stall_no'] }}</strong>
                                <div class="cr-sub">{{ $row['location'] }}</div>
                            </td>
                            <td>{{ $row['tenant_name'] }}</td>
                            <td>{{ $row['business_name'] }}</td>
                            <td>{{ $row['payment_no'] }}</td>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['time'] }}</td>
                            <td>
                                @if($row['status_key'] === 'accepted')
                                    <span class="cr-tag cr-tag-accepted">Accepted</span>
                                @elseif($row['status_key'] === 'collected_pending_confirmation')
                                    <span class="cr-tag cr-tag-await">Awaiting</span>
                                @elseif($row['status_key'] === 'rejected')
                                    <span class="cr-tag cr-tag-rejected">Rejected</span>
                                @else
                                    <span class="cr-tag cr-tag-pending">Pending</span>
                                @endif
                            </td>
                            <td class="cr-num">PHP {{ number_format($row['amount'], 2) }}</td>
                            <td>{{ $row['payer_name'] }}</td>
                            <td>{{ $row['collector'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="cr-empty">No transactions found in selected range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="cr-modal" id="crPreviewModal" aria-hidden="true">
    <div class="cr-modal-card" role="dialog" aria-modal="true" aria-labelledby="crPreviewTitle">
        <div class="cr-modal-head">
            <h4 id="crPreviewTitle"><i class="fa-solid fa-file-pdf" style="color:var(--pri);"></i> Market Collector Report Preview</h4>
            <div class="cr-modal-actions">
                <button class="cr-modal-btn" type="button" id="crPrintPreview"><i class="fa-solid fa-print"></i> Print</button>
                <button class="cr-modal-btn cr-modal-btn-primary" type="button" id="crDownloadPreview"><i class="fa-solid fa-file-arrow-down"></i> Save PDF</button>
                <button class="cr-modal-btn" type="button" id="crClosePreview"><i class="fa-solid fa-xmark"></i> Close</button>
            </div>
        </div>
        <div class="cr-modal-body">
            <iframe class="cr-preview-frame" id="crPreviewFrame" title="Market Collector Report Preview"></iframe>
        </div>
    </div>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const period = document.getElementById('crPeriod');
    const from = document.getElementById('crFrom');
    const to = document.getElementById('crTo');
    if (!period || !from || !to) return;

    const toggleCustom = () => {
        const isRange = period.value === 'range';
        from.disabled = !isRange;
        to.disabled = !isRange;
        from.style.opacity = isRange ? '1' : '.6';
        to.style.opacity = isRange ? '1' : '.6';
    };

    period.addEventListener('change', toggleCustom);
    toggleCustom();

    const previewBtn = document.getElementById('crOpenPreview');
    const exportCsv = document.getElementById('crExportCsv');
    const previewModal = document.getElementById('crPreviewModal');
    const previewFrame = document.getElementById('crPreviewFrame');
    const closePreview = document.getElementById('crClosePreview');
    const printPreview = document.getElementById('crPrintPreview');
    const downloadPreview = document.getElementById('crDownloadPreview');

    const previewBaseUrl = "{{ route('collector.reports.preview') }}";
    const downloadBaseUrl = "{{ route('collector.reports.pdf') }}";
    const csvBaseUrl = "{{ route('collector.reports.csv') }}";

    const buildReportUrl = (baseUrl) => {
        const url = new URL(baseUrl, window.location.origin);
        const periodValue = period ? period.value : '';
        const fromValue = from ? from.value : '';
        const toValue = to ? to.value : '';

        if (periodValue) {
            url.searchParams.set('period', periodValue);
        }
        if (fromValue) {
            url.searchParams.set('date_from', fromValue);
        }
        if (toValue) {
            url.searchParams.set('date_to', toValue);
        }

        return url.toString();
    };

    const openPreview = () => {
        if (!previewModal || !previewFrame) return;
        previewFrame.src = buildReportUrl(previewBaseUrl);
        previewModal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    const closePreviewModal = () => {
        if (!previewModal) return;
        previewModal.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    window.__openCollectorReportPreview = openPreview;
    if (previewBtn) {
        previewBtn.addEventListener('click', openPreview);
        previewBtn.onclick = openPreview;
    }
    if (closePreview) {
        closePreview.addEventListener('click', closePreviewModal);
    }
    if (previewModal) {
        previewModal.addEventListener('click', (event) => {
            if (event.target === previewModal) closePreviewModal();
        });
    }
    if (printPreview) {
        printPreview.addEventListener('click', () => {
            if (previewFrame && previewFrame.contentWindow) {
                previewFrame.contentWindow.focus();
                previewFrame.contentWindow.print();
            }
        });
    }
    if (downloadPreview) {
        downloadPreview.addEventListener('click', () => {
            window.location.href = buildReportUrl(downloadBaseUrl);
        });
    }
    if (exportCsv) {
        const syncCsvHref = () => {
            exportCsv.href = buildReportUrl(csvBaseUrl);
        };

        syncCsvHref();
        period.addEventListener('change', syncCsvHref);
        from.addEventListener('change', syncCsvHref);
        to.addEventListener('change', syncCsvHref);
    }
});
</script>
@endsection
