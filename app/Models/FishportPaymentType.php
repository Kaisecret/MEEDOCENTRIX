<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishportPaymentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'default_fee',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function logPayments(): HasMany
    {
        return $this->hasMany(FishportLogPayment::class, 'fishport_payment_type_id');
    }
}

