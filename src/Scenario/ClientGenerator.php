<?php

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;

class ClientGenerator implements ClientGeneratorInterface
{
    private const AUTHKEY_ENV = 'AUTHKEY';
    private const SECOND_AUTHKEY_ENV = 'SECOND_AUTHKEY';

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
        $authkey = './first.authkey';
        if (getenv(self::AUTHKEY_ENV)) {
            $authkey = getenv(self::AUTHKEY_ENV);
        }

        return trim(file_get_contents($authkey));
    }

    public function getAuthKeyStatus(): string
    {
        $authkey = './second.authkey';
        if (getenv(self::SECOND_AUTHKEY_ENV)) {
            $authkey = getenv(self::SECOND_AUTHKEY_ENV);
        }

        return trim(file_get_contents($authkey));
    }
}
