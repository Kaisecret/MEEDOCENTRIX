<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishportUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function commodities(): HasMany
    {
        return $this->hasMany(FishportCommodity::class, 'default_unit_id');
    }

    public function logItems(): HasMany
    {
        return $this->hasMany(FishportLogItem::class, 'unit_id');
    }
}

