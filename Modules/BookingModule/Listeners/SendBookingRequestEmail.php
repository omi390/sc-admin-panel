<?php

namespace Modules\BookingModule\Listeners;

use Illuminate\Support\Facades\Mail;
use Modules\BookingModule\Emails\BookingMail;
use Modules\BookingModule\Events\BookingRequested;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingRequestEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param BookingRequested $event
     * @return void
     */
    public function handle(BookingRequested $event)
    {
        try {
            $email = isNotificationActive(null, 'booking', 'email', 'user');
            $emailServices =  business_config('email_config_status', 'email_config');
            if (isset($event->booking->customer->email) && isset($emailServices) && $emailServices->live_values == 1 && $email) {
                Mail::to($event->booking->customer->email)->send(new BookingMail($event->booking));
            }
        } catch (\Exception $exception) {
            info($exception);
        }

        $notification= isNotificationActive(null, 'booking', 'notification', 'user');
        $config = business_config('booking', 'notification_settings');
        if ($config->live_values['push_notification_booking']) {
            $title = get_push_notification_message('booking_place', 'customer_notification', $event->booking?->customer?->current_language_key);
            if (isset($event->booking->customer->fcm_token) && $title && $notification) {
                device_notification($event->booking->customer->fcm_token, $title, null, null, $event->booking->id, 'booking');
            }
        }
    }
}
