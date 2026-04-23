<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminalParkingPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'terminal_parking_log_id',
        'or_number',
        'payment_date',
        'parking_rate_snapshot',
        'billed_hours_snapshot',
        'billed_amount',
        'paid_amount',
        'payment_status',
        'remarks',
        'recorded_by_user_id',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'parking_rate_snapshot' => 'decimal:2',
        'billed_hours_snapshot' => 'decimal:2',
        'billed_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function parkingLog(): BelongsTo
    {
        return $this->belongsTo(TerminalParkingLog::class, 'terminal_parking_log_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
