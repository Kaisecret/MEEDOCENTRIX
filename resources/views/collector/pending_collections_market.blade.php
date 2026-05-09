@extends('layouts.app')

@section('content')
@php
    /** @var \App\Models\CollectorDepartmentAssignment|null $assignment */
    /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\CollectionDispatchItem> $items */
@endphp

<style>
:root { --cpm-primary:#0f5fa8; --cpm-green:#059669; --cpm-red:#dc2626; --cpm-border:#e2e8f0; --cpm-soft:#f8fafc; --cpm-head:#0f172a; --cpm-muted:#64748b; }
.cpm-page{max-width:1200px;margin:0 auto;display:grid;gap:16px;padding-bottom:2rem}
.cpm-alert{border-radius:10px;padding:.8rem 1rem;display:flex;gap:8px;align-items:center;font-size:.9rem;font-weight:600}
.cpm-alert-success{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}.cpm-alert-error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.cpm-hero{border-radius:14px;padding:1.2rem 1.4rem;color:#fff;background:#155e8f;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.cpm-hero h1{margin:0 0 4px;font-size:1.35rem;font-weight:800;display:flex;align-items:center;gap:8px}.cpm-hero p{margin:0;font-size:.9rem;opacity:.9}
.cpm-search{position:relative;width:360px;max-width:100%}.cpm-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.65)}
.cpm-search input{width:100%;min-height:40px;border-radius:999px;border:1.5px solid rgba(255,255,255,.35);background:rgba(255,255,255,.15);color:#fff;padding:.5rem .9rem .5rem 2.2rem}
.cpm-search input::placeholder{color:rgba(255,255,255,.72)}
.cpm-card{border:1px solid var(--cpm-border);border-radius:14px;background:#fff;overflow:hidden}
.cpm-table-wrap{overflow-x:auto}.cpm-table{width:100%;border-collapse:collapse;min-width:980px}
.cpm-table th{background:#eef5fb;color:#103250;text-transform:uppercase;letter-spacing:.04em;font-size:.73rem;font-weight:800;text-align:left;padding:.8rem .9rem;border-bottom:1px solid var(--cpm-border)}
.cpm-table td{padding:.85rem .9rem;font-size:.9rem;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.cpm-strong{color:var(--cpm-head);font-weight:700}.cpm-sub{color:var(--cpm-muted);font-size:.8rem}
.cpm-pill{border-radius:999px;padding:.2rem .65rem;font-size:.7rem;font-weight:800;border:1px solid transparent}
.cpm-pill-blue{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}.cpm-pill-red{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
.cpm-actions{display:inline-flex;gap:6px}.cpm-btn{border-radius:8px;min-height:34px;padding:.45rem .7rem;display:inline-flex;align-items:center;gap:6px;font-size:.8rem;font-weight:700;cursor:pointer;border:1px solid transparent}
.cpm-btn-view{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}.cpm-btn-collect{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
.cpm-empty{display:grid;place-items:center;gap:8px;padding:3rem 1rem;color:#64748b;text-align:center}
.cpm-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:1rem;background:rgba(10,29,49,.55);z-index:2300}
.cpm-modal.is-open{display:flex}.cpm-modal-card{width:min(760px,100%);max-height:calc(100vh - 2rem);overflow:auto;border-radius:16px;border:1px solid var(--cpm-border);background:#fff}
.cpm-modal-head{display:flex;justify-content:space-between;align-items:center;padding:.95rem 1.2rem;border-bottom:1px solid var(--cpm-border);background:#eef5fb}
.cpm-modal-head h3{margin:0;color:var(--cpm-head);font-size:1rem;font-weight:800;display:inline-flex;align-items:center;gap:8px}
.cpm-modal-close{width:34px;height:34px;border-radius:8px;border:1px solid var(--cpm-border);background:#fff;font-size:1.2rem;cursor:pointer}
.cpm-modal-body{padding:1rem 1.2rem;display:grid;gap:12px}
.cpm-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.cpm-field{border:1px solid var(--cpm-border);background:var(--cpm-soft);border-radius:10px;padding:.62rem .8rem}
.cpm-field span{display:block;font-size:.67rem;text-transform:uppercase;letter-spacing:.04em;color:var(--cpm-muted);font-weight:700}.cpm-field strong{color:var(--cpm-head);font-size:.92rem;font-weight:800}
.cpm-note{border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;padding:.65rem .8rem;display:none;gap:8px}
.cpm-note.is-visible{display:flex}
.cpm-label{font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;display:inline-flex;align-items:center;gap:5px;margin-bottom:5px}
.cpm-label .req{color:var(--cpm-red)}.cpm-input,.cpm-textarea{width:100%;min-height:40px;border:1.5px solid var(--cpm-border);border-radius:9px;background:var(--cpm-soft);padding:.55rem .8rem}
.cpm-textarea{min-height:78px;resize:vertical}.cpm-proof{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.cpm-upload{position:relative;border:2px dashed var(--cpm-border);border-radius:10px;background:var(--cpm-soft);min-height:90px;padding:.7rem;display:grid;place-items:center;text-align:center;gap:4px}
.cpm-upload.has-file{border-style:solid;border-color:var(--cpm-green);background:#f0fdf4}.cpm-upload input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
.cpm-footer{display:flex;justify-content:flex-end;gap:8px;padding:.9rem 1.2rem;border-top:1px solid var(--cpm-border);background:var(--cpm-soft)}
.cpm-foot-btn{border-radius:9px;min-height:38px;padding:.52rem .95rem;border:1px solid var(--cpm-border);background:#fff;font-size:.86rem;font-weight:700;cursor:pointer}
.cpm-foot-btn-primary{border-color:transparent;background:var(--cpm-primary);color:#fff}
.cpm-camera-modal{width:min(700px,100%)}
.cpm-camera-wrap{display:grid;gap:10px}
.cpm-camera-frame{border:1px solid var(--cpm-border);border-radius:10px;overflow:hidden;background:#0f172a;min-height:260px;display:grid;place-items:center}
.cpm-camera-video{width:100%;max-height:62vh;display:block}
.cpm-camera-note{margin:0;color:#64748b;font-size:.82rem}
body.cpm-lock-scroll{overflow:hidden}
@media (max-width:768px){.cpm-grid,.cpm-proof{grid-template-columns:1fr}.cpm-search{width:100%}}
</style>

<div data-server-rendered-page="pending_collections" data-page-title="Pending Collections" class="cpm-page">
    @if(session('status'))<div class="cpm-alert cpm-alert-success"><i class="fa-solid fa-circle-check"></i>{{ session('status') }}</div>@endif
    @if(session('error'))<div class="cpm-alert cpm-alert-error"><i class="fa-solid fa-circle-exclamation"></i>{{ session('error') }}</div>@endif
    @if($errors->any())<div class="cpm-alert cpm-alert-error"><i class="fa-solid fa-triangle-exclamation"></i>{{ $errors->first() }}</div>@endif

    <section class="cpm-hero">
        <div>
            <h1><i class="fa-solid fa-clock-rotate-left"></i>Pending Market Collections</h1>
        </div>
        <form method="GET" action="{{ route('collector.pending_collections') }}" class="cpm-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" value="{{ $search }}" placeholder="Search stall, tenant, business..." autocomplete="off">
        </form>
    </section>

    <section class="cpm-card">
        @if($items->count() > 0)
            <div class="cpm-table-wrap">
                <table class="cpm-table">
                    <thead>
                        <tr>
                            <th>Stall</th><th>Tenant</th><th>Business</th><th>Contract</th><th>Billing</th><th>Total</th><th>Status</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php
                                $lease = $item->marketStallLease;
                                $stall = $lease?->stall;
                                $tenant = $lease?->tenant;
                                $status = (string) $item->status;
                                $isRejected = $status === 'rejected';
                                $billing = ucfirst((string) ($lease?->billing_period ?? 'monthly')) . ' x ' . (int) ($lease?->billing_cycles ?? 1);
                            @endphp
                            <tr>
                                <td class="cpm-strong">{{ $stall?->stall_no ?? '-' }}<br><span class="cpm-sub">{{ $stall?->location?->location_code ?? '-' }}</span></td>
                                <td class="cpm-strong">{{ $tenant?->fullName() ?: '-' }}</td>
                                <td>{{ $tenant?->business_name ?: '-' }}</td>
                                <td>{{ $lease?->contract_number ?: '-' }}</td>
                                <td>{{ $billing }}</td>
                                <td class="cpm-strong">PHP {{ number_format((float) $item->amount_snapshot, 2) }}</td>
                                <td>@if($isRejected)<span class="cpm-pill cpm-pill-red">Rejected</span>@else<span class="cpm-pill cpm-pill-blue">For Collection</span>@endif</td>
                                <td>
                                    <div class="cpm-actions">
                                        <button type="button" class="cpm-btn cpm-btn-view js-open-view"
                                            data-stall="{{ $stall?->stall_no ?? '-' }}"
                                            data-tenant="{{ $tenant?->fullName() ?: '-' }}"
                                            data-business="{{ $tenant?->business_name ?: '-' }}"
                                            data-contract="{{ $lease?->contract_number ?: '-' }}"
                                            data-billing="{{ $billing }}"
                                            data-total="{{ number_format((float) $item->amount_snapshot, 2, '.', '') }}"
                                            data-status="{{ $status }}"
                                            data-note="{{ $item->review_note ?? '' }}"
                                        ><i class="fa-regular fa-eye"></i>View</button>
                                        <button type="button" class="cpm-btn cpm-btn-collect js-open-collect"
                                            data-action="{{ route('collector.pending_collections.collect', $item) }}"
                                            data-stall="{{ $stall?->stall_no ?? '-' }}"
                                            data-tenant="{{ $tenant?->fullName() ?: '-' }}"
                                            data-business="{{ $tenant?->business_name ?: '-' }}"
                                            data-contract="{{ $lease?->contract_number ?: '-' }}"
                                            data-billing="{{ $billing }}"
                                            data-total="{{ number_format((float) $item->amount_snapshot, 2, '.', '') }}"
                                            data-status="{{ $status }}"
                                            data-note="{{ $item->review_note ?? '' }}"
                                            data-allow-proof="{{ $isRejected && filled($item->proof_image_path) ? '1' : '0' }}"
                                        ><i class="fa-solid fa-hand-holding-dollar"></i>Collect</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="cpm-empty"><i class="fa-solid fa-inbox"></i><h3>No Pending Collections</h3><p>All market collection items have been processed.</p></div>
        @endif
    </section>
    <div>{{ $items->links() }}</div>
</div>

<div class="cpm-modal" id="viewModal" aria-hidden="true">
    <div class="cpm-modal-card" role="dialog" aria-modal="true" aria-labelledby="viewTitle">
        <div class="cpm-modal-head"><h3 id="viewTitle"><i class="fa-regular fa-eye" style="color:var(--cpm-primary)"></i>View Details</h3><button type="button" class="cpm-modal-close" data-close="viewModal">&times;</button></div>
        <div class="cpm-modal-body">
            <div class="cpm-grid">
                <div class="cpm-field"><span>Stall</span><strong id="vStall">-</strong></div>
                <div class="cpm-field"><span>Tenant</span><strong id="vTenant">-</strong></div>
                <div class="cpm-field"><span>Business</span><strong id="vBusiness">-</strong></div>
                <div class="cpm-field"><span>Contract</span><strong id="vContract">-</strong></div>
                <div class="cpm-field"><span>Billing</span><strong id="vBilling">-</strong></div>
                <div class="cpm-field"><span>Total</span><strong id="vTotal">PHP 0.00</strong></div>
            </div>
            <div class="cpm-note" id="vNoteBox"><i class="fa-solid fa-comment-dots"></i><span id="vNoteText"></span></div>
        </div>
    </div>
</div>

<div class="cpm-modal" id="collectModal" aria-hidden="true">
    <div class="cpm-modal-card" role="dialog" aria-modal="true" aria-labelledby="collectTitle">
        <div class="cpm-modal-head"><h3 id="collectTitle"><i class="fa-solid fa-hand-holding-dollar" style="color:var(--cpm-green)"></i>Collect Transaction</h3><button type="button" class="cpm-modal-close" data-close="collectModal">&times;</button></div>
        <div class="cpm-modal-body">
            <div class="cpm-grid">
                <div class="cpm-field"><span>Stall</span><strong id="cStall">-</strong></div>
                <div class="cpm-field"><span>Tenant</span><strong id="cTenant">-</strong></div>
                <div class="cpm-field"><span>Business</span><strong id="cBusiness">-</strong></div>
                <div class="cpm-field"><span>Contract</span><strong id="cContract">-</strong></div>
                <div class="cpm-field"><span>Billing</span><strong id="cBilling">-</strong></div>
                <div class="cpm-field"><span>Total</span><strong id="cTotal">PHP 0.00</strong></div>
            </div>
            <div class="cpm-note" id="cNoteBox"><i class="fa-solid fa-triangle-exclamation"></i><span id="cNoteText"></span></div>

            <form method="POST" action="#" enctype="multipart/form-data" id="collectForm">
                @csrf
                <div>
                    <label class="cpm-label"><i class="fa-solid fa-user"></i>Payer Name <span class="req">*</span></label>
                    <input type="text" class="cpm-input" name="payer_name" maxlength="150" required placeholder="Full name of the person who paid">
                </div>
                <div>
                    <label class="cpm-label"><i class="fa-solid fa-camera"></i>Proof Photo <span class="req">*</span></label>
                    <div class="cpm-proof">
                        <label class="cpm-upload" id="uploadBox"><input type="file" name="proof_image" id="uploadInput" accept="image/*"><i class="fa-solid fa-image"></i><strong>Upload Photo</strong><small id="uploadName">Browse gallery</small></label>
                        <label class="cpm-upload" id="cameraBox"><input type="file" name="proof_camera" id="cameraInput" accept="image/*" capture="environment"><i class="fa-solid fa-camera-retro"></i><strong>Use Camera</strong><small id="cameraName">Capture live photo</small></label>
                    </div>
                    <p class="cpm-label" id="proofNote" style="text-transform:none;letter-spacing:0;margin-top:6px;"><i class="fa-solid fa-circle-info" style="color:var(--cpm-primary)"></i>Upload from gallery or capture using your camera.</p>
                </div>
                <div>
                    <label class="cpm-label"><i class="fa-solid fa-note-sticky"></i>Collector Note</label>
                    <textarea class="cpm-textarea" name="collector_note" rows="3" placeholder="Optional note for this collection..."></textarea>
                </div>
                <div class="cpm-footer" style="padding:0;border:0;background:transparent;justify-content:flex-end">
                    <button type="submit" class="cpm-foot-btn cpm-foot-btn-primary"><i class="fa-solid fa-cloud-arrow-up"></i>Submit Collection Proof</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="cpm-modal" id="cameraLiveModal" aria-hidden="true">
    <div class="cpm-modal-card cpm-camera-modal" role="dialog" aria-modal="true" aria-labelledby="cameraLiveTitle">
        <div class="cpm-modal-head"><h3 id="cameraLiveTitle"><i class="fa-solid fa-camera" style="color:var(--cpm-primary)"></i>Live Camera</h3><button type="button" class="cpm-modal-close" data-close="cameraLiveModal">&times;</button></div>
        <div class="cpm-modal-body">
            <div class="cpm-camera-wrap">
                <div class="cpm-camera-frame">
                    <video id="cameraLiveVideo" class="cpm-camera-video" autoplay playsinline muted></video>
                    <canvas id="cameraLiveCanvas" hidden></canvas>
                </div>
                <p class="cpm-camera-note">Position your camera, then tap capture.</p>
            </div>
        </div>
        <div class="cpm-footer">
            <button type="button" class="cpm-foot-btn" data-close="cameraLiveModal">Cancel</button>
            <button type="button" class="cpm-foot-btn cpm-foot-btn-primary" id="cameraCaptureBtn"><i class="fa-solid fa-camera-retro"></i>Capture Photo</button>
        </div>
    </div>
</div>

<div class="cpm-modal" id="confirmModal" aria-hidden="true">
    <div class="cpm-modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle" style="width:min(520px,100%)">
        <div class="cpm-modal-head"><h3 id="confirmTitle"><i class="fa-solid fa-shield-check" style="color:var(--cpm-primary)"></i>Confirm Submission</h3><button type="button" class="cpm-modal-close" data-close="confirmModal">&times;</button></div>
        <div class="cpm-modal-body">
            <div class="cpm-grid">
                <div class="cpm-field"><span>Stall</span><strong id="fStall">-</strong></div>
                <div class="cpm-field"><span>Tenant</span><strong id="fTenant">-</strong></div>
                <div class="cpm-field"><span>Contract</span><strong id="fContract">-</strong></div>
                <div class="cpm-field"><span>Total</span><strong id="fTotal">PHP 0.00</strong></div>
                <div class="cpm-field" style="grid-column:1/-1"><span>Payer Name</span><strong id="fPayer">-</strong></div>
            </div>
        </div>
        <div class="cpm-footer"><button type="button" class="cpm-foot-btn" data-close="confirmModal">Cancel</button><button type="button" class="cpm-foot-btn cpm-foot-btn-primary" id="confirmSubmit"><i class="fa-solid fa-cloud-arrow-up"></i>Yes, Submit Proof</button></div>
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
    const open = (m) => { if (!m) return; m.classList.add('is-open'); m.setAttribute('aria-hidden', 'false'); body.classList.add('cpm-lock-scroll'); };
    const close = (m) => {
        if (!m) return;
        if (m === cameraLiveModal) {
            stopLiveCamera();
        }
        m.classList.remove('is-open');
        m.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.cpm-modal.is-open')) body.classList.remove('cpm-lock-scroll');
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
        set('vStall', btn.dataset.stall || '-');
        set('vTenant', btn.dataset.tenant || '-');
        set('vBusiness', btn.dataset.business || '-');
        set('vContract', btn.dataset.contract || '-');
        set('vBilling', btn.dataset.billing || '-');
        set('vTotal', `PHP ${money(btn.dataset.total)}`);
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
        resetCollectUI();
        set('cStall', btn.dataset.stall || '-');
        set('cTenant', btn.dataset.tenant || '-');
        set('cBusiness', btn.dataset.business || '-');
        set('cContract', btn.dataset.contract || '-');
        set('cBilling', btn.dataset.billing || '-');
        set('cTotal', `PHP ${money(btn.dataset.total)}`);
        setRejectNote('cNoteBox', 'cNoteText', btn.dataset.status || '', btn.dataset.note || '');
        set('proofNote', 'Upload from gallery or capture using your camera.');
        if (collectForm) {
            collectForm.action = btn.dataset.action || '#';
            collectForm.dataset.stall = btn.dataset.stall || '-';
            collectForm.dataset.tenant = btn.dataset.tenant || '-';
            collectForm.dataset.contract = btn.dataset.contract || '-';
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
        set('fStall', collectForm.dataset.stall || '-');
        set('fTenant', collectForm.dataset.tenant || '-');
        set('fContract', collectForm.dataset.contract || '-');
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
