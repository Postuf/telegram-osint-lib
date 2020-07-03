<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getFavedStickers
 */
class get_faved_stickers implements TLClientMessage
{
    public const CONSTRUCTOR = 567151374; // 0x21CE0B0E

    public function getName(): string
    {
        return 'get_faved_stickers';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0);
    }
}
