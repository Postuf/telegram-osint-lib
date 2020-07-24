<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getFullChat
 */
class get_full_chat implements TLClientMessage
{
    public const CONSTRUCTOR = 998448230; // 0x3b831c66

    /** @var int */
    private int $chatId;

    public function __construct(int $chatId)
    {
        $this->chatId = $chatId;
    }

    public function getName(): string
    {
        return 'get_full_chat';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->chatId);
    }
}
