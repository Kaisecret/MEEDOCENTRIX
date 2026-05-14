<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

/**
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $role
 * @property string|null $department
 * @property bool|null $is_active
 * @property bool|null $is_absent
 * @property \Illuminate\Support\Carbon|null $absent_set_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'department',
        'is_active',
        'is_absent',
        'absent_set_at',
        'email_verified_at',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'is_absent' => 'boolean',
            'absent_set_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isCollectorAvailableForAssignment(): bool
    {
        return (bool) $this->is_active && ! (bool) $this->is_absent;
    }

    public function isAdmin(): bool
    {
        $role = strtolower(trim((string) $this->role));

        if ($role === 'admin' || $role === 'administrator') {
            return true;
        }

        $assignedRole = $this->assignedSystemRole();

        return $assignedRole?->isAdministrator() ?? false;
    }

    public function isCollector(): bool
    {
        if (
            strtolower(trim((string) $this->role)) === 'collector'
            || strtolower(trim((string) $this->department)) === 'collector'
        ) {
            return true;
        }

        return $this->assignedSystemRole()?->guard_name === 'collector';
    }

    public function collectorAssignment(): HasOne
    {
        return $this->hasOne(CollectorDepartmentAssignment::class, 'collector_user_id');
    }

    public function roleAssignment(): HasOne
    {
        return $this->hasOne(UserRoleAssignment::class);
    }

    public function sentCollectionDispatches(): HasMany
    {
        return $this->hasMany(CollectionDispatch::class, 'sent_by_user_id');
    }

    public function assignedCollectionDispatches(): HasMany
    {
        return $this->hasMany(CollectionDispatch::class, 'collector_user_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function uiRoleKey(): string
    {
        $assignedRole = $this->assignedSystemRole();
        if ($assignedRole) {
            if ($assignedRole->isAdministrator()) {
                return 'administrator';
            }

            if ($assignedRole->guard_name === 'collector') {
                return 'collector';
            }

            if ($assignedRole->guard_name === 'cashier') {
                return 'cashier';
            }

            $assignedDepartment = $this->assignedDepartmentCode();
            if ($assignedDepartment) {
                return $assignedDepartment;
            }

            if ($assignedRole->department_scope) {
                return $assignedRole->department_scope;
            }
        }

        return $this->legacyUiRoleKey();
    }

    public function roleLabel(): string
    {
        $assignedRole = $this->assignedSystemRole();
        if ($assignedRole) {
            return $assignedRole->name;
        }

        return match ($this->uiRoleKey()) {
            'administrator' => 'Administrator',
            'fishport' => 'Fishport Personnel',
            'market' => 'Market Personnel',
            'cemetery' => 'Cemetery Personnel',
            'terminal' => 'Terminal Personnel',
            'atrium' => 'Atrium Hall Personnel',
            'collector' => 'Assigned Collector',
            'cashier' => 'Main Cashier',
            default => 'Personnel',
        };
    }

    public function dashboardRouteName(): string
    {
        return match ($this->uiRoleKey()) {
            'administrator' => 'admin.dashboard',
            'fishport' => 'fishport.dashboard',
            'market' => 'market.dashboard',
            'cemetery' => 'cemetery.dashboard',
            'terminal' => 'terminal.dashboard',
            'atrium' => 'atrium.dashboard',
            'collector' => 'collector.dashboard',
            'cashier' => 'cashier.dashboard',
            default => 'home',
        };
    }

    public function hasPermission(string $permissionKey): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->roleTablesAvailable()) {
            return $this->legacyHasPermission($permissionKey);
        }

        $assignedRole = $this->assignedSystemRole();
        if (! $assignedRole || ! $assignedRole->is_active) {
            return $this->legacyHasPermission($permissionKey);
        }

        if ($assignedRole->relationLoaded('permissions')) {
            return $assignedRole->permissions->contains('key', $permissionKey);
        }

        return $assignedRole->permissions()
            ->where('system_permissions.key', $permissionKey)
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    public function permissionKeys(): array
    {
        if ($this->isAdmin()) {
            if (! $this->roleTablesAvailable()) {
                return ['*'];
            }

            return SystemPermission::query()->pluck('key')->all();
        }

        $assignedRole = $this->assignedSystemRole();
        if (! $assignedRole || ! $assignedRole->is_active) {
            return [];
        }

        return $assignedRole->permissions()
            ->orderBy('sort_order')
            ->pluck('key')
            ->all();
    }

    public function assignedDepartmentCode(): ?string
    {
        if ($this->roleTablesAvailable()) {
            $assignment = $this->resolvedRoleAssignment();
            $department = $assignment?->department;
            if ($department?->code) {
                return (string) $department->code;
            }

            if ($assignment?->role?->department_scope) {
                return (string) $assignment->role->department_scope;
            }
        }

        $department = strtolower(trim((string) $this->department));

        return $department !== '' ? $department : null;
    }

    private function assignedSystemRole(): ?SystemRole
    {
        if (! $this->roleTablesAvailable()) {
            return null;
        }

        return $this->resolvedRoleAssignment()?->role;
    }

    private function resolvedRoleAssignment(): ?UserRoleAssignment
    {
        if (! $this->roleTablesAvailable() || ! $this->exists) {
            return null;
        }

        if ($this->relationLoaded('roleAssignment')) {
            $assignment = $this->getRelation('roleAssignment');
            if ($assignment && ! $assignment->relationLoaded('role')) {
                $assignment->load('role.permissions', 'department');
            }

            return $assignment;
        }

        return $this->roleAssignment()
            ->with('role.permissions', 'department')
            ->first();
    }

    private function legacyUiRoleKey(): string
    {
        $role = strtolower(trim((string) $this->role));

        if ($role === 'admin' || $role === 'administrator') {
            return 'administrator';
        }

        if (
            $role === 'collector'
            || strtolower(trim((string) $this->department)) === 'collector'
        ) {
            return 'collector';
        }

        return match (strtolower(trim((string) $this->department))) {
            'fishport' => 'fishport',
            'market' => 'market',
            'cemetery' => 'cemetery',
            'terminal' => 'terminal',
            'atrium' => 'atrium',
            'cashier' => 'cashier',
            default => 'market',
        };
    }

    private function legacyHasPermission(string $permissionKey): bool
    {
        $roleKey = $this->legacyUiRoleKey();

        return $roleKey === 'administrator'
            || str_starts_with($permissionKey, $roleKey . '.');
    }

    private function roleTablesAvailable(): bool
    {
        static $available = null;

        if ($available === null) {
            $available = Schema::hasTable('system_roles')
                && Schema::hasTable('system_permissions')
                && Schema::hasTable('user_role_assignments');
        }

        return $available;
    }
}
