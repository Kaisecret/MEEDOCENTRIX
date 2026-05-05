<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketDueLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_stall_lease_id',
        'due_date',
        'billing_period',
        'billing_cycles',
        'expected_amount',
        'status',
        'collection_dispatch_item_id',
        'market_payment_collection_id',
        'sent_at',
        'paid_at',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'billing_cycles' => 'integer',
            'expected_amount' => 'decimal:2',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
            'closed_at' => 'datetime',
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

    public function paymentCollection(): BelongsTo
    {
        return $this->belongsTo(MarketPaymentCollection::class, 'market_payment_collection_id');
    }
}

