<?php

declare(strict_types=1);

namespace Helpers;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Scenario\BasicClientGeneratorInterface;

class NullBasicClientGenerator implements BasicClientGeneratorInterface
{
    /** @var array */
    private $traceArray;

    public function __construct(array $traceArray)
    {
        $this->traceArray = $traceArray;
    }

    public function generate(bool $trace = false): BasicClient
    {
        return new NullBasicClientImpl($this->traceArray);
    }
}
