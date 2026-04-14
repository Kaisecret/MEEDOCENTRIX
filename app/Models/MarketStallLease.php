<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketStallLease extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_stall_id',
        'market_tenant_id',
        'market_stall_rate_id',
        'contract_number',
        'start_date',
        'end_date',
        'lease_status',
        'remarks',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function stall(): BelongsTo
    {
        return $this->belongsTo(MarketStall::class, 'market_stall_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(MarketTenant::class, 'market_tenant_id');
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(MarketStallRate::class, 'market_stall_rate_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}

