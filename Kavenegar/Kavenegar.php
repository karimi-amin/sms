<?php

namespace App\SMS\Kavenegar;

use App\SMS\SMSAbstract;
use Kavenegar\KavenegarApi;

class Kavenegar extends SMSAbstract
{
    protected $settings;

    protected $client;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->client = new KavenegarApi(data_get($this->settings, 'apikey'));
    }

    public function send()
    {
        if ($this->template) {
            $responses = $this->sendVerification();
        } else {
            $responses = $this->client
                ->Send(data_get($this->settings, 'from'), $this->recipients, $this->body);
        }

        $response = collect($responses)
            ->map(function ($res) {
                return [$res->receptor => $res];
            })
            ->collapse();

        return (count($this->recipients) == 1) ? $response->first() : $response;
    }

    public function sendVerification()
    {
        if (!is_array($this->recipients) || empty($this->recipients)) {
            return false;
        }

        return $this->client
            ->verifyLookup(
                $this->recipients[0],
                $this->body,
                null,
                null,
                $this->template
            );
    }
}
