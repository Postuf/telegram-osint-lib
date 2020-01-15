<?php

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\BasicClient\BasicClient;

interface BasicClientGeneratorInterface
{
    public function generate(bool $trace = false): BasicClient;
}
