<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FishportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_number',
        'log_date',
        'log_time',
        'arr_dep',
        'fishport_vessel_id',
        'fishport_origin_id',
        'user_id',
        'remarks',
        'is_paid',
        'paid_at',
        'paid_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(FishportVessel::class, 'fishport_vessel_id');
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(FishportOrigin::class, 'fishport_origin_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FishportLogItem::class, 'fishport_log_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FishportLogPayment::class, 'fishport_log_id');
    }

    public function paymentRecord(): HasOne
    {
        return $this->hasOne(FishportPaymentRecord::class, 'fishport_log_id');
    }

    public function dispatchItems(): HasMany
    {
        return $this->hasMany(CollectionDispatchItem::class, 'fishport_log_id');
    }
}
