@extends('layouts.app')

@section('content')
@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\SystemRole> $roles */
    /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, \App\Models\SystemPermission>> $permissions */
    /** @var \Illuminate\Support\Collection<int, \App\Models\User> $users */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Department> $departments */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Department> $collectorDepartments */
    /** @var \Illuminate\Support\Collection<int, \App\Models\RoleAuditLog> $recentAuditLogs */

    $manageableRoles = $roles
        ->filter(static fn (\App\Models\SystemRole $role): bool => ! $role->isAdministrator() && $role->guard_name !== 'cashier')
        ->values();
    $selectedRoleId = (int) request('role', 0);
    $selectedRole = $selectedRoleId > 0
        ? $manageableRoles->firstWhere('id', $selectedRoleId)
        : null;
    $selectedPermissionIds = $selectedRole
        ? $selectedRole->permissions->pluck('id')->map(static fn ($id): int => (int) $id)->all()
        : [];
    $permissionPrefixes = [];
    if ($selectedRole) {
        if ($selectedRole->guard_name === 'personnel' && $selectedRole->department_scope) {
            $permissionPrefixes[] = (string) $selectedRole->department_scope;
        } elseif ($selectedRole->guard_name === 'collector') {
            $permissionPrefixes[] = 'collector';
        } elseif ($selectedRole->guard_name === 'cashier') {
            $permissionPrefixes[] = 'cashier';
        } elseif ($selectedRole->guard_name === 'admin') {
            $permissionPrefixes[] = 'admin';
        }
    }
    $matrixPermissions = $permissions
        ->map(static function (\Illuminate\Support\Collection $modulePermissions) use ($permissionPrefixes): \Illuminate\Support\Collection {
            if ($permissionPrefixes === []) {
                return $modulePermissions->values();
            }

            return $modulePermissions
                ->filter(static function ($permission) use ($permissionPrefixes): bool {
                    $key = (string) ($permission->key ?? '');
                    foreach ($permissionPrefixes as $prefix) {
                        if (str_starts_with($key, $prefix . '.')) {
                            return true;
                        }
                    }

                    return false;
                })
                ->values();
        })
        ->filter(static fn (\Illuminate\Support\Collection $modulePermissions): bool => $modulePermissions->isNotEmpty());
    $guardLabels = [
        'admin' => 'Administrator',
        'personnel' => 'Department Personnel',
        'collector' => 'Collector',
        'cashier' => 'Cashier',
    ];
    $departmentLabels = $departments->pluck('name', 'code');
@endphp

