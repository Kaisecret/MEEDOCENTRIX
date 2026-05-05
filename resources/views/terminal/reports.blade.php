@extends('layouts.app')

@section('content')
<style>
.fr, .fr-modal {--pri:var(--sidebar-bg);--pri2:#104f77;--bd:#e2e8f0;--soft:#f8fafc;--text:#334155;--mut:#64748b;--head:#0f172a;}
.fr{display:grid;gap:14px;font-family:'Inter',system-ui,sans-serif;color:var(--text);position:relative;z-index:1;margin-top:-14px}
.fr-hero{border-radius:14px;padding:1.25rem 1.4rem;color:#fff;background:var(--pri);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;position:relative;z-index:2;pointer-events:auto;box-shadow:0 6px 18px rgba(21,95,143,.18)}
.fr-hero h2{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;display:flex;align-items:center}
.fr-hero p{margin:0;font-size:.9rem;opacity:.9}
.fr-hero-actions{display:flex;gap:8px;flex-wrap:wrap}
.fr-btn{display:inline-flex;align-items:center;gap:7px;border-radius:10px;min-height:40px;padding:0 1rem;border:1px solid transparent;background:#fff;color:var(--pri);font-size:.84rem;font-weight:800;text-decoration:none;cursor:pointer;position:relative;z-index:2;transition:all .15s ease}
.fr-btn:hover{background:#f0f7fd;transform:translateY(-1px);box-shadow:0 4px 10px rgba(0,0,0,.08)}
.fr-card{border:1px solid var(--bd);border-radius:12px;background:#fff;overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,.04)}
.fr-head{border-bottom:1px solid var(--bd);padding:.85rem 1.15rem;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;background:linear-gradient(180deg,#fbfdff 0%,#ffffff 100%)}
.fr-head h3{margin:0;font-size:1rem;font-weight:800;color:var(--head);display:flex;align-items:center;gap:8px}
.fr-head h3::before{content:"";display:inline-block;width:4px;height:18px;border-radius:3px;background:var(--pri)}
.fr-head h3 i{font-size:.9rem}
.fr-head-meta{font-size:.78rem;color:#64748b;font-weight:600}
.fr-body{padding:1rem 1.15rem}
.fr-filter{display:grid;grid-template-columns:180px 1fr 1fr auto auto;gap:10px;align-items:end}
.fr-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:var(--mut);font-weight:700;margin-bottom:5px;display:block}
.fr-input{width:100%;min-height:40px;border:1.5px solid #cbd5e1;border-radius:9px;background:#fff;padding:.5rem .72rem;color:#0f172a;transition:border-color .15s ease,box-shadow .15s ease}
.fr-input:focus{outline:none;border-color:var(--pri);box-shadow:0 0 0 3px rgba(21,95,143,.12)}
.fr-btn-apply{border:1px solid var(--pri);background:var(--pri);color:#fff;border-radius:9px;min-height:40px;padding:0 1rem;font-size:.84rem;font-weight:800;cursor:pointer;transition:background .15s ease}
.fr-btn-apply:hover{background:var(--pri2)}
.fr-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.fr-kpi{position:relative;border:1px solid var(--bd);border-radius:12px;background:#fff;padding:1rem 1.15rem;display:grid;gap:6px;overflow:hidden;transition:transform .15s ease,box-shadow .15s ease}
.fr-kpi::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--pri)}
.fr-kpi:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(15,23,42,.06)}
.fr-kpi span{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--mut);font-weight:700}
.fr-kpi strong{font-size:1.55rem;color:var(--head);font-weight:800;letter-spacing:-.01em;line-height:1.1}
.fr-kpi b{font-size:.78rem;color:var(--mut);font-weight:600}
.fr-kpi.fr-kpi-paid::before{background:#047857}
.fr-kpi.fr-kpi-amount::before{background:#b45309}
.fr-tables{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.fr-tables > .fr-card:only-child{grid-column:1 / -1}
.fr-table-wrap{overflow:auto}
.fr-table{width:100%;border-collapse:collapse;min-width:620px}
.fr-table th{background:#eef5fb;color:#103250;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;font-weight:800;text-align:left;padding:.78rem .9rem;border-bottom:2px solid #d6e6f3;white-space:nowrap}
.fr-table td{padding:.72rem .9rem;border-bottom:1px solid #f1f5f9;font-size:.86rem;vertical-align:middle}
.fr-table tbody tr{transition:background .12s ease}
.fr-table tbody tr:nth-child(even) td{background:#fbfdff}
.fr-table tbody tr:hover td{background:#f0f7fd}
.fr-table tbody tr:last-child td{border-bottom:none}
.fr-table .fr-num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
.fr-paid{display:inline-flex;padding:.18rem .55rem;border-radius:999px;font-size:.68rem;font-weight:800;border:1px solid #a7f3d0;background:#ecfdf5;color:#047857;letter-spacing:.02em}
.fr-unpaid{display:inline-flex;padding:.18rem .55rem;border-radius:999px;font-size:.68rem;font-weight:800;border:1px solid #fecaca;background:#fef2f2;color:#b91c1c;letter-spacing:.02em}
.fr-empty{padding:1.5rem 1rem;color:#94a3b8;font-size:.86rem;text-align:center;font-style:italic}
.fr-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(10,25,45,.65);z-index:2400;backdrop-filter:blur(6px);font-family:'Inter',system-ui,sans-serif;color:var(--text)}
.fr-modal.is-open{display:flex;animation: fr-fade-in .2s ease-out forwards}
.fr-modal-card{width:min(1000px,100%);max-height:calc(100vh - 36px);background:#fff;border-radius:20px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,.3);animation: fr-slide-up .3s ease-out forwards}
.fr-modal-head{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.4rem;background:#fff;border-bottom:1px solid var(--bd);position:sticky;top:0;z-index:2}
.fr-modal-head h4{margin:0;font-size:1.15rem;font-weight:800;color:var(--head);display:flex;align-items:center;gap:10px}
.fr-modal-body{flex:1;overflow:auto;background:var(--soft);padding:1rem}
.fr-modal-actions{display:flex;gap:10px}
.fr-modal-btn{border-radius:10px;border:1.5px solid #cbd5e1;background:#fff;color:var(--text);padding:.5rem 1.1rem;font-size:.85rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s}
.fr-modal-btn:hover{background:#f1f5f9;border-color:#94a3b8;transform:translateY(-1px)}
.fr-modal-btn-primary{background:var(--pri);border-color:var(--pri);color:#fff}
.fr-modal-btn-primary:hover{background:var(--pri2);border-color:var(--pri2);color:#fff}
.fr-preview-frame{width:100%;height:75vh;border:1px solid var(--bd);border-radius:12px;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.03);display:block}
@keyframes fr-fade-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes fr-slide-up { from { opacity: 0; transform: translateY(20px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
@media (max-width:1080px){.fr-filter{grid-template-columns:1fr 1fr}.fr-tables{grid-template-columns:1fr}}
@media (max-width:640px){.fr-grid{grid-template-columns:1fr}.fr-filter{grid-template-columns:1fr}}
</style>

<div data-server-rendered-page="terminal_reports" data-page-title="Terminal Reports" class="fr">
    <section class="fr-hero">
        <div>
            <h2><i class="fa-solid fa-file-lines" style="margin-right:8px;opacity:.88;"></i>Terminal Reports</h2>
        </div>
        <div class="fr-hero-actions">
            <a class="fr-btn" id="frExportCsv" href="{{ route('terminal.reports.csv', ['period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
            <button class="fr-btn" type="button" id="frOpenPreview" onclick="if(window.__openReportPreview){window.__openReportPreview();}">
                <i class="fa-solid fa-file-pdf"></i> Preview & Save
            </button>
        </div>
    </section>

    <section class="fr-card">
        <div class="fr-head"><h3>Filter Report Range</h3><div style="font-size:.82rem;color:#64748b;">{{ $rangeLabel }} � {{ $rangeStart->format('M d, Y') }} to {{ $rangeEnd->format('M d, Y') }}</div></div>
        <div class="fr-body">
            <form method="GET" action="{{ route('terminal.reports') }}" class="fr-filter" id="frFilterForm">
                <div>
                    <label class="fr-label">Period</label>
                    <select class="fr-input" name="period" id="frPeriod">
                        <option value="day" {{ $period === 'day' ? 'selected' : '' }}>This Day</option>
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="range" {{ $period === 'range' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div>
                    <label class="fr-label">From</label>
                    <input class="fr-input" type="date" name="date_from" id="frFrom" value="{{ $dateFrom }}">
                </div>
                <div>
                    <label class="fr-label">To</label>
                    <input class="fr-input" type="date" name="date_to" id="frTo" value="{{ $dateTo }}">
                </div>
                <button type="submit" class="fr-btn-apply"><i class="fa-solid fa-filter"></i> Apply</button>
                <a class="fr-btn" href="{{ route('terminal.reports') }}"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            </form>
        </div>
    </section>

    <section class="fr-grid">
        <article class="fr-kpi"><span>Total Transactions</span><strong>{{ number_format($totalTransactions) }}</strong><b>Within selected range</b></article>
        <article class="fr-kpi fr-kpi-paid"><span>Paid / Not Paid</span><strong>{{ number_format($paidTransactions) }} / {{ number_format($notPaidTransactions) }}</strong><b>Collection status count</b></article>
        <article class="fr-kpi fr-kpi-amount"><span>Total / Paid / Not Paid Amount</span><strong>PHP {{ number_format($totalAmount, 2) }}</strong><b>Paid: PHP {{ number_format($paidAmount, 2) }} � Unpaid: PHP {{ number_format($notPaidAmount, 2) }}</b></article>
    </section>

    @if (in_array($period, ['week', 'month'], true))
        <section class="fr-tables">
            @if ($period === 'week')
                <article class="fr-card">
                    <div class="fr-head"><h3>Weekly Summary</h3></div>
                    <div class="fr-table-wrap">
                        <table class="fr-table">
                            <thead><tr><th>Week</th><th class="fr-num">Transactions</th><th class="fr-num">Paid</th><th class="fr-num">Not Paid</th><th class="fr-num">Total</th></tr></thead>
                            <tbody>
                                @forelse($weeklySummary as $row)
                                    <tr>
                                        <td><strong>{{ $row['label'] }}</strong></td>
                                        <td class="fr-num">{{ number_format($row['transactions']) }}</td>
                                        <td class="fr-num">{{ number_format($row['paid']) }}</td>
                                        <td class="fr-num">{{ number_format($row['not_paid']) }}</td>
                                        <td class="fr-num">PHP {{ number_format($row['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="fr-empty">No weekly data in selected range.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            @endif

            @if ($period === 'month')
                <article class="fr-card">
                    <div class="fr-head"><h3>Monthly Summary</h3></div>
                    <div class="fr-table-wrap">
                        <table class="fr-table">
                            <thead><tr><th>Month</th><th class="fr-num">Transactions</th><th class="fr-num">Paid</th><th class="fr-num">Not Paid</th><th class="fr-num">Total</th></tr></thead>
                            <tbody>
                                @forelse($monthlySummary as $row)
                                    <tr>
                                        <td><strong>{{ $row['label'] }}</strong></td>
                                        <td class="fr-num">{{ number_format($row['transactions']) }}</td>
                                        <td class="fr-num">{{ number_format($row['paid']) }}</td>
                                        <td class="fr-num">{{ number_format($row['not_paid']) }}</td>
                                        <td class="fr-num">PHP {{ number_format($row['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="fr-empty">No monthly data in selected range.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            @endif
        </section>
    @endif

    <section class="fr-card">
        <div class="fr-head"><h3>Detailed Transactions Report</h3><div class="fr-head-meta">Ticket, route, status and payment details</div></div>
        <div class="fr-table-wrap">
            <table class="fr-table">
                <thead>
                    <tr>
                        <th>Ticket #</th><th>Vehicle</th><th>Route / Operator</th><th>Date</th><th>Time</th><th>Status</th><th class="fr-num">Amount</th><th>Remarks</th><th>Saved By</th><th>Paid By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $row)
                        <tr>
                            <td><strong>{{ $row['ticket_number'] }}</strong></td>
                            <td>{{ $row['vehicle'] }}</td>
                            <td>{{ $row['route'] }}</td>
                            <td>{{ $row['recorded_date'] }}</td>
                            <td>{{ $row['recorded_time'] }}</td>
                            <td>@if($row['is_paid'])<span class="fr-paid">Paid</span>@else<span class="fr-unpaid">Not Paid</span>@endif</td>
                            <td class="fr-num">PHP {{ number_format($row['total'], 2) }}</td>
                            <td>{{ $row['remarks'] }}</td>
                            <td>{{ $row['recorded_by'] }}</td>
                            <td>{{ $row['paid_by'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="fr-empty">No transactions found in selected range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="fr-modal" id="frPreviewModal" aria-hidden="true">
    <div class="fr-modal-card" role="dialog" aria-modal="true" aria-labelledby="frPreviewTitle">
        <div class="fr-modal-head">
            <h4 id="frPreviewTitle"><i class="fa-solid fa-file-pdf" style="color:var(--pri);"></i> Terminal Report Preview</h4>
            <div class="fr-modal-actions">
                <button class="fr-modal-btn" type="button" id="frPrintPreview"><i class="fa-solid fa-print"></i> Print</button>
                <button class="fr-modal-btn fr-modal-btn-primary" type="button" id="frDownloadPreview"><i class="fa-solid fa-file-arrow-down"></i> Save PDF</button>
                <button class="fr-modal-btn" type="button" id="frClosePreview"><i class="fa-solid fa-xmark"></i> Close</button>
            </div>
        </div>
        <div class="fr-modal-body">
            <iframe class="fr-preview-frame" id="frPreviewFrame" title="Terminal Report Preview"></iframe>
        </div>
    </div>
</div>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const period = document.getElementById('frPeriod');
    const from = document.getElementById('frFrom');
    const to = document.getElementById('frTo');
    if (!period || !from || !to) return;

    const toggleCustom = () => {
        const isRange = period.value === 'range';
        from.disabled = !isRange;
        to.disabled = !isRange;
        if (!isRange) {
            from.style.opacity = '.6';
            to.style.opacity = '.6';
        } else {
            from.style.opacity = '1';
            to.style.opacity = '1';
        }
    };

    period.addEventListener('change', toggleCustom);
    toggleCustom();

    const previewBtn = document.getElementById('frOpenPreview');
    const exportCsv = document.getElementById('frExportCsv');
    const previewModal = document.getElementById('frPreviewModal');
    const previewFrame = document.getElementById('frPreviewFrame');
    const closePreview = document.getElementById('frClosePreview');
    const printPreview = document.getElementById('frPrintPreview');
    const downloadPreview = document.getElementById('frDownloadPreview');

    const previewBaseUrl = "{{ route('terminal.reports.preview') }}";
    const downloadBaseUrl = "{{ route('terminal.reports.pdf') }}";
    const csvBaseUrl = "{{ route('terminal.reports.csv') }}";

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

    window.__openReportPreview = openPreview;
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
