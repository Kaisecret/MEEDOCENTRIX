<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishportCommodity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'classification_id',
        'default_unit_id',
        'default_conversion',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_conversion' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(FishportCommodityClassification::class, 'classification_id');
    }

    public function defaultUnit(): BelongsTo
    {
        return $this->belongsTo(FishportUnit::class, 'default_unit_id');
    }

    public function logItems(): HasMany
    {
        return $this->hasMany(FishportLogItem::class, 'fishport_commodity_id');
    }
}

