<?php


namespace App\OverridenPackages\Mail\Transport;

use GuzzleHttp\ClientInterface;
use Illuminate\Mail\Transport\MailgunTransport as LaravelMailgunTransport;
use Swift_Mime_SimpleMessage;

class MailgunTransport extends LaravelMailgunTransport
{

    protected $testMode;

    /**
     * Create a new Mailgun transport instance.
     *
     * Improved with the feature of sending in test mode.
     *
     * @param ClientInterface $client
     * @param string $key
     * @param string $domain
     * @param bool $testMode - Set to true to avoid sending real emails.
     * It will still show up in mailgun's mail logs.
     * @param string|null $endpoint
     * @return void
     */
    public function __construct(ClientInterface $client, $key, $domain, $testMode, $endpoint = null)
    {
        parent::__construct($client, $key, $domain, $endpoint);
        $this->testMode = $testMode;
    }

    /**
     * {@inheritdoc}
     */
    protected function payload(Swift_Mime_SimpleMessage $message, $to)
    {
        $payload = [
            'auth' => [
                'api',
                $this->key,
            ],
            'multipart' => [
                [
                    'name' => 'to',
                    'contents' => $to,
                ],
                [
                    'name' => 'message',
                    'contents' => $message->toString(),
                    'filename' => 'message.mime',
                ],

            ],
        ];

        if ($this->testMode)
            $payload['multipart'][] = [
                'name' => 'o:testmode',
                'contents' => 'yes'
            ];

        return $payload;
    }
}
