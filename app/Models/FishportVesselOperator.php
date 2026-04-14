<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FishportVesselOperator extends Model
{
    use HasFactory;

    protected $fillable = [
        'fishport_vessel_id',
        'name',
        'license_number',
        'contact_number',
        'address',
    ];

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(FishportVessel::class, 'fishport_vessel_id');
    }
}
