<?php

namespace TelegramOSINT\Client\BasicClient;

use TelegramOSINT\Client\Client;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;
use TelegramOSINT\TGConnection\SocketMessenger\SocketMessenger;

interface BasicClient extends Client
{
    /**
     * @return SocketMessenger
     */
    public function getConnection();

    /**
     * @param string $message
     *
     * @return void
     */
    public function throwIfNotLoggedIn(string $message = '');

    /**
     * @param MessageListener $messageCallback
     *
     * @return void
     */
    public function setMessageListener(MessageListener $messageCallback);
}
