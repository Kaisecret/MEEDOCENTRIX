<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CemeteryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_no',
        'transaction_date',
        'cemetery_site_id',
        'cemetery_category_id',
        'cemetery_transaction_type_id',
        'occupant_record_id',
        'service_log_id',
        'deceased_name',
        'plot_reference',
        'quantity',
        'amount_due',
        'maintenance_type',
        'maintenance_years',
        'has_burial_permit',
        'base_fee',
        'maintenance_fee',
        'burial_permit_fee',
        'other_applicable_fee',
        'remarks',
        'status',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'quantity' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'maintenance_years' => 'integer',
            'has_burial_permit' => 'boolean',
            'base_fee' => 'decimal:2',
            'maintenance_fee' => 'decimal:2',
            'burial_permit_fee' => 'decimal:2',
            'other_applicable_fee' => 'decimal:2',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(CemeterySite::class, 'cemetery_site_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CemeteryCategory::class, 'cemetery_category_id');
    }

    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(CemeteryTransactionType::class, 'cemetery_transaction_type_id');
    }

    public function occupantRecord(): BelongsTo
    {
        return $this->belongsTo(CemeteryOccupantRecord::class, 'occupant_record_id');
    }

    public function serviceLog(): BelongsTo
    {
        return $this->belongsTo(CemeteryServiceLog::class, 'service_log_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function paymentCollection(): HasOne
    {
        return $this->hasOne(CemeteryPaymentCollection::class, 'cemetery_transaction_id');
    }
}
