<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MarketStall extends Model
{
    use HasFactory;

    protected $fillable = [
        'stall_no',
        'market_stall_location_id',
        'market_stall_type_id',
        'dimension_sq_m',
        'description',
        'stall_status',
        'is_billable',
    ];

    protected function casts(): array
    {
        return [
            'dimension_sq_m' => 'decimal:2',
            'is_billable' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(MarketStallLocation::class, 'market_stall_location_id');
    }

    public function stallType(): BelongsTo
    {
        return $this->belongsTo(MarketStallType::class, 'market_stall_type_id');
    }

    public function leases(): HasMany
    {
        return $this->hasMany(MarketStallLease::class, 'market_stall_id');
    }

    public function activeLease(): HasOne
    {
        return $this->hasOne(MarketStallLease::class, 'market_stall_id')
            ->where('lease_status', 'active')
            ->latestOfMany('start_date');
    }
}

