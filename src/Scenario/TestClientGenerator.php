<?php

declare(strict_types=1);

namespace Scenario;

use Client\InfoObtainingClient\InfoClient;
use Client\StatusWatcherClient\StatusWatcherCallbacks;

class TestClientGenerator implements ClientGeneratorInterface
{
    /** @var BasicClientGeneratorInterface */
    private $generator;
    /** @var string */
    private $authKey;

    public function __construct(BasicClientGeneratorInterface $generator, string $authKey)
    {
        $this->generator = $generator;
        $this->authKey = $authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoClient()
    {
        return new InfoClient($this->generator);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusWatcherClient(StatusWatcherCallbacks $callbacks)
    {
        return null;
    }

    public function getAuthKeyInfo(): string
    {
        return $this->authKey;
    }

    public function getAuthKeyStatus(): string
    {
        return $this->authKey;
    }
}
