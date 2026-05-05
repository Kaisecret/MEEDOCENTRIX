<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtriumEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_code',
        'date_of_event',
        'start_time',
        'event_details',
        'name_contact_person',
        'contact_number',
        'atrium_function_hall_id',
        'no_of_hours',
        'hall_payment',
        'miscellaneous_payment',
        'accommodation_payment',
        'actual_due',
        'booking_status',
        'created_by_user_id',
    ];

    protected $casts = [
        'date_of_event' => 'date',
        'no_of_hours' => 'decimal:2',
        'hall_payment' => 'decimal:2',
        'miscellaneous_payment' => 'decimal:2',
        'accommodation_payment' => 'decimal:2',
        'actual_due' => 'decimal:2',
    ];

    public function functionHall(): BelongsTo
    {
        return $this->belongsTo(AtriumFunctionHall::class, 'atrium_function_hall_id');
    }

    public function addOns(): HasMany
    {
        return $this->hasMany(AtriumEventAddOn::class, 'atrium_event_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AtriumEventPayment::class, 'atrium_event_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function addOnsTotal(): float
    {
        return (float) $this->addOns->sum('amount');
    }

    public function totalPaid(): float
    {
        return (float) $this->payments->sum('payment_amount');
    }

    public function remainingBalance(): float
    {
        return max(0.0, (float) $this->actual_due - $this->totalPaid());
    }

    public function paymentStatus(): string
    {
        $due = (float) $this->actual_due;
        $paid = $this->totalPaid();
        if ($paid <= 0.0) {
            return 'unpaid';
        }
        if ($paid + 0.009 >= $due) {
            return 'paid';
        }
        return 'partial';
    }
}
