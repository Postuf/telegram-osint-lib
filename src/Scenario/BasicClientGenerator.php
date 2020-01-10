<?php

namespace Scenario;

use Client\BasicClient\BasicClient;
use Client\BasicClient\BasicClientImpl;
use Client\BasicClient\TracingBasicClientImpl;

class BasicClientGenerator implements BasicClientGeneratorInterface
{
    public function generate(bool $trace = false): BasicClient
    {
        return $trace
            ? new TracingBasicClientImpl()
            : new BasicClientImpl();
    }
}
