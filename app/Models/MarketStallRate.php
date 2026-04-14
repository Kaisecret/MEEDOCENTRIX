<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketStallRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_stall_location_id',
        'rate_amount',
        'effective_start_date',
        'effective_end_date',
        'is_active',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'rate_amount' => 'decimal:2',
            'effective_start_date' => 'date',
            'effective_end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(MarketStallLocation::class, 'market_stall_location_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function leases(): HasMany
    {
        return $this->hasMany(MarketStallLease::class, 'market_stall_rate_id');
    }
}

