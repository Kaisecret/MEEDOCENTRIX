<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerminalVehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'parking_fee_per_hour',
        'description',
        'is_active',
    ];

    protected $casts = [
        'parking_fee_per_hour' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vehicles(): HasMany
    {
        return $this->hasMany(TerminalVehicle::class, 'terminal_vehicle_type_id');
    }
}
