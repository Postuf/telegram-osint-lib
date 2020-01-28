<?php

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\BasicClient\BasicClientImpl;
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

    public function generate(bool $trace = false): BasicClient
    {
        return $trace
            ? new TracingBasicClientImpl()
            : new BasicClientImpl();
    }

    public function getProxy(): ?Proxy
    {
        return $this->proxy;
    }
}
