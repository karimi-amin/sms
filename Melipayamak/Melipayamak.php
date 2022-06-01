<?php

namespace App\SMS\Melipayamak;

use App\SMS\SMSAbstract;
use Melipayamak\MelipayamakApi;

class Melipayamak extends SMSAbstract
{
    protected  $settings;

    protected  $client;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->client = new MelipayamakApi(data_get($this->settings, 'username'), data_get($this->settings, 'password'));
    }

    public function send()
    {
        $response = collect();
        foreach ($this->recipients as $recipient) {
            $response->put($recipient, $this->client->sms()->send(
                $recipient,
                data_get($this->settings, 'from'),
                $this->body,
                data_get($this->settings, 'flash')
            ));
        }

        return (count($this->recipients) == 1) ? $response->first() : $response;
    }
}
