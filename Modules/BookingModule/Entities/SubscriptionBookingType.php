<?php

namespace Modules\BookingModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionBookingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'type',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }


    protected static function newFactory()
    {
        return \Modules\BookingModule\Database\factories\SubscriptionBookingTypeFactory::new();
    }
}
