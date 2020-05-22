<?php

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Tools\Proxy;

interface BasicClientGeneratorInterface
{
    public function generate(bool $trace = false, bool $auxiliary = false): BasicClient;

    public function getProxy(): ?Proxy;
}
