<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollectorDepartmentAssignment;
use App\Models\Department;
use App\Models\SystemRole;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private const DEPARTMENTS = [
        'fishport',
        'market',
        'cemetery',
        'terminal',
        'atrium',
        'collector',
        'cashier',
    ];

    private const COLLECTOR_ASSIGNABLE_DEPARTMENTS = [
        'fishport',
        'market',
        'atrium',
    ];

    public function index(): View
    {
        $hasCollectorSchema = Schema::hasTable('departments')
            && Schema::hasTable('collector_department_assignments');
        $hasRoleSchema = Schema::hasTable('system_roles')
            && Schema::hasTable('user_role_assignments');

        $usersQuery = User::query()->latest();
        if ($hasCollectorSchema) {
            $usersQuery->with('collectorAssignment.department');
        }
        if ($hasRoleSchema) {
            $usersQuery->with(['roleAssignment.role', 'roleAssignment.department']);
        }

        $collectorAccountsQuery = User::query()
            ->where(function ($query) {
                $query->where('role', 'collector')
                    ->orWhere('department', 'collector');
            })
            ->orderBy('name');

        if ($hasCollectorSchema) {
            $collectorAccountsQuery->with('collectorAssignment.department');
        }
        if ($hasRoleSchema) {
            $collectorAccountsQuery->with(['roleAssignment.role', 'roleAssignment.department']);
        }

        $collectorDepartments = collect();
        if (Schema::hasTable('departments')) {
            $collectorDepartments = Department::query()
                ->where('is_active', true)
                ->where('allows_collectors', true)
                ->whereIn('code', self::COLLECTOR_ASSIGNABLE_DEPARTMENTS)
                ->orderBy('name')
                ->get();
        }

        $collectorAccounts = $collectorAccountsQuery->get();
        $assignedCollectorCount = $hasCollectorSchema
            ? $collectorAccounts->filter(static fn (User $collector) => $collector->collectorAssignment !== null)->count()
            : 0;

        return view('admin.users', [
            'users' => $usersQuery->get(),
            'departments' => self::DEPARTMENTS,
            'collectorAccounts' => $collectorAccounts,
            'collectorDepartments' => $collectorDepartments,
            'assignedCollectorCount' => $assignedCollectorCount,
            'hasCollectorSchema' => $hasCollectorSchema,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'department' => ['required', Rule::in(self::DEPARTMENTS)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isCollector = strtolower((string) $validated['department']) === 'collector';

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $isCollector ? 'collector' : 'personnel',
            'department' => $validated['department'],
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        $this->syncDefaultRoleAssignment($user, (string) $validated['department']);

        $statusMessage = $isCollector
            ? 'Collector account created. You can now assign this collector to Fishport, Public Market, or Atrium.'
            : 'User account created successfully.';

        return redirect()
            ->route('admin.users')
            ->with('status', $statusMessage)
            ->with('active_tab', $isCollector ? 'assignments' : 'users');
    }

    public function assignCollector(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('departments') || ! Schema::hasTable('collector_department_assignments')) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Collector assignment tables are not migrated yet. Run migrations first.')
                ->with('active_tab', 'assignments');
        }

        $validated = $request->validate([
            '_form' => ['nullable', 'string'],
            'collector_user_id' => ['required', 'integer', 'exists:users,id'],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
        ]);

        $collector = User::query()->findOrFail($validated['collector_user_id']);

        if (! $collector->isCollector()) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Selected user is not a collector account.')
                ->with('active_tab', 'assignments');
        }

        if (! $collector->is_active) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Collector account is inactive. Activate it first before assignment.')
                ->with('active_tab', 'assignments');
        }

        $department = Department::query()
            ->whereKey($validated['department_id'])
            ->where('is_active', true)
            ->where('allows_collectors', true)
            ->whereIn('code', self::COLLECTOR_ASSIGNABLE_DEPARTMENTS)
            ->first();

        if (! $department) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Collector can only be assigned to Fishport, Public Market, or Atrium.')
                ->with('active_tab', 'assignments');
        }

        $existingAssignment = CollectorDepartmentAssignment::query()
            ->where('collector_user_id', $collector->id)
            ->first();

        CollectorDepartmentAssignment::query()->updateOrCreate(
            ['collector_user_id' => $collector->id],
            [
                'department_id' => $department->id,
                'assigned_by_user_id' => $request->user()?->id,
            ]
        );

        $action = $existingAssignment ? 'updated' : 'created';

        return redirect()
            ->route('admin.users')
            ->with('status', "Collector assignment {$action}: {$collector->name} -> {$department->name}.")
            ->with('active_tab', 'assignments');
    }

    private function syncDefaultRoleAssignment(User $user, string $departmentCode): void
    {
        if (! Schema::hasTable('system_roles') || ! Schema::hasTable('user_role_assignments')) {
            return;
        }

        $departmentCode = strtolower(trim($departmentCode));
        $roleKey = match ($departmentCode) {
            'collector' => 'collector',
            'cashier' => 'cashier',
            'fishport' => 'fishport_personnel',
            'market' => 'market_personnel',
            'cemetery' => 'cemetery_personnel',
            'terminal' => 'terminal_personnel',
            'atrium' => 'atrium_personnel',
            default => 'market_personnel',
        };

        $role = SystemRole::query()->where('key', $roleKey)->first();
        if (! $role) {
            return;
        }

        $department = Department::query()
            ->where('code', $role->department_scope ?: $departmentCode)
            ->first();

        UserRoleAssignment::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'system_role_id' => $role->id,
                'department_id' => $department?->id,
                'assigned_by_user_id' => auth()->id(),
                'assigned_at' => now(),
                'notes' => 'Assigned during user creation.',
            ]
        );
    }
}
