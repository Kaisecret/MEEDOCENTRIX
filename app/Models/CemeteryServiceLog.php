<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CemeteryServiceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_no',
        'service_date',
        'cemetery_site_id',
        'cemetery_service_type_id',
        'deceased_name',
        'plot_reference',
        'details',
        'processed_by',
        'remarks',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(CemeterySite::class, 'cemetery_site_id');
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(CemeteryServiceType::class, 'cemetery_service_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CemeteryTransaction::class, 'service_log_id');
    }
}
