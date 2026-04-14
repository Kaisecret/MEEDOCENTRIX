<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CemeteryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_code',
        'category_name',
        'description',
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
        return $this->hasMany(CemeteryPlot::class, 'cemetery_category_id');
    }

    public function occupantRecords(): HasMany
    {
        return $this->hasMany(CemeteryOccupantRecord::class, 'cemetery_category_id');
    }
}

