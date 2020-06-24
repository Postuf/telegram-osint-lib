<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\BasicClient;

use TelegramOSINT\Client\Client;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;
use TelegramOSINT\TGConnection\SocketMessenger\SocketMessenger;

interface BasicClient extends Client
{
    public function getConnection(): ?SocketMessenger;

    /**
     * @param string $message
     *
     * @throws TGException
     */
    public function throwIfNotLoggedIn(string $message = ''): void;

    public function setMessageListener(MessageListener $messageCallback): void;
}
