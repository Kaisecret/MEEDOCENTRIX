<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerminalRouteFare extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'vehicle_kind',
        'route_name',
        'fare_amount',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'fare_amount' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}

