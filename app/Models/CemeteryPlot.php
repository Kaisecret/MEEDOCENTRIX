<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CemeteryPlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'cemetery_site_id',
        'cemetery_category_id',
        'plot_reference',
        'plot_type',
        'is_occupied',
        'is_active',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'is_occupied' => 'boolean',
            'is_active' => 'boolean',
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

    public function occupantRecords(): HasMany
    {
        return $this->hasMany(CemeteryOccupantRecord::class, 'cemetery_plot_id');
    }
}

