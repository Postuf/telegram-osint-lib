<?php


namespace TLMessage\TLMessage\ClientMessages\Api;


use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getFullChat
 */
class get_full_chat implements TLClientMessage
{
    const CONSTRUCTOR = 998448230; // 0x3b831c66

    /** @var int */
    private $chatId;

    public function __construct(int $chatId)
    {
        $this->chatId = $chatId;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'get_full_chat';
    }

    /**
     * @inheritDoc
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->chatId);
    }
}