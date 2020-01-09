<?php

declare(strict_types=1);

namespace Scenario;

use Client\BasicClient\BasicClient;
use Client\BasicClient\NullBasicClientImpl;

class NullBasicClientGenerator implements BasicClientGeneratorInterface
{
    /** @var array */
    private $traceArray;

    public function __construct(array $traceArray)
    {
        $this->traceArray = $traceArray;
    }

    public function generate(): BasicClient
    {
        return new NullBasicClientImpl($this->traceArray);
    }
}
