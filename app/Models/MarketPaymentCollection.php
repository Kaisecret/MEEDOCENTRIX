<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketPaymentCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'market_stall_lease_id',
        'collection_dispatch_item_id',
        'amount_paid',
        'payer_name',
        'payment_date',
        'collector_note',
        'remarks',
        'generated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'payment_date' => 'datetime',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(MarketStallLease::class, 'market_stall_lease_id');
    }

    public function dispatchItem(): BelongsTo
    {
        return $this->belongsTo(CollectionDispatchItem::class, 'collection_dispatch_item_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }
}

