<?php

namespace Modules\BookingModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionSubscriberBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'booking_id',
        'provider_id',
        'package_subscriber_log_id',
        ];

    protected static function newFactory()
    {
        return \Modules\BookingModule\Database\factories\SubscriptionSubscriberBookingFactory::new();
    }
}
