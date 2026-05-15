@extends('layouts.app')

@section('content')
@php
    /** @var \App\Models\User $user */
    $currentAvatar = $user->avatar ?? 'boy';
@endphp

<style>
    .ap-profile { max-width: 920px; margin: 0 auto; display: grid; gap: 20px; font-family:'Inter',system-ui,sans-serif; }
    .ap-alert-ok  { border-radius:10px; border:1px solid #a7f3d0; background:#ecfdf5; color:#065f46; padding:.85rem 1.1rem; font-size:.9rem; display:flex; align-items:center; gap:8px; }
    .ap-alert-err { border-radius:10px; border:1px solid #fecaca; background:#fef2f2; color:#991b1b; padding:.85rem 1.1rem; font-size:.9rem; display:flex; align-items:center; gap:8px; }
    .ap-card { border:1px solid #e2e8f0; border-radius:14px; background:#fff; box-shadow:0 2px 8px rgba(15,23,42,.06); overflow:hidden; }
    .ap-card-head { padding:1.15rem 1.4rem; border-bottom:1px solid #e2e8f0; background:#f8fafc; }
    .ap-card-head h3 { margin:0 0 2px; font-size:1.05rem; color:#0f172a; font-weight:700; display:flex; align-items:center; gap:8px; }
    .ap-card-head p  { margin:0; font-size:.85rem; color:#64748b; }
    .ap-card-body { padding:1.4rem; }
    .ap-avatar-picker { display:flex; gap:20px; justify-content:center; flex-wrap:wrap; }
    .ap-avatar-opt { cursor:pointer; display:flex; flex-direction:column; align-items:center; gap:10px; }
    .ap-avatar-opt input[type=radio] { display:none; }
    .ap-avatar-svg-wrap { width:100px; height:100px; border-radius:50%; border:3px solid #e2e8f0; padding:4px; transition:border-color .2s, box-shadow .2s; background:#fff; display:flex; align-items:center; justify-content:center; }
    .ap-avatar-opt input:checked ~ .ap-avatar-svg-wrap { border-color:#155f8f; box-shadow:0 0 0 4px rgba(21,95,143,.15); }
    .ap-avatar-opt:hover .ap-avatar-svg-wrap { border-color:#93c5fd; }
    .ap-avatar-svg-wrap svg { width:86px; height:86px; border-radius:50%; }
    .ap-avatar-label { font-size:.82rem; font-weight:600; color:#64748b; letter-spacing:.02em; text-transform:uppercase; }
    .ap-avatar-opt input:checked ~ .ap-avatar-svg-wrap + .ap-avatar-label { color:#155f8f; }
    .ap-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.1rem; }
    .ap-field { display:grid; gap:5px; }
    .ap-field label { font-size:.82rem; font-weight:700; color:#334155; letter-spacing:.02em; text-transform:uppercase; }
    .ap-input { border:1.5px solid #e2e8f0; border-radius:9px; padding:.65rem .9rem; font-size:.92rem; color:#0f172a; width:100%; background:#fff; transition:border-color .2s; outline:none; font-family:inherit; box-sizing:border-box; }
    .ap-input:focus { border-color:#155f8f; box-shadow:0 0 0 3px rgba(21,95,143,.1); }
    .ap-input[readonly] { background:#f8fafc; color:#64748b; cursor:not-allowed; }
    .ap-foot { display:flex; justify-content:flex-end; padding-top:1rem; border-top:1px solid #f1f5f9; margin-top:.5rem; }
    .ap-btn-save { background:#155f8f; color:#fff; border:none; border-radius:9px; padding:.7rem 1.5rem; font-size:.93rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:background .2s; }
    .ap-btn-save:hover { background:#0f4b73; }
    .ap-info-row { display:flex; align-items:center; gap:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1rem 1.25rem; }
    .ap-info-avatar { width:56px; height:56px; border-radius:50%; border:2px solid #e2e8f0; flex-shrink:0; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .ap-info-avatar svg { width:52px; height:52px; }
    .ap-info-text h4 { margin:0 0 2px; font-size:1rem; font-weight:700; color:#0f172a; }
    .ap-info-text span { font-size:.82rem; color:#64748b; }
    .ap-password-note { margin-top:10px; font-size:.8rem; color:#64748b; }
    .ap-password-wrap { position:relative; }
    .ap-password-wrap .ap-input { padding-right:2.7rem; }
    .ap-password-toggle {
        position:absolute;
        right:.45rem;
        top:50%;
        transform:translateY(-50%);
        width:32px;
        height:32px;
        border:0;
        border-radius:8px;
        background:transparent;
        color:#64748b;
        cursor:pointer;
        display:inline-flex;
        align-items:center;
        justify-content:center;
    }
    .ap-password-toggle:hover { background:#f1f5f9; color:#334155; }
</style>

<div data-server-rendered-page="profile" data-page-title="My Profile" class="ap-profile">
    @if(session('status'))
        <div class="ap-alert-ok"><i class="fa-solid fa-circle-check"></i>{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="ap-alert-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $errors->first() }}</div>
    @endif

    <div class="ap-info-row">
        <div class="ap-info-avatar">
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
        <div class="ap-info-text">
            <h4>{{ $user->name }}</h4>
            <span>{{ $user->roleLabel() }} • {{ $user->email }}</span>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card-head">
            <h3><i class="fa-solid fa-user-circle" style="color:#155f8f;"></i>Choose Your Avatar</h3>
            <p>Select boy or girl avatar for your admin account.</p>
        </div>
        <div class="ap-card-body">
            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_mode" value="avatar">

                <div class="ap-avatar-picker">
                    <label class="ap-avatar-opt">
                        <input type="radio" name="avatar" value="boy" {{ $currentAvatar === 'boy' ? 'checked' : '' }}>
                        <div class="ap-avatar-svg-wrap">
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
                        <span class="ap-avatar-label">Boy</span>
                    </label>

                    <label class="ap-avatar-opt">
                        <input type="radio" name="avatar" value="girl" {{ $currentAvatar === 'girl' ? 'checked' : '' }}>
                        <div class="ap-avatar-svg-wrap">
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
                        <span class="ap-avatar-label">Girl</span>
                    </label>
                </div>

                <div class="ap-foot" style="margin-top:1.2rem;">
                    <button type="submit" class="ap-btn-save">
                        <i class="fa-solid fa-palette"></i> Save Avatar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card-head">
            <h3><i class="fa-solid fa-id-card" style="color:#155f8f;"></i>Account Information</h3>
            <p>Update your profile details.</p>
        </div>
        <div class="ap-card-body">
            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_mode" value="profile">
                <input type="hidden" name="avatar" value="{{ $currentAvatar }}">

                <div class="ap-grid">
                    <div class="ap-field">
                        <label for="ap-name">Full Name</label>
                        <input id="ap-name" name="name" type="text" class="ap-input" value="{{ old('name', $user->name) }}" placeholder="Enter full name" required>
                    </div>
                    <div class="ap-field">
                        <label for="ap-username">Username</label>
                        <input id="ap-username" name="username" type="text" class="ap-input" value="{{ old('username', $user->username) }}" placeholder="Enter username" autocomplete="off">
                    </div>
                    <div class="ap-field">
                        <label for="ap-email">Email Address</label>
                        <input id="ap-email" name="email" type="email" class="ap-input" value="{{ old('email', $user->email) }}" placeholder="Enter email address" required>
                    </div>
                    <div class="ap-field">
                        <label>Role</label>
                        <input type="text" class="ap-input" value="{{ $user->roleLabel() }}" readonly>
                    </div>
                    <div class="ap-field">
                        <label>Department</label>
                        <input type="text" class="ap-input" value="{{ ucfirst((string) ($user->department ?: 'Administrator')) }}" readonly>
                    </div>
                </div>

                <div class="ap-foot">
                    <button type="submit" class="ap-btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card-head">
            <h3><i class="fa-solid fa-lock" style="color:#155f8f;"></i>Change Password</h3>
            <p>Update your admin password securely.</p>
        </div>
        <div class="ap-card-body">
            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_mode" value="password">

                <div class="ap-grid">
                    <div class="ap-field">
                        <label for="ap-current-password">Current Password</label>
                        <div class="ap-password-wrap">
                            <input id="ap-current-password" name="current_password" type="password" class="ap-input" placeholder="Enter current password" required autocomplete="current-password">
                            <button type="button" class="ap-password-toggle" data-toggle-password="ap-current-password" aria-label="Show current password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="ap-field">
                        <label for="ap-new-password">New Password</label>
                        <div class="ap-password-wrap">
                            <input id="ap-new-password" name="password" type="password" class="ap-input" placeholder="Enter new password" required autocomplete="new-password">
                            <button type="button" class="ap-password-toggle" data-toggle-password="ap-new-password" aria-label="Show new password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="ap-field">
                        <label for="ap-new-password-confirmation">Confirm New Password</label>
                        <div class="ap-password-wrap">
                            <input id="ap-new-password-confirmation" name="password_confirmation" type="password" class="ap-input" placeholder="Confirm new password" required autocomplete="new-password">
                            <button type="button" class="ap-password-toggle" data-toggle-password="ap-new-password-confirmation" aria-label="Show confirm password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <p class="ap-password-note">Use at least 8 characters for your new password.</p>

                <div class="ap-foot">
                    <button type="submit" class="ap-btn-save">
                        <i class="fa-solid fa-key"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const buttons = document.querySelectorAll('[data-toggle-password]');
        if (!buttons.length) return;

        buttons.forEach((button) => {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-toggle-password');
                if (!targetId) return;

                const input = document.getElementById(targetId);
                if (!input) return;

                const makeVisible = input.type === 'password';
                input.type = makeVisible ? 'text' : 'password';

                const icon = button.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye', !makeVisible);
                    icon.classList.toggle('fa-eye-slash', makeVisible);
                }
            });
        });
    })();
</script>
@endsection
