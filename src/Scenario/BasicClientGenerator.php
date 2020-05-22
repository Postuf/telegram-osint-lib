<?php

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\BasicClient\BasicClientImpl;
use TelegramOSINT\Client\BasicClient\BasicClientWithOnlineImpl;
use TelegramOSINT\Client\BasicClient\TracingBasicClientImpl;
use TelegramOSINT\Tools\Proxy;

class BasicClientGenerator implements BasicClientGeneratorInterface
{
    /** @var Proxy */
    private $proxy;

    public function __construct(?Proxy $proxy = null)
    {
        $this->proxy = $proxy;
    }

    public function generate(bool $trace = false, bool $auxiliary = false): BasicClient
    {
        if ($trace) {
            return new TracingBasicClientImpl();
        }
        if ($auxiliary) {
            return new BasicClientImpl();
        }

        return new BasicClientWithOnlineImpl();
    }

    public function getProxy(): ?Proxy
    {
        return $this->proxy;
    }
}
