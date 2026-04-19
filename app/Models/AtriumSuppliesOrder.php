<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtriumSuppliesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'atrium_event_id',
        'time_needed',
        'requested_supplies',
        'request_status',
        'remarks',
        'fulfilled_at',
        'requested_by_user_id',
    ];

    protected $casts = [
        'fulfilled_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(AtriumEvent::class, 'atrium_event_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
