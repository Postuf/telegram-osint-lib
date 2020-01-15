<?php

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;

class ClientGenerator implements ClientGeneratorInterface
{
    public function getInfoClient()
    {
        return new InfoClient();
    }

    public function getStatusWatcherClient(StatusWatcherCallbacks $callbacks)
    {
        return new StatusWatcherClient($callbacks);
    }

    public function getAuthKeyInfo(): string
    {
        return trim(file_get_contents('./first.authkey'));
    }

    public function getAuthKeyStatus(): string
    {
        return trim(file_get_contents('./second.authkey'));
    }
}
