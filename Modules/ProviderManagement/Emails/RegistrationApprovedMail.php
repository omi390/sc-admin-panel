<?php

namespace Modules\ProviderManagement\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\ProviderManagement\Entities\Provider;

class RegistrationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected Provider $provider;

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
        return $this->subject(translate('Registration Approved'))->view('providermanagement::mail-templates.registration-approved', ['provider' => $this->provider]);
    }
}
