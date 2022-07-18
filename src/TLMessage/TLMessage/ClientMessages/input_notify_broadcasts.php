<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/constructor/inputNotifyBroadcasts */
class input_notify_broadcasts implements TLClientMessage
{
    public const CONSTRUCTOR = 2983951486;

    public function getName(): string
    {
        return 'input_notify_broadcasts';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
