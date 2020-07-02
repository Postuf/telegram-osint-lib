<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.getBlocked
 */
class get_blocked_contacts implements TLClientMessage
{
    private const CONSTRUCTOR = -176409329; // 0xF57C350F

    public function getName(): string
    {
        return 'get_blocked_contacts';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0).
            Packer::packInt(200);
    }
}
