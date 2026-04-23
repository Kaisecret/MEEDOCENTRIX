@extends('layouts.app')

@section('content')
<style>
.ar, .ar-modal {--pri:#155f8f;--pri2:#0f4b73;--bd:#e2e8f0;--soft:#f8fafc;--text:#334155;--mut:#64748b;--head:#0f172a;}
.ar{display:grid;gap:16px;font-family:'Inter',system-ui,sans-serif;color:var(--text);position:relative;z-index:1}
.ar-hero{border-radius:14px;padding:1.25rem 1.4rem;color:#fff;background:linear-gradient(135deg,#0a3d6b 0%,#155f8f 55%,#1c85c8 100%);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;position:relative;z-index:2;pointer-events:auto}
.ar-hero h2{margin:0 0 4px;font-size:1.45rem;font-weight:800;letter-spacing:-.02em}
.ar-hero p{margin:0;font-size:.9rem;opacity:.92}
.ar-btn{display:inline-flex;align-items:center;gap:7px;border-radius:10px;min-height:40px;padding:0 .95rem;border:1px solid transparent;background:#fff;color:#155f8f;font-size:.84rem;font-weight:800;text-decoration:none;cursor:pointer;position:relative;z-index:2}
.ar-btn:hover{background:#f0f7fd}
.ar-card{border:1px solid var(--bd);border-radius:12px;background:#fff;overflow:hidden}
.ar-head{border-bottom:1px solid var(--bd);padding:.92rem 1.15rem;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
.ar-head h3{margin:0;font-size:1rem;font-weight:800;color:var(--head)}
.ar-body{padding:1rem 1.15rem}
.ar-filter{display:grid;grid-template-columns:200px 170px 1fr 1fr auto auto;gap:10px;align-items:end}
.ar-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:var(--mut);font-weight:700;margin-bottom:5px;display:block}
.ar-input{width:100%;min-height:40px;border:1.5px solid #cbd5e1;border-radius:9px;background:#fff;padding:.5rem .72rem;color:#0f172a}
.ar-input:focus{outline:none;border-color:var(--pri);box-shadow:0 0 0 3px rgba(21,95,143,.12)}
.ar-btn-apply{border:1px solid var(--pri);background:var(--pri);color:#fff;border-radius:9px;min-height:40px;padding:0 .95rem;font-size:.84rem;font-weight:800;cursor:pointer}
.ar-btn-apply:hover{background:var(--pri2)}
.ar-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.ar-kpi{border:1px solid var(--bd);border-radius:10px;background:var(--soft);padding:.9rem 1rem;display:grid;gap:4px}
.ar-kpi span{font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;color:var(--mut);font-weight:700}
.ar-kpi strong{font-size:1.4rem;color:var(--head);font-weight:800}
.ar-kpi b{font-size:.84rem;color:var(--mut);font-weight:700}
.ar-tables{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.ar-table-wrap{overflow:auto}
.ar-table{width:100%;border-collapse:collapse;min-width:620px}
.ar-table th{background:#eef5fb;color:#103250;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;font-weight:800;text-align:left;padding:.72rem .8rem;border-bottom:1px solid var(--bd)}
.ar-table td{padding:.72rem .8rem;border-bottom:1px solid #f1f5f9;font-size:.86rem;vertical-align:top}
.ar-empty{padding:1rem;color:#64748b;font-size:.86rem}
.ar-tag{display:inline-flex;padding:.18rem .55rem;border-radius:999px;font-size:.68rem;font-weight:800;border:1px solid transparent}
.ar-tag-good{border-color:#a7f3d0;background:#ecfdf5;color:#047857}
.ar-tag-warn{border-color:#fde68a;background:#fffbeb;color:#b45309}
.ar-tag-bad{border-color:#fecaca;background:#fef2f2;color:#b91c1c}
.ar-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(10,25,45,.65);z-index:2400;backdrop-filter:blur(6px);font-family:'Inter',system-ui,sans-serif;color:var(--text)}
.ar-modal.is-open{display:flex;animation: ar-fade-in .2s ease-out forwards}
.ar-modal-card{width:min(1000px,100%);max-height:calc(100vh - 36px);background:#fff;border-radius:20px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,.3);animation: ar-slide-up .3s ease-out forwards}
.ar-modal-head{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.4rem;background:#fff;border-bottom:1px solid var(--bd);position:sticky;top:0;z-index:2}
.ar-modal-head h4{margin:0;font-size:1.15rem;font-weight:800;color:var(--head);display:flex;align-items:center;gap:10px}
.ar-modal-body{flex:1;overflow:auto;background:var(--soft);padding:1rem}
.ar-modal-actions{display:flex;gap:10px}
.ar-modal-btn{border-radius:10px;border:1.5px solid #cbd5e1;background:#fff;color:var(--text);padding:.5rem 1.1rem;font-size:.85rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s}
.ar-modal-btn:hover{background:#f1f5f9;border-color:#94a3b8;transform:translateY(-1px)}
.ar-modal-btn-primary{background:var(--pri);border-color:var(--pri);color:#fff}
.ar-modal-btn-primary:hover{background:var(--pri2);border-color:var(--pri2);color:#fff}
.ar-preview-frame{width:100%;height:75vh;border:1px solid var(--bd);border-radius:12px;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.03);display:block}
@keyframes ar-fade-in{from{opacity:0}to{opacity:1}}
@keyframes ar-slide-up{from{opacity:0;transform:translateY(20px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}
@media (max-width:1080px){.ar-filter{grid-template-columns:1fr 1fr}.ar-tables{grid-template-columns:1fr}}
@media (max-width:640px){.ar-grid{grid-template-columns:1fr}.ar-filter{grid-template-columns:1fr}}
</style>

<div data-server-rendered-page="atrium_reports" data-page-title="Atrium Reports" class="ar">
    <section class="ar-hero">
        <div>
            <h2><i class="fa-solid fa-chart-pie" style="margin-right:8px;opacity:.88;"></i>{{ $reportTitle }}</h2>
            <p>{{ $reportDescription }}</p>
        </div>
        <button class="ar-btn" type="button" id="arOpenPreview" onclick="if(window.__openAtriumReportPreview){window.__openAtriumReportPreview();}">
            <i class="fa-solid fa-file-pdf"></i> Preview & Save
        </button>
    </section>

    <section class="ar-card">
        <div class="ar-head">
            <h3>Filter Report Range</h3>
            <div style="font-size:.82rem;color:#64748b;">{{ $rangeLabel }} | {{ $rangeStart->format('M d, Y') }} to {{ $rangeEnd->format('M d, Y') }}</div>
        </div>
        <div class="ar-body">
            <form method="GET" action="{{ route('atrium.reports') }}" class="ar-filter" id="arFilterForm">
                <div>
                    <label class="ar-label">Report</label>
                    <select class="ar-input" name="report" id="arReport">
                        <option value="booking" {{ $report === 'booking' ? 'selected' : '' }}>Booking Report</option>
                        <option value="collection" {{ $report === 'collection' ? 'selected' : '' }}>Collection Report</option>
                        <option value="supplies" {{ $report === 'supplies' ? 'selected' : '' }}>Supplies Report</option>
                    </select>
                </div>
                <div>
                    <label class="ar-label">Period</label>
                    <select class="ar-input" name="period" id="arPeriod">
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="range" {{ $period === 'range' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div>
                    <label class="ar-label">From</label>
                    <input class="ar-input" type="date" name="date_from" id="arFrom" value="{{ $dateFrom }}">
                </div>
                <div>
                    <label class="ar-label">To</label>
                    <input class="ar-input" type="date" name="date_to" id="arTo" value="{{ $dateTo }}">
                </div>
                <button type="submit" class="ar-btn-apply"><i class="fa-solid fa-filter"></i> Apply</button>
                <a class="ar-btn" href="{{ route('atrium.reports', ['report' => $report]) }}"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            </form>
        </div>
    </section>

    <section class="ar-grid">
        <article class="ar-kpi">
            <span>Total Records</span>
            <strong>{{ number_format($totalRecords) }}</strong>
            <b>Within selected range</b>
        </article>
        <article class="ar-kpi">
            <span>{{ $primaryLabel }} / {{ $secondaryLabel }}</span>
            <strong>{{ number_format($primaryCount) }} / {{ number_format($secondaryCount) }}</strong>
            <b>Status split</b>
        </article>
        <article class="ar-kpi">
            <span>{{ $metricLabel }}</span>
            <strong>
                @if ($metricIsCurrency)
                    PHP {{ number_format($metricValue, 2) }}
                @else
                    {{ number_format($metricValue) }}
                @endif
            </strong>
            <b>Aggregate metric</b>
        </article>
    </section>

    <section class="ar-tables">
        <article class="ar-card">
            <div class="ar-head"><h3>Weekly Summary</h3></div>
            <div class="ar-table-wrap">
                <table class="ar-table">
                    <thead><tr><th>Week</th><th>Records</th><th>{{ $primaryLabel }}</th><th>{{ $secondaryLabel }}</th><th>{{ $summaryTotalLabel }}</th></tr></thead>
                    <tbody>
                        @forelse($weeklySummary as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ number_format($row['records']) }}</td>
                                <td>{{ number_format($row['primary']) }}</td>
                                <td>{{ number_format($row['secondary']) }}</td>
                                <td>
                                    @if ($metricIsCurrency)
                                        PHP {{ number_format($row['total'], 2) }}
                                    @else
                                        {{ number_format($row['total']) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="ar-empty">No weekly data in selected range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="ar-card">
            <div class="ar-head"><h3>Monthly Summary</h3></div>
            <div class="ar-table-wrap">
                <table class="ar-table">
                    <thead><tr><th>Month</th><th>Records</th><th>{{ $primaryLabel }}</th><th>{{ $secondaryLabel }}</th><th>{{ $summaryTotalLabel }}</th></tr></thead>
                    <tbody>
                        @forelse($monthlySummary as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ number_format($row['records']) }}</td>
                                <td>{{ number_format($row['primary']) }}</td>
                                <td>{{ number_format($row['secondary']) }}</td>
                                <td>
                                    @if ($metricIsCurrency)
                                        PHP {{ number_format($row['total'], 2) }}
                                    @else
                                        {{ number_format($row['total']) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="ar-empty">No monthly data in selected range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="ar-card">
        <div class="ar-head"><h3>Detailed {{ ucfirst($report) }} Report</h3><div style="font-size:.8rem;color:#64748b;">Complete records in selected range</div></div>
        <div class="ar-table-wrap">
            <table class="ar-table">
                @if ($report === 'booking')
                    <thead>
                        <tr><th>Code</th><th>Date</th><th>Contact</th><th>Hall</th><th>Hours</th><th>Due</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td><strong>{{ $row['code'] }}</strong></td>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['contact'] }}</td>
                                <td>{{ $row['hall'] }}</td>
                                <td>{{ number_format($row['hours'], 2) }}</td>
                                <td>PHP {{ number_format($row['amount'], 2) }}</td>
                                <td><span class="ar-tag {{ $row['status_class'] }}">{{ $row['status_label'] }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="ar-empty">No booking records found.</td></tr>
                        @endforelse
                    </tbody>
                @elseif ($report === 'collection')
                    <thead>
                        <tr><th>OR Number</th><th>Date</th><th>Event</th><th>Amount</th><th>Status</th><th>Recorded By</th></tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td><strong>{{ $row['or_number'] }}</strong></td>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['event'] }}</td>
                                <td>PHP {{ number_format($row['amount'], 2) }}</td>
                                <td><span class="ar-tag {{ $row['status_class'] }}">{{ $row['status_label'] }}</span></td>
                                <td>{{ $row['recorded_by'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="ar-empty">No collection records found.</td></tr>
                        @endforelse
                    </tbody>
                @else
                    <thead>
                        <tr><th>Date</th><th>Event</th><th>Time Needed</th><th>Supplies</th><th>Status</th><th>Requested By</th></tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['event'] }}</td>
                                <td>{{ $row['time_needed'] }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($row['supplies'], 80) }}</td>
                                <td><span class="ar-tag {{ $row['status_class'] }}">{{ $row['status_label'] }}</span></td>
                                <td>{{ $row['requested_by'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="ar-empty">No supply request records found.</td></tr>
                        @endforelse
                    </tbody>
                @endif
            </table>
        </div>
    </section>
</div>

<div class="ar-modal" id="arPreviewModal" aria-hidden="true">
    <div class="ar-modal-card" role="dialog" aria-modal="true" aria-labelledby="arPreviewTitle">
        <div class="ar-modal-head">
            <h4 id="arPreviewTitle"><i class="fa-solid fa-file-pdf" style="color:var(--pri);"></i> Atrium Report Preview</h4>
            <div class="ar-modal-actions">
                <button class="ar-modal-btn" type="button" id="arPrintPreview"><i class="fa-solid fa-print"></i> Print</button>
                <button class="ar-modal-btn ar-modal-btn-primary" type="button" id="arDownloadPreview"><i class="fa-solid fa-file-arrow-down"></i> Save PDF</button>
                <button class="ar-modal-btn" type="button" id="arClosePreview"><i class="fa-solid fa-xmark"></i> Close</button>
            </div>
        </div>
        <div class="ar-modal-body">
            <iframe class="ar-preview-frame" id="arPreviewFrame" title="Atrium Report Preview"></iframe>
        </div>
    </div>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const period = document.getElementById('arPeriod');
    const from = document.getElementById('arFrom');
    const to = document.getElementById('arTo');

    const toggleCustom = () => {
        if (!period || !from || !to) return;
        const isRange = period.value === 'range';
        from.disabled = !isRange;
        to.disabled = !isRange;
        from.style.opacity = isRange ? '1' : '.6';
        to.style.opacity = isRange ? '1' : '.6';
    };

    if (period) {
        period.addEventListener('change', toggleCustom);
        toggleCustom();
    }

    const previewBtn = document.getElementById('arOpenPreview');
    const previewModal = document.getElementById('arPreviewModal');
    const previewFrame = document.getElementById('arPreviewFrame');
    const closePreview = document.getElementById('arClosePreview');
    const printPreview = document.getElementById('arPrintPreview');
    const downloadPreview = document.getElementById('arDownloadPreview');

    const previewUrl = "{{ route('atrium.reports.preview', ['report' => $report, 'period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}";
    const downloadUrl = "{{ route('atrium.reports.pdf', ['report' => $report, 'period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}";

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

    window.__openAtriumReportPreview = openPreview;

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
@endsection