<div class="rpm-page" data-server-rendered-page="roles" data-page-title="Roles & Permissions">
    @if (session('status'))
        <div class="rpm-alert rpm-alert-success rpm-toast" data-autohide-ms="3000"><i class="fas fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif

    @if (session('error'))
        <div class="rpm-alert rpm-alert-error"><i class="fas fa-triangle-exclamation"></i><span>{{ session('error') }}</span></div>
    @endif

    @if ($errors->any())
        <div class="rpm-alert rpm-alert-error"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <section class="rpm-grid">
        <aside class="rpm-panel rpm-role-panel">
            <div class="rpm-panel-head">
                <div>
                    <h3>System Roles</h3>
                    <p>Select a role to manage its settings and permissions.</p>
                </div>
            </div>

            <div class="rpm-role-list">
                @foreach ($manageableRoles as $role)
                    @php
                        $roleIsSelected = $selectedRole && $selectedRole->id === $role->id;
                        $scopeLabel = $role->department_scope
                            ? ($departmentLabels[$role->department_scope] ?? ucfirst($role->department_scope))
                            : ($guardLabels[$role->guard_name] ?? ucfirst($role->guard_name));
                    @endphp
                    <a class="rpm-role-item {{ $roleIsSelected ? 'is-active' : '' }}" href="{{ route('admin.roles', ['role' => $role->id]) }}">
                        <span class="rpm-role-icon"><i class="fas {{ $role->isAdministrator() ? 'fa-shield-halved' : ($role->guard_name === 'collector' ? 'fa-hand-holding-dollar' : 'fa-id-badge') }}"></i></span>
                        <span class="rpm-role-main">
                            <strong>{{ $role->name }}</strong>
                            <small>{{ $scopeLabel }} · {{ $role->permissions->count() }} permissions</small>
                        </span>
                        <span class="rpm-role-badges">
                            @if ($role->is_system)
                                <em>System</em>
                            @endif
                            <b class="{{ $role->is_active ? 'is-on' : 'is-off' }}">{{ $role->is_active ? 'Active' : 'Off' }}</b>
                        </span>
                    </a>
                @endforeach
            </div>

        </aside>

        <main class="rpm-main">
            @if ($selectedRole)
                <section class="rpm-panel">
                    <div class="rpm-panel-head rpm-panel-head-row">
                        <div>
                            <h3>Permission Matrix</h3>
                            <p>Toggle what this role can view, create, update, approve, collect, or export.</p>
                        </div>
                        @if ($selectedRole->isAdministrator())
                            <span class="rpm-chip is-good">Full Access Locked</span>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('admin.roles.permissions.update', $selectedRole) }}">
                        @csrf
                        @method('PUT')
                        @if ($matrixPermissions->isNotEmpty())
                            <div class="rpm-permission-grid {{ $matrixPermissions->count() === 1 ? 'rpm-permission-grid-single' : '' }}">
                                @foreach ($matrixPermissions as $module => $modulePermissions)
                                    <article class="rpm-permission-card">
                                        <header>
                                            <h4>{{ $module }}</h4>
                                            <button type="button" class="rpm-link-btn" data-toggle-module="{{ \Illuminate\Support\Str::slug($module) }}" {{ $selectedRole->isAdministrator() ? 'disabled' : '' }}>Toggle</button>
                                        </header>
                                        <div class="rpm-permission-list" data-module="{{ \Illuminate\Support\Str::slug($module) }}">
                                            @foreach ($modulePermissions as $permission)
                                                <label class="rpm-check">
                                                    <input
                                                        type="checkbox"
                                                        name="permission_ids[]"
                                                        value="{{ $permission->id }}"
                                                        @checked($selectedRole->isAdministrator() || in_array((int) $permission->id, $selectedPermissionIds, true))
                                                        {{ $selectedRole->isAdministrator() ? 'disabled' : '' }}
                                                    >
                                                    <span>
                                                        <strong>{{ $permission->label }}</strong>
                                                        <small>{{ $permission->key }}</small>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="rpm-empty">No permissions mapped for this selected role.</div>
                        @endif
                        <div class="rpm-actions">
                            <button type="submit" class="rpm-btn rpm-btn-primary" {{ $selectedRole->isAdministrator() ? 'disabled' : '' }}>
                                <i class="fas fa-shield-halved"></i>Save Permission Matrix
                            </button>
                        </div>
                    </form>
                </section>
            @else
                <section class="rpm-panel">
                    <div class="rpm-panel-head">
                        <h3>Permission Matrix</h3>
                        <p>Select a department role from the left list to load role permissions.</p>
                    </div>
                    <div class="rpm-empty">No role selected yet.</div>
                </section>
            @endif

            <section class="rpm-panel">
                <div class="rpm-panel-head">
                    <h3>Role Audit History</h3>
                    <p>Recent role, permission, and assignment changes made by administrators.</p>
                </div>
                <div class="rpm-audit-list">
                    @forelse ($recentAuditLogs as $log)
                        <article class="rpm-audit-item">
                            <span class="rpm-audit-icon"><i class="fas fa-clock-rotate-left"></i></span>
                            <div>
                                <strong>{{ \Illuminate\Support\Str::headline($log->action) }}</strong>
                                <p>
                                    {{ $log->actor?->name ?? 'System' }}
                                    @if ($log->targetUser)
                                        updated {{ $log->targetUser->name }}
                                    @elseif ($log->role)
                                        updated {{ $log->role->name }}
                                    @endif
                                </p>
                                @if ($log->notes)
                                    <small>{{ $log->notes }}</small>
                                @endif
                            </div>
                            <time>{{ $log->created_at?->format('M d, Y h:i A') }}</time>
                        </article>
                    @empty
                        <div class="rpm-empty">No audit events yet.</div>
                    @endforelse
                </div>
            </section>
        </main>
    </section>
</div>

