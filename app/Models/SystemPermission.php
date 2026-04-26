<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SystemPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'module',
        'action',
        'label',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(SystemRole::class, 'system_permission_role')
            ->withTimestamps();
    }
}
