<?php

namespace Modules\BusinessSettingsModule\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\ProviderManagement\Entities\Provider;

class SubscriptionToCommissionMail extends Mailable
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
        return $this->subject(translate('Subscription to commission'))->view('businesssettingsmodule::mail-templates.subscription-to-commission', ['provider' => $this->provider]);
    }
}
