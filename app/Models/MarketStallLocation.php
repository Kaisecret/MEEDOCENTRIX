<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MarketStallLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_code',
        'location_name',
        'zone',
        'floor_level',
        'remarks',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(MarketStallRate::class, 'market_stall_location_id');
    }

    public function activeRate(): HasOne
    {
        return $this->hasOne(MarketStallRate::class, 'market_stall_location_id')
            ->where('is_active', true)
            ->latestOfMany('effective_start_date');
    }

    public function stalls(): HasMany
    {
        return $this->hasMany(MarketStall::class, 'market_stall_location_id');
    }
}

