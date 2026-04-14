<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CemeteryServiceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_code',
        'type_name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function serviceLogs(): HasMany
    {
        return $this->hasMany(CemeteryServiceLog::class, 'cemetery_service_type_id');
    }
}

