<?php

namespace App\SMS;

use Illuminate\Support\Arr;

class Builder
{
    protected $recipients = [];

    protected $body;

    protected $driver = null;

    protected $template;

    public function to($recipients)
    {
        $this->recipients = Arr::wrap($recipients);

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function send($body)
    {
        $this->body = $body;

        return $this;
    }

    public function via($driver)
    {
        $this->driver = $driver;

        return $this;
    }

    public function getRecipients()
    {
        return $this->recipients;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }
}
