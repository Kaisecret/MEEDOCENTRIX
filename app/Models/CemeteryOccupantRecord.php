<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CemeteryOccupantRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_no',
        'cemetery_site_id',
        'cemetery_category_id',
        'cemetery_plot_id',
        'cemetery_contact_id',
        'deceased_name',
        'date_of_interment',
        'remarks',
        'status',
        'maintenance_fee_status',
        'coverage_start_date',
        'coverage_end_date',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_interment' => 'date',
            'coverage_start_date' => 'date',
            'coverage_end_date' => 'date',
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

    public function plot(): BelongsTo
    {
        return $this->belongsTo(CemeteryPlot::class, 'cemetery_plot_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CemeteryContact::class, 'cemetery_contact_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CemeteryTransaction::class, 'occupant_record_id');
    }

    public function serviceLogs(): HasMany
    {
        return $this->hasMany(CemeteryServiceLog::class, 'occupant_record_id');
    }
}
