<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtriumEventPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'atrium_event_id',
        'or_number',
        'date_of_payment',
        'payment_amount',
        'payment_status',
        'remarks',
        'recorded_by_user_id',
    ];

    protected $casts = [
        'date_of_payment' => 'date',
        'payment_amount' => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(AtriumEvent::class, 'atrium_event_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
