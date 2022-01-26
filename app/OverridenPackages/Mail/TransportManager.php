<?php


namespace App\OverridenPackages\Mail;

use App\OverridenPackages\Mail\Transport\MailgunTransport;
use Illuminate\Mail\TransportManager as LaravelTransportManager;

class TransportManager extends LaravelTransportManager
{
    /**
     * Create an instance of the Mailgun Swift Transport driver.
     *
     * Improved with the feature of sending in test mode.
     *
     * @return \Illuminate\Mail\Transport\MailgunTransport
     */
    protected function createMailgunDriver()
    {
        $config = $this->config->get('services.mailgun', []);

        return new MailgunTransport(
            $this->guzzle($config),
            $config['secret'],
            $config['domain'],
            $config['testmode'],
            $config['endpoint'] ?? null
        );
    }
}
