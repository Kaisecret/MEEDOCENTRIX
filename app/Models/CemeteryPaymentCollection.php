<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CemeteryPaymentCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_no',
        'cemetery_transaction_id',
        'cemetery_contact_id',
        'amount_paid',
        'official_receipt_no',
        'payment_date',
        'coverage_start_date',
        'coverage_end_date',
        'payment_status',
        'remarks',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'payment_date' => 'date',
            'coverage_start_date' => 'date',
            'coverage_end_date' => 'date',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CemeteryTransaction::class, 'cemetery_transaction_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CemeteryContact::class, 'cemetery_contact_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
