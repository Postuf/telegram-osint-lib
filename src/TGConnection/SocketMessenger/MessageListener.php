<?php

namespace TelegramOSINT\TGConnection\SocketMessenger;

use TelegramOSINT\MTSerialization\AnonymousMessage;

interface MessageListener
{
    /**
     * @param AnonymousMessage $message
     */
    public function onMessage(AnonymousMessage $message): void;
}
