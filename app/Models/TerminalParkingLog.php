<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class TerminalParkingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_number',
        'terminal_vehicle_id',
        'entry_at',
        'exit_at',
        'recorded_by_user_id',
    ];

    protected $casts = [
        'entry_at' => 'datetime',
        'exit_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(TerminalVehicle::class, 'terminal_vehicle_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(TerminalParkingPayment::class, 'terminal_parking_log_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function billedHours(?Carbon $at = null): float
    {
        $start = $this->entry_at;
        $end = $this->exit_at ?? $at ?? now();
        if (! $start instanceof Carbon || ! $end instanceof Carbon) {
            return 0.0;
        }

        $minutes = max(0, $start->diffInMinutes($end));
        $hours = (int) ceil($minutes / 60);
        return (float) max(1, $hours);
    }

    public function parkingRate(): float
    {
        return (float) ($this->vehicle?->type?->parking_fee_per_hour ?? 0);
    }

    public function billedAmount(?Carbon $at = null): float
    {
        return round($this->billedHours($at) * $this->parkingRate(), 2);
    }
}
