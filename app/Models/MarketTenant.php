<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MarketTenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'address',
        'contact_number',
        'business_name',
        'business_type',
        'mpo_control_no',
    ];

    public function leases(): HasMany
    {
        return $this->hasMany(MarketStallLease::class, 'market_tenant_id');
    }

    public function activeLease(): HasOne
    {
        return $this->hasOne(MarketStallLease::class, 'market_tenant_id')
            ->where('lease_status', 'active')
            ->latestOfMany('start_date');
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }
}
