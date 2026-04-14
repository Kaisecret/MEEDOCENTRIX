<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionDispatchItem extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'collection_dispatch_id',
        'fishport_log_id',
        'payment_record_id',
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

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
