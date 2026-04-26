<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_roles')) {
            Schema::create('system_roles', function (Blueprint $table) {
                $table->id();
                $table->string('key', 80)->unique();
                $table->string('name', 120);
                $table->string('guard_name', 40)->default('personnel');
                $table->string('department_scope', 50)->nullable()->index();
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('system_permissions')) {
            Schema::create('system_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('key', 120)->unique();
                $table->string('module', 80);
                $table->string('action', 40);
                $table->string('label', 160);
                $table->string('description', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['module', 'action']);
            });
        }

        if (! Schema::hasTable('system_permission_role')) {
            Schema::create('system_permission_role', function (Blueprint $table) {
                $table->id();
                $table->foreignId('system_role_id')->constrained('system_roles')->cascadeOnDelete();
                $table->foreignId('system_permission_id')->constrained('system_permissions')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['system_role_id', 'system_permission_id'], 'uniq_system_permission_role');
            });
        }

        if (! Schema::hasTable('user_role_assignments')) {
            Schema::create('user_role_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('system_role_id')->constrained('system_roles')->restrictOnDelete();
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique('user_id');
                $table->index(['system_role_id', 'department_id']);
            });
        }

        if (! Schema::hasTable('role_audit_logs')) {
            Schema::create('role_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('system_role_id')->nullable()->constrained('system_roles')->nullOnDelete();
                $table->string('action', 80);
                $table->json('changes')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['action', 'created_at']);
                $table->index('target_user_id');
            });
        }

        $this->seedPermissions();
        $this->seedRoles();
        $this->seedRolePermissions();
        $this->backfillUserAssignments();
    }

    public function down(): void
    {
        Schema::dropIfExists('role_audit_logs');
        Schema::dropIfExists('user_role_assignments');
        Schema::dropIfExists('system_permission_role');
        Schema::dropIfExists('system_permissions');
        Schema::dropIfExists('system_roles');
    }

    private function seedPermissions(): void
    {
        $now = now();
        $rows = [];
        $order = 10;

        $groups = [
            'Admin Control' => [
                ['admin.dashboard.view', 'View admin dashboard'],
                ['admin.users.view', 'View user management'],
                ['admin.users.create', 'Create user accounts'],
                ['admin.users.update', 'Update user accounts'],
                ['admin.roles.view', 'View roles and permissions'],
                ['admin.roles.create', 'Create roles'],
                ['admin.roles.update', 'Update roles and permissions'],
                ['admin.roles.assign', 'Assign roles to users'],
                ['admin.rates.view', 'View rates and fees'],
                ['admin.rates.update', 'Update rates and fees'],
                ['admin.transactions.view', 'View all transactions'],
                ['admin.reports.view', 'View admin reports'],
                ['admin.reports.export', 'Export admin reports'],
            ],
            'Fishport' => [
                ['fishport.dashboard.view', 'View fishport dashboard'],
                ['fishport.records.view', 'View fishport records'],
                ['fishport.records.create', 'Create fishport records'],
                ['fishport.records.update', 'Update fishport records'],
                ['fishport.records.delete', 'Delete fishport records'],
                ['fishport.payments.send', 'Send fishport payments to collector'],
                ['fishport.payments.approve', 'Approve fishport collections'],
                ['fishport.reports.view', 'View fishport reports'],
                ['fishport.reports.export', 'Export fishport reports'],
            ],
            'Public Market' => [
                ['market.dashboard.view', 'View market dashboard'],
                ['market.stalls.view', 'View stalls and tenants'],
                ['market.stalls.create', 'Create market stalls'],
                ['market.stalls.update', 'Update market stalls'],
                ['market.stalls.delete', 'Delete market stalls'],
                ['market.payments.send', 'Send market payments to collector'],
                ['market.payments.approve', 'Approve market collections'],
                ['market.reports.view', 'View market reports'],
                ['market.reports.export', 'Export market reports'],
            ],
            'Cemetery' => [
                ['cemetery.dashboard.view', 'View cemetery dashboard'],
                ['cemetery.records.view', 'View occupant records'],
                ['cemetery.records.create', 'Create occupant records'],
                ['cemetery.records.update', 'Update occupant records'],
                ['cemetery.records.delete', 'Delete occupant records'],
                ['cemetery.transactions.view', 'View cemetery transactions'],
                ['cemetery.transactions.create', 'Create cemetery transactions'],
                ['cemetery.payments.collect', 'Collect cemetery payments'],
                ['cemetery.reports.view', 'View cemetery reports'],
                ['cemetery.reports.export', 'Export cemetery reports'],
            ],
            'Terminal' => [
                ['terminal.dashboard.view', 'View terminal dashboard'],
                ['terminal.vehicles.view', 'View terminal vehicles'],
                ['terminal.vehicles.create', 'Create terminal vehicles'],
                ['terminal.vehicles.update', 'Update terminal vehicles'],
                ['terminal.records.view', 'View terminal records'],
                ['terminal.payments.collect', 'Collect terminal payments'],
            ],
            'Atrium Hall' => [
                ['atrium.dashboard.view', 'View atrium dashboard'],
                ['atrium.bookings.view', 'View atrium bookings'],
                ['atrium.bookings.create', 'Create atrium bookings'],
                ['atrium.bookings.update', 'Update atrium bookings'],
                ['atrium.bookings.delete', 'Delete atrium bookings'],
                ['atrium.payments.collect', 'Collect atrium payments'],
                ['atrium.supplies.manage', 'Manage atrium supplies'],
                ['atrium.reports.view', 'View atrium reports'],
                ['atrium.reports.export', 'Export atrium reports'],
            ],
            'Collector' => [
                ['collector.dashboard.view', 'View collector dashboard'],
                ['collector.collections.view', 'View assigned collections'],
                ['collector.collections.collect', 'Record collected payments'],
                ['collector.collections.submit_proof', 'Submit proof of collection'],
                ['collector.reports.view', 'View collector reports'],
            ],
            'Cashier' => [
                ['cashier.dashboard.view', 'View cashier dashboard'],
                ['cashier.remittance.view', 'View remittances'],
                ['cashier.remittance.verify', 'Verify remittances'],
                ['cashier.collections.view', 'View official collections'],
                ['cashier.summary.view', 'View daily summary'],
            ],
        ];

        foreach ($groups as $module => $permissions) {
            foreach ($permissions as [$key, $label]) {
                $parts = explode('.', $key);
                $rows[] = [
                    'key' => $key,
                    'module' => $module,
                    'action' => end($parts),
                    'label' => $label,
                    'description' => null,
                    'sort_order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $order += 10;
            }
        }

        DB::table('system_permissions')->upsert(
            $rows,
            ['key'],
            ['module', 'action', 'label', 'description', 'sort_order', 'updated_at']
        );
    }

    private function seedRoles(): void
    {
        $now = now();
        DB::table('system_roles')->upsert([
            [
                'key' => 'administrator',
                'name' => 'Administrator',
                'guard_name' => 'admin',
                'department_scope' => null,
                'description' => 'Full system administrator with access to all departments and settings.',
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'fishport_personnel',
                'name' => 'Fishport Personnel',
                'guard_name' => 'personnel',
                'department_scope' => 'fishport',
                'description' => 'Operational access for fishport records, payments, and reports.',
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'market_personnel',
                'name' => 'Public Market Personnel',
                'guard_name' => 'personnel',
                'department_scope' => 'market',
                'description' => 'Operational access for stall, tenant, market payment, and report workflows.',
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'cemetery_personnel',
                'name' => 'Cemetery Personnel',
                'guard_name' => 'personnel',
                'department_scope' => 'cemetery',
                'description' => 'Operational access for cemetery records, transactions, payment collection, and reports.',
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'terminal_personnel',
                'name' => 'Terminal Personnel',
                'guard_name' => 'personnel',
                'department_scope' => 'terminal',
                'description' => 'Operational access for terminal vehicles, records, and payment workflows.',
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'atrium_personnel',
                'name' => 'Atrium Hall Personnel',
                'guard_name' => 'personnel',
                'department_scope' => 'atrium',
                'description' => 'Operational access for bookings, payments, supplies, and atrium reports.',
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'collector',
                'name' => 'Assigned Collector',
                'guard_name' => 'collector',
                'department_scope' => null,
                'description' => 'Collector access for assigned collection queues and proof submission.',
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'cashier',
                'name' => 'Main Cashier',
                'guard_name' => 'cashier',
                'department_scope' => null,
                'description' => 'Cashier access for remittance verification and official collection summaries.',
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['key'], ['name', 'guard_name', 'department_scope', 'description', 'is_system', 'is_active', 'updated_at']);
    }

    private function seedRolePermissions(): void
    {
        $permissionIds = DB::table('system_permissions')->pluck('id', 'key');
        $roleIds = DB::table('system_roles')->pluck('id', 'key');
        $now = now();

        $rolePermissionKeys = [
            'administrator' => $permissionIds->keys()->all(),
            'fishport_personnel' => array_values(array_filter($permissionIds->keys()->all(), static fn (string $key): bool => str_starts_with($key, 'fishport.'))),
            'market_personnel' => array_values(array_filter($permissionIds->keys()->all(), static fn (string $key): bool => str_starts_with($key, 'market.'))),
            'cemetery_personnel' => array_values(array_filter($permissionIds->keys()->all(), static fn (string $key): bool => str_starts_with($key, 'cemetery.'))),
            'terminal_personnel' => array_values(array_filter($permissionIds->keys()->all(), static fn (string $key): bool => str_starts_with($key, 'terminal.'))),
            'atrium_personnel' => array_values(array_filter($permissionIds->keys()->all(), static fn (string $key): bool => str_starts_with($key, 'atrium.'))),
            'collector' => array_values(array_filter($permissionIds->keys()->all(), static fn (string $key): bool => str_starts_with($key, 'collector.'))),
            'cashier' => array_values(array_filter($permissionIds->keys()->all(), static fn (string $key): bool => str_starts_with($key, 'cashier.'))),
        ];

        $rows = [];
        foreach ($rolePermissionKeys as $roleKey => $keys) {
            $roleId = $roleIds[$roleKey] ?? null;
            if (! $roleId) {
                continue;
            }

            foreach ($keys as $permissionKey) {
                $permissionId = $permissionIds[$permissionKey] ?? null;
                if (! $permissionId) {
                    continue;
                }

                $rows[] = [
                    'system_role_id' => $roleId,
                    'system_permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('system_permission_role')->upsert(
                $rows,
                ['system_role_id', 'system_permission_id'],
                ['updated_at']
            );
        }
    }

    private function backfillUserAssignments(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('user_role_assignments')) {
            return;
        }

        $roleIds = DB::table('system_roles')->pluck('id', 'key');
        $departmentIds = Schema::hasTable('departments')
            ? DB::table('departments')->pluck('id', 'code')
            : collect();
        $now = now();
        $rows = [];

        DB::table('users')
            ->select(['id', 'role', 'department'])
            ->orderBy('id')
            ->get()
            ->each(function (object $user) use ($roleIds, $departmentIds, $now, &$rows): void {
                $role = strtolower(trim((string) ($user->role ?? 'personnel')));
                $department = strtolower(trim((string) ($user->department ?? '')));
                $roleKey = match (true) {
                    in_array($role, ['admin', 'administrator'], true) => 'administrator',
                    $role === 'collector' || $department === 'collector' => 'collector',
                    $role === 'cashier' || $department === 'cashier' => 'cashier',
                    $department === 'fishport' => 'fishport_personnel',
                    $department === 'market' => 'market_personnel',
                    $department === 'cemetery' => 'cemetery_personnel',
                    $department === 'terminal' => 'terminal_personnel',
                    $department === 'atrium' => 'atrium_personnel',
                    default => 'market_personnel',
                };

                $rows[] = [
                    'user_id' => $user->id,
                    'system_role_id' => $roleIds[$roleKey] ?? $roleIds['market_personnel'],
                    'department_id' => $departmentIds[$department] ?? null,
                    'assigned_by_user_id' => null,
                    'assigned_at' => $now,
                    'notes' => 'Backfilled from existing user role and department fields.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

        if ($rows !== []) {
            DB::table('user_role_assignments')->upsert(
                $rows,
                ['user_id'],
                ['system_role_id', 'department_id', 'assigned_at', 'notes', 'updated_at']
            );
        }
    }
};
