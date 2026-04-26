<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'guard_name',
        'department_scope',
        'description',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(SystemPermission::class, 'system_permission_role')
            ->withTimestamps();
    }

    public function userAssignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(RoleAuditLog::class);
    }

    public function isAdministrator(): bool
    {
        return $this->key === 'administrator' || $this->guard_name === 'admin';
    }
}
