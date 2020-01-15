<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/** @see https://core.telegram.org/constructor/inputPeerChat */
class input_peer_chat extends input_peer
{
    const CONSTRUCTOR = 396093539; // 0x179be863

    /**
     * @var int
     */
    private $chatId;

    /**
     * @param int $chatId
     */
    public function __construct(int $chatId)
    {
        $this->chatId = $chatId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'input_peer_chat';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->chatId);
    }
}
