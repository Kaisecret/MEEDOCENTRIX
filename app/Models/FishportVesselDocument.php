<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FishportVesselDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'fishport_vessel_id',
        'certificate_of_ownership_path',
        'previous_registration_path',
        'boat_permit_license_path',
        'engine_receipt_proof_path',
        'valid_id_path',
        'inspection_certificate_path',
    ];

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(FishportVessel::class, 'fishport_vessel_id');
    }
}
