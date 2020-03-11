<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/help.getInviteText */
class get_invite_text implements TLClientMessage
{
    const CONSTRUCTOR = 1295590211; // 0x4D392343

    public function getName(): string
    {
        return 'get_invite_text';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
