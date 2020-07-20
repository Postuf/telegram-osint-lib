<?php

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\BasicClient\BasicClientImpl;
use TelegramOSINT\Client\BasicClient\BasicClientWithStatusReportingImpl;
use TelegramOSINT\Client\BasicClient\TracingBasicClientImpl;
use TelegramOSINT\LibConfig;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\Logger\NullLogger;
use TelegramOSINT\Tools\Proxy;

class BasicClientGenerator implements BasicClientGeneratorInterface
{
    /** @var Proxy|null */
    private $proxy;
    /** @var Logger|null */
    private $logger;

    public function __construct(?Proxy $proxy = null, ?ClientDebugLogger $logger = null)
    {
        $this->proxy = $proxy;
        if (!$logger) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;
    }

    public function generate(bool $trace = false, bool $auxiliary = false): BasicClient
    {
        if ($trace) {
            return new TracingBasicClientImpl();
        }
        if ($auxiliary) {
            return new BasicClientImpl(LibConfig::CONN_SOCKET_PROXY_TIMEOUT_SEC, $this->logger);
        }

        return new BasicClientWithStatusReportingImpl(LibConfig::CONN_SOCKET_PROXY_TIMEOUT_SEC, $this->logger);
    }

    public function getProxy(): ?Proxy
    {
        return $this->proxy;
    }
}
