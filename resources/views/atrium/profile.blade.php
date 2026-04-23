@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')

@php
    /** @var \App\Models\User $user */
    $currentAvatar = $user->avatar ?? 'boy';
@endphp

<style>
    .atr-profile-wrap {
        max-width: 980px;
        margin: 0 auto;
        display: grid;
        gap: 16px;
    }
    .atr-profile-alert-ok {
        border-radius: 10px;
        border: 1px solid #a7f3d0;
        background: #ecfdf5;
        color: #065f46;
        padding: .85rem 1.1rem;
        font-size: .9rem;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }
    .atr-profile-alert-err {
        border-radius: 10px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
        padding: .85rem 1.1rem;
        font-size: .9rem;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }
    .atr-profile-banner {
        border: 1px solid var(--atr-border);
        border-radius: 14px;
        background: linear-gradient(135deg, #f0f7ff 0%, #e8f2fb 45%, #f8fbff 100%);
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .atr-profile-banner-main {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .atr-profile-avatar-lg {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 2px solid #dbeafe;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 6px rgba(2, 6, 23, .08);
    }
    .atr-profile-avatar-lg svg {
        width: 56px;
        height: 56px;
        border-radius: 50%;
    }
    .atr-profile-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 800;
    }
    .atr-profile-sub {
        margin: 2px 0 0;
        color: #64748b;
        font-size: .84rem;
        font-weight: 600;
    }
    .atr-profile-chip {
        padding: .26rem .64rem;
        border-radius: 999px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }
    .atr-avatar-grid {
        display: flex;
        gap: 22px;
        justify-content: center;
        flex-wrap: wrap;
        padding: .2rem 0 .35rem;
    }
    .atr-avatar-item {
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 9px;
    }
    .atr-avatar-item input[type=radio] {
        display: none;
    }
    .atr-avatar-frame {
        width: 104px;
        height: 104px;
        border-radius: 50%;
        border: 3px solid #e2e8f0;
        padding: 4px;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: border-color .2s, box-shadow .2s, transform .2s;
    }
    .atr-avatar-frame svg {
        width: 88px;
        height: 88px;
        border-radius: 50%;
    }
    .atr-avatar-item:hover .atr-avatar-frame {
        border-color: #93c5fd;
        transform: translateY(-2px);
    }
    .atr-avatar-item input:checked ~ .atr-avatar-frame {
        border-color: var(--atr-primary);
        box-shadow: 0 0 0 4px rgba(15,95,168,.14);
    }
    .atr-avatar-label {
        font-size: .8rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .atr-avatar-item input:checked ~ .atr-avatar-frame + .atr-avatar-label {
        color: var(--atr-primary);
    }
    .atr-profile-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
    }
    .atr-profile-field {
        display: grid;
        gap: 6px;
    }
    .atr-profile-field label {
        font-size: .8rem;
        font-weight: 800;
        color: var(--atr-head);
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .atr-profile-actions {
        display: flex;
        justify-content: flex-end;
        padding-top: 1rem;
        margin-top: .4rem;
        border-top: 1px solid #eef2f7;
        gap: 8px;
        flex-wrap: wrap;
    }
    @media (max-width: 640px) {
        .atr-profile-banner {
            align-items: flex-start;
        }
        .atr-profile-chip {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="atr atr-profile-wrap" data-server-rendered-page="profile" data-page-title="Atrium Profile">
    <section class="atr-hero">
        <div>
            <h2><i class="fa-solid fa-user-gear" style="margin-right:8px;opacity:.88;"></i>Atrium Profile</h2>
            <p>Manage your account details and pick a cool avatar for your Atrium workspace.</p>
        </div>
    </section>

    @if (session('status'))
        <div class="atr-profile-alert-ok"><i class="fa-solid fa-circle-check"></i>{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="atr-profile-alert-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $errors->first() }}</div>
    @endif

    <section class="atr-profile-banner">
        <div class="atr-profile-banner-main">
            <div class="atr-profile-avatar-lg">
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
            <div>
                <h3 class="atr-profile-title">{{ $user->name }}</h3>
                <p class="atr-profile-sub">{{ $user->roleLabel() }} - {{ ucfirst((string) $user->department) }}</p>
            </div>
        </div>
        <span class="atr-profile-chip">Atrium Personnel</span>
    </section>

    <section class="atr-card">
        <div class="atr-card-head">
            <h3><i class="fa-solid fa-palette" style="color:var(--atr-primary);"></i>Avatar Style</h3>
            <span>Displayed in sidebar and top-right profile menu</span>
        </div>
        <div class="atr-card-body">
            <form action="{{ route('atrium.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_mode" value="avatar">
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">
                <input type="hidden" name="username" value="{{ $user->username }}">

                <div class="atr-avatar-grid">
                    <label class="atr-avatar-item">
                        <input type="radio" name="avatar" value="boy" {{ $currentAvatar === 'boy' ? 'checked' : '' }}>
                        <div class="atr-avatar-frame">
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
                        <span class="atr-avatar-label">Boy</span>
                    </label>

                    <label class="atr-avatar-item">
                        <input type="radio" name="avatar" value="girl" {{ $currentAvatar === 'girl' ? 'checked' : '' }}>
                        <div class="atr-avatar-frame">
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
                        <span class="atr-avatar-label">Girl</span>
                    </label>
                </div>

                <div class="atr-profile-actions">
                    <button type="submit" class="atr-btn-primary"><i class="fa-solid fa-wand-magic-sparkles"></i> Save Avatar</button>
                </div>
            </form>
        </div>
    </section>

    <section class="atr-card">
        <div class="atr-card-head">
            <h3><i class="fa-solid fa-id-card" style="color:var(--atr-primary);"></i>Account Details</h3>
            <span>Update your display name, username, and email</span>
        </div>
        <div class="atr-card-body">
            <form action="{{ route('atrium.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_mode" value="profile">
                <input type="hidden" name="avatar" value="{{ $currentAvatar }}">

                <div class="atr-profile-grid">
                    <div class="atr-profile-field">
                        <label for="atrName">Full Name</label>
                        <input id="atrName" name="name" type="text" class="atr-input" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="atr-profile-field">
                        <label for="atrUsername">Username</label>
                        <input id="atrUsername" name="username" type="text" class="atr-input" value="{{ old('username', $user->username) }}" autocomplete="off">
                    </div>
                    <div class="atr-profile-field">
                        <label for="atrEmail">Email Address</label>
                        <input id="atrEmail" name="email" type="email" class="atr-input" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="atr-profile-field">
                        <label>Department</label>
                        <input type="text" class="atr-input" value="{{ ucfirst((string) $user->department) }}" readonly>
                    </div>
                    <div class="atr-profile-field">
                        <label>Role</label>
                        <input type="text" class="atr-input" value="{{ $user->roleLabel() }}" readonly>
                    </div>
                </div>

                <div class="atr-profile-actions">
                    <button type="submit" class="atr-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
