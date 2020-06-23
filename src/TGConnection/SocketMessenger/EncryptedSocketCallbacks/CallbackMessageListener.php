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
    /** @var string */
    private $from;

    public function __construct(callable $onMessage, string $from = '')
    {
        $this->callback = $onMessage;
        /** @noinspection UnusedConstructorDependenciesInspection */
        $this->from = $from;
    }

    public function onMessage(AnonymousMessage $message): void
    {
        ($this->callback)($message);
    }
}
