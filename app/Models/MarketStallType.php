<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketStallType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_name',
        'description',
        'default_rate',
        'rate_notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function stalls(): HasMany
    {
        return $this->hasMany(MarketStall::class, 'market_stall_type_id');
    }
}
