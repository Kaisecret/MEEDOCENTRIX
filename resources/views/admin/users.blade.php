@extends('layouts.app')

@section('content')
@php
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $collectorAccounts */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Department> $collectorDepartments */
    /** @var array<int, string> $departments */
    /** @var int $assignedCollectorCount */
    /** @var bool $hasCollectorSchema */

    $departmentLabels = [
        'fishport' => 'Fishport',
        'market' => 'Public Market',
        'cemetery' => 'Cemetery',
        'terminal' => 'Terminal',
        'atrium' => 'Atrium',
        'collector' => 'Collector',
        'cashier' => 'Cashier',
    ];

    $unassignedCollectorCount = max($collectorAccounts->count() - $assignedCollectorCount, 0);
@endphp

<div data-server-rendered-page="users" data-page-title="User Management" class="um-page">
    <section class="um-hero">
        <div>
            <h1>User Management</h1>
            <p>Create accounts, manage roles, and assign collectors to the correct department workflow.</p>
        </div>
        <div class="um-hero-metrics">
            <div class="um-kpi">
                <span class="um-kpi-label">Total Users</span>
                <strong>{{ $users->count() }}</strong>
            </div>
            <div class="um-kpi">
                <span class="um-kpi-label">Collectors</span>
                <strong>{{ $collectorAccounts->count() }}</strong>
            </div>
            <div class="um-kpi">
                <span class="um-kpi-label">Assigned</span>
                <strong>{{ $assignedCollectorCount }}</strong>
            </div>
        </div>
    </section>

    @if (session('status'))
        <div class="um-alert um-alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="um-alert um-alert-error">
            <i class="fas fa-triangle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="um-alert um-alert-error">
            <i class="fas fa-circle-exclamation"></i>
            <span>Unable to save changes. {{ $errors->first() }}</span>
        </div>
    @endif

    <div class="um-tabs">
        <button id="tab-btn-users" type="button" onclick="switchTab('users')" class="um-tab-btn um-tab-btn-active">
            <i class="fas fa-users"></i> Registered Users
        </button>
        <button id="tab-btn-add" type="button" onclick="switchTab('add')" class="um-tab-btn">
            <i class="fas fa-user-plus"></i> Add User
        </button>
        <button id="tab-btn-assignments" type="button" onclick="switchTab('assignments')" class="um-tab-btn">
            <i class="fas fa-user-check"></i> Collector Assignments
        </button>
    </div>

    <div id="tab-content-users" class="um-panel" style="display:block;">
        <section class="um-card">
            <header class="um-card-head">
                <div>
                    <h3>Registered Accounts</h3>
                    <p>Search by name, email, username, role, or department.</p>
                </div>
                <div class="um-search-wrap">
                    <i class="fas fa-search"></i>
                    <input id="userSearchInput" type="text" placeholder="Search users..." autocomplete="off">
                    <button id="userSearchClear" type="button" onclick="clearUserSearch()" aria-label="Clear search">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </header>

            <div class="um-table-wrap">
                <table class="um-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email / Username</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody id="registered-users-body">
                        @forelse ($users as $user)
                            @php
                                $departmentCode = strtolower((string) $user->department);
                                $departmentLabel = $departmentLabels[$departmentCode] ?? ucfirst($departmentCode ?: 'Administration');
                                $searchValue = strtolower(trim(implode(' ', array_filter([
                                    $user->name,
                                    $user->email,
                                    $user->username,
                                    $user->role,
                                    $departmentCode,
                                    $departmentLabel,
                                ], static fn ($value) => filled($value)))));
                            @endphp
                            <tr data-user-row data-search="{{ $searchValue }}">
                                <td>
                                    <div class="um-name-cell">
                                        <span class="um-name-avatar">{{ strtoupper(substr((string) $user->name, 0, 1)) }}</span>
                                        <span>{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="um-email">{{ $user->email }}</div>
                                    <div class="um-sub">{{ $user->username ?: 'No username' }}</div>
                                </td>
                                <td>{{ $user->roleLabel() }}</td>
                                <td>{{ $departmentLabel }}</td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="um-pill um-pill-success">Active</span>
                                    @else
                                        <span class="um-pill um-pill-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="um-sub">{{ $user->created_at?->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="um-empty">No users found.</td>
                            </tr>
                        @endforelse
                        <tr id="users-search-empty" style="display:none;">
                            <td colspan="6" class="um-empty">No users match your search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div id="tab-content-add" class="um-panel" style="display:none;">
        <section class="um-card">
            <header class="um-card-head um-card-head-stack">
                <div>
                    <h3>Create New User</h3>
                    <p>Create any personnel account including collector accounts.</p>
                </div>
                <div class="um-info-note">
                    <i class="fas fa-lightbulb"></i>
                    <span>For collector workflow: create account with <strong>Department = Collector</strong>, then assign it in Collector Assignments.</span>
                </div>
            </header>

            <form action="{{ route('admin.users.store') }}" method="POST" class="um-form">
                @csrf
                <input type="hidden" name="_form" value="add_user">

                <div class="um-form-grid">
                    <label>
                        <span>Full Name</span>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" class="form-control" required>
                    </label>
                    <label>
                        <span>Username</span>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" class="form-control" required>
                    </label>
                    <label>
                        <span>Email Address</span>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" required>
                    </label>
                    <label>
                        <span>Department</span>
                        <select id="department" name="department" class="form-control" required>
                            <option value="">Select department...</option>
                            @foreach ($departments as $department)
                                @php $departmentCode = strtolower((string) $department); @endphp
                                <option value="{{ $departmentCode }}" {{ old('department') === $departmentCode ? 'selected' : '' }}>
                                    {{ $departmentLabels[$departmentCode] ?? ucfirst($departmentCode) }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>Password</span>
                        <input id="password" name="password" type="password" class="form-control" required>
                    </label>
                    <label>
                        <span>Confirm Password</span>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                    </label>
                </div>

                <div class="um-form-actions">
                    <label class="um-checkbox">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <span>Account is active</span>
                    </label>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-check"></i> Create Account
                    </button>
                </div>
            </form>
        </section>
    </div>

    <div id="tab-content-assignments" class="um-panel" style="display:none;">
        <section class="um-card">
            <header class="um-card-head um-card-head-stack">
                <div>
                    <h3>Collector Department Assignments</h3>
                    <p>Only Fishport, Public Market, and Atrium allow collector assignment. Cemetery is direct payment only.</p>
                </div>
                <div class="um-stat-row">
                    <span class="um-pill um-pill-soft-blue">Collectors: {{ $collectorAccounts->count() }}</span>
                    <span class="um-pill um-pill-soft-green">Assigned: {{ $assignedCollectorCount }}</span>
                    <span class="um-pill um-pill-soft-orange">Unassigned: {{ $unassignedCollectorCount }}</span>
                </div>
            </header>

            @if (! $hasCollectorSchema)
                <div class="um-alert um-alert-error" style="margin: 1.1rem 1.2rem;">
                    <i class="fas fa-database"></i>
                    <span>Assignment tables are not migrated yet. Run <code>php artisan migrate</code> first.</span>
                </div>
            @else
                <div class="um-assignment-box">
                    <form id="collectorAssignmentForm" action="{{ route('admin.users.collector_assignments.store') }}" method="POST" class="um-assign-form">
                        @csrf
                        <input type="hidden" name="_form" value="collector_assignment">

                        <label>
                            <span>Collector Account</span>
                            <select id="collector_user_id" name="collector_user_id" class="form-control" required>
                                <option value="">Select collector...</option>
                                @foreach ($collectorAccounts as $collector)
                                    <option
                                        value="{{ $collector->id }}"
                                        data-collector-name="{{ $collector->name }}"
                                        data-current-department-id="{{ (string) ($collector->collectorAssignment?->department_id ?? '') }}"
                                        data-current-department-name="{{ (string) ($collector->collectorAssignment?->department?->name ?? '') }}"
                                        {{ (string) old('collector_user_id') === (string) $collector->id ? 'selected' : '' }}
                                    >
                                        {{ $collector->name }}{{ $collector->is_active ? '' : ' (Inactive)' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Assign To Department</span>
                            <select id="department_id" name="department_id" class="form-control" required>
                                <option value="">Select department...</option>
                                @foreach ($collectorDepartments as $department)
                                    <option value="{{ $department->id }}" {{ (string) old('department_id') === (string) $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <button type="submit" class="btn btn-primary um-assign-btn">
                            <i class="fas fa-link"></i> Save Assignment
                        </button>
                    </form>
                </div>

                <div class="um-table-wrap">
                    <table class="um-table">
                        <thead>
                            <tr>
                                <th>Collector</th>
                                <th>Status</th>
                                <th>Assigned Department</th>
                                <th>Assigned By</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($collectorAccounts as $collector)
                                @php
                                    $assignment = $collector->collectorAssignment;
                                    $departmentName = $assignment?->department?->name;
                                    $assignedBy = $assignment?->assignedBy?->name;
                                @endphp
                                <tr>
                                    <td class="um-name-strong">{{ $collector->name }}</td>
                                    <td>
                                        @if ($collector->is_active)
                                            <span class="um-pill um-pill-success">Active</span>
                                        @else
                                            <span class="um-pill um-pill-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $departmentName ?: 'Not assigned' }}</td>
                                    <td class="um-sub">{{ $assignedBy ?: '-' }}</td>
                                    <td class="um-sub">{{ $assignment?->updated_at?->format('M d, Y h:i A') ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="um-empty">No collector accounts yet. Create one first in Add User.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</div>

<div id="reassignConfirmBackdrop" class="um-modal-backdrop" aria-hidden="true">
    <div class="um-modal" role="dialog" aria-modal="true" aria-labelledby="reassignConfirmTitle">
        <div class="um-modal-head">
            <h3 id="reassignConfirmTitle">Confirm Collector Reassignment</h3>
            <button type="button" id="reassignCancelTop" class="um-modal-close" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="um-modal-body">
            <div class="um-modal-icon">
                <i class="fas fa-shuffle"></i>
            </div>
            <div class="um-modal-message">
                <p id="reassignConfirmText"></p>
                <div class="um-modal-route">
                    <span id="reassignFromDepartment"></span>
                    <i class="fas fa-arrow-right"></i>
                    <span id="reassignToDepartment"></span>
                </div>
            </div>
        </div>
        <div class="um-modal-foot">
            <button type="button" id="reassignCancelBtn" class="btn btn-secondary">Cancel</button>
            <button type="button" id="reassignConfirmBtn" class="btn btn-primary">
                <i class="fas fa-check"></i> Yes, Reassign Collector
            </button>
        </div>
    </div>
</div>

<style>
    .um-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0.35rem 0 2rem;
    }

    .um-hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.2rem 1.25rem;
        border-radius: 16px;
        background: linear-gradient(135deg, #113b68 0%, #205d91 52%, #2a77ac 100%);
        color: #f8fbff;
        box-shadow: 0 14px 30px rgba(11, 52, 92, 0.28);
        margin-bottom: 1rem;
    }

    .um-hero h1 {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: 0.01em;
    }

    .um-hero p {
        margin: 0.4rem 0 0;
        opacity: 0.9;
        font-size: 0.93rem;
        max-width: 620px;
    }

    .um-hero-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(95px, 1fr));
        gap: 0.6rem;
    }

    .um-kpi {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 11px;
        padding: 0.55rem 0.65rem;
        min-width: 92px;
        text-align: right;
    }

    .um-kpi-label {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.85;
    }

    .um-kpi strong {
        font-size: 1.18rem;
        line-height: 1.1;
    }

    .um-alert {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        border-radius: 12px;
        padding: 0.85rem 1rem;
        margin-bottom: 0.95rem;
        font-weight: 500;
    }

    .um-alert-success {
        border: 1px solid #99e2bd;
        background: #ebfff4;
        color: #065f46;
    }

    .um-alert-error {
        border: 1px solid #fecaca;
        background: #fff1f2;
        color: #991b1b;
    }

    .um-tabs {
        display: inline-flex;
        gap: 0.4rem;
        flex-wrap: wrap;
        background: #ecf3fb;
        border: 1px solid #d7e4f2;
        border-radius: 13px;
        padding: 0.35rem;
        margin-bottom: 1rem;
    }

    .um-tab-btn {
        border: 0;
        background: transparent;
        color: #4b5f78;
        border-radius: 10px;
        padding: 0.58rem 0.92rem;
        font-size: 0.94rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.46rem;
    }

    .um-tab-btn-active {
        background: #ffffff;
        color: #0f4f87;
        box-shadow: 0 2px 7px rgba(17, 68, 114, 0.14);
    }

    .um-panel {
        animation: umFadeIn 0.22s ease-in-out;
    }

    .um-card {
        border: 1px solid #dce6f1;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 51, 82, 0.08);
        overflow: hidden;
    }

    .um-card-head {
        border-bottom: 1px solid #e2ebf4;
        padding: 1.05rem 1.2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .um-card-head-stack {
        align-items: flex-start;
        justify-content: flex-start;
    }

    .um-card-head h3 {
        margin: 0;
        font-size: 1.14rem;
        color: #0f2640;
        font-weight: 800;
    }

    .um-card-head p {
        margin: 0.3rem 0 0;
        color: #56708a;
        font-size: 0.92rem;
    }

    .um-search-wrap {
        position: relative;
        width: 420px;
        max-width: 100%;
    }

    .um-search-wrap i {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #87a1bb;
    }

    .um-search-wrap input {
        width: 100%;
        border: 1px solid #bcd0e4;
        border-radius: 999px;
        padding: 0.62rem 2.5rem 0.62rem 2.1rem;
        font-size: 0.92rem;
        color: #18324d;
        background: #f7fbff;
    }

    .um-search-wrap input:focus,
    .um-form label input:focus,
    .um-form label select:focus,
    .um-assign-form label select:focus {
        outline: 0;
        border-color: #3f8ad1;
        box-shadow: 0 0 0 3px rgba(60, 138, 209, 0.14);
        background: #fff;
    }

    #userSearchClear {
        display: none;
        align-items: center;
        justify-content: center;
        position: absolute;
        top: 50%;
        right: 8px;
        transform: translateY(-50%);
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 999px;
        background: #eaf2fc;
        color: #2568a5;
        cursor: pointer;
    }

    .um-table-wrap {
        overflow-x: auto;
    }

    .um-table {
        width: 100%;
        border-collapse: collapse;
    }

    .um-table thead th {
        background: #f3f8fd;
        color: #5d748f;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.79rem;
        font-weight: 700;
        padding: 0.86rem 0.95rem;
        border-bottom: 1px solid #dce6f1;
        text-align: left;
        white-space: nowrap;
    }

    .um-table tbody td {
        padding: 0.95rem;
        border-bottom: 1px solid #ebf0f7;
        color: #1f3952;
        font-size: 0.93rem;
        vertical-align: middle;
    }

    .um-table tbody tr:hover td {
        background: #f8fbff;
    }

    .um-name-cell {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        font-weight: 700;
        color: #102a43;
    }

    .um-name-avatar {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.76rem;
        font-weight: 800;
        color: #1f537f;
        background: #d8e8f8;
        border: 1px solid #bdd4ea;
    }

    .um-name-strong {
        font-weight: 700;
        color: #112f4d;
    }

    .um-email {
        font-size: 0.92rem;
        color: #274563;
    }

    .um-sub {
        color: #5f7790;
        font-size: 0.85rem;
    }

    .um-empty {
        text-align: center;
        color: #647d95;
        padding: 1.7rem;
    }

    .um-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        padding: 0.24rem 0.62rem;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .um-pill-success {
        border-color: #9be6c6;
        background: #e7fff2;
        color: #0e7b55;
    }

    .um-pill-danger {
        border-color: #fdc8cf;
        background: #fff1f2;
        color: #b4233f;
    }

    .um-pill-soft-blue {
        border-color: #bfd8f2;
        background: #ecf5ff;
        color: #1d4f7f;
    }

    .um-pill-soft-green {
        border-color: #9de2c1;
        background: #e9fff2;
        color: #0f704f;
    }

    .um-pill-soft-orange {
        border-color: #f7cb92;
        background: #fff5e8;
        color: #9a4f00;
    }

    .um-form {
        padding: 1.2rem;
        display: grid;
        gap: 1rem;
    }

    .um-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 0.85rem;
    }

    .um-form label,
    .um-assign-form label {
        display: grid;
        gap: 0.34rem;
    }

    .um-form label span,
    .um-assign-form label span {
        color: #19354f;
        font-size: 0.84rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .um-form label input,
    .um-form label select,
    .um-assign-form label select {
        width: 100%;
        border: 1px solid #bcd0e4;
        border-radius: 10px;
        background: #f8fbff;
        min-height: 40px;
        padding: 0.54rem 0.7rem;
        color: #173651;
    }

    .um-form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        border-top: 1px solid #e8eef5;
        padding-top: 0.95rem;
        flex-wrap: wrap;
    }

    .um-checkbox {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: #274563;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .um-info-note {
        display: inline-flex;
        align-items: flex-start;
        gap: 0.55rem;
        border: 1px solid #c5ddf5;
        background: #eef6ff;
        color: #14497a;
        border-radius: 10px;
        padding: 0.62rem 0.78rem;
        font-size: 0.86rem;
        line-height: 1.45;
    }

    .um-info-note i {
        margin-top: 0.1rem;
    }

    .um-stat-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.48rem;
    }

    .um-assignment-box {
        margin: 0 1.2rem 1rem;
        border: 1px solid #dce7f2;
        border-radius: 12px;
        background: linear-gradient(180deg, #f8fbff 0%, #f2f7fd 100%);
        padding: 0.95rem;
    }

    .um-assign-form {
        display: grid;
        grid-template-columns: minmax(210px, 1fr) minmax(210px, 1fr) auto;
        gap: 0.75rem;
        align-items: end;
    }

    .um-assign-btn {
        min-height: 40px;
        border-radius: 10px;
        box-shadow: 0 8px 18px rgba(26, 98, 164, 0.2);
    }

    .um-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(7, 28, 49, 0.48);
        backdrop-filter: blur(2px);
        z-index: 1200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .um-modal-backdrop.is-open {
        display: flex;
    }

    .um-modal {
        width: min(560px, 100%);
        border-radius: 14px;
        background: #fff;
        border: 1px solid #d6e3f0;
        box-shadow: 0 22px 48px rgba(7, 28, 49, 0.28);
        overflow: hidden;
    }

    .um-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.95rem 1.05rem;
        border-bottom: 1px solid #e6eef7;
        background: linear-gradient(180deg, #f9fbff 0%, #f4f8fd 100%);
    }

    .um-modal-head h3 {
        margin: 0;
        font-size: 1.03rem;
        color: #0f2c49;
    }

    .um-modal-close {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid #d2dfed;
        background: #fff;
        color: #2b4f73;
        cursor: pointer;
    }

    .um-modal-body {
        padding: 1rem 1.1rem;
        display: flex;
        gap: 0.9rem;
        align-items: flex-start;
    }

    .um-modal-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #184f82;
        background: #e9f4ff;
        border: 1px solid #b9d7f3;
        flex-shrink: 0;
    }

    .um-modal-message p {
        margin: 0;
        color: #2a4865;
        line-height: 1.5;
        font-size: 0.92rem;
    }

    .um-modal-route {
        margin-top: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        border: 1px solid #d2e2f1;
        background: #f6fbff;
        border-radius: 999px;
        padding: 0.32rem 0.7rem;
        color: #1b4b76;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .um-modal-route i {
        color: #2f7fbd;
        font-size: 0.72rem;
    }

    .um-modal-foot {
        border-top: 1px solid #e6eef7;
        padding: 0.85rem 1.05rem;
        display: flex;
        justify-content: flex-end;
        gap: 0.6rem;
        background: #fbfdff;
    }

    @keyframes umFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 1080px) {
        .um-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .um-hero-metrics {
            width: 100%;
            grid-template-columns: repeat(3, minmax(80px, 1fr));
        }

        .um-assign-form {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .um-page {
            padding-bottom: 1.35rem;
        }

        .um-tabs {
            width: 100%;
        }

        .um-tab-btn {
            flex: 1 1 100%;
            justify-content: center;
        }

        .um-hero h1 {
            font-size: 1.22rem;
        }

        .um-card-head,
        .um-form,
        .um-assignment-box {
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }
    }
</style>

<script>
    function setTabActive(tabButton, isActive) {
        if (!tabButton) {
            return;
        }

        if (isActive) {
            tabButton.classList.add('um-tab-btn-active');
        } else {
            tabButton.classList.remove('um-tab-btn-active');
        }
    }

    function switchTab(tab) {
        const buttons = {
            users: document.getElementById('tab-btn-users'),
            add: document.getElementById('tab-btn-add'),
            assignments: document.getElementById('tab-btn-assignments'),
        };

        const panels = {
            users: document.getElementById('tab-content-users'),
            add: document.getElementById('tab-content-add'),
            assignments: document.getElementById('tab-content-assignments'),
        };

        Object.keys(buttons).forEach((key) => {
            setTabActive(buttons[key], key === tab);
        });

        Object.keys(panels).forEach((key) => {
            const panel = panels[key];
            if (!panel) {
                return;
            }

            panel.style.display = key === tab ? 'block' : 'none';
        });
    }

    function clearUserSearch() {
        const searchInput = document.getElementById('userSearchInput');
        if (!searchInput) {
            return;
        }

        searchInput.value = '';
        applyUserSearch('');
        searchInput.focus();
    }

    function applyUserSearch(rawQuery) {
        const rows = Array.from(document.querySelectorAll('[data-user-row]'));
        const emptyState = document.getElementById('users-search-empty');
        const clearButton = document.getElementById('userSearchClear');
        const query = rawQuery.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach((row) => {
            const haystack = row.dataset.search || '';
            const isMatch = query === '' || haystack.includes(query);
            row.style.display = isMatch ? '' : 'none';
            if (isMatch) {
                visibleCount += 1;
            }
        });

        if (clearButton) {
            clearButton.style.display = query === '' ? 'none' : 'inline-flex';
        }

        if (emptyState) {
            emptyState.style.display = query !== '' && visibleCount === 0 ? '' : 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('userSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function (event) {
                applyUserSearch(event.target.value);
            });
        }

        applyUserSearch('');

        const activeTab = @json(session('active_tab'));
        const oldForm = @json(old('_form'));
        const hasErrors = @json($errors->any());

        if (activeTab === 'users' || activeTab === 'add' || activeTab === 'assignments') {
            switchTab(activeTab);
            return;
        }

        if (oldForm === 'collector_assignment') {
            switchTab('assignments');
            return;
        }

        if (oldForm === 'add_user' || hasErrors) {
            switchTab('add');
            return;
        }

        switchTab('users');

        const assignmentForm = document.getElementById('collectorAssignmentForm');
        const collectorSelect = document.getElementById('collector_user_id');
        const departmentSelect = document.getElementById('department_id');
        const modalBackdrop = document.getElementById('reassignConfirmBackdrop');
        const modalText = document.getElementById('reassignConfirmText');
        const fromDepartmentText = document.getElementById('reassignFromDepartment');
        const toDepartmentText = document.getElementById('reassignToDepartment');
        const confirmBtn = document.getElementById('reassignConfirmBtn');
        const cancelBtn = document.getElementById('reassignCancelBtn');
        const cancelTopBtn = document.getElementById('reassignCancelTop');

        if (!assignmentForm || !collectorSelect || !departmentSelect || !modalBackdrop || !confirmBtn) {
            return;
        }

        let allowSubmitAfterConfirm = false;

        const closeReassignModal = function () {
            modalBackdrop.classList.remove('is-open');
            modalBackdrop.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        const openReassignModal = function (collectorName, fromDepartment, toDepartment) {
            modalText.textContent = collectorName + ' is currently assigned to ' + fromDepartment + '. Continue reassigning this collector?';
            fromDepartmentText.textContent = fromDepartment;
            toDepartmentText.textContent = toDepartment;

            modalBackdrop.classList.add('is-open');
            modalBackdrop.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        const getSelectedOption = function (selectElement) {
            return selectElement.options[selectElement.selectedIndex] || null;
        };

        assignmentForm.addEventListener('submit', function (event) {
            if (allowSubmitAfterConfirm) {
                return;
            }

            if (!assignmentForm.checkValidity()) {
                return;
            }

            const selectedCollectorOption = getSelectedOption(collectorSelect);
            const selectedDepartmentOption = getSelectedOption(departmentSelect);

            if (!selectedCollectorOption || !selectedDepartmentOption) {
                return;
            }

            const currentDepartmentId = (selectedCollectorOption.dataset.currentDepartmentId || '').trim();
            const currentDepartmentName = (selectedCollectorOption.dataset.currentDepartmentName || '').trim();
            const newDepartmentId = (selectedDepartmentOption.value || '').trim();
            const newDepartmentName = selectedDepartmentOption.textContent.trim();

            if (currentDepartmentId !== '' && newDepartmentId !== '' && currentDepartmentId !== newDepartmentId) {
                event.preventDefault();
                openReassignModal(
                    selectedCollectorOption.dataset.collectorName || 'Selected collector',
                    currentDepartmentName || 'Current department',
                    newDepartmentName || 'New department'
                );
            }
        });

        confirmBtn.addEventListener('click', function () {
            allowSubmitAfterConfirm = true;
            closeReassignModal();
            assignmentForm.submit();
        });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeReassignModal);
        }

        if (cancelTopBtn) {
            cancelTopBtn.addEventListener('click', closeReassignModal);
        }

        modalBackdrop.addEventListener('click', function (event) {
            if (event.target === modalBackdrop) {
                closeReassignModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modalBackdrop.classList.contains('is-open')) {
                closeReassignModal();
            }
        });
    });
</script>
@endsection
