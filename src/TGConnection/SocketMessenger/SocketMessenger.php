<?php

namespace TGConnection\SocketMessenger;

use MTSerialization\AnonymousMessage;
use TGConnection\DataCentre;
use TLMessage\TLMessage\TLClientMessage;

/**
 * Telegram-specific socket interface
 */
interface SocketMessenger
{
    /**
     * @return AnonymousMessage
     */
    public function readMessage();

    public function terminate();

    /**
     * @param TLClientMessage $payload
     */
    public function writeMessage(TLClientMessage $payload);

    /**
     * @param TLClientMessage $message
     * @param callable        $onAsyncResponse function(AnonymousMessage $message)
     */
    public function getResponseAsync(TLClientMessage $message, callable $onAsyncResponse);

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
