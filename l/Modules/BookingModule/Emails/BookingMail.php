<?php

namespace Modules\BookingModule\Emails;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BookingModule\Entities\Booking;

class BookingMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected Booking $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $pdf = PDF::loadView('bookingmodule::admin.booking.invoice', ['booking' => $this->booking]);
        return $this->subject(translate('Booking Place'))->view('bookingmodule::mail-templates.booking-request-sent', ['booking' => $this->booking])->attachData($pdf->output(), "invoice.pdf");
    }
}
