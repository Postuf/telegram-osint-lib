<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\BasicClient\BasicClient;

class ReusableBasicClientGenerator extends BasicClientGenerator
{
    /** @var BasicClient */
    private ?BasicClient $instance = null;

    public function generate(bool $trace = false, bool $aux = false): BasicClient
    {
        if (!$this->instance) {
            $this->instance = parent::generate($trace, $aux);
        }

        return $this->instance;
    }
}
