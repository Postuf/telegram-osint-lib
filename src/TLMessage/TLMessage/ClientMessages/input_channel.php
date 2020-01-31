<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/** @see https://core.telegram.org/constructor/inputChannel */
class input_channel extends input_peer
{
    const CONSTRUCTOR = -1343524562; // 0xafeb712e

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

    /**
     * @return string
     */
    public function getName()
    {
        return 'input_channel';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->chatId).
            Packer::packLong($this->accessHash);
    }
}
