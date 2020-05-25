<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;
use TelegramOSINT\Tools\Proxy;

class StaticGenerator implements ClientGeneratorInterface
{
    /** @var string */
    private $authKey;
    /** @var Proxy|null */
    private $proxy;

    public function __construct(string $authKey, ?Proxy $proxy = null)
    {
        $this->authKey = $authKey;
        $this->proxy = $proxy;
    }

    public function getInfoClient(): InfoClient
    {
        return new InfoClient(new BasicClientGenerator());
    }

    public function getStatusWatcherClient(StatusWatcherCallbacks $callbacks): StatusWatcherClient
    {
        return new StatusWatcherClient($callbacks);
    }

    public function getAuthKey(): string
    {
        return $this->authKey;
    }

    public function getProxy(): ?Proxy
    {
        return $this->proxy;
    }
}
