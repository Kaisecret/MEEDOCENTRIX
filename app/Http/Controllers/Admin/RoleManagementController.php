<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollectorDepartmentAssignment;
use App\Models\Department;
use App\Models\RoleAuditLog;
use App\Models\SystemPermission;
use App\Models\SystemRole;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const GUARDS = ['admin', 'personnel', 'collector', 'cashier'];

    public function index(): View
    {
        $roles = SystemRole::query()
            ->with(['permissions' => static fn ($query) => $query->orderBy('sort_order')])
            ->withCount('userAssignments')
            ->orderByDesc('is_system')
            ->orderBy('guard_name')
            ->orderBy('name')
            ->get();

        $permissions = SystemPermission::query()
            ->orderBy('sort_order')
            ->get()
            ->groupBy('module');

        $users = User::query()
            ->with(['roleAssignment.role', 'roleAssignment.department', 'collectorAssignment.department'])
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $recentAuditLogs = RoleAuditLog::query()
            ->with(['actor:id,name', 'targetUser:id,name', 'role:id,name'])
            ->latest()
            ->limit(18)
            ->get();

        return view('admin.roles', [
            'roles' => $roles,
            'permissions' => $permissions,
            'users' => $users,
            'departments' => $departments,
            'collectorDepartments' => $departments->where('allows_collectors', true)->values(),
            'recentAuditLogs' => $recentAuditLogs,
            'guardOptions' => self::GUARDS,
            'stats' => [
                'roles' => $roles->count(),
                'custom_roles' => $roles->where('is_system', false)->count(),
                'permissions' => $permissions->flatten(1)->count(),
                'assigned_users' => $users->filter(static fn (User $user): bool => $user->roleAssignment !== null)->count(),
            ],
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'guard_name' => ['required', Rule::in(self::GUARDS)],
            'department_scope' => ['nullable', 'string', Rule::exists('departments', 'code')],
            'description' => ['nullable', 'string', 'max:1000'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', Rule::exists('system_permissions', 'id')],
        ]);

        $role = DB::transaction(function () use ($validated): SystemRole {
            $role = SystemRole::query()->create([
                'key' => $this->uniqueRoleKey((string) $validated['name']),
                'name' => trim((string) $validated['name']),
                'guard_name' => (string) $validated['guard_name'],
                'department_scope' => $validated['guard_name'] === 'personnel'
                    ? ($validated['department_scope'] ?? null)
                    : null,
                'description' => trim((string) ($validated['description'] ?? '')) ?: null,
                'is_system' => false,
                'is_active' => true,
            ]);

            $permissionIds = $validated['guard_name'] === 'admin'
                ? SystemPermission::query()->pluck('id')->all()
                : array_map('intval', $validated['permission_ids'] ?? []);

            $role->permissions()->sync($permissionIds);

            $this->logRoleAction('role_created', $role, null, [
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'department_scope' => $role->department_scope,
                'permission_count' => count($permissionIds),
            ]);

            return $role;
        });

        return redirect()
            ->route('admin.roles', ['role' => $role->id])
            ->with('status', "Role {$role->name} created.");
    }

    public function updateRole(Request $request, SystemRole $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'guard_name' => ['required', Rule::in(self::GUARDS)],
            'department_scope' => ['nullable', 'string', Rule::exists('departments', 'code')],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($role->isAdministrator() && ! $request->boolean('is_active', true)) {
            return redirect()
                ->route('admin.roles', ['role' => $role->id])
                ->with('error', 'Administrator role cannot be deactivated.');
        }

        $before = $role->only(['name', 'guard_name', 'department_scope', 'description', 'is_active']);

        DB::transaction(function () use ($role, $validated, $request, $before): void {
            $role->update([
                'name' => trim((string) $validated['name']),
                'guard_name' => $role->is_system ? $role->guard_name : (string) $validated['guard_name'],
                'department_scope' => $role->is_system
                    ? $role->department_scope
                    : ((string) $validated['guard_name'] === 'personnel' ? ($validated['department_scope'] ?? null) : null),
                'description' => trim((string) ($validated['description'] ?? '')) ?: null,
                'is_active' => $request->boolean('is_active', false),
            ]);

            $this->logRoleAction('role_updated', $role, null, [
                'before' => $before,
                'after' => $role->fresh()?->only(['name', 'guard_name', 'department_scope', 'description', 'is_active']),
            ]);
        });

        return redirect()
            ->route('admin.roles', ['role' => $role->id])
            ->with('status', "Role {$role->name} updated.");
    }

    public function updatePermissions(Request $request, SystemRole $role): RedirectResponse
    {
        $validated = $request->validate([
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', Rule::exists('system_permissions', 'id')],
        ]);

        $permissionIds = $role->isAdministrator()
            ? SystemPermission::query()->pluck('id')->all()
            : array_map('intval', $validated['permission_ids'] ?? []);

        DB::transaction(function () use ($role, $permissionIds): void {
            $before = $role->permissions()->pluck('system_permissions.key')->all();
            $role->permissions()->sync($permissionIds);
            $after = $role->permissions()->pluck('system_permissions.key')->all();

            $this->logRoleAction('permissions_updated', $role, null, [
                'before' => $before,
                'after' => $after,
            ]);
        });

        return redirect()
            ->route('admin.roles', ['role' => $role->id])
            ->with('status', "Permissions updated for {$role->name}.");
    }

    public function assignUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'system_role_id' => ['required', 'integer', Rule::exists('system_roles', 'id')],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = User::query()->findOrFail((int) $validated['user_id']);
        $role = SystemRole::query()->findOrFail((int) $validated['system_role_id']);

        if (! $role->is_active) {
            return redirect()
                ->route('admin.roles')
                ->with('error', 'Inactive roles cannot be assigned.');
        }

        if ($this->wouldRemoveLastAdministrator($user, $role)) {
            return redirect()
                ->route('admin.roles')
                ->with('error', 'At least one active administrator must remain.');
        }

        try {
            DB::transaction(function () use ($validated, $user, $role): void {
                $department = $this->resolveAssignmentDepartment($role, $validated['department_id'] ?? null);
                $before = [
                    'role' => $user->roleAssignment?->role?->name ?? $user->roleLabel(),
                    'department' => $user->roleAssignment?->department?->name ?? $user->department,
                ];

                UserRoleAssignment::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'system_role_id' => $role->id,
                        'department_id' => $department?->id,
                        'assigned_by_user_id' => Auth::id(),
                        'assigned_at' => now(),
                        'notes' => trim((string) ($validated['notes'] ?? '')) ?: null,
                    ]
                );

                $this->syncLegacyUserFields($user, $role, $department);
                $this->syncCollectorAssignment($user, $role, $department);

                $this->logRoleAction('user_role_assigned', $role, $user, [
                    'before' => $before,
                    'after' => [
                        'role' => $role->name,
                        'department' => $department?->name,
                    ],
                ], trim((string) ($validated['notes'] ?? '')) ?: null);
            });
        } catch (\InvalidArgumentException $exception) {
            return redirect()
                ->route('admin.roles')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.roles')
            ->with('status', "{$user->name} is now assigned to {$role->name}.");
    }

    private function uniqueRoleKey(string $name): string
    {
        $base = Str::of($name)->slug('_')->lower()->value() ?: 'custom_role';
        $key = $base;
        $counter = 2;

        while (SystemRole::query()->where('key', $key)->exists()) {
            $key = $base . '_' . $counter;
            $counter++;
        }

        return $key;
    }

    private function resolveAssignmentDepartment(SystemRole $role, mixed $departmentId): ?Department
    {
        if ($role->department_scope) {
            return Department::query()
                ->where('code', $role->department_scope)
                ->where('is_active', true)
                ->firstOrFail();
        }

        if ($role->guard_name === 'personnel') {
            if (! $departmentId) {
                throw new \InvalidArgumentException('Department is required for personnel roles.');
            }

            return Department::query()
                ->whereKey((int) $departmentId)
                ->where('is_active', true)
                ->firstOrFail();
        }

        if ($role->guard_name === 'collector') {
            if (! $departmentId) {
                throw new \InvalidArgumentException('Collector roles require a collection department assignment.');
            }

            $department = Department::query()
                ->whereKey((int) $departmentId)
                ->where('is_active', true)
                ->where('allows_collectors', true)
                ->first();

            if (! $department) {
                throw new \InvalidArgumentException('Collectors can only be assigned to departments that allow collection.');
            }

            return $department;
        }

        return null;
    }

    private function syncLegacyUserFields(User $user, SystemRole $role, ?Department $department): void
    {
        $payload = match ($role->guard_name) {
            'admin' => ['role' => 'administrator', 'department' => null],
            'collector' => ['role' => 'collector', 'department' => 'collector'],
            'cashier' => ['role' => 'cashier', 'department' => 'cashier'],
            default => ['role' => 'personnel', 'department' => $department?->code],
        };

        $user->forceFill($payload)->save();
    }

    private function syncCollectorAssignment(User $user, SystemRole $role, ?Department $department): void
    {
        if ($role->guard_name !== 'collector') {
            CollectorDepartmentAssignment::query()
                ->where('collector_user_id', $user->id)
                ->delete();

            return;
        }

        if (! $department) {
            return;
        }

        CollectorDepartmentAssignment::query()->updateOrCreate(
            ['collector_user_id' => $user->id],
            [
                'department_id' => $department->id,
                'assigned_by_user_id' => Auth::id(),
            ]
        );
    }

    private function wouldRemoveLastAdministrator(User $user, SystemRole $nextRole): bool
    {
        if ($nextRole->isAdministrator()) {
            return false;
        }

        if (! $user->isAdmin()) {
            return false;
        }

        $adminRoleIds = SystemRole::query()
            ->where('guard_name', 'admin')
            ->orWhere('key', 'administrator')
            ->pluck('id');

        $adminCount = User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($adminRoleIds): void {
                $query->whereIn('role', ['admin', 'administrator'])
                    ->orWhereHas('roleAssignment', static function ($assignmentQuery) use ($adminRoleIds): void {
                        $assignmentQuery->whereIn('system_role_id', $adminRoleIds);
                    });
            })
            ->distinct()
            ->count('users.id');

        return $adminCount <= 1;
    }

    /**
     * @param array<string, mixed> $changes
     */
    private function logRoleAction(
        string $action,
        ?SystemRole $role = null,
        ?User $targetUser = null,
        array $changes = [],
        ?string $notes = null
    ): void {
        RoleAuditLog::query()->create([
            'actor_user_id' => Auth::id(),
            'target_user_id' => $targetUser?->id,
            'system_role_id' => $role?->id,
            'action' => $action,
            'changes' => $changes,
            'notes' => $notes,
        ]);
    }
}
