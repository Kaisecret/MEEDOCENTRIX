<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FishportLogPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fishport_log_id',
        'fishport_payment_type_id',
        'fee',
        'quantity',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'quantity' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function log(): BelongsTo
    {
        return $this->belongsTo(FishportLog::class, 'fishport_log_id');
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(FishportPaymentType::class, 'fishport_payment_type_id');
    }
}

