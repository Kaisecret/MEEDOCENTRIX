<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'allows_collectors',
        'direct_payment_only',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allows_collectors' => 'boolean',
            'direct_payment_only' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function collectorAssignments(): HasMany
    {
        return $this->hasMany(CollectorDepartmentAssignment::class);
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class);
    }
}
