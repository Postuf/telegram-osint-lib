<?php

declare(strict_types=1);

namespace Helpers;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Scenario\BasicClientGeneratorInterface;
use TelegramOSINT\Tools\Proxy;

class NullBasicClientGenerator implements BasicClientGeneratorInterface
{
    /** @var array */
    private array $traceArray;

    public function __construct(array $traceArray)
    {
        $this->traceArray = $traceArray;
    }

    public function generate(bool $trace = false, bool $auxiliary = false): BasicClient
    {
        return new NullBasicClientImpl($this->traceArray);
    }

    public function getProxy(): ?Proxy
    {
        return null;
    }
}
