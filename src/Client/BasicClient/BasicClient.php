<?php

namespace Client\BasicClient;

use Client\Client;
use TGConnection\SocketMessenger\MessageListener;
use TGConnection\SocketMessenger\SocketMessenger;

interface BasicClient extends Client
{
    /**
     * @return SocketMessenger
     */
    public function getConnection();

    /**
     * @return void
     */
    public function throwIfNotLoggedIn();

    /**
     * @param MessageListener $messageCallback
     *
     * @return void
     */
    public function setMessageListener(MessageListener $messageCallback);
}
