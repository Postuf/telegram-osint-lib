<?php

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\BasicClient\BasicClientImpl;
use TelegramOSINT\Client\BasicClient\TracingBasicClientImpl;

class BasicClientGenerator implements BasicClientGeneratorInterface
{
    public function generate(bool $trace = false): BasicClient
    {
        return $trace
            ? new TracingBasicClientImpl()
            : new BasicClientImpl();
    }
}
