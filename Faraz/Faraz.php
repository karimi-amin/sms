<?php

namespace App\SMS\Faraz;

use App\SMS\SMSAbstract;
use IPPanel\Client as FarazClient;

class Faraz extends SMSAbstract
{
    protected $settings;

    protected $client;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->client = new FarazClient(data_get($this->settings, 'apikey'));
    }

    public function send()
    {
        if ($this->template) {
            $responses = $this->sendVerification();
        } else {
            $responses = $this->client->send(
                data_get($this->settings, 'from'),
                $this->recipients,
                $this->body
            );
        }

        return $responses;
    }

    public function sendVerification()
    {
        if (!is_array($this->recipients) || empty($this->recipients)) {
            return false;
        }

        return $this->client
            ->sendPattern(
                $this->template,
                data_get($this->settings, 'from'),
                $this->recipients[0],
                ['verification-code' => $this->body]
            );
    }
}
