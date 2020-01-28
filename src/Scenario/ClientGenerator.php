<?php

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\LibConfig;
use TelegramOSINT\Tools\Proxy;

class ClientGenerator implements ClientGeneratorInterface
{
    /** @var string */
    private $envName;

    public function __construct(string $envName = LibConfig::ENV_AUTHKEY)
    {
        $this->envName = $envName;
    }

    public function getInfoClient()
    {
        return new InfoClient();
    }

    public function getStatusWatcherClient(StatusWatcherCallbacks $callbacks)
    {
        return new StatusWatcherClient($callbacks);
    }

    /**
     * @throws TGException
     *
     * @return string
     */
    public function getAuthKey(): string
    {
        $envPath = getenv($this->envName);
        if (!$envPath) {
            throw new TGException(0, "Please set {$this->envName} env var to valid key or @filename");
        }

        if (strpos($envPath, '@') === 0) {
            $envPath = substr($envPath, 1);
            if (!file_exists($envPath)) {
                throw new TGException(0, "Please set {$this->envName} env var to valid key or @filename");
            }

            return trim(file_get_contents($envPath));
        }

        return trim($envPath);
    }

    public function getProxy(): ?Proxy
    {
        return null;
    }
}
