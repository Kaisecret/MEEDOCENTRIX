@extends('layouts.app')

@section('content')
<style>
.cr, .cr-modal {--pri:#0f5fa8;--pri2:#0a4880;--bd:#e2e8f0;--soft:#f8fafc;--text:#334155;--mut:#64748b;--head:#0f172a;}
.cr{display:grid;gap:16px;font-family:'Inter',system-ui,sans-serif;color:var(--text);position:relative;z-index:1}
.cr-hero{border-radius:14px;padding:1.25rem 1.4rem;color:#fff;background:linear-gradient(135deg,#0a3d6b 0%,#0f5fa8 55%,#1a7fd4 100%);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;box-shadow:0 4px 14px rgba(10,63,168,.22)}
.cr-hero h2{margin:0 0 4px;font-size:1.45rem;font-weight:800;letter-spacing:-.02em}
.cr-hero p{margin:0;font-size:.9rem;opacity:.9}
.cr-btn{display:inline-flex;align-items:center;gap:7px;border-radius:10px;min-height:40px;padding:0 .95rem;border:1px solid transparent;background:#fff;color:#0f5fa8;font-size:.84rem;font-weight:800;text-decoration:none;cursor:pointer;position:relative;z-index:2}
.cr-btn:hover{background:#f0f7ff}
.cr-card{border:1px solid var(--bd);border-radius:12px;background:#fff;overflow:hidden}
.cr-head{border-bottom:1px solid var(--bd);padding:.92rem 1.15rem;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
.cr-head h3{margin:0;font-size:1rem;font-weight:800;color:var(--head)}
.cr-body{padding:1rem 1.15rem}
.cr-filter{display:grid;grid-template-columns:180px 1fr 1fr auto auto;gap:10px;align-items:end}
.cr-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:var(--mut);font-weight:700;margin-bottom:5px;display:block}
.cr-input{width:100%;min-height:40px;border:1.5px solid #cbd5e1;border-radius:9px;background:#fff;padding:.5rem .72rem;color:#0f172a}
.cr-input:focus{outline:none;border-color:var(--pri);box-shadow:0 0 0 3px rgba(15,95,168,.12)}
.cr-btn-apply{border:1px solid var(--pri);background:var(--pri);color:#fff;border-radius:9px;min-height:40px;padding:0 .95rem;font-size:.84rem;font-weight:800;cursor:pointer}
.cr-btn-apply:hover{background:var(--pri2)}
.cr-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.cr-kpi{border:1px solid var(--bd);border-radius:10px;background:var(--soft);padding:.9rem 1rem;display:grid;gap:4px}
.cr-kpi span{font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;color:var(--mut);font-weight:700}
.cr-kpi strong{font-size:1.4rem;color:var(--head);font-weight:800}
.cr-kpi b{font-size:.84rem;color:var(--mut);font-weight:700}
.cr-tables{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.cr-table-wrap{overflow:auto}
.cr-table{width:100%;border-collapse:collapse;min-width:620px}
.cr-table th{background:#eef5fb;color:#103250;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;font-weight:800;text-align:left;padding:.72rem .8rem;border-bottom:1px solid var(--bd)}
.cr-table td{padding:.72rem .8rem;border-bottom:1px solid #f1f5f9;font-size:.86rem;vertical-align:top}
.cr-tag{display:inline-flex;padding:.15rem .5rem;border-radius:999px;font-size:.68rem;font-weight:800;border:1px solid #e2e8f0;background:#fff;color:#0f172a}
.cr-tag-pending{border-color:#fde68a;background:#fffbeb;color:#b45309}
.cr-tag-await{border-color:#a5f3fc;background:#ecfeff;color:#0e7490}
.cr-tag-accepted{border-color:#a7f3d0;background:#ecfdf5;color:#047857}
.cr-tag-rejected{border-color:#fecaca;background:#fef2f2;color:#b91c1c}
.cr-empty{padding:1rem;color:#64748b;font-size:.86rem}
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

<div data-server-rendered-page="collector_reports" data-page-title="Collector Reports" class="cr">
    <section class="cr-hero">
        <div>
            <h2><i class="fa-solid fa-file-lines" style="margin-right:8px;opacity:.88;"></i>Collector Reports</h2>
            <p>Track assigned collection transactions by day, week, month, or custom range.</p>
        </div>
        <button class="cr-btn" type="button" id="crOpenPreview" onclick="if(window.__openCollectorReportPreview){window.__openCollectorReportPreview();}">
            <i class="fa-solid fa-file-pdf"></i> Preview & Save
        </button>
    </section>

    <section class="cr-card">
        <div class="cr-head">
            <h3>Filter Report Range</h3>
            <div style="font-size:.82rem;color:#64748b;">{{ $rangeLabel }} • {{ $rangeStart->format('M d, Y') }} to {{ $rangeEnd->format('M d, Y') }}</div>
        </div>
        <div class="cr-body">
            <form method="GET" action="{{ route('collector.reports') }}" class="cr-filter" id="crFilterForm">
                <div>
                    <label class="cr-label">Period</label>
                    <select class="cr-input" name="period" id="crPeriod">
                        <option value="day" {{ $period === 'day' ? 'selected' : '' }}>Today</option>
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
        <article class="cr-kpi"><span>Accepted / Pending</span><strong>{{ number_format($acceptedTransactions) }} / {{ number_format($pendingTransactions) }}</strong><b>Accepted plus pending queue</b></article>
        <article class="cr-kpi"><span>Total / Accepted / Pending</span><strong>PHP {{ number_format($totalAmount, 2) }}</strong><b>Accepted: PHP {{ number_format($acceptedAmount, 2) }} • Pending: PHP {{ number_format($pendingAmount, 2) }}</b></article>
    </section>

    <section class="cr-tables">
        <article class="cr-card">
            <div class="cr-head"><h3>Weekly Summary</h3></div>
            <div class="cr-table-wrap">
                <table class="cr-table">
                    <thead><tr><th>Week</th><th>Transactions</th><th>Accepted</th><th>Pending</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse($weeklySummary as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ number_format($row['transactions']) }}</td>
                                <td>{{ number_format($row['accepted']) }}</td>
                                <td>{{ number_format($row['pending']) }}</td>
                                <td>PHP {{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="cr-empty">No weekly data in selected range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="cr-card">
            <div class="cr-head"><h3>Monthly Summary</h3></div>
            <div class="cr-table-wrap">
                <table class="cr-table">
                    <thead><tr><th>Month</th><th>Transactions</th><th>Accepted</th><th>Pending</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse($monthlySummary as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ number_format($row['transactions']) }}</td>
                                <td>{{ number_format($row['accepted']) }}</td>
                                <td>{{ number_format($row['pending']) }}</td>
                                <td>PHP {{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="cr-empty">No monthly data in selected range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="cr-card">
        <div class="cr-head"><h3>Detailed Collection Report</h3><div style="font-size:.8rem;color:#64748b;">Includes time and payment details</div></div>
        <div class="cr-table-wrap">
            <table class="cr-table">
                <thead>
                    <tr>
                        <th>Log ID</th><th>Payment No.</th><th>Date</th><th>Time</th><th>Vessel</th><th>ARR/DEP</th><th>Origin</th><th>Status</th><th>Total</th><th>Payer</th><th>Collector</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $row)
                        <tr>
                            <td><strong>{{ $row['log_id'] }}</strong></td>
                            <td>{{ $row['payment_no'] }}</td>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['time'] }}</td>
                            <td>{{ $row['vessel'] }}</td>
                            <td>{{ $row['arr_dep'] }}</td>
                            <td>{{ $row['origin'] }}</td>
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
                            <td>PHP {{ number_format($row['amount'], 2) }}</td>
                            <td>{{ $row['payer_name'] }}</td>
                            <td>{{ $row['collector'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="cr-empty">No transactions found in selected range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const period = document.getElementById('crPeriod');
    const from = document.getElementById('crFrom');
    const to = document.getElementById('crTo');

    if (period && from && to) {
        const toggleCustom = () => {
            const isRange = period.value === 'range';
            from.disabled = !isRange;
            to.disabled = !isRange;
            from.style.opacity = isRange ? '1' : '.6';
            to.style.opacity = isRange ? '1' : '.6';
        };
        period.addEventListener('change', toggleCustom);
        toggleCustom();
    }

    const previewBtn = document.getElementById('crOpenPreview');
    const previewModal = document.getElementById('crPreviewModal');
    const previewFrame = document.getElementById('crPreviewFrame');
    const closePreview = document.getElementById('crClosePreview');
    const printPreview = document.getElementById('crPrintPreview');
    const downloadPreview = document.getElementById('crDownloadPreview');

    const previewUrl = "{{ route('collector.reports.preview', ['period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}";
    const downloadUrl = "{{ route('collector.reports.pdf', ['period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}";

    const openPreview = () => {
        if (!previewModal || !previewFrame) return;
        previewFrame.src = previewUrl;
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
            window.location.href = downloadUrl;
        });
    }
});
</script>

<div class="cr-modal" id="crPreviewModal" aria-hidden="true">
    <div class="cr-modal-card" role="dialog" aria-modal="true" aria-labelledby="crPreviewTitle">
        <div class="cr-modal-head">
            <h4 id="crPreviewTitle"><i class="fa-solid fa-file-pdf" style="color:var(--pri);"></i> Collector Report Preview</h4>
            <div class="cr-modal-actions">
                <button class="cr-modal-btn" type="button" id="crPrintPreview"><i class="fa-solid fa-print"></i> Print</button>
                <button class="cr-modal-btn cr-modal-btn-primary" type="button" id="crDownloadPreview"><i class="fa-solid fa-file-arrow-down"></i> Save PDF</button>
                <button class="cr-modal-btn" type="button" id="crClosePreview"><i class="fa-solid fa-xmark"></i> Close</button>
            </div>
        </div>
        <div class="cr-modal-body">
            <iframe class="cr-preview-frame" id="crPreviewFrame" title="Collector Report Preview"></iframe>
        </div>
    </div>
</div>
@endsection
