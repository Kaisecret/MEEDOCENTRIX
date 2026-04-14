<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CemeteryContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_person',
        'contact_number',
        'address',
    ];

    public function occupantRecords(): HasMany
    {
        return $this->hasMany(CemeteryOccupantRecord::class, 'cemetery_contact_id');
    }

    public function paymentCollections(): HasMany
    {
        return $this->hasMany(CemeteryPaymentCollection::class, 'cemetery_contact_id');
    }
}
