<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\CollectionDispatchItem;
use App\Models\CollectorDepartmentAssignment;
use App\Models\Department;
use App\Models\SystemRole;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\MarketQueueLifecycle;
use Illuminate\Database\QueryException;
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
    ];

    private const COLLECTOR_ASSIGNABLE_DEPARTMENTS = [
        'fishport',
        'market',
    ];

    public function index(): View
    {
        $hasCollectorSchema = Schema::hasTable('departments')
            && Schema::hasTable('collector_department_assignments');
        $hasRoleSchema = Schema::hasTable('system_roles')
            && Schema::hasTable('user_role_assignments');

        $searchTerm = trim((string) request('q', ''));

        $usersQuery = User::query()->latest();
        if ($hasCollectorSchema) {
            $usersQuery->with('collectorAssignment.department');
        }
        if ($hasRoleSchema) {
            $usersQuery->with(['roleAssignment.role', 'roleAssignment.department']);
        }
        if ($searchTerm !== '') {
            $usersQuery->where(static function ($query) use ($searchTerm): void {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('username', 'like', '%' . $searchTerm . '%')
                    ->orWhere('role', 'like', '%' . $searchTerm . '%')
                    ->orWhere('department', 'like', '%' . $searchTerm . '%');
            });
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
            'users' => $usersQuery->paginate(10)->withQueryString(),
            'totalUsers' => User::query()->count(),
            'searchTerm' => $searchTerm,
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
            ? 'Collector account created. You can now assign this collector to Fishport or Public Market.'
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
                ->with('error', 'Collector can only be assigned to Fishport or Public Market.')
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

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            '_form' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'department' => ['required', Rule::in(self::DEPARTMENTS)],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($request->user()?->id === $user->id && ! $request->boolean('is_active', false)) {
            return redirect()
                ->route('admin.users', $this->usersQueryParamsFromRequest($request))
                ->with('error', 'You cannot deactivate your own account.')
                ->with('active_tab', 'users');
        }

        $isCollector = strtolower((string) $validated['department']) === 'collector';

        $payload = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'department' => $validated['department'],
            'role' => $isCollector ? 'collector' : 'personnel',
            'is_active' => $request->boolean('is_active', false),
        ];

        if (filled($validated['password'] ?? null)) {
            $payload['password'] = (string) $validated['password'];
        }

        $user->fill($payload);
        $user->save();

        if (! $isCollector && Schema::hasTable('collector_department_assignments')) {
            $user->collectorAssignment()?->delete();
        }

        $this->syncDefaultRoleAssignment($user, (string) $validated['department']);

        return redirect()
            ->route('admin.users', $this->usersQueryParamsFromRequest($request))
            ->with('status', 'User account updated successfully.')
            ->with('active_tab', 'users');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            return redirect()
                ->route('admin.users', $this->usersQueryParamsFromRequest($request))
                ->with('error', 'You cannot delete your own account.')
                ->with('active_tab', 'users');
        }

        try {
            $user->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.users', $this->usersQueryParamsFromRequest($request))
                ->with('error', 'Cannot delete this user because it is linked to existing transactions or records.')
                ->with('active_tab', 'users');
        }

        return redirect()
            ->route('admin.users', $this->usersQueryParamsFromRequest($request))
            ->with('status', 'User account deleted successfully.')
            ->with('active_tab', 'users');
    }

    public function generateMissedPaymentNotice(Request $request, User $collector): RedirectResponse
    {
        if (! $collector->isCollector()) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Selected user is not a collector account.')
                ->with('active_tab', 'assignments');
        }

        $assignment = CollectorDepartmentAssignment::query()
            ->with('department:id,code,name')
            ->where('collector_user_id', $collector->id)
            ->first();

        $departmentCode = strtolower(trim((string) ($assignment?->department?->code ?? '')));
        if (! in_array($departmentCode, self::COLLECTOR_ASSIGNABLE_DEPARTMENTS, true)) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Collector must be assigned to Fishport or Public Market before generating a missed-payment notice.')
                ->with('active_tab', 'assignments');
        }

        $cutoff = now()->subHours(MarketQueueLifecycle::SENT_TIMEOUT_HOURS);

        $missedQuery = CollectionDispatchItem::query()
            ->whereHas('dispatch', static function ($query) use ($collector, $departmentCode): void {
                $query->where('collector_user_id', (int) $collector->id)
                    ->where('department_code', $departmentCode);
            })
            ->where(static function ($query) use ($cutoff): void {
                $query->where('status', 'rejected')
                    ->orWhere(static function ($sentQuery) use ($cutoff): void {
                        $sentQuery->where('status', 'sent')
                            ->where('created_at', '<=', $cutoff);
                    });
            });

        $missedCount = (int) (clone $missedQuery)->count();
        if ($missedCount <= 0) {
            return redirect()
                ->route('admin.users')
                ->with('status', 'No missed payments found for ' . $collector->name . '.')
                ->with('active_tab', 'assignments');
        }

        $missedAmount = (float) (clone $missedQuery)->sum('amount_snapshot');
        $departmentName = (string) ($assignment?->department?->name ?? ucfirst($departmentCode));

        AppNotification::query()->create([
            'user_id' => (int) $collector->id,
            'type' => 'warning',
            'title' => 'Missed Payments Reminder',
            'message' => 'You currently have ' . number_format($missedCount) . ' missed payment item(s) in ' . $departmentName . ' totaling PHP ' . number_format($missedAmount, 2) . '. Please review your pending collections.',
            'action_url' => route('collector.pending_collections'),
            'event_key' => null,
            'payload' => [
                'department_code' => $departmentCode,
                'missed_count' => $missedCount,
                'missed_amount' => round($missedAmount, 2),
            ],
            'created_by_user_id' => $request->user()?->id,
            'is_read' => false,
            'read_at' => null,
        ]);

        return redirect()
            ->route('admin.users')
            ->with('status', 'Missed-payment notice sent to collector: ' . $collector->name . '.')
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

    /**
     * @return array<string, int|string>
     */
    private function usersQueryParamsFromRequest(Request $request): array
    {
        $params = [];

        $page = (int) $request->input('page', 1);
        if ($page > 1) {
            $params['page'] = $page;
        }

        $query = trim((string) $request->input('q', ''));
        if ($query !== '') {
            $params['q'] = $query;
        }

        return $params;
    }
}
