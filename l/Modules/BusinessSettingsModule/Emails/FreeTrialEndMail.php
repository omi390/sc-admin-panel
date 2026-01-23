<?php

namespace Modules\BusinessSettingsModule\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FreeTrialEndMail extends Mailable
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
        return $this->subject(translate('Free Trial End Mail'))->view('businesssettingsmodule::mail-templates.free-trial-end', ['provider' => $this->provider]);
    }
}
