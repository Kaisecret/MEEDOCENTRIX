<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishportVessel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_name',
        'vessel_type',
        'registration_number',
        'official_number',
        'plate_permit_number',
        'home_port',
        'gross_tonnage',
        'net_tonnage',
        'vessel_length',
        'beam_width',
        'vessel_depth',
        'engine_type',
        'engine_horsepower',
        'hull_material',
        'color_markings',
        'year_built',
        'owner_address',
        'owner_contact_number',
        'owner_email',
        'owner_government_id_number',
        'business_name',
        'captain_operator_name',
        'captain_license_number',
        'captain_contact_number',
        'captain_address',
        'registration_date',
        'expiration_date',
        'registration_status',
        'renewal_date',
        'issued_by',
        'supporting_documents_uploaded',
        'certificate_of_ownership_path',
        'previous_registration_path',
        'boat_permit_license_path',
        'engine_receipt_proof_path',
        'valid_id_path',
        'inspection_certificate_path',
        'created_by',
        'updated_by',
        'remarks',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'supporting_documents_uploaded' => 'boolean',
            'registration_date' => 'date',
            'expiration_date' => 'date',
            'renewal_date' => 'date',
            'gross_tonnage' => 'decimal:2',
            'net_tonnage' => 'decimal:2',
            'vessel_length' => 'decimal:2',
            'beam_width' => 'decimal:2',
            'vessel_depth' => 'decimal:2',
            'engine_horsepower' => 'decimal:2',
            'year_built' => 'integer',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FishportLog::class, 'fishport_vessel_id');
    }

    public function ownerProfile(): HasOne
    {
        return $this->hasOne(FishportVesselOwner::class, 'fishport_vessel_id');
    }

    public function operatorProfile(): HasOne
    {
        return $this->hasOne(FishportVesselOperator::class, 'fishport_vessel_id');
    }

    public function registrationProfile(): HasOne
    {
        return $this->hasOne(FishportVesselRegistration::class, 'fishport_vessel_id');
    }

    public function documentProfile(): HasOne
    {
        return $this->hasOne(FishportVesselDocument::class, 'fishport_vessel_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
