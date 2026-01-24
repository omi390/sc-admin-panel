<?php

namespace Modules\BusinessSettingsModule\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionTimeEndMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $provider;
    /**
     * Create a new message instance.
     *
     * @return void
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
    public function build()
    {
        return $this->subject(translate('Subscription Time End'))->view('businesssettingsmodule::mail-templates.subscription-time-end', ['provider' => $this->provider]);
    }
}
