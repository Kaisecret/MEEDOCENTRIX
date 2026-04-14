<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CemeterySite extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_code',
        'site_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function plots(): HasMany
    {
        return $this->hasMany(CemeteryPlot::class, 'cemetery_site_id');
    }

    public function occupantRecords(): HasMany
    {
        return $this->hasMany(CemeteryOccupantRecord::class, 'cemetery_site_id');
    }
}

