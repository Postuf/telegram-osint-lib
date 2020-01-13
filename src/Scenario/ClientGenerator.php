<?php

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

namespace Scenario;

use Client\InfoObtainingClient\InfoClient;
use Client\StatusWatcherClient\StatusWatcherCallbacks;
use Client\StatusWatcherClient\StatusWatcherClient;

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
