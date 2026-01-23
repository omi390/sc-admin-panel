<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MaintenanceModeStartEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $provider;

    /**
     * Create a new message instance.
     */
    public function __construct($provider)
    {
        $this->provider = $provider;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject(translate('Maintenance Mode start!'))->view('email-templates.maintenance-mode-start', ['provider' => $this->provider]);
    }
}