<style>
    .topbar {
        height: 56px;
        padding: 0 16px;
    }

    #contentArea,
    .content-area {
        padding-top: 10px;
    }

    .rpm-page {
        --rpm-ink: #0b1a2c;
        --rpm-ink-soft: #2a3e57;
        --rpm-muted: #6b7d93;
        --rpm-line: #e3eaf3;
        --rpm-line-strong: #cfdae6;
        --rpm-soft: #f6f9fd;
        --rpm-softer: #fafcfe;
        --rpm-panel: #ffffff;
        --rpm-primary: #2563eb;
        --rpm-primary-dark: #1d4ed8;
        --rpm-good: #0f8a5f;
        --rpm-danger: #b1342f;
        --rpm-radius: 14px;
        --rpm-radius-sm: 10px;
        --rpm-shadow-sm: 0 1px 2px rgba(15, 35, 60, 0.04);
        --rpm-shadow-md: 0 4px 14px rgba(15, 35, 60, 0.06);
        max-width: 1480px;
        margin: 0 auto;
        padding: 10px 0 16px;
        display: grid;
        gap: 10px;
        color: var(--rpm-ink);
    }

    .rpm-panel {
        background: var(--rpm-panel);
        border: 1px solid var(--rpm-line);
        border-radius: var(--rpm-radius);
        box-shadow: var(--rpm-shadow-sm);
    }

    .rpm-panel h3,
    .rpm-create-form h4 {
        margin: 0;
        font-weight: 850;
        letter-spacing: -0.005em;
    }

    .rpm-panel-head p {
        margin: 6px 0 0;
        color: var(--rpm-muted);
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .rpm-table small,
    .rpm-audit-item small {
        display: block;
        color: var(--rpm-muted);
        font-size: 0.69rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .rpm-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        border-radius: var(--rpm-radius-sm);
        padding: 14px 16px;
        font-weight: 700;
        box-shadow: var(--rpm-shadow-sm);
    }

    .rpm-alert i {
        font-size: 1.05rem;
    }

    .rpm-alert-success {
        color: var(--rpm-good);
        background: linear-gradient(180deg, #f0fbf5, #e6f7ee);
        border: 1px solid #b5e4cc;
    }

    .rpm-alert-error {
        color: var(--rpm-danger);
        background: linear-gradient(180deg, #fff5f4, #ffeceb);
        border: 1px solid #f3c0bf;
    }

    .rpm-toast {
        position: fixed;
        top: 86px;
        right: 14px;
        z-index: 1200;
        width: min(360px, calc(100vw - 22px));
        min-height: 40px;
        padding: 10px 12px;
        border-radius: 10px;
        box-shadow: 0 10px 26px rgba(15, 35, 60, 0.18);
        animation: rpmToastIn 0.18s ease;
    }

    .rpm-toast.is-hiding {
        opacity: 0;
        transform: translateX(8px);
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    @keyframes rpmToastIn {
        from {
            opacity: 0;
            transform: translateX(10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .rpm-grid {
        display: grid;
        grid-template-columns: minmax(300px, 360px) minmax(0, 1fr);
        gap: 10px;
        align-items: start;
    }

    .rpm-role-panel {
        position: static;
        top: auto;
        display: flex;
        flex-direction: column;
        max-height: none;
        border: 1px solid #bcd0e4;
        background: #dfeaf6;
        border-radius: 16px;
        padding: 10px;
        box-shadow: none;
    }

    .rpm-role-panel .rpm-panel-head {
        flex-shrink: 0;
    }

    .rpm-role-panel .rpm-create-form {
        flex-shrink: 0;
    }

    .rpm-role-panel .rpm-role-list {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        padding-right: 4px;
        scrollbar-gutter: stable;
    }

    .rpm-role-panel .rpm-role-list::-webkit-scrollbar {
        width: 6px;
    }

    .rpm-role-panel .rpm-role-list::-webkit-scrollbar-thumb {
        background: var(--rpm-line-strong);
        border-radius: 999px;
    }

    .rpm-role-panel .rpm-role-list::-webkit-scrollbar-track {
        background: transparent;
    }

    .rpm-main {
        display: grid;
        gap: 10px;
    }

    .rpm-panel {
        overflow: hidden;
        transition: box-shadow 0.18s ease;
    }

    .rpm-panel:hover {
        box-shadow: var(--rpm-shadow-md);
    }

    .rpm-panel-head {
        padding: 10px;
        border-bottom: 1px solid var(--rpm-line);
        background: #ffffff;
    }

    .rpm-role-panel .rpm-panel-head {
        border-bottom: 0;
        background: transparent;
        padding: 2px 2px 8px;
    }

    .rpm-panel-head h3 {
        position: relative;
        padding-left: 12px;
        font-size: 1.05rem;
    }

    .rpm-panel-head h3::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.2rem;
        bottom: 0.2rem;
        width: 3px;
        border-radius: 3px;
        background: linear-gradient(180deg, var(--rpm-primary), #60a5fa);
    }

    .rpm-panel-head-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .rpm-role-list {
        display: grid;
        gap: 8px;
        padding: 0 0 2px;
    }

    .rpm-role-item {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
        padding: 10px;
        color: inherit;
        text-decoration: none;
        border: 1px solid #c7d7e9;
        border-radius: var(--rpm-radius-sm);
        background: #f4f8fd;
        transition: transform 0.14s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        position: relative;
    }

    .rpm-role-item::before {
        content: '';
        position: absolute;
        left: -1px;
        top: 50%;
        transform: translateY(-50%) scaleY(0);
        width: 3px;
        height: 60%;
        border-radius: 0 3px 3px 0;
        background: var(--rpm-primary);
        transition: transform 0.18s ease;
    }

    .rpm-role-item:hover {
        background: #ffffff;
        border-color: #c3d6e9;
        color: inherit;
    }

    .rpm-role-item.is-active {
        background: #ffffff;
        border-color: #9cbce0;
        color: inherit;
        box-shadow: 0 1px 2px rgba(15, 35, 60, 0.08);
    }

    .rpm-role-item.is-active::before {
        transform: translateY(-50%) scaleY(1);
    }

    .rpm-role-icon,
    .rpm-audit-icon {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        color: var(--rpm-primary-dark);
        background: #d6e2f3;
        flex-shrink: 0;
        transition: transform 0.18s ease;
    }

    .rpm-role-item:hover .rpm-role-icon,
    .rpm-role-item.is-active .rpm-role-icon {
        transform: scale(1.05);
    }

    .rpm-role-main {
        min-width: 0;
    }

    .rpm-role-main strong,
    .rpm-role-main small {
        display: block;
    }

    .rpm-role-main strong {
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--rpm-ink);
        line-height: 1.25;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .rpm-role-main small {
        color: var(--rpm-muted);
        margin-top: 3px;
        font-size: 0.76rem;
        line-height: 1.35;
    }

    .rpm-role-badges {
        display: grid;
        gap: 4px;
        justify-items: end;
        font-size: 0.6rem;
        text-transform: uppercase;
        font-weight: 850;
        align-self: start;
    }

    .rpm-role-badges em,
    .rpm-role-badges b,
    .rpm-status,
    .rpm-chip {
        font-style: normal;
        border-radius: 999px;
        padding: 2px 8px;
        border: 1px solid var(--rpm-line);
        background: #fff;
        color: #42526b;
        white-space: nowrap;
        letter-spacing: 0.04em;
        line-height: 1.4;
    }

    .rpm-role-badges .is-on,
    .rpm-status.is-on,
    .rpm-chip.is-good {
        border-color: #b5e4cc;
        background: #e6f7ee;
        color: var(--rpm-good);
    }

    .rpm-role-badges .is-off,
    .rpm-status.is-off,
    .rpm-chip.is-muted {
        border-color: #f3c0bf;
        background: #ffeceb;
        color: var(--rpm-danger);
    }

    .rpm-create-form,
    .rpm-settings-form,
    .rpm-assign-form {
        padding: 10px;
        display: grid;
        gap: 10px;
    }

    .rpm-create-form {
        border-top: 1px solid var(--rpm-line);
        background: var(--rpm-softer);
    }

    .rpm-create-form h4 {
        font-size: 0.95rem;
        position: relative;
        padding-left: 12px;
    }

    .rpm-create-form h4::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.18rem;
        bottom: 0.18rem;
        width: 3px;
        border-radius: 3px;
        background: linear-gradient(180deg, var(--rpm-primary), #60a5fa);
    }

    .rpm-form-grid,
    .rpm-assign-form {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .rpm-form-two {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .rpm-page label {
        display: grid;
        gap: 5px;
    }

    .rpm-page label > span:not(.rpm-check span):not(.rpm-switch span),
    .rpm-switch-field > span {
        color: var(--rpm-ink-soft);
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .rpm-check > span {
        text-transform: none !important;
        font-size: inherit !important;
        font-weight: inherit !important;
        letter-spacing: 0 !important;
        color: inherit !important;
    }

    .rpm-page input,
    .rpm-page select,
    .rpm-page textarea {
        width: 100%;
        min-height: 42px;
        border: 1px solid var(--rpm-line);
        border-radius: var(--rpm-radius-sm);
        padding: 10px 12px;
        background: var(--rpm-soft);
        color: var(--rpm-ink);
        font-size: 0.92rem;
        outline: none;
        transition: background 0.14s ease, border-color 0.14s ease, box-shadow 0.14s ease;
    }

    .rpm-page input:hover,
    .rpm-page select:hover,
    .rpm-page textarea:hover {
        border-color: var(--rpm-line-strong);
    }

    .rpm-page textarea {
        resize: vertical;
        min-height: 80px;
    }

    .rpm-page input:focus,
    .rpm-page select:focus,
    .rpm-page textarea:focus {
        background: #ffffff;
        border-color: var(--rpm-primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.14);
    }

    .rpm-span-2 {
        grid-column: span 2;
    }

    .rpm-span-3 {
        grid-column: 1 / -1;
    }

    .rpm-switch {
        display: inline-flex !important;
        grid-template-columns: auto auto;
        align-items: center;
        gap: 9px !important;
        min-height: 40px;
    }

    .rpm-switch input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 1px;
    }

    .rpm-switch span {
        width: 46px;
        height: 26px;
        border-radius: 999px;
        background: #cbd7e4;
        position: relative;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.06);
        transition: background 0.2s ease;
    }

    .rpm-switch span::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 2px 5px rgba(12, 38, 63, 0.22);
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .rpm-switch input:checked + span {
        background: linear-gradient(180deg, #14a574, var(--rpm-good));
    }

    .rpm-switch input:checked + span::after {
        transform: translateX(20px);
    }

    .rpm-switch b {
        font-size: 0.86rem;
        font-weight: 700;
        color: var(--rpm-ink-soft);
    }

    .rpm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        padding: 10px;
        border-top: 1px solid var(--rpm-line);
        background: #ffffff;
    }

    .rpm-btn {
        min-height: 44px;
        border-radius: var(--rpm-radius-sm);
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 18px;
        font-weight: 800;
        font-size: 0.92rem;
        cursor: pointer;
        transition: transform 0.12s ease, box-shadow 0.18s ease, background 0.18s ease, border-color 0.18s ease;
    }

    .rpm-btn-primary {
        background: var(--sidebar-bg, #155e8f);
        border-color: var(--sidebar-bg, #155e8f);
        color: #ffffff;
        box-shadow: 0 5px 12px rgba(21, 94, 143, 0.25);
    }

    .rpm-btn-primary:hover {
        background: #124f78;
        border-color: #124f78;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 7px 16px rgba(18, 79, 120, 0.3);
    }

    .rpm-btn-secondary {
        background: #ffffff;
        border-color: var(--rpm-line-strong);
        color: var(--rpm-ink);
    }

    .rpm-btn-secondary:hover {
        background: var(--rpm-soft);
        border-color: var(--rpm-primary);
        color: var(--rpm-primary);
        transform: translateY(-1px);
    }

    .rpm-btn:disabled,
    .rpm-link-btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none !important;
    }

    .rpm-permission-grid {
        padding: 10px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 12px;
        align-items: start;
    }

    .rpm-permission-grid.rpm-permission-grid-single {
        grid-template-columns: minmax(0, 1fr);
    }

    .rpm-permission-grid.rpm-permission-grid-single .rpm-permission-list {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .rpm-permission-grid.rpm-permission-grid-single .rpm-check {
        border-right: 1px solid #f1f4f8;
    }

    .rpm-permission-grid.rpm-permission-grid-single .rpm-check:nth-child(2n) {
        border-right: 0;
    }

    .rpm-permission-card {
        border: 1px solid var(--rpm-line);
        border-radius: var(--rpm-radius-sm);
        overflow: hidden;
        background: #ffffff;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .rpm-permission-card:hover {
        border-color: var(--rpm-line-strong);
        box-shadow: var(--rpm-shadow-sm);
    }

    .rpm-permission-card header {
        display: flex;
        justify-content: flex-start;
        gap: 10px;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid var(--rpm-line);
        background: linear-gradient(180deg, #f7fafd 0%, #eef3f9 100%);
    }

    .rpm-permission-card h4 {
        margin: 0;
        font-size: 0.84rem;
        font-weight: 850;
        color: var(--rpm-ink);
        letter-spacing: -0.005em;
    }

    .rpm-link-btn {
        margin-left: auto;
        min-height: 30px;
        border: 1px solid rgba(21, 94, 143, 0.18);
        background: var(--sidebar-bg, #155e8f);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 6px 12px;
        border-radius: 999px;
        cursor: pointer;
        transition: background 0.18s ease, transform 0.12s ease;
        box-shadow: 0 4px 10px rgba(21, 94, 143, 0.2);
    }

    .rpm-link-btn:hover:not(:disabled) {
        background: #124f78;
        transform: translateY(-1px);
    }

    .rpm-link-btn:disabled {
        background: #9cb8cb;
        border-color: #9cb8cb;
        color: #f8fbff;
        box-shadow: none;
    }

    .rpm-permission-list {
        display: grid;
    }

    .rpm-check {
        display: grid !important;
        grid-template-columns: auto 1fr;
        gap: 10px !important;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #eef2f7;
        cursor: pointer;
        transition: background 0.14s ease;
    }

    .rpm-check:hover {
        background: var(--rpm-softer);
    }

    .rpm-check:last-child {
        border-bottom: 0;
    }

    .rpm-permission-grid.rpm-permission-grid-single .rpm-check:last-child {
        border-bottom: 1px solid #f1f4f8;
    }

    .rpm-check input {
        width: 16px;
        min-height: auto;
        accent-color: var(--rpm-primary);
        align-self: center;
    }

    .rpm-check strong,
    .rpm-check small {
        display: block;
    }

    .rpm-check strong {
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--rpm-ink);
        text-transform: none;
        letter-spacing: 0;
        line-height: 1.25;
    }

    .rpm-check small {
        margin-top: 2px;
        color: var(--rpm-muted);
        font-size: 0.7rem;
        font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
        text-transform: lowercase;
        letter-spacing: 0;
        line-height: 1.2;
    }

    .rpm-check input:checked ~ span strong {
        color: var(--rpm-primary-dark);
    }

    @media (max-width: 920px) {
        .rpm-permission-grid.rpm-permission-grid-single .rpm-permission-list {
            grid-template-columns: 1fr;
        }

        .rpm-permission-grid.rpm-permission-grid-single .rpm-check {
            border-right: 0;
        }

        .rpm-permission-grid.rpm-permission-grid-single .rpm-check:last-child {
            border-bottom: 0;
        }
    }

    .rpm-table-wrap {
        overflow-x: auto;
        padding: 0 4px 4px;
    }

    .rpm-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 820px;
    }

    .rpm-table th {
        text-align: left;
        padding: 13px 16px;
        color: #4a5e76;
        background: linear-gradient(180deg, #f7fafd, #eef3f9);
        border-bottom: 1px solid var(--rpm-line);
        font-size: 0.7rem;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .rpm-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: top;
        font-size: 0.9rem;
        color: var(--rpm-ink-soft);
    }

    .rpm-table tbody tr {
        transition: background 0.14s ease;
    }

    .rpm-table tbody tr:hover td {
        background: var(--rpm-softer);
    }

    .rpm-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .rpm-table strong {
        color: var(--rpm-ink);
        font-weight: 800;
    }

    .rpm-table code {
        border: 1px solid var(--rpm-line);
        border-radius: 6px;
        padding: 2px 7px;
        background: rgba(37, 99, 235, 0.06);
        color: var(--rpm-primary-dark);
        font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .rpm-audit-list {
        display: grid;
    }

    .rpm-audit-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 10px;
        align-items: start;
        padding: 10px;
        border-bottom: 1px solid var(--rpm-line);
        transition: background 0.14s ease, border-color 0.14s ease;
    }

    .rpm-audit-item:hover {
        background: var(--rpm-softer);
        border-color: var(--rpm-line-strong);
    }

    .rpm-audit-item:last-child {
        border-bottom: 0;
    }

    .rpm-audit-item strong,
    .rpm-audit-item p {
        display: block;
        margin: 0;
    }

    .rpm-audit-item strong {
        color: var(--rpm-ink);
        font-weight: 800;
    }

    .rpm-audit-item p {
        margin-top: 4px;
        color: var(--rpm-muted);
        font-size: 0.85rem;
        line-height: 1.5;
    }

    .rpm-audit-item time {
        color: var(--rpm-muted);
        font-size: 0.76rem;
        font-weight: 700;
        white-space: nowrap;
        padding-top: 2px;
    }

    .rpm-empty {
        padding: 10px;
        color: var(--rpm-muted);
        text-align: center;
        font-size: 0.92rem;
    }

    #assignmentHelp {
        color: var(--rpm-muted);
        font-size: 0.76rem;
    }

    @media (min-width: 1500px) {
        .rpm-permission-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
    }

    @media (max-width: 1120px) {
        .rpm-grid {
            grid-template-columns: 1fr;
        }

        .rpm-role-panel {
            position: static;
            max-height: none;
        }

        .rpm-form-grid,
        .rpm-assign-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .rpm-span-3 {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 720px) {
        .rpm-topbar,
        .rpm-panel-head-row {
            align-items: stretch;
        }

        .rpm-form-grid,
        .rpm-form-two,
        .rpm-assign-form {
            grid-template-columns: 1fr;
        }

        .rpm-span-2,
        .rpm-span-3 {
            grid-column: auto;
        }

        .rpm-role-item,
        .rpm-audit-item {
            grid-template-columns: auto 1fr;
        }

        .rpm-role-badges,
        .rpm-audit-item time {
            grid-column: 2;
            justify-self: start;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.rpm-toast[data-autohide-ms]').forEach(function (toast) {
        const delay = Number(toast.dataset.autohideMs || 3000);
        window.setTimeout(function () {
            toast.classList.add('is-hiding');
            window.setTimeout(function () {
                toast.remove();
            }, 220);
        }, delay);
    });

    document.querySelectorAll('[data-toggle-module]').forEach(function (button) {
        button.addEventListener('click', function () {
            const moduleKey = button.dataset.toggleModule;
            const checkboxes = Array.from(document.querySelectorAll('[data-module="' + moduleKey + '"] input[type="checkbox"]:not(:disabled)'));
            if (checkboxes.length === 0) return;

            const shouldCheck = checkboxes.some(function (checkbox) {
                return !checkbox.checked;
            });

            checkboxes.forEach(function (checkbox) {
                checkbox.checked = shouldCheck;
            });
        });
    });

    const roleSelect = document.getElementById('assignmentRoleSelect');
    const departmentSelect = document.getElementById('assignmentDepartmentSelect');
    const helpText = document.getElementById('assignmentHelp');

    function syncAssignmentHelp() {
        if (!roleSelect || !departmentSelect || !helpText) return;

        const selected = roleSelect.options[roleSelect.selectedIndex];
        const guard = selected ? selected.dataset.guard : '';
        const scope = selected ? selected.dataset.scope : '';

        Array.from(departmentSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            if (guard === 'collector') {
                option.hidden = option.dataset.collector !== '1';
                return;
            }

            if (scope) {
                option.hidden = option.dataset.code !== scope;
                return;
            }

            option.hidden = guard !== 'personnel' && guard !== 'collector';
        });

        if (guard === 'collector') {
            helpText.textContent = 'Choose the department queue this collector can receive from.';
            return;
        }

        if (scope) {
            helpText.textContent = 'This role is locked to its department scope.';
            return;
        }

        helpText.textContent = guard === 'personnel'
            ? 'Choose the department this personnel account can access.'
            : 'No department is required for this role.';
    }

    roleSelect && roleSelect.addEventListener('change', syncAssignmentHelp);
    syncAssignmentHelp();
});
</script>
@endsection

