<?php

namespace TelegramOSINT\TGConnection\SocketMessenger\EncryptedSocketCallbacks;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;

class CallbackMessageListener implements MessageListener
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $onMessage)
    {
        $this->callback = $onMessage;
    }

    public function onMessage(AnonymousMessage $message): void
    {
        ($this->callback)($message);
    }
}
