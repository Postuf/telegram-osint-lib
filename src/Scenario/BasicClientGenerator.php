<?php

namespace Scenario;

use Client\BasicClient\BasicClient;
use Client\BasicClient\BasicClientImpl;

class BasicClientGenerator implements BasicClientGeneratorInterface
{
    public function generate(): BasicClient
    {
        return new BasicClientImpl();
    }
}
