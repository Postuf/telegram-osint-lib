<?php

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

    public function getAuthKey($path): string
    {
        return trim(file_get_contents($path));
    }
}
