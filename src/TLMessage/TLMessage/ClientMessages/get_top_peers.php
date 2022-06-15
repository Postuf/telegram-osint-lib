<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.getTopPeers
 */
class get_top_peers implements TLClientMessage
{
    public const CONSTRUCTOR = 2536798390;

    public function getName(): string
    {
        return 'get_top_peers';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0b101).
            Packer::packInt(0).
            Packer::packInt(20).
            Packer::packLong(0); //hash
    }
}
