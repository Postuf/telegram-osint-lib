<?php

declare(strict_types=1);

namespace Helpers;

use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;
use TelegramOSINT\Scenario\BasicClientGeneratorInterface;
use TelegramOSINT\Scenario\ClientGeneratorInterface;
use TelegramOSINT\Tools\Proxy;
use Unit\Client\StatusWatcherClient\StatusWatcherClientTestCallbacks;

class TestClientGenerator implements ClientGeneratorInterface
{
    /** @var BasicClientGeneratorInterface */
    protected $generator;
    /** @var string */
    private $authKey;
    /** @var InfoClient */
    protected $client;

    public function __construct(BasicClientGeneratorInterface $generator, string $authKey)
    {
        $this->generator = $generator;
        $this->authKey = $authKey;
    }

    public function getInfoClient(): InfoClient
    {
        if (!$this->client) {
            $this->client = new InfoClient($this->generator);
        }

        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusWatcherClient(StatusWatcherCallbacks $callbacks): StatusWatcherClient
    {
        return new StatusWatcherClient(new StatusWatcherClientTestCallbacks());
    }

    public function getAuthKey(): string
    {
        return $this->authKey;
    }

    public function getProxy(): ?Proxy
    {
        return null;
    }
}
