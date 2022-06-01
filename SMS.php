<?php

namespace App\SMS;

use Exception;
use ReflectionClass;

class SMS
{
    protected $config;
    protected $builder;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->setBuilder(new Builder());
        $this->via($this->config['default']);
    }

    public function to($recipients)
    {
        $this->builder->to($recipients);

        return $this;
    }

    public function template($template)
    {
        $this->builder->setTemplate($template);

        return $this;
    }

    public function via($driver)
    {
        $this->driver = $driver;
        $this->validateDriver();
        $this->builder->via($driver);
        $this->settings = $this->config['drivers'][$driver];
        return $this;
    }

    public function send($message, $callback = null)
    {
        if ($message instanceof Builder) {
            return $this->setBuilder($message)->dispatch();
        }

        $this->builder->send($message);
        if (!$callback) {
            return $this;
        }

        $driver = $this->getDriverInstance();
        $driver->message($message);
        call_user_func($callback, $driver);

        return $driver->send();
    }

    public function sendVerification($verificationCode)
    {
        if ($verificationCode instanceof Builder) {
            return $this->setBuilder($verificationCode)->dispatch();
        }

        $this->builder->send($verificationCode);

        $driver = $this->getDriverInstance();
        $driver->message($verificationCode);

        return $driver->send();
    }

    public function dispatch()
    {
        $this->driver = $this->builder->getDriver() ?: $this->driver;

        if (empty($this->driver)) {
            $this->via($this->config['default']);
        }

        $driver = $this->getDriverInstance();
        $driver->message($this->builder->getBody());
        $driver->to($this->builder->getRecipients());
        $driver->template($this->builder->getTemplate());

        return $driver->send();
    }

    protected function setBuilder(Builder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    protected function getDriverInstance()
    {
        $this->validateDriver();
        $class = $this->config['map'][$this->driver];
        return new $class($this->settings);
    }

    protected function validateDriver()
    {
        $conditions = [
            'Driver not selected or default driver does not exist.' => empty($this->driver),
            'Driver not found in config file. Try updating the package.' => empty($this->config['drivers'][$this->driver]) || empty($this->config['map'][$this->driver]),
            'Driver source not found. Please update the package.' => !class_exists($this->config['map'][$this->driver]),
            'Driver must be an instance of Contracts\Driver.' => !(new ReflectionClass($this->config['map'][$this->driver]))->isSubclassOf(SMSAbstract::class),
        ];

        foreach ($conditions as $ex => $condition) {
            throw_if($condition, new Exception($ex));
        }
    }
}
