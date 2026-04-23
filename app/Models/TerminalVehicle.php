<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerminalVehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'operator_name',
        'terminal_vehicle_type_id',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(TerminalVehicleType::class, 'terminal_vehicle_type_id');
    }

    public function parkingLogs(): HasMany
    {
        return $this->hasMany(TerminalParkingLog::class, 'terminal_vehicle_id');
    }
}
