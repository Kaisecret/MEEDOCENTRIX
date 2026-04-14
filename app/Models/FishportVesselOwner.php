<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FishportVesselOwner extends Model
{
    use HasFactory;

    protected $fillable = [
        'fishport_vessel_id',
        'full_name',
        'address',
        'contact_number',
        'email',
        'government_id_number',
        'business_name',
    ];

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(FishportVessel::class, 'fishport_vessel_id');
    }
}
