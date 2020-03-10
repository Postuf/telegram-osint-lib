<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getFeaturedStickers
 */
class get_featured_stickers implements TLClientMessage
{
    const CONSTRUCTOR = 766298703; // 0x2DACCA4F

    public function getName(): string
    {
        return 'get_featured_stickers';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0);
    }
}
