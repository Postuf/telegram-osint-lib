<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\BasicClient\BasicClient;

class ReusableBasicClientGenerator extends BasicClientGenerator
{
    /** @var BasicClient */
    private $instance;

    public function generate(bool $trace = false): BasicClient
    {
        if (!$this->instance) {
            $this->instance = parent::generate($trace);
        }

        return $this->instance;
    }
}
