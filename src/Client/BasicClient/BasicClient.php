<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\BasicClient;

use TelegramOSINT\Client\Client;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;
use TelegramOSINT\TGConnection\SocketMessenger\SocketMessenger;

interface BasicClient extends Client
{
    public function getConnection(): ?SocketMessenger;

    public function throwIfNotLoggedIn(string $message = ''): void;

    public function setMessageListener(MessageListener $messageCallback): void;
}
