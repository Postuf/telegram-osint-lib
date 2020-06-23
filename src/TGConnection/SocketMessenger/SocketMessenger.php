<?php

namespace TelegramOSINT\TGConnection\SocketMessenger;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * Telegram-specific socket interface
 */
interface SocketMessenger
{
    /**
     * @return AnonymousMessage
     */
    public function readMessage(): ?AnonymousMessage;

    public function terminate();

    /**
     * @param TLClientMessage $payload
     */
    public function writeMessage(TLClientMessage $payload);

    /**
     * @param TLClientMessage $message
     * @param callable        $onAsyncResponse function(AnonymousMessage $message)
     */
    public function getResponseAsync(TLClientMessage $message, callable $onAsyncResponse): void;

    /**
     * @param TLClientMessage[] $messages
     * @param callable          $onLastResponse function(AnonymousMessage $message)
     */
    public function getResponseConsecutive(array $messages, callable $onLastResponse);

    /**
     * @return DataCentre
     */
    public function getDCInfo();
}
