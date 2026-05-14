@extends('layouts.app')

@section('content')
@php
    /** @var \App\Models\User $user */
    $currentAvatar = $user->avatar ?? 'boy';
@endphp

<style>
    .fp-profile { max-width: 860px; margin: 0 auto; display: grid; gap: 20px; font-family:'Inter',system-ui,sans-serif; }
    .fp-alert-ok  { border-radius:10px; border:1px solid #a7f3d0; background:#ecfdf5; color:#065f46; padding:.85rem 1.1rem; font-size:.9rem; display:flex; align-items:center; gap:8px; }
    .fp-alert-err { border-radius:10px; border:1px solid #fecaca; background:#fef2f2; color:#991b1b; padding:.85rem 1.1rem; font-size:.9rem; display:flex; align-items:center; gap:8px; }
    .fp-card { border:1px solid #e2e8f0; border-radius:14px; background:#fff; box-shadow:0 2px 8px rgba(15,23,42,.06); overflow:hidden; }
    .fp-card-head { padding:1.15rem 1.4rem; border-bottom:1px solid #e2e8f0; background:linear-gradient(135deg,#eff6ff,#f8fafc); }
    .fp-card-head h3 { margin:0 0 2px; font-size:1.05rem; color:#0f172a; font-weight:700; display:flex; align-items:center; gap:8px; }
    .fp-card-head p  { margin:0; font-size:.85rem; color:#64748b; }
    .fp-card-body { padding:1.4rem; }
    .fp-avatar-picker { display:flex; gap:20px; justify-content:center; flex-wrap:wrap; }
    .fp-avatar-opt { cursor:pointer; display:flex; flex-direction:column; align-items:center; gap:10px; }
    .fp-avatar-opt input[type=radio] { display:none; }
    .fp-avatar-svg-wrap { width:100px; height:100px; border-radius:50%; border:3px solid #e2e8f0; padding:4px; transition:border-color .2s, box-shadow .2s; background:#fff; display:flex; align-items:center; justify-content:center; }
    .fp-avatar-opt input:checked ~ .fp-avatar-svg-wrap { border-color:#155f8f; box-shadow:0 0 0 4px rgba(21,95,143,.15); }
    .fp-avatar-opt:hover .fp-avatar-svg-wrap { border-color:#93c5fd; }
    .fp-avatar-svg-wrap svg { width:86px; height:86px; border-radius:50%; }
    .fp-avatar-label { font-size:.82rem; font-weight:600; color:#64748b; letter-spacing:.02em; text-transform:uppercase; }
    .fp-avatar-opt input:checked ~ .fp-avatar-svg-wrap + .fp-avatar-label { color:#155f8f; }
    .fp-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.1rem; }
    .fp-field { display:grid; gap:5px; }
    .fp-field label { font-size:.82rem; font-weight:700; color:#334155; letter-spacing:.02em; text-transform:uppercase; }
    .fp-input { border:1.5px solid #e2e8f0; border-radius:9px; padding:.65rem .9rem; font-size:.92rem; color:#0f172a; width:100%; background:#fff; transition:border-color .2s; outline:none; font-family:inherit; box-sizing:border-box; }
    .fp-input:focus { border-color:#155f8f; box-shadow:0 0 0 3px rgba(21,95,143,.1); }
    .fp-input[readonly] { background:#f8fafc; color:#64748b; cursor:not-allowed; }
    .fp-foot { display:flex; justify-content:flex-end; padding-top:1rem; border-top:1px solid #f1f5f9; margin-top:.5rem; }
    .fp-btn-save { background:#155f8f; color:#fff; border:none; border-radius:9px; padding:.7rem 1.5rem; font-size:.93rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:background .2s; }
    .fp-btn-save:hover { background:#0f4b73; }
    .fp-info-row { display:flex; align-items:center; gap:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1rem 1.25rem; }
    .fp-info-avatar { width:56px; height:56px; border-radius:50%; border:2px solid #e2e8f0; flex-shrink:0; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .fp-info-avatar svg { width:52px; height:52px; }
    .fp-info-text h4 { margin:0 0 2px; font-size:1rem; font-weight:700; color:#0f172a; }
    .fp-info-text span { font-size:.82rem; color:#64748b; }
    .fp-info-actions { margin-left:auto; display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
    .fp-availability-badge {
        display:inline-flex;
        align-items:center;
        gap:6px;
        border:1px solid transparent;
        border-radius:999px;
        padding:.34rem .72rem;
        font-size:.76rem;
        font-weight:800;
        letter-spacing:.02em;
        text-transform:uppercase;
    }
    .fp-availability-badge.is-available { background:#ecfdf5; border-color:#86efac; color:#166534; }
    .fp-availability-badge.is-absent { background:#fff7ed; border-color:#fdba74; color:#9a3412; }
    .fp-btn-status {
        border:1px solid #cbd5e1;
        border-radius:9px;
        background:#fff;
        color:#0f172a;
        min-height:36px;
        padding:0 .82rem;
        font-size:.84rem;
        font-weight:700;
        cursor:pointer;
        display:inline-flex;
        align-items:center;
        gap:7px;
    }
    .fp-btn-status:hover { background:#f8fafc; }
    .fp-btn-status.is-absent-action { border-color:#fdba74; color:#9a3412; background:#fff7ed; }
    .fp-btn-status.is-available-action { border-color:#86efac; color:#166534; background:#ecfdf5; }
    .fp-modal-backdrop {
        position:fixed;
        inset:0;
        background:rgba(15,23,42,.52);
        display:grid;
        place-items:center;
        z-index:1200;
        padding:14px;
    }
    .fp-modal-backdrop[hidden] { display:none !important; }
    .fp-modal {
        width:min(460px, 96vw);
        border:1px solid #cbd5e1;
        border-radius:14px;
        background:#fff;
        box-shadow:0 20px 52px rgba(2,6,23,.35);
        overflow:hidden;
    }
    .fp-modal-head { padding:12px 14px; border-bottom:1px solid #e2e8f0; background:#f8fafc; }
    .fp-modal-head h4 { margin:0; font-size:1rem; color:#0f172a; font-weight:800; }
    .fp-modal-body { padding:14px; color:#334155; font-size:.9rem; }
    .fp-modal-foot { padding:12px 14px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px; }
    .fp-btn-muted {
        border:1px solid #cbd5e1;
        border-radius:9px;
        background:#fff;
        color:#334155;
        min-height:36px;
        padding:0 .9rem;
        font-size:.85rem;
        font-weight:700;
        cursor:pointer;
    }
    .fp-btn-danger {
        border:1px solid #f97316;
        border-radius:9px;
        background:#ea580c;
        color:#fff;
        min-height:36px;
        padding:0 .95rem;
        font-size:.85rem;
        font-weight:700;
        cursor:pointer;
    }
    .fp-btn-success {
        border:1px solid #059669;
        border-radius:9px;
        background:#047857;
        color:#fff;
        min-height:36px;
        padding:0 .95rem;
        font-size:.85rem;
        font-weight:700;
        cursor:pointer;
    }
    @media (max-width:700px) {
        .fp-info-row { align-items:flex-start; }
        .fp-info-actions { margin-left:0; width:100%; justify-content:flex-start; }
    }
</style>

<div data-server-rendered-page="profile" data-page-title="My Profile" class="fp-profile">
    @if(session('status'))
        <div class="fp-alert-ok"><i class="fa-solid fa-circle-check"></i>{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="fp-alert-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $errors->first() }}</div>
    @endif

    <div class="fp-info-row">
        <div class="fp-info-avatar">
            @if($currentAvatar === 'girl')
                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="50" fill="#ffe4f0"/>
                    <ellipse cx="50" cy="70" rx="26" ry="20" fill="#f472b6"/>
                    <circle cx="50" cy="42" r="18" fill="#fcd5b0"/>
                    <ellipse cx="50" cy="28" rx="20" ry="14" fill="#be185d"/>
                    <ellipse cx="35" cy="30" rx="10" ry="16" fill="#be185d"/>
                    <ellipse cx="65" cy="30" rx="10" ry="16" fill="#be185d"/>
                    <circle cx="43" cy="43" r="2.5" fill="#374151"/>
                    <circle cx="57" cy="43" r="2.5" fill="#374151"/>
                    <path d="M44 50 Q50 55 56 50" stroke="#d97706" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                </svg>
            @else
                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="50" fill="#dbeafe"/>
                    <ellipse cx="50" cy="72" rx="26" ry="20" fill="#3b82f6"/>
                    <circle cx="50" cy="42" r="18" fill="#fcd5b0"/>
                    <rect x="30" y="18" width="40" height="20" rx="10" fill="#1e3a6e"/>
                    <rect x="28" y="28" width="44" height="10" rx="5" fill="#2563eb"/>
                    <circle cx="43" cy="43" r="2.5" fill="#374151"/>
                    <circle cx="57" cy="43" r="2.5" fill="#374151"/>
                    <path d="M44 50 Q50 55 56 50" stroke="#d97706" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                </svg>
            @endif
        </div>
        <div class="fp-info-text">
            <h4>{{ $user->name }}</h4>
            <span>{{ $user->roleLabel() }} &bull; {{ ucfirst((string) $user->department) }}</span>
        </div>
        <div class="fp-info-actions">
            <span class="fp-availability-badge {{ $user->is_absent ? 'is-absent' : 'is-available' }}">
                <i class="fa-solid {{ $user->is_absent ? 'fa-user-clock' : 'fa-circle-check' }}"></i>
                {{ $user->is_absent ? 'Absent' : 'Available' }}
            </span>
            <button
                type="button"
                class="fp-btn-status {{ $user->is_absent ? 'is-available-action' : 'is-absent-action' }}"
                id="fpToggleAvailabilityBtn"
                data-next-state="{{ $user->is_absent ? 'available' : 'absent' }}"
            >
                <i class="fa-solid {{ $user->is_absent ? 'fa-user-check' : 'fa-user-slash' }}"></i>
                {{ $user->is_absent ? 'Set Available' : 'Set Absent' }}
            </button>
        </div>
    </div>

    <div class="fp-card">
        <div class="fp-card-head">
            <h3><i class="fa-solid fa-user-circle" style="color:#155f8f;"></i>Choose Your Avatar</h3>
            <p>Pick the avatar that represents you — it shows in the sidebar and your profile.</p>
        </div>
        <div class="fp-card-body">
            <form action="{{ route('collector.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_mode" value="avatar">
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">
                <input type="hidden" name="username" value="{{ $user->username }}">

                <div class="fp-avatar-picker">
                    <label class="fp-avatar-opt">
                        <input type="radio" name="avatar" value="boy" {{ $currentAvatar === 'boy' ? 'checked' : '' }}>
                        <div class="fp-avatar-svg-wrap">
                            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="50" cy="50" r="50" fill="#dbeafe"/>
                                <ellipse cx="50" cy="72" rx="26" ry="20" fill="#3b82f6"/>
                                <circle cx="50" cy="42" r="18" fill="#fcd5b0"/>
                                <rect x="30" y="18" width="40" height="20" rx="10" fill="#1e3a6e"/>
                                <rect x="28" y="28" width="44" height="10" rx="5" fill="#2563eb"/>
                                <circle cx="43" cy="43" r="2.5" fill="#374151"/>
                                <circle cx="57" cy="43" r="2.5" fill="#374151"/>
                                <path d="M44 50 Q50 55 56 50" stroke="#d97706" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="fp-avatar-label">Boy</span>
                    </label>

                    <label class="fp-avatar-opt">
                        <input type="radio" name="avatar" value="girl" {{ $currentAvatar === 'girl' ? 'checked' : '' }}>
                        <div class="fp-avatar-svg-wrap">
                            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="50" cy="50" r="50" fill="#ffe4f0"/>
                                <ellipse cx="50" cy="70" rx="26" ry="20" fill="#f472b6"/>
                                <circle cx="50" cy="42" r="18" fill="#fcd5b0"/>
                                <ellipse cx="50" cy="28" rx="20" ry="14" fill="#be185d"/>
                                <ellipse cx="35" cy="30" rx="10" ry="16" fill="#be185d"/>
                                <ellipse cx="65" cy="30" rx="10" ry="16" fill="#be185d"/>
                                <circle cx="43" cy="43" r="2.5" fill="#374151"/>
                                <circle cx="57" cy="43" r="2.5" fill="#374151"/>
                                <path d="M44 50 Q50 55 56 50" stroke="#d97706" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="fp-avatar-label">Girl</span>
                    </label>
                </div>

                <div class="fp-foot" style="margin-top:1.2rem;">
                    <button type="submit" class="fp-btn-save">
                        <i class="fa-solid fa-palette"></i> Save Avatar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="fp-card">
        <div class="fp-card-head">
            <h3><i class="fa-solid fa-id-card" style="color:#155f8f;"></i>Account Information</h3>
            <p>Update your name, username, and email address.</p>
        </div>
        <div class="fp-card-body">
            <form action="{{ route('collector.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_mode" value="profile">
                <input type="hidden" name="avatar" value="{{ $currentAvatar }}">

                <div class="fp-grid">
                    <div class="fp-field">
                        <label for="cp-name">Full Name</label>
                        <input id="cp-name" name="name" type="text" class="fp-input" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="fp-field">
                        <label for="cp-username">Username</label>
                        <input id="cp-username" name="username" type="text" class="fp-input" value="{{ old('username', $user->username) }}" autocomplete="off">
                    </div>
                    <div class="fp-field">
                        <label for="cp-email">Email Address</label>
                        <input id="cp-email" name="email" type="email" class="fp-input" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="fp-field">
                        <label>Department</label>
                        <input type="text" class="fp-input" value="{{ ucfirst((string) $user->department) }}" readonly>
                    </div>
                    <div class="fp-field">
                        <label>Role</label>
                        <input type="text" class="fp-input" value="{{ $user->roleLabel() }}" readonly>
                    </div>
                </div>

                <div class="fp-foot">
                    <button type="submit" class="fp-btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="fp-modal-backdrop" id="fpAvailabilityModal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="fpAvailabilityTitle">
    <div class="fp-modal">
        <div class="fp-modal-head">
            <h4 id="fpAvailabilityTitle">Confirm Collector Status</h4>
        </div>
        <div class="fp-modal-body" id="fpAvailabilityText">
            Confirm status update.
        </div>
        <div class="fp-modal-foot">
            <button type="button" class="fp-btn-muted" id="fpAvailabilityCancel">Cancel</button>
            <form action="{{ route('collector.profile.update') }}" method="POST" id="fpAvailabilityForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_mode" value="availability">
                <input type="hidden" name="availability_state" id="fpAvailabilityState" value="">
                <button type="submit" class="{{ $user->is_absent ? 'fp-btn-success' : 'fp-btn-danger' }}" id="fpAvailabilitySubmit">
                    {{ $user->is_absent ? 'Set Available' : 'Set Absent' }}
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const openButton = document.getElementById('fpToggleAvailabilityBtn');
        const modal = document.getElementById('fpAvailabilityModal');
        const cancelButton = document.getElementById('fpAvailabilityCancel');
        const stateInput = document.getElementById('fpAvailabilityState');
        const textNode = document.getElementById('fpAvailabilityText');
        const submitButton = document.getElementById('fpAvailabilitySubmit');

        if (!openButton || !modal || !cancelButton || !stateInput || !textNode || !submitButton) {
            return;
        }

        const closeModal = function () {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        openButton.addEventListener('click', function () {
            const nextState = (openButton.dataset.nextState || '').toLowerCase() === 'available' ? 'available' : 'absent';
            const isAbsentAction = nextState === 'absent';

            stateInput.value = nextState;
            textNode.textContent = isAbsentAction
                ? 'Set status to Absent? You will be hidden from Fishport and Market assignment dropdowns until you set Available again.'
                : 'Set status to Available? You will be visible again in Fishport and Market assignment dropdowns.';

            submitButton.textContent = isAbsentAction ? 'Set Absent' : 'Set Available';
            submitButton.classList.toggle('fp-btn-danger', isAbsentAction);
            submitButton.classList.toggle('fp-btn-success', !isAbsentAction);

            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        });

        cancelButton.addEventListener('click', closeModal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });
    })();
</script>
@endsection
