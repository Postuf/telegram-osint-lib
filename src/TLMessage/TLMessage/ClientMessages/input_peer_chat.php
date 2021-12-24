<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/** @see https://core.telegram.org/constructor/inputPeerChat */
class input_peer_chat extends input_peer
{
    public const CONSTRUCTOR = 900291769;

    private int $chatId;

    /**
     * @param int $chatId
     */
    public function __construct(int $chatId)
    {
        $this->chatId = $chatId;
    }

    public function getName(): string
    {
        return 'input_peer_chat';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packLong($this->chatId);
    }
}
