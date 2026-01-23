<?php

namespace Modules\BusinessSettingsModule\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\ProviderManagement\Entities\Provider;

class PurchaseSubscriptionMail extends Mailable
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
        return $this->subject(translate('Subscription Plan Successfully Subscribed!'))->view('businesssettingsmodule::mail-templates.purchase-subscription', ['provider' => $this->provider]);
    }
}
