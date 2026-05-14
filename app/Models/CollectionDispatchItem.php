<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CollectionDispatchItem extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'collection_dispatch_id',
        'fishport_log_id',
        'market_stall_lease_id',
        'payment_record_id',
        'market_payment_collection_id',
        'amount_snapshot',
        'status',
        'payer_name',
        'collected_at',
        'collected_by_user_id',
        'proof_image_path',
        'collector_note',
        'reviewed_at',
        'reviewed_by_user_id',
        'review_note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_snapshot' => 'decimal:2',
            'collected_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(CollectionDispatch::class, 'collection_dispatch_id');
    }

    public function fishportLog(): BelongsTo
    {
        return $this->belongsTo(FishportLog::class, 'fishport_log_id');
    }

    public function paymentRecord(): BelongsTo
    {
        return $this->belongsTo(FishportPaymentRecord::class, 'payment_record_id');
    }

    public function marketStallLease(): BelongsTo
    {
        return $this->belongsTo(MarketStallLease::class, 'market_stall_lease_id');
    }

    public function marketPaymentCollection(): BelongsTo
    {
        return $this->belongsTo(MarketPaymentCollection::class, 'market_payment_collection_id');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function marketDueLog(): HasOne
    {
        return $this->hasOne(MarketDueLog::class, 'collection_dispatch_item_id');
    }
}
