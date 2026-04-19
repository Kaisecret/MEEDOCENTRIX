<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtriumFunctionHall extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'capacity',
        'hourly_rate',
        'description',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(AtriumEvent::class, 'atrium_function_hall_id');
    }
}
