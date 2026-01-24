<?php

namespace Modules\CustomerModule\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\UserManagement\Entities\User;

class CustomerRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected User $user;
    protected mixed $password;
    protected string $otp;
    protected mixed $url;

    public function __construct($user, $password, $otp, $url)
    {
        $this->user = $user;
        $this->password = $password;
        $this->otp = $otp;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject(translate('Customer Registration'))->view('customermodule::mail-templates.customer-registration', ['customer' => $this->user, 'password' => $this->password, 'otp' => $this->otp, 'url' => $this->url]);
    }
}
