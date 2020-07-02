<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/** @see https://core.telegram.org/constructor/inputPeerChannel */
class input_peer_channel extends input_peer
{
    public const CONSTRUCTOR = 548253432; // 0x20adaef8

    /**
     * @var int
     */
    private $chatId;
    /** @var int */
    private $accessHash;

    /**
     * @param int $chatId
     * @param int $accessHash
     */
    public function __construct(int $chatId, int $accessHash)
    {
        $this->chatId = $chatId;
        $this->accessHash = $accessHash;
    }

    public function getName(): string
    {
        return 'input_peer_channel';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->chatId).
            Packer::packLong($this->accessHash);
    }
}
