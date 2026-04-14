<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FishportVesselRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'fishport_vessel_id',
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
        'registration_date',
        'expiration_date',
        'registration_status',
        'renewal_date',
        'issued_by',
        'supporting_documents_uploaded',
        'created_by',
        'updated_by',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'expiration_date' => 'date',
            'renewal_date' => 'date',
            'gross_tonnage' => 'decimal:2',
            'net_tonnage' => 'decimal:2',
            'vessel_length' => 'decimal:2',
            'beam_width' => 'decimal:2',
            'vessel_depth' => 'decimal:2',
            'engine_horsepower' => 'decimal:2',
            'supporting_documents_uploaded' => 'boolean',
            'year_built' => 'integer',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(FishportVessel::class, 'fishport_vessel_id');
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
