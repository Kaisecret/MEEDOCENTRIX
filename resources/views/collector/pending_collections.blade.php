@extends('layouts.app')

@section('content')
@php
    /** @var \App\Models\CollectorDepartmentAssignment|null $assignment */
    /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\CollectionDispatchItem> $items */
@endphp

<style>
:root { --cp-primary:#0f5fa8; --cp-green:#059669; --cp-red:#dc2626; --cp-border:#e2e8f0; --cp-soft:#f8fafc; --cp-head:#0f172a; --cp-muted:#64748b; }
.cp-page{max-width:1200px;margin:0 auto;display:grid;gap:16px;padding-bottom:2rem}
.cp-alert{border-radius:10px;padding:.8rem 1rem;display:flex;gap:8px;align-items:center;font-size:.9rem;font-weight:600}
.cp-alert-success{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}.cp-alert-error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.cp-hero{border-radius:14px;padding:1.2rem 1.4rem;color:#fff;background:#155e8f;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.cp-hero h1{margin:0 0 4px;font-size:1.35rem;font-weight:800;display:flex;align-items:center;gap:8px}.cp-hero p{margin:0;font-size:.9rem;opacity:.9}
.cp-search{position:relative;width:360px;max-width:100%}.cp-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.65)}
.cp-search input{width:100%;min-height:40px;border-radius:999px;border:1.5px solid rgba(255,255,255,.35);background:rgba(255,255,255,.15);color:#fff;padding:.5rem .9rem .5rem 2.2rem}
.cp-search input::placeholder{color:rgba(255,255,255,.72)}
.cp-card{border:1px solid var(--cp-border);border-radius:14px;background:#fff;overflow:hidden}
.cp-table-wrap{overflow-x:auto}.cp-table{width:100%;border-collapse:collapse;min-width:980px}
.cp-mobile-list{display:none}
.cp-table th{background:#eef5fb;color:#103250;text-transform:uppercase;letter-spacing:.04em;font-size:.73rem;font-weight:800;text-align:left;padding:.8rem .9rem;border-bottom:1px solid var(--cp-border)}
.cp-table td{padding:.85rem .9rem;font-size:.9rem;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.cp-code{font-family:"Courier New",monospace;font-size:.83rem;color:var(--cp-muted)}.cp-strong{color:var(--cp-head);font-weight:700}
.cp-pill{border-radius:999px;padding:.2rem .65rem;font-size:.7rem;font-weight:800;border:1px solid transparent}
.cp-pill-blue{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}.cp-pill-red{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
.cp-actions{display:inline-flex;gap:6px}.cp-btn{border-radius:8px;min-height:34px;padding:.45rem .7rem;display:inline-flex;align-items:center;gap:6px;font-size:.8rem;font-weight:700;cursor:pointer;border:1px solid transparent}
.cp-btn-view{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}.cp-btn-collect{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
.cp-empty{display:grid;place-items:center;gap:8px;padding:3rem 1rem;color:#64748b;text-align:center}
.cp-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:1rem;background:rgba(10,29,49,.55);z-index:2300}
.cp-modal.is-open{display:flex}.cp-modal-card{width:min(760px,100%);max-height:calc(100vh - 2rem);overflow:auto;border-radius:16px;border:1px solid var(--cp-border);background:#fff}
.cp-modal-head{display:flex;justify-content:space-between;align-items:center;padding:.95rem 1.2rem;border-bottom:1px solid var(--cp-border);background:#eef5fb}
.cp-modal-head h3{margin:0;color:var(--cp-head);font-size:1rem;font-weight:800;display:inline-flex;align-items:center;gap:8px}
.cp-modal-close{width:34px;height:34px;border-radius:8px;border:1px solid var(--cp-border);background:#fff;font-size:1.2rem;cursor:pointer}
.cp-modal-body{padding:1rem 1.2rem;display:grid;gap:12px}
.cp-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.cp-field{border:1px solid var(--cp-border);background:var(--cp-soft);border-radius:10px;padding:.62rem .8rem}
.cp-field span{display:block;font-size:.67rem;text-transform:uppercase;letter-spacing:.04em;color:var(--cp-muted);font-weight:700}.cp-field strong{color:var(--cp-head);font-size:.92rem;font-weight:800}
.cp-items{border:1px solid var(--cp-border);border-radius:10px;overflow:hidden}.cp-items-head{display:flex;justify-content:space-between;background:#f1f7fd;border-bottom:1px solid var(--cp-border);padding:.58rem .82rem}
.cp-items-head h4{margin:0;font-size:.73rem;text-transform:uppercase;letter-spacing:.04em}
.cp-items-list{padding:.58rem .82rem;display:grid;gap:2px}.cp-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:6px;padding:.25rem 0}.cp-item+.cp-item{border-top:1px solid #f1f5f9}
.cp-main{display:inline-flex;align-items:baseline;gap:7px}.cp-qty{font-size:.7rem;font-weight:700;color:#64748b;background:#e2e8f0;border-radius:4px;padding:.1rem .35rem}
.cp-total{font-weight:800;color:var(--cp-primary)}.cp-note{border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;padding:.65rem .8rem;display:none;gap:8px}
.cp-note.is-visible{display:flex}
.cp-label{font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;display:inline-flex;align-items:center;gap:5px;margin-bottom:5px}
.cp-label .req{color:var(--cp-red)}.cp-input,.cp-textarea{width:100%;min-height:40px;border:1.5px solid var(--cp-border);border-radius:9px;background:var(--cp-soft);padding:.55rem .8rem}
.cp-textarea{min-height:78px;resize:vertical}.cp-proof{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.cp-upload{position:relative;border:2px dashed var(--cp-border);border-radius:10px;background:var(--cp-soft);min-height:90px;padding:.7rem;display:grid;place-items:center;text-align:center;gap:4px}
.cp-upload.has-file{border-style:solid;border-color:var(--cp-green);background:#f0fdf4}.cp-upload input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
.cp-footer{display:flex;justify-content:flex-end;gap:8px;padding:.9rem 1.2rem;border-top:1px solid var(--cp-border);background:var(--cp-soft)}
.cp-foot-btn{border-radius:9px;min-height:38px;padding:.52rem .95rem;border:1px solid var(--cp-border);background:#fff;font-size:.86rem;font-weight:700;cursor:pointer}
.cp-foot-btn-primary{border-color:transparent;background:var(--cp-primary);color:#fff}
.cp-camera-modal{width:min(700px,100%)}
.cp-camera-wrap{display:grid;gap:10px}
.cp-camera-frame{border:1px solid var(--cp-border);border-radius:10px;overflow:hidden;background:#0f172a;min-height:260px;display:grid;place-items:center}
.cp-camera-video{width:100%;max-height:62vh;display:block}
.cp-camera-note{margin:0;color:#64748b;font-size:.82rem}
body.cp-lock-scroll{overflow:hidden}
@media (max-width:768px){.cp-grid,.cp-proof{grid-template-columns:1fr}.cp-search{width:100%}}
@media (max-width:640px){
    .cp-table-wrap{display:none}
    .cp-mobile-list{display:grid;gap:12px;padding:12px}
    .cp-mobile-card{border:1px solid var(--cp-border);border-radius:12px;background:#fff;padding:12px;box-shadow:0 1px 4px rgba(0,0,0,.04);display:grid;gap:8px}
    .cp-mobile-head{display:flex;justify-content:space-between;gap:8px;align-items:flex-start}
    .cp-mobile-title{font-weight:800;color:var(--cp-head)}
    .cp-mobile-meta{font-size:.8rem;color:var(--cp-muted)}
    .cp-mobile-row{display:flex;justify-content:space-between;gap:8px;font-size:.86rem}
    .cp-mobile-actions{display:flex;gap:8px;flex-wrap:wrap}
    .cp-mobile-actions .cp-btn{flex:1;justify-content:center}
}
</style>

<div data-server-rendered-page="pending_collections" data-page-title="Pending Collections" class="cp-page">
    @if(session('status'))<div class="cp-alert cp-alert-success"><i class="fa-solid fa-circle-check"></i>{{ session('status') }}</div>@endif
    @if(session('error'))<div class="cp-alert cp-alert-error"><i class="fa-solid fa-circle-exclamation"></i>{{ session('error') }}</div>@endif
    @if($errors->any())<div class="cp-alert cp-alert-error"><i class="fa-solid fa-triangle-exclamation"></i>{{ $errors->first() }}</div>@endif

    <section class="cp-hero">
        <div>
            <h1><i class="fa-solid fa-clock-rotate-left"></i>Pending Collections</h1>
        </div>
        <form method="GET" action="{{ route('collector.pending_collections') }}" class="cp-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ $search }}" placeholder="Search log no, payment no, vessel..." autocomplete="off">
        </form>
    </section>

    <section class="cp-card">
        @if($items->count() > 0)
            <div class="cp-table-wrap">
                <table class="cp-table">
                    <thead>
                        <tr>
                            <th>Log ID</th><th>Payment No.</th><th>Vessel</th><th>Route</th><th>Date/Time</th><th>Total</th><th>Status</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php
                                $log = $item->fishportLog;
                                $status = (string) $item->status;
                                $isRejected = $status === 'rejected';
                                $paymentItems = ($log?->payments ?? collect())->map(function ($line): array {
                                    return ['name' => (string) ($line->paymentType?->name ?? 'Charge'), 'qty' => (float) ($line->quantity ?? 0), 'total' => (float) ($line->total ?? 0)];
                                })->values();
                                $logDate = optional($log?->log_date)->format('m/d/Y');
                                $logTime = substr((string) ($log?->log_time ?? ''), 0, 5);
                                $route = ($log?->origin?->name ?? '-') . ' (' . ($log?->arr_dep ?? '-') . ')';
                                $jsonItems = $paymentItems->toJson();
                            @endphp
                            <tr>
                                <td class="cp-strong">{{ $log?->log_number ?? '-' }}</td>
                                <td class="cp-code">{{ $item->paymentRecord?->payment_number ?? 'No payment no.' }}</td>
                                <td class="cp-strong">{{ $log?->vessel?->name ?? '-' }}</td>
                                <td>{{ $route }}</td>
                                <td>{{ ($logDate ?: '-') . ' ' . ($logTime ?: '-') }}</td>
                                <td class="cp-strong">PHP {{ number_format((float) $item->amount_snapshot, 2) }}</td>
                                <td>@if($isRejected)<span class="cp-pill cp-pill-red">Rejected</span>@else<span class="cp-pill cp-pill-blue">For Collection</span>@endif</td>
                                <td>
                                    <div class="cp-actions">
                                        <button type="button" class="cp-btn cp-btn-view js-open-view"
                                            data-log="{{ $log?->log_number ?? '-' }}"
                                            data-pay="{{ $item->paymentRecord?->payment_number ?? 'No payment no.' }}"
                                            data-vessel="{{ $log?->vessel?->name ?? '-' }}"
                                            data-route="{{ $route }}"
                                            data-dt="{{ ($logDate ?: '-') . ' ' . ($logTime ?: '-') }}"
                                            data-total="{{ number_format((float) $item->amount_snapshot, 2, '.', '') }}"
                                            data-status="{{ $status }}"
                                            data-note="{{ $item->review_note ?? '' }}"
                                            data-items="{{ $jsonItems }}"
                                        ><i class="fa-regular fa-eye"></i>View</button>
                                        <button type="button" class="cp-btn cp-btn-collect js-open-collect"
                                            data-action="{{ route('collector.pending_collections.collect', $item) }}"
                                            data-log="{{ $log?->log_number ?? '-' }}"
                                            data-pay="{{ $item->paymentRecord?->payment_number ?? 'No payment no.' }}"
                                            data-vessel="{{ $log?->vessel?->name ?? '-' }}"
                                            data-route="{{ $route }}"
                                            data-dt="{{ ($logDate ?: '-') . ' ' . ($logTime ?: '-') }}"
                                            data-total="{{ number_format((float) $item->amount_snapshot, 2, '.', '') }}"
                                            data-status="{{ $status }}"
                                            data-note="{{ $item->review_note ?? '' }}"
                                            data-items="{{ $jsonItems }}"
                                            data-allow-proof="{{ $isRejected && filled($item->proof_image_path) ? '1' : '0' }}"
                                        ><i class="fa-solid fa-hand-holding-dollar"></i>Collect</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="cp-mobile-list">
                @foreach ($items as $item)
                    @php
                        $log = $item->fishportLog;
                        $status = (string) $item->status;
                        $isRejected = $status === 'rejected';
                        $logDate = optional($log?->log_date)->format('m/d/Y');
                        $logTime = substr((string) ($log?->log_time ?? ''), 0, 5);
                        $route = ($log?->origin?->name ?? '-') . ' (' . ($log?->arr_dep ?? '-') . ')';
                        $paymentItems = ($log?->payments ?? collect())->map(function ($line): array {
                            return ['name' => (string) ($line->paymentType?->name ?? 'Charge'), 'qty' => (float) ($line->quantity ?? 0), 'total' => (float) ($line->total ?? 0)];
                        })->values();
                        $jsonItems = $paymentItems->toJson();
                    @endphp
                    <div class="cp-mobile-card">
                        <div class="cp-mobile-head">
                            <div>
                                <div class="cp-mobile-title">{{ $log?->log_number ?? '-' }}</div>
                                <div class="cp-mobile-meta">{{ $item->paymentRecord?->payment_number ?? 'No payment no.' }}</div>
                            </div>
                            <div>
                                @if($isRejected)
                                    <span class="cp-pill cp-pill-red">Rejected</span>
                                @else
                                    <span class="cp-pill cp-pill-blue">For Collection</span>
                                @endif
                            </div>
                        </div>
                        <div class="cp-mobile-row"><span class="cp-mobile-meta">Vessel</span><strong>{{ $log?->vessel?->name ?? '-' }}</strong></div>
                        <div class="cp-mobile-row"><span class="cp-mobile-meta">Route</span><strong>{{ $route }}</strong></div>
                        <div class="cp-mobile-row"><span class="cp-mobile-meta">Date/Time</span><strong>{{ ($logDate ?: '-') . ' ' . ($logTime ?: '-') }}</strong></div>
                        <div class="cp-mobile-row"><span class="cp-mobile-meta">Total</span><strong>PHP {{ number_format((float) $item->amount_snapshot, 2) }}</strong></div>
                        <div class="cp-mobile-actions">
                            <button type="button" class="cp-btn cp-btn-view js-open-view"
                                data-log="{{ $log?->log_number ?? '-' }}"
                                data-pay="{{ $item->paymentRecord?->payment_number ?? 'No payment no.' }}"
                                data-vessel="{{ $log?->vessel?->name ?? '-' }}"
                                data-route="{{ $route }}"
                                data-dt="{{ ($logDate ?: '-') . ' ' . ($logTime ?: '-') }}"
                                data-total="{{ number_format((float) $item->amount_snapshot, 2, '.', '') }}"
                                data-status="{{ $status }}"
                                data-note="{{ $item->review_note ?? '' }}"
                                data-items="{{ $jsonItems }}"
                            ><i class="fa-regular fa-eye"></i>View</button>
                            <button type="button" class="cp-btn cp-btn-collect js-open-collect"
                                data-action="{{ route('collector.pending_collections.collect', $item) }}"
                                data-log="{{ $log?->log_number ?? '-' }}"
                                data-pay="{{ $item->paymentRecord?->payment_number ?? 'No payment no.' }}"
                                data-vessel="{{ $log?->vessel?->name ?? '-' }}"
                                data-route="{{ $route }}"
                                data-dt="{{ ($logDate ?: '-') . ' ' . ($logTime ?: '-') }}"
                                data-total="{{ number_format((float) $item->amount_snapshot, 2, '.', '') }}"
                                data-status="{{ $status }}"
                                data-note="{{ $item->review_note ?? '' }}"
                                data-items="{{ $jsonItems }}"
                                data-allow-proof="{{ $isRejected && filled($item->proof_image_path) ? '1' : '0' }}"
                            ><i class="fa-solid fa-hand-holding-dollar"></i>Collect</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="cp-empty"><i class="fa-solid fa-inbox"></i><h3>No Pending Collections</h3><p>All collection items have been processed. Check back later.</p></div>
        @endif
    </section>
    <div>{{ $items->links() }}</div>
</div>

<div class="cp-modal" id="viewModal" aria-hidden="true">
    <div class="cp-modal-card" role="dialog" aria-modal="true" aria-labelledby="viewTitle">
        <div class="cp-modal-head"><h3 id="viewTitle"><i class="fa-regular fa-eye" style="color:var(--cp-primary)"></i>View Details</h3><button type="button" class="cp-modal-close" data-close="viewModal">&times;</button></div>
        <div class="cp-modal-body">
            <div class="cp-grid">
                <div class="cp-field"><span>Log ID</span><strong id="vLog">-</strong></div>
                <div class="cp-field"><span>Payment No.</span><strong id="vPay">-</strong></div>
                <div class="cp-field"><span>Vessel</span><strong id="vVessel">-</strong></div>
                <div class="cp-field"><span>Route</span><strong id="vRoute">-</strong></div>
                <div class="cp-field"><span>Date/Time</span><strong id="vDt">-</strong></div>
                <div class="cp-field"><span>Total</span><strong id="vTotal">PHP 0.00</strong></div>
            </div>
            <div class="cp-items">
                <div class="cp-items-head"><h4>Payment Items</h4><span id="vItemsCount">0 item(s)</span></div>
                <div class="cp-items-list" id="vItems"></div>
            </div>
            <div class="cp-note" id="vNoteBox"><i class="fa-solid fa-comment-dots"></i><span id="vNoteText"></span></div>
        </div>
    </div>
</div>

<div class="cp-modal" id="collectModal" aria-hidden="true">
    <div class="cp-modal-card" role="dialog" aria-modal="true" aria-labelledby="collectTitle">
        <div class="cp-modal-head"><h3 id="collectTitle"><i class="fa-solid fa-hand-holding-dollar" style="color:var(--cp-green)"></i>Collect Transaction</h3><button type="button" class="cp-modal-close" data-close="collectModal">&times;</button></div>
        <div class="cp-modal-body">
            <div class="cp-grid">
                <div class="cp-field"><span>Log ID</span><strong id="cLog">-</strong></div>
                <div class="cp-field"><span>Payment No.</span><strong id="cPay">-</strong></div>
                <div class="cp-field"><span>Vessel</span><strong id="cVessel">-</strong></div>
                <div class="cp-field"><span>Route</span><strong id="cRoute">-</strong></div>
                <div class="cp-field"><span>Date/Time</span><strong id="cDt">-</strong></div>
                <div class="cp-field"><span>Total</span><strong id="cTotal">PHP 0.00</strong></div>
            </div>
            <div class="cp-items">
                <div class="cp-items-head"><h4>Payment Items</h4><span id="cItemsCount">0 item(s)</span></div>
                <div class="cp-items-list" id="cItems"></div>
            </div>
            <div class="cp-note" id="cNoteBox"><i class="fa-solid fa-triangle-exclamation"></i><span id="cNoteText"></span></div>

            <form method="POST" action="#" enctype="multipart/form-data" id="collectForm">
                @csrf
                <div>
                    <label class="cp-label"><i class="fa-solid fa-user"></i>Payer Name <span class="req">*</span></label>
                    <input type="text" class="cp-input" name="payer_name" maxlength="150" required placeholder="Full name of the person who paid">
                </div>
                <div>
                    <label class="cp-label"><i class="fa-solid fa-camera"></i>Proof Photo <span class="req">*</span></label>
                    <div class="cp-proof">
                        <label class="cp-upload" id="uploadBox"><input type="file" name="proof_image" id="uploadInput" accept="image/*"><i class="fa-solid fa-image"></i><strong>Upload Photo</strong><small id="uploadName">Browse gallery</small></label>
                        <label class="cp-upload" id="cameraBox"><input type="file" name="proof_camera" id="cameraInput" accept="image/*" capture="environment"><i class="fa-solid fa-camera-retro"></i><strong>Use Camera</strong><small id="cameraName">Capture live photo</small></label>
                    </div>
                    <p class="cp-label" id="proofNote" style="text-transform:none;letter-spacing:0;margin-top:6px;"><i class="fa-solid fa-circle-info" style="color:var(--cp-primary)"></i>Upload from gallery or capture using your camera.</p>
                </div>
                <div>
                    <label class="cp-label"><i class="fa-solid fa-note-sticky"></i>Collector Note</label>
                    <textarea class="cp-textarea" name="collector_note" rows="3" placeholder="Optional note for this collection..."></textarea>
                </div>
                <div class="cp-footer" style="padding:0;border:0;background:transparent;justify-content:flex-end">
                    <button type="submit" class="cp-foot-btn cp-foot-btn-primary"><i class="fa-solid fa-cloud-arrow-up"></i>Submit Collection Proof</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="cp-modal" id="cameraLiveModal" aria-hidden="true">
    <div class="cp-modal-card cp-camera-modal" role="dialog" aria-modal="true" aria-labelledby="cameraLiveTitle">
        <div class="cp-modal-head"><h3 id="cameraLiveTitle"><i class="fa-solid fa-camera" style="color:var(--cp-primary)"></i>Live Camera</h3><button type="button" class="cp-modal-close" data-close="cameraLiveModal">&times;</button></div>
        <div class="cp-modal-body">
            <div class="cp-camera-wrap">
                <div class="cp-camera-frame">
                    <video id="cameraLiveVideo" class="cp-camera-video" autoplay playsinline muted></video>
                    <canvas id="cameraLiveCanvas" hidden></canvas>
                </div>
                <p class="cp-camera-note">Position your camera, then tap capture.</p>
            </div>
        </div>
        <div class="cp-footer">
            <button type="button" class="cp-foot-btn" data-close="cameraLiveModal">Cancel</button>
            <button type="button" class="cp-foot-btn cp-foot-btn-primary" id="cameraCaptureBtn"><i class="fa-solid fa-camera-retro"></i>Capture Photo</button>
        </div>
    </div>
</div>

<div class="cp-modal" id="confirmModal" aria-hidden="true">
    <div class="cp-modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle" style="width:min(520px,100%)">
        <div class="cp-modal-head"><h3 id="confirmTitle"><i class="fa-solid fa-shield-check" style="color:var(--cp-primary)"></i>Confirm Submission</h3><button type="button" class="cp-modal-close" data-close="confirmModal">&times;</button></div>
        <div class="cp-modal-body">
            <div class="cp-grid">
                <div class="cp-field"><span>Log ID</span><strong id="fLog">-</strong></div>
                <div class="cp-field"><span>Payment No.</span><strong id="fPay">-</strong></div>
                <div class="cp-field"><span>Vessel</span><strong id="fVessel">-</strong></div>
                <div class="cp-field"><span>Total</span><strong id="fTotal">PHP 0.00</strong></div>
                <div class="cp-field" style="grid-column:1/-1"><span>Payer Name</span><strong id="fPayer">-</strong></div>
            </div>
        </div>
        <div class="cp-footer"><button type="button" class="cp-foot-btn" data-close="confirmModal">Cancel</button><button type="button" class="cp-foot-btn cp-foot-btn-primary" id="confirmSubmit"><i class="fa-solid fa-cloud-arrow-up"></i>Yes, Submit Proof</button></div>
    </div>
</div>

<script>
(() => {
    const body = document.body;
    const viewModal = document.getElementById('viewModal');
    const collectModal = document.getElementById('collectModal');
    const confirmModal = document.getElementById('confirmModal');
    const collectForm = document.getElementById('collectForm');
    const uploadInput = document.getElementById('uploadInput');
    const cameraInput = document.getElementById('cameraInput');
    const cameraBox = document.getElementById('cameraBox');
    const cameraLiveModal = document.getElementById('cameraLiveModal');
    const cameraLiveVideo = document.getElementById('cameraLiveVideo');
    const cameraLiveCanvas = document.getElementById('cameraLiveCanvas');
    const cameraCaptureBtn = document.getElementById('cameraCaptureBtn');
    let cameraStream = null;

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    const money = (v) => Number.isFinite(Number(v)) ? Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
    const parseItems = (raw) => {
        const source = String(raw || '').trim();
        if (!source) return [];
        try {
            const parsed = JSON.parse(source);
            return Array.isArray(parsed) ? parsed : [];
        } catch {
            try {
                const decoded = source
                    .replaceAll('&quot;', '"')
                    .replaceAll('&#34;', '"')
                    .replaceAll('&amp;', '&');
                const parsed = JSON.parse(decoded);
                return Array.isArray(parsed) ? parsed : [];
            } catch {
                return [];
            }
        }
    };
    const open = (m) => { if (!m) return; m.classList.add('is-open'); m.setAttribute('aria-hidden', 'false'); body.classList.add('cp-lock-scroll'); };
    const close = (m) => {
        if (!m) return;
        if (m === cameraLiveModal) {
            stopLiveCamera();
        }
        m.classList.remove('is-open');
        m.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.cp-modal.is-open')) body.classList.remove('cp-lock-scroll');
    };

    const renderItems = (listId, countId, items) => {
        const list = document.getElementById(listId);
        const count = document.getElementById(countId);
        if (!list || !count) return;
        count.textContent = `${items.length} item(s)`;
        if (!items.length) {
            list.innerHTML = '<div class="cp-item"><div class="cp-main"><div>Collected charges (summary)</div><div class="cp-qty">x1.00</div></div><div class="cp-total">Included in total</div></div>';
            return;
        }
        list.innerHTML = items.map((it) => `<div class="cp-item"><div class="cp-main"><div>${String(it.name ?? 'Charge')}</div><div class="cp-qty">x${Number(it.qty ?? 0).toFixed(2)}</div></div><div class="cp-total">PHP ${money(it.total)}</div></div>`).join('');
    };

    const setRejectNote = (boxId, textId, status, note) => {
        const box = document.getElementById(boxId), text = document.getElementById(textId);
        if (!box || !text) return;
        const show = status === 'rejected' && String(note || '').trim() !== '';
        text.textContent = show ? note : '';
        box.classList.toggle('is-visible', show);
    };

    document.querySelectorAll('[data-close]').forEach((btn) => btn.addEventListener('click', () => close(document.getElementById(btn.dataset.close))));
    [viewModal, collectModal, confirmModal, cameraLiveModal].forEach((m) => m?.addEventListener('click', (e) => { if (e.target === m) close(m); }));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') [cameraLiveModal, confirmModal, collectModal, viewModal].forEach((m) => m?.classList.contains('is-open') && close(m)); });

    document.querySelectorAll('.js-open-view').forEach((btn) => btn.addEventListener('click', () => {
        const items = parseItems(btn.dataset.items);
        set('vLog', btn.dataset.log || '-'); set('vPay', btn.dataset.pay || '-'); set('vVessel', btn.dataset.vessel || '-');
        set('vRoute', btn.dataset.route || '-'); set('vDt', btn.dataset.dt || '-'); set('vTotal', `PHP ${money(btn.dataset.total)}`);
        renderItems('vItems', 'vItemsCount', items);
        setRejectNote('vNoteBox', 'vNoteText', btn.dataset.status || '', btn.dataset.note || '');
        open(viewModal);
    }));

    const resetCollectUI = () => {
        collectForm?.reset();
        document.getElementById('uploadBox')?.classList.remove('has-file');
        document.getElementById('cameraBox')?.classList.remove('has-file');
        set('uploadName', 'Browse gallery');
        set('cameraName', 'Capture live photo');
        set('proofNote', 'Upload from gallery or capture using your camera.');
    };

    document.querySelectorAll('.js-open-collect').forEach((btn) => btn.addEventListener('click', () => {
        const items = parseItems(btn.dataset.items);
        resetCollectUI();
        set('cLog', btn.dataset.log || '-'); set('cPay', btn.dataset.pay || '-'); set('cVessel', btn.dataset.vessel || '-');
        set('cRoute', btn.dataset.route || '-'); set('cDt', btn.dataset.dt || '-'); set('cTotal', `PHP ${money(btn.dataset.total)}`);
        renderItems('cItems', 'cItemsCount', items);
        setRejectNote('cNoteBox', 'cNoteText', btn.dataset.status || '', btn.dataset.note || '');
        set('proofNote', 'Upload from gallery or capture using your camera.');
        if (collectForm) {
            collectForm.action = btn.dataset.action || '#';
            collectForm.dataset.log = btn.dataset.log || '-';
            collectForm.dataset.pay = btn.dataset.pay || '-';
            collectForm.dataset.vessel = btn.dataset.vessel || '-';
            collectForm.dataset.total = btn.dataset.total || '0';
            collectForm.dataset.confirmed = '0';
        }
        open(collectModal);
    }));

    const updateFileChip = (input, boxId, nameId, fallback) => {
        const box = document.getElementById(boxId);
        const label = document.getElementById(nameId);
        const file = input?.files?.[0];
        if (!box || !label) return;
        if (!file) { box.classList.remove('has-file'); label.textContent = fallback; return; }
        box.classList.add('has-file');
        label.textContent = file.name.length > 22 ? `${file.name.slice(0, 20)}...` : file.name;
    };
    uploadInput?.addEventListener('change', () => updateFileChip(uploadInput, 'uploadBox', 'uploadName', 'Browse gallery'));
    cameraInput?.addEventListener('change', () => updateFileChip(cameraInput, 'cameraBox', 'cameraName', 'Capture live photo'));

    const stopLiveCamera = () => {
        if (!cameraStream) return;
        cameraStream.getTracks().forEach((track) => track.stop());
        cameraStream = null;
        if (cameraLiveVideo) {
            cameraLiveVideo.srcObject = null;
        }
    };

    const closeCameraLiveModal = () => {
        stopLiveCamera();
        close(cameraLiveModal);
    };

    const openCameraPicker = () => {
        if (!cameraInput) return;
        cameraInput.setAttribute('accept', 'image/*');
        cameraInput.setAttribute('capture', 'environment');
        cameraInput.click();
    };

    const openLiveCamera = async () => {
        if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
            openCameraPicker();
            return;
        }
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });
            if (cameraLiveVideo) {
                cameraLiveVideo.srcObject = cameraStream;
            }
            open(cameraLiveModal);
        } catch (_error) {
            openCameraPicker();
        }
    };

    cameraBox?.addEventListener('click', (event) => {
        if (!cameraInput) return;
        if (event.target === cameraInput) return;
        event.preventDefault();
        openLiveCamera();
    });

    cameraCaptureBtn?.addEventListener('click', () => {
        if (!cameraLiveVideo || !cameraLiveCanvas || !cameraInput) return;
        const width = cameraLiveVideo.videoWidth || 1280;
        const height = cameraLiveVideo.videoHeight || 720;
        cameraLiveCanvas.width = width;
        cameraLiveCanvas.height = height;
        const ctx = cameraLiveCanvas.getContext('2d');
        if (!ctx) return;
        ctx.drawImage(cameraLiveVideo, 0, 0, width, height);
        cameraLiveCanvas.toBlob((blob) => {
            if (!blob) return;
            const file = new File([blob], `camera-${Date.now()}.jpg`, { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(file);
            cameraInput.files = dt.files;
            updateFileChip(cameraInput, 'cameraBox', 'cameraName', 'Capture live photo');
            closeCameraLiveModal();
        }, 'image/jpeg', 0.92);
    });

    document.querySelectorAll('[data-close="cameraLiveModal"]').forEach((btn) => {
        btn.addEventListener('click', closeCameraLiveModal);
    });

    collectForm?.addEventListener('submit', (e) => {
        if (collectForm.dataset.confirmed === '1') { collectForm.dataset.confirmed = '0'; return; }
        const hasProof = (uploadInput?.files?.length || 0) > 0 || (cameraInput?.files?.length || 0) > 0;
        if (uploadInput) uploadInput.setCustomValidity('');
        if (!hasProof) {
            e.preventDefault();
            if (uploadInput) {
                uploadInput.setCustomValidity('Please upload a proof photo or capture one using the camera.');
                uploadInput.reportValidity();
                uploadInput.setCustomValidity('');
            }
            return;
        }
        if (!collectForm.reportValidity()) { e.preventDefault(); return; }
        e.preventDefault();
        const payer = collectForm.querySelector('input[name="payer_name"]');
        set('fLog', collectForm.dataset.log || '-');
        set('fPay', collectForm.dataset.pay || '-');
        set('fVessel', collectForm.dataset.vessel || '-');
        set('fTotal', `PHP ${money(collectForm.dataset.total)}`);
        set('fPayer', (payer?.value || '').trim() || '-');
        open(confirmModal);
    });

    document.getElementById('confirmSubmit')?.addEventListener('click', () => {
        if (!collectForm) return;
        collectForm.dataset.confirmed = '1';
        close(confirmModal);
        close(collectModal);
        collectForm.requestSubmit();
    });
})();
</script>
@endsection
