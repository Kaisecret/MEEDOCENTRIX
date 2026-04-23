<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        $role = strtolower(trim((string) $this->role));

        return $role === 'admin' || $role === 'administrator';
    }

    public function isCollector(): bool
    {
        return strtolower(trim((string) $this->role)) === 'collector'
            || strtolower(trim((string) $this->department)) === 'collector';
    }

    public function collectorAssignment(): HasOne
    {
        return $this->hasOne(CollectorDepartmentAssignment::class, 'collector_user_id');
    }

    public function sentCollectionDispatches(): HasMany
    {
        return $this->hasMany(CollectionDispatch::class, 'sent_by_user_id');
    }

    public function assignedCollectionDispatches(): HasMany
    {
        return $this->hasMany(CollectionDispatch::class, 'collector_user_id');
    }

    public function uiRoleKey(): string
    {
        if ($this->isAdmin()) {
            return 'administrator';
        }

        if ($this->isCollector()) {
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

    public function roleLabel(): string
    {
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
}
