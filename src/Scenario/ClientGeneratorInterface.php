<?php

declare(strict_types=1);

namespace Scenario;

use Client\InfoObtainingClient\InfoClient;
use Client\StatusWatcherClient\StatusWatcherCallbacks;
use Client\StatusWatcherClient\StatusWatcherClient;
use Exception\TGException;

interface ClientGeneratorInterface
{
    /**
     * @return InfoClient
     */
    public function getInfoClient();

    /**
     * @param StatusWatcherCallbacks $callbacks
     *
     * @throws TGException
     *
     * @return StatusWatcherClient
     */
    public function getStatusWatcherClient(StatusWatcherCallbacks $callbacks);

    public function getAuthKey($path): string;
}
