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
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function stalls(): HasMany
    {
        return $this->hasMany(MarketStall::class, 'market_stall_type_id');
    }
}

