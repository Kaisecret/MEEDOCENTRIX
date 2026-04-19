<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtriumEventAddOn extends Model
{
    use HasFactory;

    protected $fillable = [
        'atrium_event_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(AtriumEvent::class, 'atrium_event_id');
    }
}
