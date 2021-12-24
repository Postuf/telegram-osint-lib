<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/updates.getState */
class get_state implements TLClientMessage
{
    public const CONSTRUCTOR = 3990128682;

    public function getName(): string
    {
        return 'get_state';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
