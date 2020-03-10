<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * Class get_all_chats
 *
 * @see https://core.telegram.org/method/messages.getAllChats
 */
class get_all_chats implements TLClientMessage
{
    const CONSTRUCTOR = -341307408; // 0xeba80ff0

    /** @var int[] */
    private $exceptIds;

    /**
     * @param int[] $exceptIds
     */
    public function __construct(array $exceptIds = [])
    {
        $this->exceptIds = $exceptIds;
    }

    public function getName(): string
    {
        return 'get_all_chats';
    }

    private function getElementGenerator(): callable
    {
        return function ($exceptId) {
            return Packer::packInt((int) $exceptId);
        };
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packVector($this->exceptIds, $this->getElementGenerator());
    }
}
