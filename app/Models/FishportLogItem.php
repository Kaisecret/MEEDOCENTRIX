<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FishportLogItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fishport_log_id',
        'fishport_commodity_id',
        'unit_id',
        'quantity',
        'unit_conversion',
        'volume',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_conversion' => 'decimal:4',
            'volume' => 'decimal:4',
        ];
    }

    public function log(): BelongsTo
    {
        return $this->belongsTo(FishportLog::class, 'fishport_log_id');
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(FishportCommodity::class, 'fishport_commodity_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(FishportUnit::class, 'unit_id');
    }
}

