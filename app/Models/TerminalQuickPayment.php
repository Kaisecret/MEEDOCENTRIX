<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminalQuickPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payer_name',
        'ticket_number',
        'vehicle_kind',
        'route_name',
        'route_code',
        'total_payment',
        'payment_date',
        'remarks',
        'recorded_by_user_id',
        'is_paid',
        'paid_at',
        'paid_by_user_id',
    ];

    protected $casts = [
        'total_payment' => 'decimal:2',
        'payment_date' => 'datetime',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }
}
