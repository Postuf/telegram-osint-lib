<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/constructor/inputNotifyUsers */
class input_notify_users implements TLClientMessage
{
    const CONSTRUCTOR = 423314455; // 0x193B4417

    public function getName(): string
    {
        return 'input_notify_users';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
