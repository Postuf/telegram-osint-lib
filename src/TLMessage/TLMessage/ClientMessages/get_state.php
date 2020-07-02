<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/updates.getState */
class get_state implements TLClientMessage
{
    public const CONSTRUCTOR = -304838614; // 0xEDD4882A

    public function getName(): string
    {
        return 'get_state';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
