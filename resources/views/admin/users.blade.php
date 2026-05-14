@extends('layouts.app')

@section('content')
@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\User> $users */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $collectorAccounts */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Department> $collectorDepartments */
    /** @var array<int, string> $departments */
    /** @var int $assignedCollectorCount */
    /** @var bool $hasCollectorSchema */
    /** @var int $totalUsers */
    /** @var string $searchTerm */

    $departmentLabels = [
        'fishport' => 'Fishport',
        'market' => 'Public Market',
        'cemetery' => 'Cemetery',
        'terminal' => 'Terminal',
        'atrium' => 'Atrium',
        'collector' => 'Collector',
        'cashier' => 'Cashier',
    ];
    $displayTimezone = 'Asia/Manila';

    $unassignedCollectorCount = max($collectorAccounts->count() - $assignedCollectorCount, 0);
@endphp

<div
    data-server-rendered-page="users"
    data-page-title="User Management"
    data-active-tab="{{ (string) session('active_tab', '') }}"
    data-old-form="{{ (string) old('_form', '') }}"
    data-has-errors="{{ $errors->any() ? '1' : '0' }}"
    class="um-page"
>
    <section class="um-hero">
        <div class="um-hero-metrics">
            <div class="um-kpi">
                <span class="um-kpi-icon"><i class="fas fa-users"></i></span>
                <div>
                    <span class="um-kpi-label">Total Users</span>
                    <strong>{{ $totalUsers }}</strong>
                </div>
            </div>
            <div class="um-kpi">
                <span class="um-kpi-icon um-kpi-icon-teal"><i class="fas fa-hand-holding-dollar"></i></span>
                <div>
                    <span class="um-kpi-label">Collectors</span>
                    <strong>{{ $collectorAccounts->count() }}</strong>
                </div>
            </div>
            <div class="um-kpi">
                <span class="um-kpi-icon um-kpi-icon-green"><i class="fas fa-user-check"></i></span>
                <div>
                    <span class="um-kpi-label">Assigned</span>
                    <strong>{{ $assignedCollectorCount }}</strong>
                </div>
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
                <form action="{{ route('admin.users') }}" method="GET" class="um-search-form">
                    <div class="um-search-wrap">
                        <i class="fas fa-search"></i>
                        <input id="userSearchInput" name="q" type="text" value="{{ $searchTerm }}" placeholder="Search" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm um-search-submit">Search</button>
                    @if ($searchTerm !== '')
                        <a href="{{ route('admin.users') }}" class="btn btn-outline btn-sm um-search-clear">Clear</a>
                    @endif
                </form>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="registered-users-body">
                        @forelse ($users as $user)
                            @php
                                $departmentCode = strtolower((string) $user->department);
                                $departmentLabel = $departmentLabels[$departmentCode] ?? ucfirst($departmentCode ?: 'Administration');
                                $isCurrentUser = (int) auth()->id() === (int) $user->id;
                            @endphp
                            <tr>
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
                                <td class="um-sub">
                                    {{ $user->created_at?->copy()->timezone($displayTimezone)->format('M d, Y h:i A') }}
                                </td>
                                <td>
                                    <div class="um-row-actions">
                                        <button
                                            type="button"
                                            class="btn btn-outline btn-sm um-user-edit-btn um-action-btn um-action-edit"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ e((string) $user->name) }}"
                                            data-user-username="{{ e((string) $user->username) }}"
                                            data-user-email="{{ e((string) $user->email) }}"
                                            data-user-department="{{ e((string) $departmentCode) }}"
                                            data-user-active="{{ $user->is_active ? '1' : '0' }}"
                                        >
                                            <i class="fas fa-pen-to-square"></i> Edit
                                        </button>
                                        @if (! $isCurrentUser)
                                            <button
                                                type="button"
                                                class="btn btn-danger btn-sm um-user-delete-btn um-action-btn um-action-delete"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ e((string) $user->name) }}"
                                            >
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        @else
                                            <span class="um-sub">Current account</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="um-empty">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="um-pagination">
                <div class="um-pagination-summary">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                </div>
                @if ($users->hasPages())
                    @php
                        $startPage = max($users->currentPage() - 2, 1);
                        $endPage = min($users->currentPage() + 2, $users->lastPage());
                    @endphp
                    <div class="um-pagination-links">
                        @if ($users->onFirstPage())
                            <span class="um-page-link is-disabled">Previous</span>
                        @else
                            <a class="um-page-link" href="{{ $users->previousPageUrl() }}">Previous</a>
                        @endif

                        @for ($page = $startPage; $page <= $endPage; $page++)
                            <a class="um-page-link {{ $page === $users->currentPage() ? 'is-active' : '' }}" href="{{ $users->url($page) }}">{{ $page }}</a>
                        @endfor

                        @if ($users->hasMorePages())
                            <a class="um-page-link" href="{{ $users->nextPageUrl() }}">Next</a>
                        @else
                            <span class="um-page-link is-disabled">Next</span>
                        @endif
                    </div>
                @endif
            </div>
        </section>
    </div>

    <div id="tab-content-add" class="um-panel" style="display:none;">
        <section class="um-card">
            <header class="um-card-head um-card-head-stack">
                <div>
                    <h3>Create New User</h3>
                </div>
            </header>

            <form action="{{ route('admin.users.store') }}" method="POST" class="um-form um-form-modern">
                @csrf
                <input type="hidden" name="_form" value="add_user">

                <div class="um-form-intro">
                    <div class="um-form-intro-item">
                        <i class="fas fa-circle-check"></i>
                        <span>Use a department-specific account for easier audit tracking.</span>
                    </div>
                    <div class="um-form-intro-item">
                        <i class="fas fa-shield-halved"></i>
                        <span>Set a strong password to reduce unauthorized access risk.</span>
                    </div>
                </div>

                <div class="um-form-grid um-form-grid-modern">
                    <label class="um-field">
                        <span class="um-field-label">Full Name</span>
                        <div class="um-input-wrap">
                            <i class="fas fa-id-badge"></i>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" class="form-control" placeholder="e.g. Juan Dela Cruz" autocomplete="name" required>
                        </div>
                    </label>
                    <label class="um-field">
                        <span class="um-field-label">Username</span>
                        <div class="um-input-wrap">
                            <i class="fas fa-at"></i>
                            <input id="username" name="username" type="text" value="{{ old('username') }}" class="form-control" placeholder="e.g. juan.dela.cruz" autocomplete="username" required>
                        </div>
                    </label>
                    <label class="um-field">
                        <span class="um-field-label">Email Address</span>
                        <div class="um-input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" placeholder="name@domain.com" autocomplete="email" required>
                        </div>
                    </label>
                    <label class="um-field">
                        <span class="um-field-label">Department</span>
                        <div class="um-input-wrap">
                            <i class="fas fa-building-user"></i>
                            <select id="department" name="department" class="form-control" required>
                                <option value="">Select department...</option>
                                @foreach ($departments as $department)
                                    @php $departmentCode = strtolower((string) $department); @endphp
                                    <option value="{{ $departmentCode }}" {{ old('department') === $departmentCode ? 'selected' : '' }}>
                                        {{ $departmentLabels[$departmentCode] ?? ucfirst($departmentCode) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <small class="um-field-help">Collector accounts require assignment in the next tab.</small>
                    </label>
                    <label class="um-field">
                        <span class="um-field-label">Password</span>
                        <div class="um-input-wrap um-input-wrap-password">
                            <i class="fas fa-lock"></i>
                            <input id="password" name="password" type="password" class="form-control" autocomplete="new-password" placeholder="Enter a strong password" required>
                            <button type="button" class="um-password-toggle" data-toggle-password="password" aria-label="Show password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </label>
                    <label class="um-field">
                        <span class="um-field-label">Confirm Password</span>
                        <div class="um-input-wrap um-input-wrap-password">
                            <i class="fas fa-lock"></i>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" placeholder="Re-enter password" required>
                            <button type="button" class="um-password-toggle" data-toggle-password="password_confirmation" aria-label="Show password confirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </label>
                </div>

                <div class="um-form-actions">
                    <label class="um-switch">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <span class="um-switch-track" aria-hidden="true"></span>
                        <span class="um-switch-text">Account is active</span>
                    </label>
                    <button type="submit" class="btn btn-primary um-create-btn">
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
                    <p>Only Fishport and Public Market allow collector assignment. Cemetery is direct payment only.</p>
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
                                <th>Actions</th>
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
                                    <td class="um-sub">
                                        {{ $assignment?->updated_at?->copy()->timezone($displayTimezone)->format('M d, Y h:i A') ?: '-' }}
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.users.collector_assignments.generate_missed_notice', $collector) }}" method="POST" onsubmit="return confirm('Send missed-payments reminder to this collector now?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">
                                                <i class="fas fa-bell"></i> Generate Missed Notice
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="um-empty">No collector accounts yet. Create one first in Add User.</td>
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

<div id="editUserBackdrop" class="um-modal-backdrop" aria-hidden="true">
    <div class="um-modal" role="dialog" aria-modal="true" aria-labelledby="editUserTitle">
        <div class="um-modal-head">
            <h3 id="editUserTitle">Edit User Account</h3>
            <button type="button" id="editUserCloseTop" class="um-modal-close" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editUserForm" method="POST" class="um-form um-form-modern">
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" value="edit_user">
            <input type="hidden" name="page" value="{{ $users->currentPage() }}">
            <input type="hidden" name="q" value="{{ $searchTerm }}">

            <div class="um-form-grid um-form-grid-modern">
                <label class="um-field">
                    <span class="um-field-label">Full Name</span>
                    <div class="um-input-wrap">
                        <i class="fas fa-id-badge"></i>
                        <input id="edit_user_name" name="name" type="text" class="form-control" required>
                    </div>
                </label>
                <label class="um-field">
                    <span class="um-field-label">Username</span>
                    <div class="um-input-wrap">
                        <i class="fas fa-at"></i>
                        <input id="edit_user_username" name="username" type="text" class="form-control" required>
                    </div>
                </label>
                <label class="um-field">
                    <span class="um-field-label">Email Address</span>
                    <div class="um-input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input id="edit_user_email" name="email" type="email" class="form-control" required>
                    </div>
                </label>
                <label class="um-field">
                    <span class="um-field-label">Department</span>
                    <div class="um-input-wrap">
                        <i class="fas fa-building-user"></i>
                        <select id="edit_user_department" name="department" class="form-control" required>
                            @foreach ($departments as $department)
                                @php $departmentCode = strtolower((string) $department); @endphp
                                <option value="{{ $departmentCode }}">
                                    {{ $departmentLabels[$departmentCode] ?? ucfirst($departmentCode) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </label>
                <label class="um-field">
                    <span class="um-field-label">New Password (Optional)</span>
                    <div class="um-input-wrap um-input-wrap-password">
                        <i class="fas fa-lock"></i>
                        <input id="edit_user_password" name="password" type="password" class="form-control" autocomplete="new-password" placeholder="Leave blank to keep current password">
                        <button type="button" class="um-password-toggle" data-toggle-password="edit_user_password" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </label>
                <label class="um-field">
                    <span class="um-field-label">Confirm New Password</span>
                    <div class="um-input-wrap um-input-wrap-password">
                        <i class="fas fa-lock"></i>
                        <input id="edit_user_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" placeholder="Re-enter new password">
                        <button type="button" class="um-password-toggle" data-toggle-password="edit_user_password_confirmation" aria-label="Show password confirmation">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </label>
            </div>

            <div class="um-form-actions">
                <label class="um-switch">
                    <input id="edit_user_is_active" type="checkbox" name="is_active" value="1">
                    <span class="um-switch-track" aria-hidden="true"></span>
                    <span class="um-switch-text">Account is active</span>
                </label>
                <div class="um-modal-form-actions">
                    <button type="button" id="editUserCancelBtn" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary um-create-btn">
                        <i class="fas fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="deleteUserBackdrop" class="um-modal-backdrop" aria-hidden="true">
    <div class="um-modal" role="dialog" aria-modal="true" aria-labelledby="deleteUserTitle">
        <div class="um-modal-head">
            <h3 id="deleteUserTitle">Delete User Account</h3>
            <button type="button" id="deleteUserCloseTop" class="um-modal-close" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="um-modal-body">
            <div class="um-modal-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="um-modal-message">
                <p>
                    This will permanently delete <strong id="deleteUserName"></strong>.
                    This action cannot be undone.
                </p>
            </div>
        </div>
        <div class="um-modal-foot">
            <button type="button" id="deleteUserCancelBtn" class="btn btn-secondary">Cancel</button>
            <form id="deleteUserForm" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="page" value="{{ $users->currentPage() }}">
                <input type="hidden" name="q" value="{{ $searchTerm }}">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete User
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    #contentArea {
        padding-top: 4px;
    }

    .um-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0.35rem 0 2rem;
    }

    .um-hero {
        margin-bottom: 1rem;
    }

    .um-hero-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .um-kpi {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 1rem 1.1rem;
        background: #ffffff;
        border: 1px solid #e3eaf3;
        border-radius: 14px;
        box-shadow: 0 1px 2px rgba(15, 35, 60, 0.04);
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .um-kpi::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #2563eb, #60a5fa);
    }

    .um-kpi:nth-child(2)::before {
        background: linear-gradient(90deg, #14b8a6, #2dd4bf);
    }

    .um-kpi:nth-child(3)::before {
        background: linear-gradient(90deg, #16a34a, #4ade80);
    }

    .um-kpi:hover {
        transform: translateY(-2px);
        border-color: #cfdae6;
        box-shadow: 0 6px 16px rgba(15, 35, 60, 0.07);
    }

    .um-kpi-icon {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        font-size: 1.05rem;
    }

    .um-kpi-icon-teal {
        background: rgba(20, 184, 166, 0.1);
        color: #14b8a6;
    }

    .um-kpi-icon-green {
        background: rgba(22, 163, 74, 0.1);
        color: #16a34a;
    }

    .um-kpi-label {
        display: block;
        color: #6b7d93;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .um-kpi strong {
        display: block;
        margin-top: 0.25rem;
        color: #0b1a2c;
        font-size: 1.45rem;
        font-weight: 850;
        line-height: 1;
        letter-spacing: -0.01em;
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

    .um-search-form {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
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

    .um-row-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    .um-pagination {
        border-top: 1px solid #e2ebf4;
        padding: 0.85rem 1rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        flex-wrap: wrap;
    }

    .um-pagination-summary {
        color: #536f8b;
        font-size: 0.88rem;
        font-weight: 600;
    }

    .um-pagination-links {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .um-page-link {
        min-width: 36px;
        height: 34px;
        padding: 0 0.7rem;
        border-radius: 9px;
        border: 1px solid #c6d8ea;
        background: #f5f9fe;
        color: #234c73;
        font-size: 0.84rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .um-page-link.is-active {
        border-color: #2f7fbd;
        background: #2f7fbd;
        color: #fff;
    }

    .um-page-link.is-disabled {
        opacity: 0.45;
        pointer-events: none;
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

    .um-modal-form-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    /* Modern UI overrides */
    .um-page {
        padding-top: 0.6rem;
    }

    .um-tabs {
        gap: 0.5rem;
        background: linear-gradient(180deg, #edf4fc 0%, #e6eff9 100%);
        border: 1px solid #ccddee;
        border-radius: 14px;
        padding: 0.45rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.82);
    }

    .um-tab-btn {
        color: #395571;
        padding: 0.66rem 1rem;
        border-radius: 11px;
        font-weight: 700;
    }

    .um-tab-btn:hover {
        color: #123e65;
        background: rgba(255, 255, 255, 0.58);
    }

    .um-tab-btn-active {
        background: #ffffff;
        color: #0f4f87;
        box-shadow: 0 10px 20px rgba(13, 72, 123, 0.15);
    }

    .um-card {
        border: 1px solid #d7e4f1;
        border-radius: 16px;
        box-shadow: 0 16px 32px rgba(8, 39, 70, 0.09);
    }

    .um-card-head {
        padding: 1.18rem 1.28rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .um-form-modern {
        gap: 1.15rem;
        padding: 1.3rem;
    }

    .um-form-intro {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.6rem;
    }

    .um-form-intro-item {
        display: flex;
        align-items: center;
        gap: 0.56rem;
        padding: 0.68rem 0.78rem;
        border-radius: 12px;
        border: 1px solid #d4e2f0;
        background: linear-gradient(180deg, #f8fbff 0%, #f1f7ff 100%);
        color: #214666;
        font-size: 0.84rem;
        font-weight: 600;
    }

    .um-form-intro-item i {
        color: #1d6eb0;
    }

    .um-form-grid-modern {
        gap: 0.95rem;
    }

    .um-field {
        gap: 0.42rem;
    }

    .um-field-label {
        color: #183b5a;
        font-size: 0.82rem;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        font-weight: 800;
    }

    .um-field-help {
        color: #5b7794;
        font-size: 0.78rem;
        margin-top: 0.06rem;
    }

    .um-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .um-input-wrap > i {
        position: absolute;
        left: 12px;
        color: #6d88a3;
        font-size: 0.83rem;
        pointer-events: none;
    }

    .um-form-modern .form-control {
        width: 100%;
        min-height: 44px;
        border: 1px solid #c6d8ea;
        background: #f7fbff;
        border-radius: 12px;
        padding: 0.6rem 0.76rem 0.6rem 2.15rem;
        color: #163550;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        appearance: none;
    }

    .um-form-modern .form-control:focus {
        border-color: #2f7fbd;
        box-shadow: 0 0 0 4px rgba(32, 110, 172, 0.14);
        background: #ffffff;
        outline: none;
    }

    .um-input-wrap-password .form-control {
        padding-right: 2.45rem;
    }

    .um-password-toggle {
        position: absolute;
        right: 8px;
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #507090;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .um-password-toggle:hover {
        background: #e8f1fb;
        color: #205b8d;
    }

    .um-password-toggle:focus-visible {
        outline: 2px solid #2f7fbd;
        outline-offset: 1px;
    }

    .um-form-actions {
        margin-top: 0.15rem;
        border-top: 1px solid #deebf7;
        padding-top: 1rem;
    }

    .um-switch {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 0.62rem;
        font-weight: 700;
        color: #214a6d;
        cursor: pointer;
        user-select: none;
    }

    .um-switch input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .um-switch-track {
        width: 48px;
        height: 28px;
        border-radius: 999px;
        background: #cad8e7;
        position: relative;
        transition: background 0.22s ease;
        box-shadow: inset 0 1px 2px rgba(11, 43, 74, 0.16);
    }

    .um-switch-track::after {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        top: 3px;
        left: 3px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(10, 46, 79, 0.22);
        transition: transform 0.22s ease;
    }

    .um-switch input:checked + .um-switch-track {
        background: linear-gradient(135deg, #0f7d5a 0%, #1f9f73 100%);
    }

    .um-switch input:checked + .um-switch-track::after {
        transform: translateX(20px);
    }

    .um-switch input:focus-visible + .um-switch-track {
        outline: 2px solid #2f7fbd;
        outline-offset: 2px;
    }

    .um-create-btn {
        min-height: 44px;
        border-radius: 12px;
        padding: 0.64rem 1.22rem;
        font-weight: 800;
        letter-spacing: 0.01em;
        box-shadow: 0 12px 20px rgba(16, 91, 151, 0.22);
    }

    @keyframes umFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 1080px) {
        .um-hero-metrics {
            width: 100%;
            grid-template-columns: repeat(3, minmax(80px, 1fr));
        }

        .um-assign-form {
            grid-template-columns: 1fr;
        }

        .um-form-intro {
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

        .um-hero-metrics {
            grid-template-columns: 1fr;
        }

        .um-kpi strong {
            font-size: 1.25rem;
        }

        .um-card-head,
        .um-form,
        .um-assignment-box {
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }

        .um-search-form {
            width: 100%;
        }

        .um-search-form .um-search-wrap {
            width: 100%;
        }

        .um-pagination {
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }
    }

    /* Compact spacing pass: keep key page spacing at 10px */
    .um-page {
        display: grid;
        gap: 8px;
        padding-top: 0;
    }

    .um-hero {
        margin: 0;
    }

    .um-hero-metrics {
        gap: 10px;
    }

    .um-kpi {
        gap: 10px;
        padding: 10px;
    }

    .um-alert {
        margin-bottom: 10px;
        padding: 10px;
    }

    .um-tabs {
        gap: 10px;
        padding: 10px;
        margin-bottom: 10px;
    }

    .um-tab-btn {
        padding: 10px;
    }

    .um-card-head {
        padding: 10px;
        gap: 10px;
    }

    .um-form,
    .um-form-modern {
        padding: 10px;
        gap: 10px;
    }

    .um-form-grid,
    .um-form-grid-modern,
    .um-form-intro,
    .um-stat-row,
    .um-assign-form,
    .um-modal-foot {
        gap: 10px;
    }

    .um-form-actions {
        padding-top: 10px;
        gap: 10px;
    }

    .um-assignment-box {
        margin: 0 10px 10px;
        padding: 10px;
    }

    .um-table thead th,
    .um-table tbody td {
        padding: 10px;
    }

    /* Refined admin UI polish */
    .um-page {
        gap: 12px;
    }

    .um-card {
        border-color: #cfdeed;
        box-shadow: 0 14px 30px rgba(11, 48, 82, 0.08);
    }

    .um-card-head {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        padding: 1rem 1.2rem;
    }

    .um-card-head h3 {
        font-size: 1.28rem;
        letter-spacing: -0.01em;
    }

    .um-card-head p {
        margin-top: 0.35rem;
    }

    .um-search-form {
        margin-left: auto;
        gap: 0.55rem;
    }

    .um-search-wrap {
        width: 500px;
    }

    .um-search-wrap input {
        min-height: 46px;
        border-color: #b5cde5;
        background: #f4f9ff;
        font-size: 0.98rem;
    }

    .um-search-submit,
    .um-search-clear {
        min-height: 44px;
        border-radius: 11px;
        padding: 0.58rem 1rem;
        font-weight: 800;
        min-width: 90px;
    }

    .um-search-submit {
        box-shadow: 0 10px 20px rgba(25, 97, 165, 0.2);
    }

    .um-search-clear {
        border-color: #c1d4e7;
        color: #325272;
        background: #f2f7fd;
    }

    .um-table-wrap {
        margin: 0 0.8rem;
        border: 1px solid #d8e6f3;
        border-radius: 14px;
        overflow: auto;
        background: #fff;
    }

    .um-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #edf4fc;
        color: #436380;
        font-size: 0.8rem;
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
    }

    .um-table tbody td {
        font-size: 1rem;
        color: #183754;
        border-bottom: 1px solid #e4edf6;
        padding-top: 0.95rem;
        padding-bottom: 0.95rem;
    }

    .um-table tbody tr:nth-child(even) td {
        background: #fbfdff;
    }

    .um-table tbody tr:hover td {
        background: #eef6ff;
    }

    .um-email {
        color: #143452;
        font-size: 1.02rem;
    }

    .um-sub {
        color: #5e7893;
    }

    .um-table th:last-child,
    .um-table td:last-child {
        width: 1%;
        white-space: nowrap;
    }

    .um-row-actions {
        gap: 0.5rem;
        flex-wrap: nowrap;
    }

    .um-action-btn {
        min-height: 38px;
        min-width: 84px;
        border-radius: 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
    }

    .um-action-edit {
        border-color: #c2d4e7;
        color: #2b4f73;
        background: #f4f9ff;
    }

    .um-action-edit:hover {
        background: #eaf3ff;
        color: #123e65;
    }

    .um-action-delete {
        box-shadow: 0 10px 18px rgba(222, 52, 58, 0.22);
    }

    .um-pagination {
        padding: 0.9rem 1.2rem 1.05rem;
        border-top-color: #dbe7f3;
        background: #fafdff;
    }

    .um-pagination-summary {
        font-size: 0.9rem;
        color: #456381;
    }

    .um-page-link {
        min-width: 38px;
        height: 36px;
        border-radius: 10px;
        font-size: 0.85rem;
    }

    .um-page-link:hover {
        background: #e9f3ff;
        border-color: #9ec0e0;
        color: #1f4f78;
    }

    @media (max-width: 980px) {
        .um-search-wrap {
            width: 100%;
        }

        .um-search-form {
            width: 100%;
        }

        .um-search-submit,
        .um-search-clear {
            min-width: 78px;
        }

        .um-table-wrap {
            margin: 0 0.55rem;
        }
    }

    @media (max-width: 720px) {
        .um-card-head {
            padding: 0.9rem;
        }

        .um-card-head h3 {
            font-size: 1.6rem;
        }

        .um-search-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            align-items: center;
            width: 100%;
        }

        .um-row-actions {
            flex-wrap: wrap;
        }

        .um-action-btn {
            min-width: 76px;
            padding: 0.45rem 0.6rem;
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

    document.addEventListener('DOMContentLoaded', function () {
        const pageRoot = document.querySelector('.um-page');
        const activeTab = (pageRoot?.dataset.activeTab || '').trim();
        const oldForm = (pageRoot?.dataset.oldForm || '').trim();
        let targetTab = 'users';

        if (activeTab === 'users' || activeTab === 'add' || activeTab === 'assignments') {
            targetTab = activeTab;
        } else if (oldForm === 'collector_assignment') {
            targetTab = 'assignments';
        } else if (oldForm === 'add_user') {
            targetTab = 'add';
        }

        switchTab(targetTab);

        const passwordToggles = Array.from(document.querySelectorAll('[data-toggle-password]'));
        passwordToggles.forEach((toggleButton) => {
            toggleButton.addEventListener('click', function () {
                const inputId = toggleButton.getAttribute('data-toggle-password');
                if (!inputId) {
                    return;
                }

                const input = document.getElementById(inputId);
                if (!input) {
                    return;
                }

                const nextType = input.type === 'password' ? 'text' : 'password';
                input.type = nextType;

                const icon = toggleButton.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye', nextType === 'password');
                    icon.classList.toggle('fa-eye-slash', nextType === 'text');
                }

                toggleButton.setAttribute('aria-label', nextType === 'password' ? 'Show password' : 'Hide password');
            });
        });

        const updateRouteTemplate = @json(route('admin.users.update', ['user' => '__USER_ID__']));
        const deleteRouteTemplate = @json(route('admin.users.destroy', ['user' => '__USER_ID__']));
        const editBackdrop = document.getElementById('editUserBackdrop');
        const deleteBackdrop = document.getElementById('deleteUserBackdrop');
        const editForm = document.getElementById('editUserForm');
        const deleteForm = document.getElementById('deleteUserForm');
        const deleteUserName = document.getElementById('deleteUserName');
        const editNameInput = document.getElementById('edit_user_name');
        const editUsernameInput = document.getElementById('edit_user_username');
        const editEmailInput = document.getElementById('edit_user_email');
        const editDepartmentSelect = document.getElementById('edit_user_department');
        const editActiveInput = document.getElementById('edit_user_is_active');
        const editPasswordInput = document.getElementById('edit_user_password');
        const editPasswordConfirmInput = document.getElementById('edit_user_password_confirmation');

        const closeBackdrop = function (backdrop) {
            if (!backdrop) {
                return;
            }

            backdrop.classList.remove('is-open');
            backdrop.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        const openBackdrop = function (backdrop) {
            if (!backdrop) {
                return;
            }

            backdrop.classList.add('is-open');
            backdrop.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        const editButtons = Array.from(document.querySelectorAll('.um-user-edit-btn'));
        editButtons.forEach((button) => {
            button.addEventListener('click', function () {
                const userId = button.dataset.userId || '';
                if (!userId || !editForm || !editNameInput || !editUsernameInput || !editEmailInput || !editDepartmentSelect || !editActiveInput) {
                    return;
                }

                editForm.setAttribute('action', updateRouteTemplate.replace('__USER_ID__', userId));
                editNameInput.value = button.dataset.userName || '';
                editUsernameInput.value = button.dataset.userUsername || '';
                editEmailInput.value = button.dataset.userEmail || '';
                editDepartmentSelect.value = button.dataset.userDepartment || '';
                editActiveInput.checked = (button.dataset.userActive || '0') === '1';

                if (editPasswordInput) {
                    editPasswordInput.value = '';
                }
                if (editPasswordConfirmInput) {
                    editPasswordConfirmInput.value = '';
                }

                openBackdrop(editBackdrop);
            });
        });

        const deleteButtons = Array.from(document.querySelectorAll('.um-user-delete-btn'));
        deleteButtons.forEach((button) => {
            button.addEventListener('click', function () {
                const userId = button.dataset.userId || '';
                if (!userId || !deleteForm || !deleteUserName) {
                    return;
                }

                deleteForm.setAttribute('action', deleteRouteTemplate.replace('__USER_ID__', userId));
                deleteUserName.textContent = button.dataset.userName || 'this user';
                openBackdrop(deleteBackdrop);
            });
        });

        const editCloseButtons = [
            document.getElementById('editUserCloseTop'),
            document.getElementById('editUserCancelBtn'),
        ];
        editCloseButtons.forEach((button) => {
            if (!button) {
                return;
            }
            button.addEventListener('click', function () {
                closeBackdrop(editBackdrop);
            });
        });

        const deleteCloseButtons = [
            document.getElementById('deleteUserCloseTop'),
            document.getElementById('deleteUserCancelBtn'),
        ];
        deleteCloseButtons.forEach((button) => {
            if (!button) {
                return;
            }
            button.addEventListener('click', function () {
                closeBackdrop(deleteBackdrop);
            });
        });

        if (editBackdrop) {
            editBackdrop.addEventListener('click', function (event) {
                if (event.target === editBackdrop) {
                    closeBackdrop(editBackdrop);
                }
            });
        }

        if (deleteBackdrop) {
            deleteBackdrop.addEventListener('click', function (event) {
                if (event.target === deleteBackdrop) {
                    closeBackdrop(deleteBackdrop);
                }
            });
        }

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
            if (event.key !== 'Escape') {
                return;
            }

            if (modalBackdrop.classList.contains('is-open')) {
                closeReassignModal();
            }
            if (editBackdrop?.classList.contains('is-open')) {
                closeBackdrop(editBackdrop);
            }
            if (deleteBackdrop?.classList.contains('is-open')) {
                closeBackdrop(deleteBackdrop);
            }
        });
    });
</script>
@endsection
