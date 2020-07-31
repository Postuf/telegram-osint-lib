<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;

class ReusableClientGenerator extends ClientGenerator
{
    /** @var InfoClient */
    private ?InfoClient $instance = null;
    /** @var StatusWatcherClient */
    private ?StatusWatcherClient $watcherInstance = null;
    /** @var ClientGeneratorInterface */
    private ?ClientGeneratorInterface $clientGenerator;

    public function __construct(?ClientGeneratorInterface $clientGenerator = null)
    {
        parent::__construct();
        $this->clientGenerator = $clientGenerator;
    }

    public function getAuthKey(): string
    {
        return $this->clientGenerator
            ? $this->clientGenerator->getAuthKey()
            : parent::getAuthKey();
    }

    public function getInfoClient(): InfoClient
    {
        if (!$this->instance) {
            $this->setInstance();
        }

        return $this->instance;
    }

    public function getStatusWatcherClient(StatusWatcherCallbacks $callbacks): StatusWatcherClient
    {
        if (!$this->watcherInstance) {
            $this->watcherInstance = $this->clientGenerator
                ? $this->clientGenerator->getStatusWatcherClient($callbacks)
                : parent::getStatusWatcherClient($callbacks);
        }

        return $this->watcherInstance;
    }

    private function setInstance(): void
    {
        $this->instance = $this->clientGenerator
            ? $this->clientGenerator->getInfoClient()
            : parent::getInfoClient();
    }
}
