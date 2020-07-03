<?php

namespace TelegramOSINT\TGConnection\SocketMessenger;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\DcConfigApp;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\DcOption;
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

    public function terminate(): void;

    /**
     * @param TLClientMessage $payload
     */
    public function writeMessage(TLClientMessage $payload): void;

    /**
     * @param TLClientMessage $message
     * @param callable        $onAsyncResponse function(AnonymousMessage $message)
     */
    public function getResponseAsync(TLClientMessage $message, callable $onAsyncResponse): void;

    /**
     * @param TLClientMessage[] $messages
     * @param callable          $onLastResponse function(AnonymousMessage $message)
     */
    public function getResponseConsecutive(array $messages, callable $onLastResponse): void;

    public function getDCInfo(): DataCentre;

    public function getDCConfig(): ?DcConfigApp;

    public function isDcAppropriate(DcOption $dc): bool;
}
